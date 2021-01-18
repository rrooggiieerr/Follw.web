<?php
/**
 * The concept is that hidden form fields are invisible to human users, but visible to bots.
 * When bots fill in these invisible fields, they reveal that they're not human, and the forms can be safely discarded.
 * 
 * Instead of requiring users to perform annoying extra tasks to prove they are human,
 * presume they are human unless they reveal themselves to be bots.
 * 
 * @author rogier
 *
 */
class FormHoneypot {
	var $timestamp;

	function __construct() {
		$this->timestamp = time();
	}

	function html() {
		return '<input type="text" name="subject" tabindex="-1"/>
<textarea name="message" tabindex="-1"></textarea>
<input type="checkbox" name="approve" value="true" tabindex="-1"/>
';
	}

	function css() {
		return 'input[name=subject], textarea[name=message], input[name=approve] {
	width: 50px;
	position: absolute;
	left: -1000px;
	bottom: -1000px;
	z-index: -1;
	opacity: 0;
}
';
	}

	function js() {
		return '
';
	}

	function validate(array $formData) {
		$elapsedTime = time() - $this->timestamp;
		if($elapsedTime < 1) {
			error_log('Form submitted to fast');
			return FALSE;
		}
		if($elapsedTime > 300) {
			error_log('Form submitted to slow');
			return FALSE;
		}

		// Check referer
		if(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) !== $_SERVER['HTTP_HOST']) {
			error_log('Invalid referer');
			return FALSE;
		}

		// Check honeypot text field, should be empty
		if(!empty($formData['subject'])) {
			error_log('Honeypot textfield filled');
			return FALSE;
		}

		// Check honeypot textarea, should be empty
		if(!empty($formData['message'])) {
			error_log('Honeypot textarea filled');
			return FALSE;
		}

		// Check honeypot checkbox, should be unchecked
		if(!empty($formData['approve'])) {
			error_log('Honeypot checkbox checked');
			return FALSE;
		}

		error_log('Honeypot validated');
		return TRUE;
	}
}