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
	if(!$id->enabled || ($id->expires > 0 && $id->expires < time())) {
		// If Follow ID has not been enabled or has expired
		// HTTP Response 403 Forbidden
		http_response_code(403);
		if($format === 'html') {
			$location = NULL;
			require_once(dirname(__DIR__) . '/views/follow.php');
		}
		exit();
	}

	require_once(dirname(__DIR__) . '/models/Location.php');
	$location = Location::get($id);

	if($format === 'html') {
		require_once(dirname(__DIR__) . '/views/follow.php');
		exit();
	}

	if (!$location) {
		// If no location has been set
		// HTTP Response 204 No Content
		http_response_code(204);
		exit();
	}

	switch ($format) {
		case 'json':
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