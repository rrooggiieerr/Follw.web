<?php
require_once(dirname(__DIR__) . '/models/Translation.php');

// Fixes false "Variable is undefined" validation errors
/* @var String $question */
/* @var TextCaptcha $captcha */
/* @var FormHoneypot $honeypot */
/* @var Obfuscator $obfuscator */
?>
<p><?= $tl->get('contactintro', 'html') ?></p>
<form action="<?= $_SERVER['REQUEST_URI'] ?>"  method="post">
	<input type="hidden" name="sessionid" value="<?= session_id() ?>"/>
	<div class="form-group">
		<label for="name"><?= $tl->get('namelabel', 'html') ?></label>
		<input type="text" class="form-control" id="name" name="<?= $obfuscator->obfuscate('name') ?>" placeholder="<?= $tl->get('nameplaceholder', 'htmlattr') ?>" value="<?= htmlspecialchars($name, ENT_COMPAT) ?>">
	</div>
	<div class="form-group">
		<label for="email"><?= $tl->get('emaillabel', 'html') ?></label>
		<small id="emailerror" class="form-text text-danger"><?= isset($errors['email']) ? $tl->get($errors['email'], 'html') : '' ?></small>
		<input type="email" class="form-control" id="email" name="<?= $obfuscator->obfuscate('email') ?>" aria-describedby="emailhelp" placeholder="<?= $tl->get('emailplaceholder', 'htmlattr') ?>" value="<?= htmlspecialchars($email, ENT_COMPAT) ?>" required>
		<small id="emailhelp" class="form-text text-muted"><?= $tl->get('emailhelp', 'html') ?></small>
	</div>
	<div class="form-group">
		<label for="subject"><?= $tl->get('subjectlabel', 'html') ?></label>
		<input type="text" class="form-control" id="subject" name="<?= $obfuscator->obfuscate('subject') ?>" placeholder="<?= $tl->get('subjectplaceholder', 'htmlattr') ?>" value="<?= htmlspecialchars($subject, ENT_COMPAT) ?>">
	</div>
	<div class="form-group">
		<small id="messageerror" class="form-text text-danger"><?= isset($errors['message']) ? $tl->get($errors['message'], 'html') : '' ?></small>
		<label for="message"><?= $tl->get('messagelabel', 'html') ?></label>
		<textarea id="message" class="form-control" name="<?= $obfuscator->obfuscate('message') ?>" placeholder="<?= $tl->get('messageplaceholder', 'htmlattr') ?>" rows="15" required><?= htmlspecialchars($message, ENT_NOQUOTES) ?></textarea>
	</div>
<?php if($captcha !== NULL) { ?>
	<div id="captcha" class="form-group">
		<p><?= $tl->get('captchaintro', 'html') ?></p>
		<p id="captchaquestion"><?= htmlspecialchars($captcha->question) ?></p>
		<small id="messageerror" class="form-text text-danger"><?= isset($errors['captchaanswer']) ? $tl->get($errors['captchaanswer'], 'html') : '' ?></small>
		<div id="captchaanswer" class="form-group">
			<label for="captchaanswer"><?= $tl->get('youranswer', 'html') ?></label>
			<input type="text" id="captchaanswer" class="form-control" name="<?= $obfuscator->obfuscate('captchaanswer') ?>"/>
		</div>
	</div>
<?php } ?>
	<button type="submit" class="btn btn-primary mb-2"><?= $tl->get('submit', 'html') ?></button>
</form>