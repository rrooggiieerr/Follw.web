<?php
// Fixes false "Variable is undefined" and "Variable is never used" validation errors
/* @var array $configuration */
/* @var string $protocol */

if($configuration['mode'] === 'development') {
	// Show all errors when in development mode
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

if($configuration['mode'] !== 'production') {
	// Don't let anything be indexed by search engines if not in production mode
	header('X-Robots-Tag: noindex');
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Handle static content which doesn't need a database connection
require_once(dirname(__DIR__) . '/controllers/StaticContent.php');
if ($method === 'GET' && (new StaticContent())->route($path)) {
	exit();
}

// Don't let any of the dynamic pages to be indexed by search engines
header('X-Robots-Tag: noindex');

if($method === 'GET' && $path === '/generatesharingid') {
	require_once(dirname(__DIR__) . '/models/ShareID.php');
	// Generate unique ID
	$shareID = new ShareID();
	$shareID->store();

	if ($shareID == NULL) {
		http_response_code(500);
		exit();
	}

	http_response_code(303);
	header('Location: /' . $shareID->encode());
	exit();
}

require_once(dirname(__DIR__) . '/libs/Base.php');
$configuration['id']['encodedLength'] = BASE::length($configuration['id']['nBytes'], $configuration['id']['baseEncoding']);
$configuration['id']['encodedChars'] = BASE::chars($configuration['id']['baseEncoding']);

$matches = NULL; // Fixes false "Variable is undefined" validation error
if(preg_match('/^\/([' . $configuration['id']['encodedChars'] . ']{' . $configuration['id']['encodedLength'] . '})([\/.].*)?$/', $path, $matches) == TRUE) {
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

	require_once(dirname(__DIR__) . '/models/ID.php');
	$id = ID::decode($matches[1]);

	if (!$id) {
		http_response_code(404);
		exit();
	}

	if(isset($format) && !in_array($format, ['html', 'json'])) {
		http_response_code(404);
		exit();
	}

	if($id->type === 'deleted') {
		if($action === 'location') {
			http_response_code(410);
			if($format === 'html') {
				require_once(dirname(__DIR__) . '/views/iddeleted.php');
			}
		} else {
			http_response_code(404);
		}
		exit();
	}

	if($id->type === 'reserved') {
		http_response_code(404);
		exit();
	}

	if($remainer === '/manifest.webmanifest') {
		require_once(dirname(__DIR__) . '/views/manifest.webmanifest.php');
		exit();
	}

	if($id instanceof FollowID) {
		require_once(dirname(__DIR__) . '/controllers/follow.php');
	} else if($id instanceof ShareID) {
		$shareID = $id;
		require_once(dirname(__DIR__) . '/controllers/share.php');
	}
}

//TODO Limit requests per IP to prevent DOS
http_response_code(404);
exit();