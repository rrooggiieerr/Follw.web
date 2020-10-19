<?php
// Fixes false "Variable is not defined" validation errors for variables created in other files
/* @var String $method */
/* @var String $action */
/* @var String $format */
/* @var Object $pdo */
/* @var Integer $id */

if($method == 'POST') {
	http_response_code(405);
	exit();
}

if($action == 'location') {
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

//ToDo? Limit requests per IP to prevent DOS
http_response_code(404);
exit();
