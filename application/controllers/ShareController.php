<?php
// Fixes false "Variable is never used" validation errors
/* @var boolean $showIntro */

class ShareController {
	function route(ShareID $shareID = NULL, string $action, string $format = NULL) {
		global $configuration;

		$matches = NULL; // Fixes false "Variable is undefined" validation error
		switch($action) {
			case NULL:
			case '':
				break;
			case 'generateshareid':
				if(!$this->generateShareID()) {
					session_destroy();
					//TODO Limit requests per IP to prevent DOS
					http_response_code(401); // Unauthorized
					$_SERVER['REQUEST_METHOD'] = 'GET';
					$this->generateShareID();
				}
				exit();
				break;
			case 'location':
				if($_SERVER['REQUEST_METHOD'] === 'POST' || count($_GET) !== 0) {
					$this->shareLocation($shareID);
				} else {
					$this->getLocation($shareID, $format);
				}
				break;
			case 'settings.json':
				header('Content-Type: application/json');
				$json = json_encode($shareID->jsonSerialize(), $configuration['jsonoptions']);
				header('Content-Length: ' . strlen($json));
				print($json);
				exit();
				break;
			case 'deletelocation':
				$this->deleteLocation($shareID);
				break;
			case 'config':
				$this->configSharer($shareID);
				break;
			case 'delete':
				$this->deleteSharer($shareID);
				break;
			case 'generatefollowid':
				$this->createupdateFollowID($shareID);
				break;
			case 'followers.json':
				$this->getFollowers($shareID);
				break;
			case 'serviceworker.js':
				require_once(dirname(__DIR__) . '/views/share.serviceworker.js.php');
				exit();
			case 'manifest.webmanifest':
				require_once(dirname(__DIR__) . '/views/share.manifest.webmanifest.php');
				exit();
			case (preg_match('/^follower\/(' . $configuration['id']['regexPattern'] . ')$/', $action, $matches) ? TRUE : FALSE):
				$this->createupdateFollowID($shareID, $matches[1]);
			case (preg_match('/^follower\/(' . $configuration['id']['regexPattern'] . ')\.json$/', $action, $matches) ? TRUE : FALSE):
				$this->getFollower($shareID, $matches[1]);
				exit();
			case (preg_match('/^follower\/(' . $configuration['id']['regexPattern'] . ')\/(enable|disable|delete)$/', $action, $matches) ? TRUE : FALSE):
				$followID = ID::decode($matches[1], $shareID);

				if (!$followID) {
					http_response_code(404);
					exit();
				}

				if($followID->type === 'deleted') {
					http_response_code(410);
					exit();
				}

				switch($matches[2]) {
					case 'enable':
						if ($followID->enable()) {
							http_response_code(204);
							exit();
						}
						break;
					case 'disable':
						if ($followID->disable()) {
							http_response_code(204);
							exit();
						}
						break;
					case 'delete':
						if ($followID->delete()) {
							http_response_code(204);
							exit();
						}
						break;
				}

				http_response_code(500);
				exit();
			default:
				http_response_code(404);
				exit();
		}
	}

	function generateShareID() {
		global $configuration, $method;

		if($method === 'GET') {
			session_start(['gc_maxlifetime' => 300]);

			require_once(dirname(__DIR__) . '/libs/FormHoneypot.php');
			$honeypot = new FormHoneypot();
			$_SESSION['honeypot'] = $honeypot;
			require_once(dirname(__DIR__) . '/libs/Obfuscator.php');
			$obfuscator = new Obfuscator();
			$_SESSION['obfuscator'] = $obfuscator;

			//TODO Maybe only show captcha if client IP address is listed in spam list? 
			$captcha = NULL;
			if($configuration['captcha']['enabled']) {
				require_once(dirname(__DIR__) . '/libs/TextCaptcha.php');
				$captcha = TextCaptcha::getCaptcha();
				$_SESSION['captcha'] = $captcha;
			}

			require_once(dirname(__DIR__) . '/views/generateshareid.php');
			exit();
		}

		if($method === 'POST') {
			if(empty($_POST['sessionid'])) {
				error_log('No session ID');
				return FALSE;
			}

			require_once(dirname(__DIR__) . '/libs/FormHoneypot.php');
			require_once(dirname(__DIR__) . '/libs/Obfuscator.php');
			require_once(dirname(__DIR__) . '/libs/TextCaptcha.php');
			$sessionid = $_POST['sessionid'];
			session_id($sessionid);
			session_start();

			if(empty($_SESSION['honeypot'])) {
				error_log('No honeypot in session');
				return FALSE;
			}

			$honeypot = $_SESSION['honeypot'];
			$honeypot->validate($_POST);

			$obfuscator = $_SESSION['obfuscator'];

			if($configuration['captcha']['enabled']) {
				if(empty($_SESSION['captcha'])) {
					error_log('No captcha in session');
					return FALSE;
				}

				$captcha = $_SESSION['captcha'];

				if(empty($_POST[$obfuscator['captchaanswer']])) {
					error_log('Empty captcha answer');
					return FALSE;
				}

				$answer = $_POST[$obfuscator['captchaanswer']];

				if(!$captcha->validate($answer)) {
					error_log('Captcha answer invalid');
					// The request is not validated to be performed by a human, try again
					return FALSE;
				}
				// The request is validated to be performed by a human, continue with generating a Share ID
			}

			// Check agree checkbox, should be checked
			if(empty($_POST[$obfuscator['agreetermsconditions']]) || $_POST[$obfuscator['agreetermsconditions']] !== 'true') {
				error_log('Not agreed to terms & conditions');
				return FALSE;
			}
			error_log('Agreed');

			session_destroy();

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
	}

	function shareLocation(ShareID $shareID) {
		require_once(dirname(__DIR__) . '/models/Location.php');

		$location = new Location($shareID);

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$formValues = $_POST;
		} else {
			$formValues = $_GET;
		}

		foreach($formValues as $name => $value) {
			if(is_numeric($value))
				$value = floatval($value);

				switch($name) {
					case (substr($name, 0, 2) == 'la' ? TRUE : FALSE): // latitude
						if(empty($value) || $value < -90 || $value > 90) {
							http_response_code(500);
							exit();
						}
						$location['latitude'] = $value;
						break;
					case (substr($name, 0, 2) == 'lo' ? TRUE : FALSE): // longitude
						if(empty($value) || $value < -180 || $value > 180) {
							http_response_code(500);
							exit();
						}
						$location['longitude'] = $value;
						break;
					case (substr($name, 0, 2) == 'hd' ? TRUE : FALSE): // horizontal dilution of position
					case (substr($name, 0, 2) == 'ac' ? TRUE : FALSE): // accuracy
						if(!empty($value)) {
							$location['accuracy'] = $value;
						}
						break;
					case (substr($name, 0, 2) == 'al' ? TRUE : FALSE): // altitude
					case (substr($name, 0, 2) == 'el' ? TRUE : FALSE): // elevation
						if(!empty($value)) {
							$location['altitude'] = $value;
						}
						break;
					case (substr($name, 0, 2) == 'be' ? TRUE : FALSE): // bearing
					case (substr($name, 0, 2) == 'he' ? TRUE : FALSE): // heading
					case (substr($name, 0, 2) == 'di' ? TRUE : FALSE): // direction
					case (substr($name, 0, 2) == 'az' ? TRUE : FALSE): // azimuth
					case (substr($name, 0, 2) == 'co' ? TRUE : FALSE): // course
						if(!empty($value)) {
							if($value < 0 || $value > 360) {
								http_response_code(500);
								exit();
							}
							$location['direction'] = $value;
						}
						break;
					case (substr($name, 0, 2) == 'sp' ? TRUE : FALSE): // speed
						if(!empty($value)) {
							if($value < 0) {
								http_response_code(500);
								exit();
							}
							$location['speed'] = $value;
						}
						break;
				}
		}

		if (!$location->store()) {
			http_response_code(500);
			exit();
		}

		http_response_code(204);
		exit();
	}

	function getLocation(ShareID $shareID, $format) {
		if($format == 'html') {
			// If a referer is given we assume the page was not bookmarked
			$showIntro = FALSE;
			if(array_key_exists('HTTP_REFERER', $_SERVER)) {
				$showIntro = TRUE;
			}

			if(!array_key_exists('alias', $shareID->config)) {
				$shareID->config['alias'] = '';
			}
			require_once(dirname(__DIR__) . '/views/share.php');
			exit();
		}

		require_once(dirname(__DIR__) . '/models/Location.php');

		$location = Location::get($shareID);

		if (!$location) {
			http_response_code(204);
			exit();
		}

		switch ($format) {
			case 'json':
				header('Content-Type: application/geo+json');
				header('Cache-Control: max-age=' . $location->refresh);
				header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $location->refresh));

				$json = $location->geoJson();
				header('Content-Length: ' . strlen($json));
				print($json);
				break;
				//TODO Implement other formats
			default:
				http_response_code(500);
		}

		exit();
	}

	function configSharer(ShareID $shareID) {
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$formValues = $_POST;
		} else {
			$formValues = $_GET;
		}

		foreach($formValues as $name => $value) {
			switch($name) {
				case 'alias':
					$shareID['alias'] = trim(filter_var($value, FILTER_SANITIZE_STRING));
					if($shareID['alias'] === '') {
						unset($shareID['alias']);
					}
					break;
			}
		}

		if ($shareID->store()) {
			http_response_code(204);
			exit();
		}

		http_response_code(500);
		exit();
	}

	function deleteLocation(ShareID $shareID) {
		require_once(dirname(__DIR__) . '/models/Location.php');

		$location = new Location($shareID);

		if ($location->delete()) {
			http_response_code(204);
			exit();
		}

		http_response_code(500);
		exit();
	}

	function deleteSharer(ShareID $shareID) {
		if ($shareID->delete()) {
			http_response_code(204);
			exit();
		}

		http_response_code(500);
		exit();
	}

	function createupdateFollowID(ShareID $shareID, string $followID = NULL) {
		if($followID) {
			$followID = ID::decode($followID, $shareID);

			if (!$followID) {
				http_response_code(404);
				exit();
			}

			if(!$followID instanceof FollowID) {
				http_response_code(500);
				exit();
			}
		} else {
			require_once(dirname(__DIR__) . '/models/FollowID.php');
			$followID = new FollowID($shareID);
		}

		if($_SERVER['REQUEST_METHOD'] === 'POST') {
			$formValues = $_POST;
		} else {
			$formValues = $_GET;
		}

		if(!empty($formValues['reference'])) {
			$followID['reference'] = filter_var($formValues['reference'], FILTER_SANITIZE_STRING);
		} else if(isset($followID['reference'])){
			unset($followID['reference']);
		}

		if(!empty($formValues['alias'])) {
			$followID['alias'] = filter_var($formValues['alias'], FILTER_SANITIZE_STRING);
		} else if(isset($followID['alias'])) {
			unset($followID['alias']);
		}

		if(!empty($formValues['starts']) && is_numeric($formValues['starts'])) {
			// Unix time
			$followID->starts = intval($formValues['starts']);
		} else if(!empty($formValues['starts'])) {
			// ISO 8601 date time
			$starts = $formValues['starts'];
			// ISO 8601 date time to unix time
			$followID->starts = strtotime($starts);
		} else {
			$followID->starts = NULL;
		}

		if(!empty($formValues['expires']) && is_numeric($formValues['expires'])) {
			// Unix time
			$followID->expires = intval($formValues['expires']);
		} else if(!empty($formValues['expires'])) {
			// ISO 8601 date time
			$expires = $formValues['expires'];
			// ISO 8601 date time to unix time
			$followID->expires = strtotime($expires);
		} else {
			$followID->expires = NULL;
		}

		if(!empty($formValues['delay']) && is_numeric($formValues['delay'])) {
			$followID->delay = intval($formValues['delay']);
		}

		$followID->enabled = $formValues['enabled'] === 'true';

		if ($followID->store()) {
			header('Content-Type: application/json');
			$json = $followID->json();
			header('Content-Length: ' . strlen($json));
			print($json);
			exit();
		}

		http_response_code(500);
		exit();
	}

	function getFollowers(ShareID $shareID) {
		$followers = $shareID->getFollowers();

		if(is_array($followers)) {
			global $configuration;
			header('Content-Type: application/json');
			$json = json_encode($followers, $configuration['jsonoptions']);
			header('Content-Length: ' . strlen($json));
			print($json);
			exit();
		}

		http_response_code(500);
		exit();
	}

	function getFollower(ShareID $shareID, string $followID) {
		$followID = ID::decode($followID, $shareID);

		if (!$followID) {
			http_response_code(404);
			exit();
		}

		if($followID->type === 'deleted') {
			http_response_code(410);
			exit();
		}

		if(!$followID instanceof FollowID) {
			http_response_code(404);
			exit();
		}

		header('Content-Type: application/json');
		$json = $followID->json();
		header('Content-Length: ' . strlen($json));
		print($json);
		exit();
	}
}