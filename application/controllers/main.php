<?php
// Fixes false "Variable is undefined" and "Variable is never used" validation errors
/* @var array $configuration */
/* @var string $protocol */

ini_set('session.use_cookies', 0);
ini_set('session.use_only_cookies', 0);
ini_set('session.use_strict_mode', 1);

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

if($path === '/generateshareid') {
	require_once(dirname(__DIR__) . '/controllers/ShareController.php');
	(new ShareController())->route(NULL, 'generateshareid', NULL);
}

require_once(dirname(__DIR__) . '/libs/Base.php');
$configuration['id']['encodedLength'] = BASE::length($configuration['id']['nBytes'], $configuration['id']['baseEncoding']);
$configuration['id']['encodedChars'] = BASE::chars($configuration['id']['baseEncoding']);

$matches = NULL; // Fixes false "Variable is undefined" validation error
if(preg_match('/^\/([' . $configuration['id']['encodedChars'] . ']{' . $configuration['id']['encodedLength'] . '})([\/.].*)?$/', $path, $matches) == TRUE) {
	$id = $matches[1];
	$remainer = '';
	if(count($matches) === 3) {
		$remainer = $matches[2];
	}

	$action = NULL;
	$format = NULL;
	if(in_array($remainer, [ NULL, '', '/'], TRUE)) {
		$action = 'location';
		$format = 'html';
	} else if(preg_match('/^\.[a-z]+\.?[a-z]+$/', $remainer)) {
		$action = 'location';
		$format = substr($remainer, 1);
	} else if(preg_match('/^\/qrcode\.([a-z]+)$/', $remainer, $matches)) {
		$action = 'qrcode';
		$format = $matches[1];
	} else {
		$action = substr($remainer, 1);
	}

	if($action === 'location' && !in_array($format, ['html', 'json', 'kml', 'update.kml', 'kmz', 'update.kmz', 'gpx'])) {
		http_response_code(404);
		exit();
	}

	require_once(dirname(__DIR__) . '/models/ID.php');
	$id = ID::decode($id);

	if (!$id) {
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

	if($action === 'manifest.webmanifest') {
		require_once(dirname(__DIR__) . '/views/manifest.webmanifest.php');
		exit();
	}

	if($action === 'qrcode') {
		require_once(dirname(__DIR__) . '/libs/phpqrcode.php');
		switch($format) {
			case 'svg':
				header('Content-Type: image/svg+xml');
				QRcode::svg($id->url());
				exit();
			case 'png':
				header('Content-Type: image/png');
				QRcode::png($id->url());
				exit();
			case 'eps':
				header('Content-Type: application/postscript');
				QRcode::eps($id->url());
				exit();
		}

		http_response_code(404);
		exit();
	}

	if($id instanceof FollowID) {
		require_once(dirname(__DIR__) . '/controllers/FollowController.php');
		(new FollowController())->route($id, $action, $format);
	} else if($id instanceof ShareID) {
		require_once(dirname(__DIR__) . '/controllers/ShareController.php');
		(new ShareController())->route($id, $action, $format);
	}
}

//TODO Limit requests per IP to prevent DOS
http_response_code(404);
exit();