<?php
if($location == NULL) {
		$title = "No location is currently being shared";
		$_location = 'null';
		$_accuracy = 'null';
} else {
		$title = $location['alias'] . " is here";
		$_location = '[' . $location['latitude'] . ', ' . $location['longitude'] . ']';
		if($location['accuracy']  == NULL)
			$_accuracy = 'null';
		else
			$_accuracy = $location['accuracy'];
}
?>
<!doctype html>
<html lang="en">
	<head>
		<title><?= $title ?></title>
		<link rel="manifest" href="/<?=bin2hex($id)?>/manifest.webmanifest">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<link rel="stylesheet" href="//unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin=""/>
		<style>
			body {
				padding: 0;
				margin: 0;
				font-family: sans-serif;
			}

			html, body {
				height: 100%;
				width: 100vw;
				padding: 0;
				margin: 0;
			}

			#header, #footer {
				padding: 8px;
			}
			
			#header > *:first-of-type, #footer > *:first-of-type {
				margin-top: 0;
			}

			#header > *:last-of-type, #footer > *:last-of-type {
				margin-bottom: 0;
			}

			#iAmHereMap {
				height: 250px;
			}
			
			#navigate {
				visibility: hidden;
			}
		</style>
		<script src="//unpkg.com/jquery@3.5.1/dist/jquery.js" crossorigin="anonymous"></script>
		<script src="//unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin="anonymous"></script>
		<script src="/IAmHere.js"></script>
		<script>
			document.ontouchmove = function(e) {e.preventDefault()};
			
			function resizeMap() {
				$("#iAmHereMap").height($("body").innerHeight() - ($("#header").outerHeight() + $("#footer").outerHeight()));
				iAmHere.invalidateSize();
			}
			
			window.addEventListener("resize", resizeMap);
			
			function onLocationChange(data) {
				if(data != null) {
					s = data.alias + " is here";
					if($("title").text() != s) {
						$("title").text(s);
						$("#title").text(s);
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
				}
			}
			
			function onDelete() {
				location.reload();
			}

 			var iAmHere = new IAmHere("iAmHereMap", "/<?=bin2hex($id)?>", 12);
 			iAmHere.onLocationChange(onLocationChange);
 			iAmHere.onIDDeleted(onDelete);
 			
 			$().ready(function() {
	 			iAmHere.setLocation(<?= $_location ?>, <?= $_accuracy ?>);
 				resizeMap()
 			});
		</script>
	</head>
	<body>
		<div id="header">
			<h1 id="title"><?= $title ?></h1>
		</div>
		<div id="iAmHereMap"></div>
		<div id="footer">
			<p><a href="#" target="_blank" id="navigate">Navigate to this location</a></p>
		</div>
	</body>
</html>