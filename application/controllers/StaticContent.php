<?php
class StaticContent {
	/**
	 * Handles static content
	 * @param string $path
	 * @return boolean TRUE if succesfully handled else FALSE
	 */
	function route(string $path) {
		global $configuration, $method;
		
		if($path === '/contact') {
			return $this->contact();
		}

		if($method === 'POST') {
			return FALSE;
		}

		switch($path) {
			case '/':
				require_once(dirname(__DIR__) . '/views/intro.php');
				return TRUE;
			case '/apidoc':
				$raw = TRUE;
			case '/credits':
			case '/privacy':
			case '/terms':
			case '/htmlsnippet':
			case '/wordpress':
				$filename = dirname(__DIR__) . '/views' . $path. '.html';
				$evaluate = FALSE;
				if(!file_exists($filename)) {
					$filename = dirname(__DIR__) . '/views' . $path. '.php';
					$evaluate = TRUE;
					if(!file_exists($filename)) {
						http_response_code(404);
						exit();
					}
				}

				if(array_key_exists('raw', $_GET)) {
					header('X-Robots-Tag: noindex');
					$raw = TRUE;
				}

				$lastmodified = filemtime($filename);
				if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && date('D, d M Y H:i:s T', $lastmodified) === $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
					http_response_code(304);
					return TRUE;
				}
				header('Last-Modified: ' . date('D, d M Y H:i:s T', $lastmodified));

				if(isset($raw)) {
					if($evaluate) {
						include($filename);
					} else {
						print(file_get_contents($filename));
					}
				} else {
					require_once(dirname(__DIR__) . '/views/static.php');
				}

				return TRUE;
			case '/openapi.json':
				require_once(dirname(__DIR__) . '/views/openapi.json.php');
				return TRUE;
			case '/robots.txt':
				require_once(dirname(__DIR__) . '/views/robots.txt.php');
				return TRUE;
			case '/sitemap.xml':
				require_once(dirname(__DIR__) . '/views/sitemap.xml.php');
				return TRUE;
			case $path === '/phpinfo' && $configuration['mode'] !== 'production':
				phpinfo();
				return TRUE;
		}

		return FALSE;
	}

	function contact() {
		global $configuration, $method;

		if(empty($configuration['contactemail'])) {
			http_response_code(404);
			exit();
		}

		require_once(dirname(__DIR__) . '/models/Translation.php');
		$tl = new Translation('contact');
		header('Content-Language: ' . $tl->language);
		
		$errors = [];
		$name = '';
		$email = '';
		$subject = '';
		$message = '';
		
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
				// The request is validated to be performed by a human, continue sending the message
			}

			if(!empty($_POST['name'])) {
				$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
			}
				
			if(!empty($_POST['email'])) {
				$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
				if(!$email) {
					$errors['email'] = 'errorinvalidemail';
					$email = $_POST['email'];
				}
			} else {
				$errors['email'] = 'errornoemail';
			}
				
			$subject = '';
			if(!empty($_POST['subject'])) {
				$subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
			}
				
			if(isset($_POST['message']) && !empty($_POST['message'])) {
				$message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
			} else {
				$errors['message'] = 'errornomessage';
			}
				
			if(!$errors) {
				$headers = [];
				
				$headers['MIME-Version'] = '1.0';
				$headers['Content-type'] = 'text/plain; charset=utf-8';
				if(!empty($name)) {
					$headers['From'] = $name . ' <' . $email . '>';
				} else {
					$headers['From'] = $email;
				}
				
				if(mail($configuration['contactemail'], $subject, $message, $headers)) {
?>
<p>Thank you</p>
<?php
				} else {
?>
<p>Failed to send message</p>
<?php
				}

				return TRUE;
			}
		}

		if($method === 'GET' || !empty($errors)) {
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
			
			$filename = dirname(__DIR__) . '/views/contact.php';
			$evaluate = TRUE;

			if(array_key_exists('raw', $_GET)) {
				header('X-Robots-Tag: noindex');
				$raw = TRUE;
			}
			
			$lastmodified = filemtime($filename);
			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && date('D, d M Y H:i:s T', $lastmodified) === $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
				http_response_code(304);
				return TRUE;
			}
			header('Last-Modified: ' . date('D, d M Y H:i:s T', $lastmodified));
			
			if(isset($raw)) {
				include($filename);
			} else {
				require_once(dirname(__DIR__) . '/views/static.php');
			}

			return TRUE;
		}
	}
}