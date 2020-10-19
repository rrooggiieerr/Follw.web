<?php
// Fixes false "Variable is not defined" validation errors for variables created in other files
/* @var String $method */
/* @var String $action */
/* @var String $format */
/* @var Object $pdo */
/* @var String $protocol */
/* @var Integer $id */

if(!isset($action)) {
	switch($remainer) {
		case NULL:
		case '':
			break;
		case '/generatefollowid':
			$action = 'generatefollowid';
			break;
		case '/followers.json':
			$action = 'getfollowers';
			break;
		case (preg_match('/^\/follower\/[0-9a-f]{16}\/enable$/', $remainer) ? TRUE : FALSE):
			$followid = hex2bin(substr($remainer, 10, 16));
			$action = 'enablefollower';
			break;
		case (preg_match('/^\/follower\/[0-9a-f]{16}\/disable$/', $remainer) ? TRUE : FALSE):
			$followid = hex2bin(substr($remainer, 10, 16));
			$action = 'disablefollower';
			break;
		case (preg_match('/^\/follower\/[0-9a-f]{16}\/delete$/', $remainer) ? TRUE : FALSE):
			$followid = hex2bin(substr($remainer, 10, 16));
			$action = 'deletefollower';
			break;
	}
}

if(!isset($action)) {
	http_response_code(404);
	exit();
}

if($action == 'location' && ($method == 'POST' || count($_GET) !== 0)) {
	// Add location to database
	
	$location = [];
	
	if($method == 'POST') {
		$formValues = $_POST;
	} else {
		$formValues = $_GET;
	}
	
	foreach($formValues as $name => $value) {
		$value = floatval($value);
		
		switch($name) {
			case (substr($name, 0, 2) == 'la' ? TRUE : FALSE): // latitude
				if($value === '' || $value < -90 || $value > 90) {
					http_response_code(500);
					exit();
				}
				$location["latitude"] = $value;
				break;
			case (substr($name, 0, 2) == 'lo' ? TRUE : FALSE): // longitude
				if($value === '' || $value < -180 || $value > 180) {
					http_response_code(500);
					exit();
				}
				$location["longitude"] = $value;
				break;
			case (substr($name, 0, 2) == 'hd' ? TRUE : FALSE): // horizontal dilution of position
			case (substr($name, 0, 2) == 'ac' ? TRUE : FALSE): // accuracy
				if($value !== '')
					$location["accuracy"] = $value;
				break;
			case (substr($name, 0, 2) == 'al' ? TRUE : FALSE): // altitude
				if($value !== '')
					$location["altitude"] = $value;
				break;
			case (substr($name, 0, 2) == 'be' ? TRUE : FALSE): // bearing
			case (substr($name, 0, 2) == 'he' ? TRUE : FALSE): // heading
			case (substr($name, 0, 2) == 'di' ? TRUE : FALSE): // direction
			case (substr($name, 0, 2) == 'az' ? TRUE : FALSE): // azimuth
			case (substr($name, 0, 2) == 'co' ? TRUE : FALSE): // course
				if($value !== '') {
					if($value < 0 || $value > 360) {
						http_response_code(500);
						exit();
					}
					$location["direction"] = $value;
				}
				break;
			case (substr($name, 0, 2) == 'sp' ? TRUE : FALSE): // speed
				if($value !== '') {
					if($value < 0) {
						http_response_code(500);
						exit();
					}
					$location["speed"] = $value;
				}
				break;
		}
	}
	
	try {
		$json = json_encode($location);
		// Insert location in database
		$query = 'INSERT INTO `locations` (`id`, `location`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `location` = ?';
		$statement = $pdo->prepare($query);
		$statement->execute([$id, $json, $json]);
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}
	
	http_response_code(204);
	exit();
}

if($action == 'location') {
	if($format == 'html') {
		require_once(dirname(__DIR__) . '/views/share.php');
		exit();
	}

	try {
		// Get location from database
		$query = 'SELECT UNIX_TIMESTAMP(`timestamp`) as `timestamp`, `location` FROM `locations` WHERE `id` = ?';
		$statement = $pdo->prepare($query);
		$result = $statement->execute([$id]);
		
		if($statement->rowCount() != 1) {
			http_response_code(204);
			exit();
		}
		
		$result = $statement->fetch();
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}

	$location = json_decode($result['location'], TRUE);
	$location['timestamp'] = $result['timestamp'] + 0;
	
	switch ($format) {
		case 'json':
			header('Content-Type: application/json');
			echo(json_encode($location));
			break;
		// ToDo Implement other formats
		default:
			http_response_code(500);
	}
	
	exit();
}

if($action == 'generatefollowid') {
	$reference = NULL;
	$alias = NULL;
	$enabled = FALSE;
	$expires = NULL;
	
	if($method == 'POST') {
		$formValues = $_POST;
	} else {
		$formValues = $_GET;
	}
	
	if($formValues['reference'] != '')
		$reference = $formValues['reference'];
	if($formValues['alias'] != '')
		$alias = $formValues['alias'];
	if($formValues['enabled'] == 'true')
		$enabled = TRUE;
	if($formValues['expires'] != '')
		$expires = $formValues['expires'];
	
	$failureconter = 0;
	do {
		// Generate unique ID
		$followid = random_bytes(8);
		
		$failure = FALSE;
		try {
			// Insert ID in database
			$query = 'INSERT INTO `issuedids` (`id`, `type`, `alias`) VALUES (?, \'follow\', ?)';
			$statement = $pdo->prepare($query);
			$statement->execute([$followid, $alias]);
		} catch(PDOException $e) {
			$failure = TRUE;
			$failureconter++;
			//ToDo Log error
			//$e->getCode()
		}
	} while ($failure && $failureconter < 10);
	
	// Check if insert was succesfull
	if ($failure) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}
	
	try {
		// Insert ID in database
		$query = 'INSERT INTO `followers` (`id`, `followid`, `reference`, `enabled`, `expires`) VALUES (?, ?, ?, ?, ?)';
		$statement = $pdo->prepare($query);
		$statement->execute([$id, $followid, $reference, $enabled, $expires]);
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}
	
	echo(bin2hex($followid));
	exit();
}

if($method == 'GET' && $action == 'getfollowers') {
	$followers = array();;
	
	try {
		// Insert ID in database
		$query = 'SELECT UNIX_TIMESTAMP(i.`created`) AS `created`, f.`followid`, f.`reference`, i.`alias`, f.`enabled`, f.`expires` FROM `followers` f, `issuedids` i WHERE i.`id` = f.`followid` AND f.`id` = ? ORDER BY i.`created`';
		$statement = $pdo->prepare($query);
		
		if($statement->execute([$id])) {
			//echo($statement->rowCount());
			while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
				$entry = [];
				$entry['created'] = $row['created'] + 0; // Convert to number
				$entry['id'] = bin2hex($row['followid']);
				$entry['url'] = $protocol . $_SERVER['HTTP_HOST'] . "/" . $entry['id'];
				$entry['reference'] = $row['reference'];
				$entry['alias'] = $row['alias'];
				$entry['enabled'] = $row['enabled'] ? TRUE : FALSE;
				$entry['expires'] = $row['expires'];
				$entry['expired'] = FALSE;
				$followers[] = $entry;
			}
		}
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}
	
	header('Content-Type: application/json');
	echo(json_encode($followers));
	exit();
}

if($action == 'enablefollower' && $method == 'GET') {
	try {
		$query = 'UPDATE `followers` SET `enabled` = 1 WHERE `id` = ? AND `followid` = ?;';
		$statement = $pdo->prepare($query);
		
		$statement->execute([$id, $followid]);
		if($statement->rowCount() == 0) {
			http_response_code(404);
			exit();
		} else if($statement->rowCount() > 1) {
			//ToDo Log error
			http_response_code(500);
			exit();
		}
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}

	http_response_code(204);
	exit();
}

if($action == 'disablefollower' && $method == 'GET') {
	try {
		$query = 'UPDATE `followers` SET `enabled` = 0 WHERE `id` = ? AND `followid` = ?;';
		$statement = $pdo->prepare($query);
		
		$statement->execute([$id, $followid]);
		if($statement->rowCount() == 0) {
			http_response_code(404);
			exit();
		} else if($statement->rowCount() > 1) {
			//ToDo Log error
			http_response_code(500);
			exit();
		}
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}
	
	http_response_code(204);
	exit();
}

if($action == 'deletefollower' && $method == 'GET') {
	try {
		$pdo->beginTransaction();
		
		$query = 'DELETE FROM `followers` WHERE `id` = ? AND `followid` = ?;';
		$statement = $pdo->prepare($query);
		
		$statement->execute([$id, $followid]);
		if($statement->rowCount() == 0) {
			//ToDo Log error
			$pdo->rollback();
			http_response_code(404);
			exit();
		} else if($statement->rowCount() > 1) {
			//ToDo Log error
			$pdo->rollback();
			http_response_code(500);
			exit();
		}
		
		$query = "UPDATE `issuedids` set `type` = 'deleted', `alias` = NULL WHERE `id` = ?";
		$statement = $pdo->prepare($query);
		
		$statement->execute([$followid]);
		if($statement->rowCount() == 0) {
			//ToDo Log error
			$pdo->rollback();
			http_response_code(404);
			exit();
		} else if($statement->rowCount() > 1) {
			//ToDo Log error
			$pdo->rollback();
			http_response_code(500);
			exit();
		}
		
		$pdo->commit();
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}
	
	http_response_code(204);
	exit();
}