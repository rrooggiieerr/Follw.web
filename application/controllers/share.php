<?php
// Fixes false "Variable is not defined" validation errors for variables created in other files
/* @var String $method */
/* @var String $action */
/* @var String $remainer */
/* @var String $format */
/* @var Object $pdo */
/* @var String $protocol */
/* @var Integer $id */
/* @var Integer $idlength */

if($action === 'location') {
	if($_SERVER['REQUEST_METHOD'] === 'POST' || count($_GET) !== 0)
		shareLocation($id);
	else
		getLocation($id, $format);
} else {
	$matches = NULL;
	if(!isset($action)) {
		switch($remainer) {
			case NULL:
			case '':
				break;
			case '/deletelocation':
				deleteLocation($id);
				break;
			case '/config':
				configSharer($id);
				break;
			case '/delete':
				deleteSharer($id);
				break;
			case '/generatefollowid':
				generateFollowID($id);
				break;
			case '/followers.json':
				getFollowers($id);
				break;
			case (preg_match('/^\/follower\/([0-9a-fA-Z]{' . (2 * $idlength) . '})\/enable$/', $remainer, $matches) ? TRUE : FALSE):
				$followid = hex2bin($matches[1]);
				enableFollower($id, $followid);
				break;
			case (preg_match('/^\/follower\/([0-9a-fA-Z]{' . (2 * $idlength) . '})\/disable$/', $remainer, $matches) ? TRUE : FALSE):
				$followid = hex2bin($matches[1]);
				disableFollower($id, $followid);
				break;
			case (preg_match('/^\/follower\/([0-9a-fA-Z]{' . (2 * $idlength) . '})\/delete$/', $remainer, $matches) ? TRUE : FALSE):
				$followid = hex2bin($matches[1]);
				deleteFollower($id, $followid);
				break;
			default:
				http_response_code(404);
				exit();
				break;
		}
	}
}

if(!isset($action)) {
	http_response_code(404);
	exit();
}

function shareLocation($shareid) {
	global $pdo;
	
	$location = [];
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
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
		$statement->execute([$shareid, $json, $json]);
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}
	
	http_response_code(204);
	exit();
}

function getLocation($shareid, $format) {
	global $pdo;
	global $protocol;
	global $config;

	if($format == 'html') {
		$shareid = strtoupper(bin2hex($shareid));
		if(!array_key_exists('alias', $config))
			$config['alias'] = "";
		require_once(dirname(__DIR__) . '/views/share.php');
		exit();
	}

	try {
		// Get location from database
		$query = 'SELECT UNIX_TIMESTAMP(`timestamp`) as `timestamp`, `location` FROM `locations` WHERE `id` = ?';
		$statement = $pdo->prepare($query);
		$result = $statement->execute([$shareid]);
		
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

function configSharer($shareid) {
	global $pdo;
	global $config;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$formValues = $_POST;
	} else {
		$formValues = $_GET;
	}
	
	foreach($formValues as $name => $value) {
		switch($name) {
			case 'alias':
				$config['alias'] = $value;
				break;
		}
	}
	
	try {
		$json = json_encode($config);
		// Insert location in database
		$query = 'UPDATE `issuedids` SET `config` = ? WHERE `id` = ?';
		$statement = $pdo->prepare($query);
		$statement->execute([$json, $shareid]);
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}
	
	http_response_code(204);
	exit();
}

function deleteLocation($shareid) {
	global $pdo;
	
	try {
		// Delete location from database
		$query = 'DELETE FROM `locations` WHERE `id` = ?';
		$statement = $pdo->prepare($query);
		$statement->execute([$shareid]);
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
	}
	
	http_response_code(204);
	exit();
}

function deleteSharer($shareid) {
	global $pdo;
	
	try {
		$pdo->beginTransaction();
		
		// Delete location
		$query = 'DELETE FROM `locations` WHERE `id` = ?';
		$statement = $pdo->prepare($query);
		$statement->execute([$shareid]);
		
		// Delete followers
		$query = 'UPDATE `issuedids` set `type` = \'deleted\', `config` = NULL WHERE `id` IN (SELECT `followid` FROM `followers` WHERE `id` = ?)';
		$statement = $pdo->prepare($query);
		$statement->execute([$shareid]);

		$query = 'DELETE FROM `followers` WHERE `id` = ?';
		$statement = $pdo->prepare($query);
		$statement->execute([$shareid]);

		// Delete sharer
		$query = 'UPDATE `issuedids` set `type` = \'deleted\', `config` = NULL WHERE `id` = ?';
		$statement = $pdo->prepare($query);
		$statement->execute([$shareid]);
		
		$pdo->commit();
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		$pdo->rollBack();
		http_response_code(500);
		exit();
	}

	http_response_code(204);
	exit();
}

function generateFollowID($shareid) {
	global $pdo;
	global $idlength;
	$enabled = FALSE;
	$expires = NULL;
	$delay = NULL;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$formValues = $_POST;
	} else {
		$formValues = $_GET;
	}
	
	$followerConfig = [];
	
	if(!empty($formValues['reference']))
		$followerConfig['reference'] = $formValues['reference'];
	if(!empty($formValues['alias']))
		$followerConfig['alias'] = $formValues['alias'];
	if($formValues['enabled'] == 'true')
		$enabled = TRUE;

	if(!empty($formValues['expires']) && is_numeric($formValues['expires'])) {
		$expires = intval($formValues['expires']);
	} else if(!empty($formValues['expires'])) {
		$expires = $formValues['expires'];
		// ISO 8601 date time to unix time
		$expires = strtotime($expires);
		// Unix time to MySQL timestamps
		$expires = date('Y-m-d H:i:s', $expires);
	}
	if(!empty($formValues['delay']) && is_numeric($formValues['delay']))
		$delay = intval($formValues['delay']);
	$json = json_encode($followerConfig);

	$failureconter = 0;
	do {
		// Generate unique ID
		$followid = random_bytes($idlength);
		
		$failure = FALSE;
		try {
			// Insert ID in database
			$query = 'INSERT INTO `issuedids` (`id`, `type`, `config`) VALUES (?, \'follow\', ?)';
			$statement = $pdo->prepare($query);
			$statement->execute([$followid, $json]);
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
		$query = 'INSERT INTO `followers` (`id`, `followid`, `enabled`, `expires`, `delay`) VALUES (?, ?, ?, FROM_UNIXTIME(?), ?)';
		$statement = $pdo->prepare($query);
		$statement->execute([$shareid, $followid, $enabled, $expires, $delay]);
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}
	
	echo(strtoupper(bin2hex($followid)));
	exit();
}

function getFollowers($shareid) {
	global $pdo;
	global $protocol;
	$followers = array();;
	
	try {
		$query = 'SELECT UNIX_TIMESTAMP(i.`created`) AS `created`, f.`followid`, i.`config`, f.`enabled`, UNIX_TIMESTAMP(f.`expires`) AS `expires`, f.`delay` FROM `followers` f, `issuedids` i WHERE i.`id` = f.`followid` AND f.`id` = ? ORDER BY i.`created`';
		$statement = $pdo->prepare($query);
		
		if($statement->execute([$shareid])) {
			//echo($statement->rowCount());
			while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
				$followerConfig = $row['config'];
				if($followerConfig)
					$followerConfig = json_decode($row['config'], TRUE);
				else
					$followerConfig = [];
				$entry = [];
				$entry['created'] = intval($row['created']); // Convert to number
				$entry['id'] = strtoupper(bin2hex($row['followid']));
				$entry['url'] = $protocol . $_SERVER['HTTP_HOST'] . "/" . $entry['id'];
				if(array_key_exists('reference', $followerConfig) && $followerConfig['reference'])
					$entry['reference'] = $followerConfig['reference'];
				if(array_key_exists('alias', $followerConfig) && $followerConfig['alias'])
					$entry['alias'] = $followerConfig['alias'];
				$entry['enabled'] = $row['enabled'] ? TRUE : FALSE;
				if($row['expires']) {
					$entry['expires'] = intval($row['expires']); // Convert to number
					$entry['expired'] = $entry['expires'] < time();
				}
				if($row['delay'])
					$entry['delay'] = intval($row['delay']);
				$entry['time'] = time();
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

function enableFollower($shareid, $followid) {
	global $pdo;

	try {
		$query = 'UPDATE `followers` SET `enabled` = 1 WHERE `id` = ? AND `followid` = ?;';
		$statement = $pdo->prepare($query);
		
		$statement->execute([$shareid, $followid]);
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

function disableFollower($shareid, $followid) {
	global $pdo;

	try {
		$query = 'UPDATE `followers` SET `enabled` = 0 WHERE `id` = ? AND `followid` = ?;';
		$statement = $pdo->prepare($query);
		
		$statement->execute([$shareid, $followid]);
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

function deleteFollower($shareid, $followid) {
	global $pdo;

	try {
		$pdo->beginTransaction();
		
		$query = 'DELETE FROM `followers` WHERE `id` = ? AND `followid` = ?;';
		$statement = $pdo->prepare($query);
		
		$statement->execute([$shareid, $followid]);
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
		
		$query = "UPDATE `issuedids` set `type` = 'deleted', `config` = NULL WHERE `id` = ?";
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