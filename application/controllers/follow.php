<?php
// Fixes false "Variable is not defined" validation errors for variables created in other files
/* @var Object $config */
/* @var String $method */
/* @var String $action */
/* @var String $format */
/* @var Object $pdo */
/* @var Integer $followid */

if(!isset($action)) {
	http_response_code(404);
	exit();
}

if($method == 'POST') {
	http_response_code(405);
	exit();
}

if($action == 'location') {
	try {
		// Get location from database
		$query = 'SELECT UNIX_TIMESTAMP(l.`timestamp`) AS `timestamp`, l.`location`, sid.`config` AS `sharerConfig`
			FROM `locations` l, `followers` f, `issuedids` sid
			WHERE sid.`id` = f.`id` AND l.`id` = sid.`id` AND f.`enabled` = 1 AND (f.`expires` IS NULL OR f.`expires` >= NOW()) AND f.`followid` = ?';
		$statement = $pdo->prepare($query);
		$statement->execute([$followid]);
		
		if($statement->rowCount() != 1) {
			if($format == 'html') {
				$location = null;
				require_once(dirname(__DIR__) . '/views/follow.php');
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
	
	$sharerConfig = json_decode($result['sharerConfig'], TRUE);
	$location = json_decode($result['location'], TRUE);
	$location['timestamp'] = $result['timestamp'] + 0;
	if(array_key_exists('alias', $config) && $config['alias'])
		$location['alias'] = $config['alias'];
	else if($sharerConfig && array_key_exists('alias', $sharerConfig) && $sharerConfig['alias'])
		$location['alias'] = $sharerConfig['alias'];
	else
		$location['alias'] = "Something";
	// Calculate the recomended refresh interval based on timestamp
	if(date_create()->getTimestamp() - $result['timestamp'] < 60)
		$location['interval'] = 1;
	else
		$location['interval'] = 5;
	
	switch ($format) {
		case 'html':
			require_once(dirname(__DIR__) . '/views/follow.php');
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