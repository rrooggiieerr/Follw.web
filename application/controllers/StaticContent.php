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
		}

		return FALSE;
	}
}