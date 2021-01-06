<?php
// Fixes false "Variable is undefined" validation errors
/* @var String $method */
/* @var String $action */
/* @var String $format */
/* @var FollowID $id */

if(!isset($action)) {
	http_response_code(404);
	exit();
}

if($method === 'POST') {
	http_response_code(405);
	exit();
}

if($action === 'location') {
	if($format === 'html') {
		require_once(dirname(__DIR__) . '/views/follow.php');
		exit();
	}
	
	if(!$id->enabled) {
		http_response_code(403);
		exit();
	}

	if($id->expires > 0 && $id->expires < time()) {
		http_response_code(403);
		exit();
	}

	require_once(dirname(__DIR__) . '/models/Location.php');
	$location = Location::get($id);

	switch ($format) {
		case 'json':
			if (!$location) {
				http_response_code(204);
				exit();
			}
			header('Content-Type: application/json');
			$json = json_encode(array_merge($location->jsonSerialize(), $id->jsonSerialize()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			header('Content-Length: ' . strlen($json));
			echo($json);
			break;
			//TODO Implement other formats
		default:
			http_response_code(404);
	}

	exit();
}