<?php
class Translation extends ArrayObject {
	var $language = NULL;
	var $translations = [];

	function __construct($page) {
		$acceptedLanguages = [];

		if(isset($_GET['lang']) && preg_match('/[a-z]+/', $_GET['lang'])) {
			$acceptedLanguages[] = $_GET['lang'];
		}

		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$acceptedLanguages = array_merge($acceptedLanguages, explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
			foreach($acceptedLanguages as $i => $language) {
				$language = explode(';', $language);
				if(count($language) == 1) {
					$language[1] = 1;
				} elseif(count($language) == 2) {
					$language[1] = (explode('=', $language[1]))[1];
				}
				$acceptedLanguages[$i] = $language;
			}
			//TODO Sort languages?

			$filename = NULL;
			foreach($acceptedLanguages as $language) {
				// Check if there is a translation file for the given language
				$filename = dirname(__DIR__) . '/translations/' . $page . '.' . $language[0] . '.txt';
				if(file_exists($filename)) {
					$this->language = $language[0];
					break;
				}
			}
		}

		// If no language is set fall back to English
		if(!isset($this->language)) {
			$this->language = 'en';
			$filename = dirname(__DIR__) . '/translations/' . $page . '.en.txt';
		}

		// Read the translation file
		$file = fopen($filename, 'r');
		$key = null;
		while(($line = fgets($file)) !== FALSE) {
			$line = trim($line);
			// Empty strings and strings starting with # are ignored
			if($line !== '' && $line[0] !== '#') {
				if(strpos($line , '=') !== FALSE) {
					$line = explode('=', $line, 2);
					$key = trim($line[0]);
					$this[$key] = trim($line[1]);
				} else if(isset($key)) {
					// If line does not contain an = append to previous entry
					$this[$key] .= PHP_EOL . $line;
				}
			}
		}
		fclose($file);
	}

	function get(string $key, string $escaping = NULL, ...$values) {
		$s = '';
		if(isset($this[$key])) {
			$s = $this[$key];

			if($values) {
				$s = sprintf($s, ...$values);
			}
		}

		switch($escaping) {
			case 'xml':
			case 'html':
				$s = htmlspecialchars($s, ENT_NOQUOTES);
				break;
			case 'xmlattr':
			case 'htmlattr':
				$s = htmlspecialchars($s, ENT_COMPAT);
				break;
			case 'js':
				$s = json_encode($s, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
				break;
			case 'json':
				global $configuration;
				$s = json_encode($s, $configuration['jsonoptions']);
				break;
		}

		return $s;
	}
}