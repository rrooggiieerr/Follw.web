<?php
require_once(dirname(__DIR__) . '/models/Translation.php');

global $configuration;

if(empty($configuration['contactemail'])) {
	ob_end_clean();
	http_response_code(404);
	exit();
	
}

$tl = new Translation('contact');
header('Content-Language: ' . $tl->language);

$errors = [];
$name = '';
$email = '';
$subject = '';
$message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST' || count($_GET) !== 0) {
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$formValues = $_POST;
	} else {
		$formValues = $_GET;
	}

	if(!empty($formValues['name'])) {
		$name = filter_var($formValues['name'], FILTER_SANITIZE_STRING);
	}

	if(!empty($formValues['email'])) {
		$email = filter_var($formValues['email'], FILTER_VALIDATE_EMAIL);
		if(!$email) {
			$errors['email'] = 'errorinvalidemail';
			$email = $formValues['email'];
		}
	} else {
		$errors['email'] = 'errornoemail';
	}

	$subject = '';
	if(!empty($formValues['subject'])) {
		$subject = filter_var($formValues['subject'], FILTER_SANITIZE_STRING);
	}

	if(isset($formValues['message']) && !empty($formValues['message'])) {
		$message = filter_var($formValues['message'], FILTER_SANITIZE_STRING);
	} else {
		$errors['message'] = 'errornomessage';
	}

	if(!$errors) {
		$headers = [];

		$headers['MIME-Version'] = '1.0';
		$headers['Content-type'] = 'text/plain; charset=utf-8';
		if(!empty($name)) {
			$headers['From'] = $name . ' <' . $email . '>';
		} else {
			$headers['From'] = $email;
		}

		if(mail($configuration['contactemail'], $subject, $message, $headers)) {
?>
<p>Thank you</p>
<?php
		} else {
?>
<p>Failed to send message</p>
<?php
		}
	}
}

if(($_SERVER['REQUEST_METHOD'] === 'GET' && count($_GET) == 0) || !empty($errors)) {
?>
<p></p>
<form action="/contact"  method="post">
	<div class="form-group">
		<label for="name"><?= $tl->get('namelabel', 'html') ?></label>
		<input type="text" class="form-control" id="name" name="name" placeholder="<?= $tl->get('nameplaceholder', 'htmlattr') ?>" value="<?= htmlspecialchars($name, ENT_COMPAT) ?>">
	</div>
	<div class="form-group">
		<label for="email"><?= $tl->get('emaillabel', 'html') ?></label>
		<small id="emailerror" class="form-text text-danger"><?= isset($errors['email']) ? $tl->get($errors['email'], 'html') : '' ?></small>
		<input type="email" class="form-control" id="email" name="email" aria-describedby="emailhelp" placeholder="<?= $tl->get('emailplaceholder', 'htmlattr') ?>" value="<?= htmlspecialchars($email, ENT_COMPAT) ?>" required>
		<small id="emailhelp" class="form-text text-muted"><?= $tl->get('emailhelp', 'html') ?></small>
	</div>
	<div class="form-group">
		<label for="subject"><?= $tl->get('subjectlabel', 'html') ?></label>
		<input type="text" class="form-control" id="subject" name="subject" placeholder="<?= $tl->get('subjectplaceholder', 'htmlattr') ?>" value="<?= htmlspecialchars($subject, ENT_COMPAT) ?>">
	</div>
	<div class="form-group">
		<small id="emailerror" class="form-text text-danger"><?= isset($errors['message']) ? $tl->get($errors['message'], 'html') : '' ?></small>
		<label for="message"><?= $tl->get('messagelabel', 'html') ?></label>
		<textarea id="message" class="form-control" name="message" placeholder="<?= $tl->get('messageplaceholder', 'htmlattr') ?>" rows="15" required><?= htmlspecialchars($message, ENT_NOQUOTES) ?></textarea>
	</div>
	<button type="submit" class="btn btn-primary mb-2"><?= $tl->get('submit', 'html') ?></button>
</form>
<?php } ?>