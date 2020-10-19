<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$path = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Catch all
switch($path) {
	case '/':
		// If /
		// Show introduction
		require_once(dirname(__DIR__) . '/views/intro.php');
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
		require_once(dirname(__DIR__) . '/views/iddeleted.php');
		http_response_code(410);
		exit();
}

if($action == 'webmanifest') {
	header('Content-Type: application/json');
	require_once(dirname(__DIR__) . '/views/manifest.webmanifest.php');
	exit();
}

if($type == 'follow') {
	require_once(dirname(__DIR__) . '/controllers/follow.php');
} else if($type == 'share') {
	require_once(dirname(__DIR__) . '/controllers/share.php');
}