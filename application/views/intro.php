<!doctype html>
<html lang="en">
	<head>
		<title>Follw · Sharing your location with privacy</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
<?php // Icons
/* TODO
		<link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">
		<link rel="icon" href="/favicon-32x32.png" sizes="32x32" type="image/png">
		<link rel="icon" href="/favicon-16x16.png" sizes="16x16" type="image/png">
		<link rel="icon" href="/favicon.ico">
		<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#563d7c">
		<meta name="msapplication-config" content="/browserconfig.xml">
		<meta name="theme-color" content="#563d7c">
*/
?>
<?php // Styles ?>
		<link rel="stylesheet" href="https://unpkg.com/bootstrap@4.5.3/dist/css/bootstrap.min.css"
			integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
			crossorigin="anonymous"/>
		<style>
		</style>
	</head>
	<body>
		<main>
			<div class="jumbotron">
				<div class="container">
					<h1>Follw <small class="h4 text-muted">· Sharing your location with privacy</small></h1>
					<p>Follw is a privacy focused location sharing service. Only a unique Sharing ID is assigned and
					no user credentials, cookies and other sensitive information are stored on the Follw servers.</p>
					<p>Whenever a location is expired it is deleted, no unnecessary location history is stored. If you
					decide to delete your Sharing ID all location details are removed from the Follw servers.</p>
					<p>Start sharing any location, it doesn't have to be where you currently are, by generating a Sharing ID.
					Configure who can follow you when, prospone or retract following permission anytime and limit the follow
					duration. You are in control.</p>
					<p><a class="btn btn-primary btn-lg" href="/generatesharingid" role="button">Start sharing any location &raquo;</a></p>
				</div>
			</div>
			<div class="container">
				<div class="row">
					<div class="col-lg">
						<div class="row">
							<div class="col-md">
								<h2>Privacy focused</h2>
								<p>Follw doesn't store any credentials other than your Sharing ID, location and configuration
								parameters.</p>
								<p><a class="btn btn-secondary" href="#" role="button">Read the Privacy Statement &raquo;</a></p>
							</div>
							<div class="col-md">
								<h2>Add free</h2>
								<p>The Follw bussiness model is not based on advertisments and is free for personal and low volume
								use.</p>
								<p><a class="btn btn-secondary" href="#" role="button">Read more &raquo;</a></p>
							</div>
						</div>
					</div>
					<div class="col-lg">
						<div class="row">
							<div class="col-md">
								<h2>Documented API</h2>
								<p>Easy integration with mapping software, tracking hardware and your custom software.</p>
								<p><a class="btn btn-secondary" href="/api" role="button" target="_blank">Read the documentation &raquo;</a></p>
							</div>
							<div class="col-md">
								<h2>Open Source</h2>
								<p>You can validate the security and workings of Follw, copy it, modify it to your own
								needs and host it yourself.</p>
								<p><a class="btn btn-secondary" href="https://github.com/rrooggiieerr/Follw.web" role="button" target="_blank">Get the sourcode on GitHub &raquo;</a></p>
							</div>
						</div>
					</div>
				</div>
<?php include_once('footer.php');?>
			</div>
		</main>
<?php	// Scripts
		// Placed at the end of the document so the pages load faster ?>
		<script src="https://unpkg.com/jquery@3.5.1/dist/jquery.min.js"
			integrity="sha384-ZvpUoO/+PpLXR1lu4jmpXWu80pZlYUAfxl5NsBMWOEPSjUn/6Z/hRTt8+pR6L4N2"
			crossorigin="anonymous"></script>
		<script src="https://unpkg.com/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
			integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
			crossorigin="anonymous"></script>
	</body>
</html>