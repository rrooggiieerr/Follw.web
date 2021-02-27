<?php
require_once(dirname(__DIR__) . '/models/Translation.php');

// Fixes false "Variable is undefined" validation errors
/* @var String $question */
/* @var TextCaptcha $captcha */
/* @var FormHoneypot $honeypot */
/* @var Obfuscator $obfuscator */

global $protocol;

$tr = new Translation('generateshareid');
header('Content-Language: ' . $tr->language);
?>
<!doctype html>
<html lang="<?= $tr->language ?>">
	<head>
		<title>Captcha</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<meta name="robots" content="noindex" />
		<style>
			html, body {
				width: 100vw;
				height: 100%;
				overflow: hidden;
				margin: 0;
				border: 0;
				padding: 0;
				font-family: sans-serif;
			}

			#content {
				width: 100vw;
				height: 100%;
			}

			#content form {
				box-sizing: border-box;
				width: 50vw;
				margin: 0 auto;
			}

			#captcha {
				margin-bottom: 8px;
			}

			#content form textarea {
				width: 100%;
			}

			#content form #submit {
				width: 100%;
				text-align: center;
			}

			#content form button[type=submit] {
				margin: 0 auto;
			}

			#footer {
				width: 100vw;
				position: absolute;
				bottom: 0;
				text-align: center;
			}

			#footercontent {
				padding: 8px 0;
			}

			<?= $honeypot->css() ?>
		</style>
		<script>
			'use strict';
		</script>
	</head>
	<body>
		<div id="content">
			<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" autocomplete="off">
				<input type="hidden" name="sessionid" value="<?= session_id() ?>"/>
<?php if($captcha !== NULL) { ?>
				<div id="captcha">
					<div id="captchaquestion"><?= htmlspecialchars($captcha->question) ?></div>
					<div id="captchaanswer">
						<?= $tr->get('youranswer', 'html') ?>: <input type="text" name="<?= $obfuscator->obfuscate('captchaanswer') ?>" autofocus="autofocus"/><br/>
					</div>
				</div>
<?php } ?>
				<?= $honeypot->html() ?>
				<div id="termsconditions">
					<textarea><?php
$filename = dirname(__DIR__) . '/translations/termsconditions.en.txt';
print(htmlspecialchars(file_get_contents($filename)));
?></textarea>
				</div>
				<div id="agreetermsconditions">
					<input type="checkbox" name="<?= $obfuscator->obfuscate('agreetermsconditions') ?>" value="true"/> <?= $tr->get('agreetermsconditions', 'html') ?><br/>
				</div>
				<div id="submit">
					<button type="submit"><?= $tr->get('submit', 'html') ?></button>
				</div>
			</form>
		</div>
		<div id="footer">
			<div id="footercontent">
				<a href="<?= $protocol ?><?= $_SERVER['HTTP_HOST'] ?>" target="_blank" rel="noopener noreferrer"><?= $tr->get('credits', 'html') ?></a> · <a href="/privacy" target="_blank" rel="noopener noreferrer"><?= $tr->get('privacystatement', 'html') ?></a>
			</div>
		</div>
		<script>
			<?= $honeypot->js() ?>
		</script>
	</body>
</html>
