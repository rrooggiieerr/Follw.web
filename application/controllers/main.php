<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Static content
switch($path) {
	case '/':
		require_once(dirname(__DIR__) . '/views/intro.php');
		exit();
		break;
	case '/privacy':
	case '/terms':
	case '/htmlsnippet':
	case '/wordpress':
	case '/osmand':
		require_once(dirname(__DIR__) . '/views/static.php');
		exit();
		break;
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

if($method === 'GET' && $path === '/generatesharingid') {
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
	
	header('Location: /' . strtoupper(bin2hex($id)));
	exit();
}

$matches = NULL;
if(preg_match('/^\/([0-9a-fA-F]{16})([\/.].*)?$/', $path, $matches) == TRUE) {
	$id = $matches[1];
	$id = hex2bin(substr($path, 1, 16));

	$remainer = '';
	if(count($matches) === 3) {
		$remainer = $matches[2];
	}

	try {
		// See if it is a sharing or follow ID
		$query = 'SELECT `type`, `config` FROM `issuedids` WHERE `id` = ?';
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
		$config = $result['config'];
		if($config)
			$config = json_decode($config, TRUE);
		else
			$config = [];
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}
	
	$action = NULL;
	$format = NULL;
	if(in_array($remainer, [ NULL, '', '/'], TRUE)) {
		$action = 'location';
		$format = 'html';
	} else if(preg_match('/^\.[a-z]*$/', $remainer)) {
		$action = 'location';
		$format = substr($remainer, 1);
	}

	if(isset($format) && !in_array($format, ['html', 'json'])) {
		http_response_code(404);
		exit();
	}

	if($type === 'deleted') {
		if($action === 'location') {
			if($format === 'html')
				require_once(dirname(__DIR__) . '/views/iddeleted.php');
			http_response_code(410);
		} else {
			http_response_code(404);
		}
		exit();
	}

	if($remainer === '/manifest.webmanifest') {
		$id = strtoupper(bin2hex($id));
		header('Content-Type: application/json');
		require_once(dirname(__DIR__) . '/views/manifest.webmanifest.php');
		exit();
	}
	
	if($type === 'follow') {
		require_once(dirname(__DIR__) . '/controllers/follow.php');
	} else if($type === 'share') {
		require_once(dirname(__DIR__) . '/controllers/share.php');
	}
}

//ToDo? Limit requests per IP to prevent DOS
http_response_code(404);
exit();