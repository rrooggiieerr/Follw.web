<?php
// These database settings should be good enough for a local test environment
// Override settings in config.php for public testing and production environments
$servername = '127.0.0.1';
$dbname = 'follw';
$username = 'root';
$password = NULL;

@include_once('config.php');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$path = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Catch all
switch($path) {
	case '/':
		// If /
		// Show introduction
		require_once('views/intro.php');
		exit();
	case '/generatesharingid':
		$id = NULL;
		$action = 'generatesharingid';
		break;
	case (preg_match('/^\/[0-9a-f]{16}\/?$/', $path) ? TRUE : FALSE):
		// If ID
		$id = hex2bin(substr($path, 1, 16));
		$action = 'location';
		$format = 'html';
		break;
	case (preg_match('/^\/[0-9a-f]{16}\/?\?.*$/', $path) ? TRUE : FALSE):
		// If ID
		$id = hex2bin(substr($path, 1, 16));
		$action = 'location';
		$format = 'html';
		break;
	case (preg_match('/^\/[0-9a-f]{16}\.[a-z]*$/', $path) ? TRUE : FALSE):
		$id = hex2bin(substr($path, 1, 16));
		$action = 'location';
		$format = substr($path, 18);
		
		//ToDo Support GML, KML e.a.
		if(!in_array($format, ['json'])) {
			http_response_code(404);
			exit();
		}
		break;
	case (preg_match('/^\/[0-9a-f]{16}\/manifest.webmanifest$/', $path) ? TRUE : FALSE):
		$id = hex2bin(substr($path, 1, 16));
		$action = 'webmanifest';
		break;
	case (preg_match('/^\/[0-9a-f]{16}\/generatefollowid$/', $path) ? TRUE : FALSE):
		$id = hex2bin(substr($path, 1, 16));
		$action = 'generatefollowid';
		break;
	case (preg_match('/^\/[0-9a-f]{16}\/followers.json$/', $path) ? TRUE : FALSE):
		$id = hex2bin(substr($path, 1, 16));
		$action = 'getfollowers';
		break;
	case (preg_match('/^\/[0-9a-f]{16}\/follower\/[0-9a-f]{16}\/enable$/', $path) ? TRUE : FALSE):
		$id = hex2bin(substr($path, 1, 16));
		$followid = hex2bin(substr($path, 27, 16));
		$action = 'enablefollower';
		break;
	case (preg_match('/^\/[0-9a-f]{16}\/follower\/[0-9a-f]{16}\/disable$/', $path) ? TRUE : FALSE):
		$id = hex2bin(substr($path, 1, 16));
		$followid = hex2bin(substr($path, 27, 16));
		$action = 'disablefollower';
		break;
	case (preg_match('/^\/[0-9a-f]{16}\/follower\/[0-9a-f]{16}\/delete$/', $path) ? TRUE : FALSE):
		$id = hex2bin(substr($path, 1, 16));
		$followid = hex2bin(substr($path, 27, 16));
		$action = 'deletefollower';
		break;
	default:
		http_response_code(404);
		exit();
}

try {
	$pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
	// set the PDO error mode to exception
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
	// Log error
	echo("Connection failed: " . $e->getMessage());
	http_response_code(500);
	exit();
}

if($method == 'GET' && $action == 'generatesharingid') {
	$failureconter = 0;
	do {
		// Generate unique ID
		$id = random_bytes(8);
		
		$failure = FALSE;
		try {
			// Insert ID in database
			$query = 'INSERT INTO `issuedids` (`id`, `type`) VALUES (?, \'share\')';
			$statement = $pdo->prepare($query);
			$statement->execute([$id]);
		} catch(PDOException $e) {
			$failure = TRUE;
			$failureconter++;
			//ToDo Log error
			//$e->getCode()
		}
	} while ($failure && $failureconter < 10);
	
	// Check if insert was succesfull
	if ($failure) {
		http_response_code(500);
		exit();
	}
	
	header('Location: /' . bin2hex($id));
	exit();
}

try {
	// See if it is a sharing or follow ID
	$query = 'SELECT `type` FROM `issuedids` WHERE `id` = ?';
	$statement = $pdo->prepare($query);
	$result = $statement->execute([$id]);
	
	if($statement->rowCount() != 1) {
		// ID does not exist
		//ToDo Rate limit requests per IP to prevent guessing
		//http_response_code(429);
		http_response_code(404);
		exit();
	}
	
	$result = $statement->fetch();
	$type = $result['type'];
} catch(PDOException $e) {
	//ToDo Log error
	//$e->getCode()
	http_response_code(500);
	exit();
}

if($type == 'deleted') {
	if($format == 'html')
		require_once('views/iddeleted.php');
	http_response_code(410);
	exit();
}

if($type == 'follow' && $method == 'POST') {
	http_response_code(405);
	exit();
}

if($type == 'follow' && $action == 'location') {
	try {
		// Get location from database
		$query = 'SELECT UNIX_TIMESTAMP(l.`timestamp`) AS `timestamp`, l.`location`, IF(fid.`alias` IS NOT NULL, fid.`alias`, sid.`alias`) AS "alias"
			FROM `locations` l, `followers` f, `issuedids` fid, `issuedids` sid
			WHERE fid.`id` = f.`followid` AND sid.`id` = f.`id` AND l.`id` = sid.`id` AND f.`enabled` = 1 AND (f.`expires` IS NULL OR f.`expires` <= NOW()) AND f.`followid` = ?';
		$statement = $pdo->prepare($query);
		$statement->execute([$id]);

		if($statement->rowCount() != 1) {
			if($format == 'html') {
				$location = null;
				require_once('views/follow.php');
			} else
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
	if($result['alias'] != null)
		$location['alias'] = $result['alias'];
	else
		$location['alias'] = "Something";
	// Calculate the recomended refresh interval based on timestamp
	if(date_create()->getTimestamp() - $result['timestamp'] < 60)
		$location['interval'] = 1;
	else
		$location['interval'] = 5;
		
	switch ($format) {
		case 'html':
			require_once('views/follow.php');
			break;
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

if($type == 'follow' && $action == 'webmanifest') {
	header('Content-Type: application/json');
	require_once('views/manifest.webmanifest.php');
	exit();
}


if($type == 'follow') {
	//ToDo? Limit requests per IP to prevent DOS
	http_response_code(404);
	exit();
}

if($type == 'share' && $action == 'location' && ($method == 'POST' || count($_GET) !== 0)) {
	//ToDo Add location to database
	
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

if($type == 'share' && $action == 'location') {
	if($format == 'html') {
		require_once('views/share.php');
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

if($type == 'share' && $action == 'webmanifest') {
	header('Content-Type: application/json');
	require_once('views/manifest.webmanifest.php');
	exit();
}

if($type == 'share' && $action == 'generatefollowid') {
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

if($type == 'share' && $method == 'GET' && $action == 'getfollowers') {
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

if($type == 'share' && $action == 'enablefollower' && $method == 'GET') {
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

if($type == 'share' && $action == 'disablefollower' && $method == 'GET') {
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

if($type == 'share' && $action == 'deletefollower' && $method == 'GET') {
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