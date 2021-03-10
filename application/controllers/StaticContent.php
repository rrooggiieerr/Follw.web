<?php
class StaticContent {
	/**
	 * Handles static content
	 * @param string $path
	 * @return boolean TRUE if succesfully handled else FALSE
	 */
	function route(string $path) {
		global $configuration;

		switch($path) {
			case '/':
				require_once(dirname(__DIR__) . '/views/intro.php');
				return TRUE;
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
					
					if($evaluate) {
						include($filename);
					} else {
						print(file_get_contents($filename));
					}
				} else {
					require_once(dirname(__DIR__) . '/views/static.php');
				}

				return TRUE;
			case '/robots.txt':
				require_once(dirname(__DIR__) . '/views/robots.txt.php');
				return TRUE;
			case $path === '/phpinfo' && $configuration['mode'] !== 'production':
				phpinfo();
				return TRUE;
		}

		return FALSE;
	}
}