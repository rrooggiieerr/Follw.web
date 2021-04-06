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
}