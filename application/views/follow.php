<?php
// Fixes false "Variable is undefined" validation errors
/* @var FollowID $id */
/* @var Location $location */
/* @var String $protocol */

$title = 'No location is currently being shared';
$_location = 'null';
$_accuracy = 'null';

if(isset($location)) {
	$title = htmlspecialchars($id['alias']) . ' is here';
	$_location = '[' . $location['latitude'] . ', ' . $location['longitude'] . ']';
	if(isset($location['accuracy'])) {
		$_accuracy = $location['accuracy'];
	}
}
?>
<!doctype html>
<html lang="en">
	<head>
		<title><?= $title ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<meta name="robots" content="noindex" />
		<link rel="manifest" href="/<?=$id->encode()?>/manifest.webmanifest" />
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
		<link rel="stylesheet" href="//unpkg.com/leaflet@1.7.1/dist/leaflet.css"
			integrity="sha384-VzLXTJGPSyTLX6d96AxgkKvE/LRb7ECGyTxuwtpjHnVWVZs2gp5RDjeM/tgBnVdM"
			crossorigin="anonymous"/>
		<style>
			html, body {
				height: 100%;
				width: 100vw;
				padding: 0;
				margin: 0;
				overflow: hidden;
				font-family: sans-serif;
			}

			#header, #footer {
				text-align: center;
				padding: 8px;
			}

			#header > *:first-of-type, #footer > *:first-of-type {
				margin-top: 0;
			}

			#header > *:last-of-type, #footer > *:last-of-type {
				margin-bottom: 0;
			}

			#coordinates {
				word-spacing: -0.1em;
			}

			#follwMap {
				height: 250px;
			}

			#navigate {
				visibility: hidden;
			}
		</style>
<?php // Scripts ?>
		<script src="//unpkg.com/jquery@3.5.1/dist/jquery.js" crossorigin="anonymous"></script>
		<script src="//unpkg.com/leaflet@1.7.1/dist/leaflet.js"
			integrity="sha384-RFZC58YeKApoNsIbBxf4z6JJXmh+geBSgkCQXFyh+4tiFSJmJBt+2FbjxW7Ar16M"
			crossorigin="anonymous"></script>
		<script src="/follw.js" crossorigin="anonymous"></script>
		<script>
			'use strict';

			document.ontouchmove = function(e) {e.preventDefault()};

			function resizeMap() {
				$("#follwMap").height($("body").innerHeight() - ($("#header").outerHeight() + $("#footer").outerHeight()));
				follw.invalidateSize();
			}

			window.addEventListener("resize", resizeMap);

			function onLocationChange(data) {
				if(data != null) {
					var s = data.alias + " is here";
					if($("title").text() != s) {
						$("title").text(s);
						$("#title").text(s);
						resizeMap();
					}

					s = follw.prettyPrintCoordinates(data.latitude, data.longitude);
					if($("#coordinates").text() != s) {
						$("#coordinates").text(s);
						resizeMap();
					}

					// Update links
					$("a#navigate").attr("href", "https://www.google.com/maps/dir/?api=1&destination=" + data.latitude + "," + data.longitude);
					$("a#navigate").css("visibility", "visible");
				} else {
					s = "No location is currently being shared";
					if($("title").text() != s) {
						$("title").text(s);
						$("#title").text(s);
						$("a#navigate").css("visibility", "hidden");
						resizeMap();
					}
					
					s = "&nbsp;";
					if($("#coordinates").html() != s) {
						$("#coordinates").html(s);
					}
				}
			}

			function onDelete() {
				location.reload();
			}

			var follw = new Follw("follwMap", "/<?=$id->encode()?>", 12);
			follw.onLocationChange(onLocationChange);
			follw.onIDDeleted(onDelete);

			$(window).scroll(function() {
				try {
					document.body.requestFullscreen();
				} catch(error) {
					console.error(error);
				}
			});

			$().ready(function() {
				resizeMap()
	 			follw.setMarker(<?= $_location ?>, <?= $_accuracy ?>);
			});
		</script>
	</head>
	<body>
		<div id="header">
			<h1 id="title"><?= $title ?></h1>
			<div id="coordinates">&nbsp;</div>
		</div>
		<div id="follwMap"></div>
		<div id="footer">
			<a href="<?= $protocol ?><?= $_SERVER['HTTP_HOST'] ?>" target="_blank">Location shared with Follw</a> · <a href="/privacy" target="_blank">Privacy statement</a>
		</div>
	</body>
</html>