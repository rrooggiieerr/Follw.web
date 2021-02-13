<?php
class Translation extends ArrayObject {
	var $language = NULL;
	var $translations = [];

	function __construct($page) {
		$acceptedLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
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

		// If no language is set fall back to English
		if(!isset($this->language)) {
			$this->language = 'en';
			$filename = dirname(__DIR__) . '/translations/' . $page . '.en.txt';
		}

		// Read the translation file
		$file = fopen($filename, 'r');
		while(($line = fgets($file)) !== FALSE) {
			$line = explode('=', $line, 2);
			$line[0] = trim($line[0]);
			$line[1] = trim($line[1]);
			$this[$line[0]] = $line[1];
		}
		fclose($file);
	}

	function get($key) {
		return $this[$key];
	}
}