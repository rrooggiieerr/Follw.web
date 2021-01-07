<?php
class StaticContent {
	/**
	 * Handles static content
	 * @param string $path
	 * @return boolean TRUE if succesfully handled else FALSE
	 */
	function route(string $path) {
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
			case '/follw.js':
				header('Content-Type: text/javascript');
				print(file_get_contents(dirname(__DIR__) . '/../htdocs/follw.js'));
				return TRUE;
		}

		return FALSE;
	}
}