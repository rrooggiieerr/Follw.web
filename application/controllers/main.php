<?php
/* @var String $servername */
/* @var String $dbname */
/* @var String $username */
/* @var String $password */
/* @var Integer $idlength */

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

header('X-Robots-Tag: noindex');

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
		$shareidraw = random_bytes($idlength);
		$shareidrawstr = strtoupper(bin2hex($shareidraw));
		
		$failure = FALSE;
		try {
			// Insert ID in database
			$query = 'INSERT INTO `issuedids` (`md5`, `type`, `config`) VALUES (?, \'share\', "{}")';
			$statement = $pdo->prepare($query);
			$statement->execute([md5($shareidraw, TRUE)]);
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

	http_response_code(303);
	header('Location: /' . $shareidrawstr);
	exit();
}

$matches = NULL;
if(preg_match('/^\/([0-9A-F]{' . (2 * $idlength) . '})([\/.].*)?$/', $path, $matches) == TRUE) {
	$idrawstr = $matches[1];
	$idraw = hex2bin($matches[1]);
	$idrawmd5 = md5($idraw, TRUE);

	$remainer = '';
	if(count($matches) === 3) {
		$remainer = $matches[2];
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
	
	/*try {
		$query = 'SELECT `md5` FROM `deletedids` WHERE `md5` = ?';
		$statement = $pdo->prepare($query);
		$result = $statement->execute([$idrawmd5]);

		if($statement->rowCount() > 1) {
			//TODO Log error
			//$e->getCode()
			http_response_code(500);
			exit();
		}
		if($statement->rowCount() > 0) {
			if($action === 'location') {
				http_response_code(410);
				if($format === 'html')
					require_once(dirname(__DIR__) . '/views/iddeleted.php');
			} else {
				http_response_code(404);
			}
			exit();
		}
	} catch(PDOException $e) {
		//ToDo Log error
		//$e->getCode()
		http_response_code(500);
		exit();
	}*/

	try {
		// See if it is a sharing or follow ID
		$query = 'SELECT `id`, `type`, `config` FROM `issuedids` WHERE `md5` = ?';
		$statement = $pdo->prepare($query);
		$result = $statement->execute([$idrawmd5]);
		
		if($statement->rowCount() > 1) {
			//TODO Log error
			//$e->getCode()
			http_response_code(500);
			exit();
		}
		if($statement->rowCount() < 1) {
			// ID does not exist
			//TODO Rate limit requests per IP to prevent guessing
			//http_response_code(429);
			http_response_code(404);
			exit();
		}
		
		$result = $statement->fetch();
		$id = $result['id'];
		$type = $result['type'];
		$config = $result['config'];
		if($config)
			$config = json_decode($config, TRUE);
		else
			$config = [];
	} catch(PDOException $e) {
		//TODO Log error
		//$e->getCode()
		http_response_code(500);
		exit();
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
		header('Content-Type: application/json');
		require_once(dirname(__DIR__) . '/views/manifest.webmanifest.php');
		exit();
	}
	
	if($type === 'follow') {
		$followid = $id;
		$followidrawstr= $idrawstr;
		require_once(dirname(__DIR__) . '/controllers/follow.php');
	} else if($type === 'share') {
		$shareid = $id;
		$shareidrawstr= $idrawstr;
		require_once(dirname(__DIR__) . '/controllers/share.php');
	}
}

//TODO Limit requests per IP to prevent DOS
http_response_code(404);
exit();