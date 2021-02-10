<?php
require_once(dirname(__DIR__) . '/libs/Translation.php');

// Fixes false "Variable is undefined" validation errors
/* @var FollowID $id */
/* @var Location $location */

global $protocol;

// Preconnect to third party domains to improve page loading speed
header('Link: <https://unpkg.com/>; rel=preconnect');
header('Link: <https://a.tile.openstreetmap.org/>; rel=preconnect');
header('Link: <https://b.tile.openstreetmap.org/>; rel=preconnect');
header('Link: <https://c.tile.openstreetmap.org/>; rel=preconnect');
header('Link: <https://unpkg.com/>; rel=dns-prefetch');
header('Link: <https://a.tile.openstreetmap.org/>; rel=dns-prefetch');
header('Link: <https://b.tile.openstreetmap.org/>; rel=dns-prefetch');
header('Link: <https://c.tile.openstreetmap.org/>; rel=dns-prefetch');

$translation = new Translation('follow');
header('Content-Language: ' . $translation->language);

$title = htmlspecialchars($translation->translations['nolocation']);
$_location = 'null';
$_accuracy = 'null';

if(isset($location)) {
	$title = htmlspecialchars($id['alias'] . ' ' . $translation->translations['ishere']);
	$_location = '[' . $location['latitude'] . ', ' . $location['longitude'] . ']';
	if(isset($location['accuracy'])) {
		$_accuracy = $location['accuracy'];
	}
}
?>
<!doctype html>
<html lang="<?= $translation->language ?>">
	<head>
		<title><?= $title ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<meta name="robots" content="noindex" />
		<meta name="referrer" content="no-referrer" />
		<link rel="manifest" href="/<?=$id->encode()?>/manifest.webmanifest" />
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-title" content="Follw <?= htmlspecialchars($id['alias']) ?>">
<?php // Icons ?>
		<link rel="icon" href="/favicon-96x96.png" sizes="96x96" type="image/png">
		<link rel="icon" href="/favicon-64x64.png" sizes="64x64" type="image/png">
		<link rel="icon" href="/favicon-48x48.png" sizes="48x48" type="image/png">
		<link rel="icon" href="/favicon-32x32.png" sizes="32x32" type="image/png">
		<link rel="icon" href="/favicon-16x16.png" sizes="16x16" type="image/png">
		<link rel="icon" href="/favicon.svg" sizes="any" type="image/svg+xml">
		<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#006400">
		<meta name="msapplication-config" content="/<?=$id->encode()?>/browserconfig.xml">
		<meta name="msapplication-TileColor" content="#006400">
		<meta name="msapplication-TileImage" content="/mstile-144x144.png">
<?php // Styles ?>
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
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
		<script src="https://unpkg.com/jquery@3.5.1/dist/jquery.js"
			integrity="sha384-/LjQZzcpTzaYn7qWqRIWYC5l8FWEZ2bIHIz0D73Uzba4pShEcdLdZyZkI4Kv676E"
			crossorigin="anonymous"></script>
		<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
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

			function onLocationChange(follw, data) {
				if(data != null) {
					var s = data.alias + <?= json_encode(' ' . $translation->translations['ishere']) ?>;
					var resize = false;
					if($("title").text() != s) {
						$("title").text(s);
						$("#title").text(s);
						resize = true;
					}

					s = follw.prettyPrintCoordinates(data.latitude, data.longitude);
					if($("#coordinates").text() != s) {
						$("#coordinates").text(s);
						resize = true;
					}

					if(resize) {
						resizeMap();
					}

					// Update links
					$("a#navigate").attr("href", "https://www.google.com/maps/dir/?api=1&destination=" + data.latitude + "," + data.longitude);
					$("a#navigate").css("visibility", "visible");
				} else {
					s = follw.nolocation;
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
			follw.nolocation = <?= json_encode($translation->translations['nolocation']) ?>;
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
			<a href="<?= $protocol ?><?= $_SERVER['HTTP_HOST'] ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($translation->translations['credits']) ?></a> · <a href="/privacy" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($translation->translations['privacystatement']) ?></a>
		</div>
	</body>
</html>