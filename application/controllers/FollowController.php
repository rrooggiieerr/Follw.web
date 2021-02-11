<?php
class FollowController {
	function route(FollowID $id, string $action, string $format = NULL) {
		if(!isset($action)) {
			http_response_code(404);
			exit();
		}

		if($_SERVER['REQUEST_METHOD'] === 'POST') {
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
					global $configuration;
					header('Content-Type: application/json');
					$json = json_encode(array_merge($location->jsonSerialize(), $id->jsonSerialize()), $configuration['jsonoptions']);
					header('Content-Length: ' . strlen($json));
					print($json);
					break;
				//case 'kmz':
					//TODO KMZ is zipped KML
				case 'kml':
					require_once(dirname(__DIR__) . '/views/location.kml.php');
					break;
				//case 'update.kmz':
					//TODO KMZ is zipped KML
				case 'update.kml':
					require_once(dirname(__DIR__) . '/views/location.update.kml.php');
					break;
				case 'gpx':
					require_once(dirname(__DIR__) . '/views/location.gpx.php');
					break;
					break;
				//TODO Implement other formats
				default:
					http_response_code(404);
			}

			exit();
		}
	}
}