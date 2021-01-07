<?php
class StaticContent {
	/**
	 * Handles static content
	 * @param string $path
	 * @return boolean TRUE if succesfully handled else FALSE
	 */
	function route(string $path) {
		$matches = NULL; // Fixes false "Variable is undefined" validation error
		switch($path) {
			case '/':
				require_once(dirname(__DIR__) . '/views/intro.php');
				return TRUE;
			case '/privacy':
			case '/terms':
			case '/htmlsnippet':
			case '/wordpress':
			case '/osmand':
				require_once(dirname(__DIR__) . '/views/static.php');
				return TRUE;
			case '/robots.txt':
				require_once(dirname(__DIR__) . '/views/robots.txt.php');
				return TRUE;
			case (preg_match('/^\/[a-zA-Z0-9]*\.(html|js|css)$/', $path, $matches) ? true : false):
				$filename = dirname(__DIR__) . '/../htdocs' . $path;
				if(file_exists($filename)) {
					switch($matches[1]) {
						case 'js':
							header('Content-Type: text/javascript');
							break;
						case 'css':
							header('Content-Type: text/css');
							break;
					}
					print(file_get_contents($filename));
					return TRUE;
				}
				return FALSE;
		}

		return FALSE;
	}
}