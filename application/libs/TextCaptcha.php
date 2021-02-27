<?php
/**
 * Implements https://textcaptcha.com/
 * @author rogier
 *
 */
class TextCaptcha {
	var $question = NULL;
	var $answermd5s = NULL;
	var $timestamp = NULL;

	static function getCaptcha() {
		global $configuration;

		$url = 'https://api.textcaptcha.com/' . $configuration['captcha']['id'] . '.json';
		$json = @file_get_contents($url);

		if($json === FALSE) {
			// Error occurted when requesting the captcha
			error_log('Failed to request a TextCaptcha');
			//return NULL;
		}

		$captcha = json_decode($json, TRUE);

		if($captcha === NULL) {
			// Error occurted when decoding the JSON
			error_log('Failed to decode TextCaptcha JSON');
			//return NULL;

			// Fallback challenge
			$captcha = array(
				'question'=>'Is ice hot or cold?',
				'answers'=>array(md5('cold'))
			);
		}

		$instance = new TextCaptcha();
		$instance->question = $captcha['q'];
		$instance->answermd5s = $captcha['a'];
		$instance->timestamp = time();

		return $instance;
	}

	function validate(string $answer) {
		$elapsedTime = time() - $this->timestamp;
		if($elapsedTime < 5) {
			error_log('Form submitted to fast');
			return FALSE;
		}
		if($elapsedTime > 300) {
			error_log('Form submitted to slow');
			return FALSE;
		}

		$answer = strtolower(trim($answer));
		if(strlen($answer) === 0) {
			error_log('Empty captcha answer');
			return FALSE;
		}

		$checksum = md5($answer);
		if(!in_array($checksum, $this->answermd5s)) {
			error_log('Captcha answer invalid');
			return FALSE;
		}

		error_log('Captcha validated');
		return TRUE;
	}
}