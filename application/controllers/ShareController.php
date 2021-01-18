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
				$this->generateFollowID($shareID);
				break;
			case 'followers.json':
				$this->getFollowers($shareID);
				break;
			case (preg_match('/^follower\/([' . $configuration['id']['encodedChars'] . ']{' . $configuration['id']['encodedLength'] . '})\/(enable|disable|delete)$/', $action, $matches) ? TRUE : FALSE):
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
					default:
						// This should not happen as the regex only supports the options enable, disable and delete
						http_response_code(500);
						exit();
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
		global $configuration;

		$method = $_SERVER['REQUEST_METHOD'];

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
						if($value === '' || $value < -90 || $value > 90) {
							http_response_code(500);
							exit();
						}
						$location['latitude'] = $value;
						break;
					case (substr($name, 0, 2) == 'lo' ? TRUE : FALSE): // longitude
						if($value === '' || $value < -180 || $value > 180) {
							http_response_code(500);
							exit();
						}
						$location['longitude'] = $value;
						break;
					case (substr($name, 0, 2) == 'hd' ? TRUE : FALSE): // horizontal dilution of position
					case (substr($name, 0, 2) == 'ac' ? TRUE : FALSE): // accuracy
						if($value !== '')
							$location['accuracy'] = $value;
							break;
					case (substr($name, 0, 2) == 'al' ? TRUE : FALSE): // altitude
						if($value !== '')
							$location['altitude'] = $value;
							break;
					case (substr($name, 0, 2) == 'be' ? TRUE : FALSE): // bearing
					case (substr($name, 0, 2) == 'he' ? TRUE : FALSE): // heading
					case (substr($name, 0, 2) == 'di' ? TRUE : FALSE): // direction
					case (substr($name, 0, 2) == 'az' ? TRUE : FALSE): // azimuth
					case (substr($name, 0, 2) == 'co' ? TRUE : FALSE): // course
						if($value !== '') {
							if($value < 0 || $value > 360) {
								http_response_code(500);
								exit();
							}
							$location['direction'] = $value;
						}
						break;
					case (substr($name, 0, 2) == 'sp' ? TRUE : FALSE): // speed
						if($value !== '') {
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
				header('Content-Type: application/json');
				$json = json_encode(array_merge($location->jsonSerialize(), $shareID->jsonSerialize()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
					//TODO Input validation
					$shareID['alias'] = $value;
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

	function generateFollowID(ShareID $shareID) {
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$formValues = $_POST;
		} else {
			$formValues = $_GET;
		}

		require_once(dirname(__DIR__) . '/models/FollowID.php');
		$follower = new FollowID($shareID);

		if(!empty($formValues['reference'])) {
			//TODO Input validation
			$follower['reference'] = $formValues['reference'];
		}
		if(!empty($formValues['alias'])) {
			//TODO Input validation
			$follower['alias'] = $formValues['alias'];
		}
		if($formValues['enabled'] == 'true') {
			$follower->enabled = TRUE;
		}

		if(!empty($formValues['expires']) && is_numeric($formValues['expires'])) {
			$follower->expires = $formValues['expires'];
		} else if(!empty($formValues['expires'])) {
			$expires = $formValues['expires'];
			// ISO 8601 date time to unix time
			$expires = strtotime($expires);
			// Unix time to MySQL timestamps
			$follower->expires = date('Y-m-d H:i:s', $expires);
		}
		if(!empty($formValues['delay']) && is_numeric($formValues['delay'])) {
			$follower->delay = $formValues['delay'];
		}

		if ($follower->store()) {
			echo($follower->encode());
			exit();
		}

		http_response_code(500);
		exit();
	}

	function getFollowers(ShareID $shareID) {
		$followers = $shareID->getFollowers();

		if(is_array($followers)) {
			header('Content-Type: application/json');
			echo(json_encode($followers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
			exit();
		}

		http_response_code(500);
		exit();
	}
}