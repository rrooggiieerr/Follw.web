<?php
// Fixes false "Variable is undefined" validation errors
/* @var boolean $evaluate */
/* @var String $filename */

// Preconnect to third party domains to improve page loading speed
header('Link: <https://unpkg.com/>; rel=preconnect', FALSE);
header('Link: <https://unpkg.com/>; rel=dns-prefetch', FALSE);
?>
<!doctype html>
<html lang="en">
	<head>
		<title>Follw · Sharing your location with privacy</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
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
		</style>
	</head>
	<body>
		<main>
			<div class="container">
				<div class="jumbotron">
					<h1><a href="/">Follw</a> <small class="h4 text-muted">· Sharing your location with privacy</small></h1>
				</div>
				<div>
<?php
if($evaluate) {
	include($filename);
} else {
	print(file_get_contents($filename));
}
?>
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
	</body>
</html>