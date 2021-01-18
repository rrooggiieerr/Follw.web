<?php
require_once(dirname(__DIR__) . '/libs/Base.php');

class Obfuscator extends ArrayObject {
	var $map = array();

	function obfuscate(string $s) {
		if(isset($this[$s])) {
			return $this[$s];
		}

		do {
			// Generate unique string
			$o = bin2hex(random_bytes(2));
		} while(in_array($o, array_values($this->getArrayCopy())));
		$this[$s] = $o;

		return $o;
	}
}