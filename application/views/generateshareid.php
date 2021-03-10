<?php
require_once(dirname(__DIR__) . '/models/Translation.php');

// Fixes false "Variable is undefined" validation errors
/* @var String $question */
/* @var TextCaptcha $captcha */
/* @var FormHoneypot $honeypot */
/* @var Obfuscator $obfuscator */

global $protocol;

// Preconnect to third party domains to improve page loading speed
header('Link: <https://unpkg.com/>; rel=preconnect', FALSE);
header('Link: <https://unpkg.com/>; rel=dns-prefetch', FALSE);

$tr = new Translation('generateshareid');
header('Content-Language: ' . $tr->language);
?>
<!doctype html>
<html lang="<?= $tr->language ?>">
	<head>
		<title>Follw · Sharing your location with privacy</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<meta name="robots" content="noindex" />
<?php // Icons ?>
		<link rel="icon" href="/favicon-96x96.png" sizes="96x96" type="image/png">
		<link rel="icon" href="/favicon-64x64.png" sizes="64x64" type="image/png">
		<link rel="icon" href="/favicon-48x48.png" sizes="48x48" type="image/png">
		<link rel="icon" href="/favicon-32x32.png" sizes="32x32" type="image/png">
		<link rel="icon" href="/favicon-16x16.png" sizes="16x16" type="image/png">
		<link rel="icon" href="/favicon.svg" sizes="any" type="image/svg+xml">
		<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#006400">
<?php // Styles ?>
		<link rel="stylesheet" href="https://unpkg.com/bootstrap@4.6.0/dist/css/bootstrap.min.css"
			integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l"
			crossorigin="anonymous"/>
		<link rel="stylesheet" href="https://unpkg.com/font-awesome@4.7.0/css/font-awesome.css"
			integrity="sha384-FckWOBo7yuyMS7In0aXZ0aoVvnInlnFMwCv77x9sZpFgOonQgnBj1uLwenWVtsEj"
			crossorigin="anonymous"/>
		<style>
			code {
				display: block;
				white-space: pre;
				background-color: #F8F8F8;
				margin-bottom: 1rem;
			}

			p > code {
				display: inline;
			}

			@media (max-width: 575px) {
				header, footer {
					text-align: center;
					padding: 4px;
				}
			}

			@media (max-width: 991px) {
				.container {
					max-width: 960px;
				}
			}

			footer {
				font-size: 0.8rem;
			}

			footer h5 {
				font-size: 1rem;
			}

			#termsconditions {
				height: 200px;
				overflow: auto;
			}

			<?= $honeypot->css() ?>
		</style>
	</head>
	<body>
		<main>
			<div class="container">
				<div class="jumbotron">
					<h1><a href="/">Follw</a> <small class="h4 text-muted">· Sharing your location with privacy</small></h1>
				</div>
				<div>
					<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" autocomplete="off">
						<input type="hidden" name="sessionid" value="<?= session_id() ?>"/>
<?php if($captcha !== NULL) { ?>
						<div id="captcha">
							<p><?= $tr->get('captchaintro', 'html') ?></p>
							<p id="captchaquestion"><?= htmlspecialchars($captcha->question) ?></p>
							<div id="captchaanswer" class="form-group">
								<label for="captchaanswer"><?= $tr->get('youranswer', 'html') ?></label>
								<input type="text" id="captchaanswer" class="form-control" name="<?= $obfuscator->obfuscate('captchaanswer') ?>" autofocus="autofocus"/>
							</div>
						</div>
<?php } ?>
						<?= $honeypot->html() ?>
						<div class="form-group">
							<div id="termsconditions" class="form-control form-control-sm">
<?= file_get_contents((dirname(__DIR__) . '/views/terms.html')) ?>
							</div>
							<div id="agreetermsconditions" class="form-check">
								<input class="form-check-input" type="checkbox" id="agreetermsconditionscheckbox" name="<?= $obfuscator->obfuscate('agreetermsconditions') ?>" value="true"/>
								<label class="form-check-label" for="defaultCheck1" for="agreetermsconditionscheckbox"><?= $tr->get('agreetermsconditions', 'html') ?></label>
							</div>
						</div>
						<div id="submit">
							<button type="submit" class="btn btn-primary"><?= $tr->get('submit', 'html') ?></button>
						</div>
					</form>
				</div>
<?php include_once('footer.php');?>
			</div>
		</main>
<?php	// Scripts
		// Placed at the end of the document so the pages load faster ?>
		<script src="https://unpkg.com/jquery@3.5.1/dist/jquery.min.js"
			integrity="sha384-ZvpUoO/+PpLXR1lu4jmpXWu80pZlYUAfxl5NsBMWOEPSjUn/6Z/hRTt8+pR6L4N2"
			crossorigin="anonymous"></script>
		<script src="https://unpkg.com/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"
			integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns"
			crossorigin="anonymous"></script>
		<script>
			'use strict';
			<?= $honeypot->js() ?>
		</script>
	</body>
</html>
