<?php
// Fixes false "Variable is not defined" validation errors for variables created in other files
/* @var Integer $id */
/* @var Integer $location */

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
<?php // Styles ?>
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
			integrity="sha384-VzLXTJGPSyTLX6d96AxgkKvE/LRb7ECGyTxuwtpjHnVWVZs2gp5RDjeM/tgBnVdM"
			crossorigin="anonymous"/>
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

			#follwMap {
				height: 250px;
			}
			
			#navigate {
				visibility: hidden;
			}
		</style>
<?php // Scripts ?>
		<script src="//unpkg.com/jquery@3.5.1/dist/jquery.js" crossorigin="anonymous"></script>
		<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
			integrity="sha384-RFZC58YeKApoNsIbBxf4z6JJXmh+geBSgkCQXFyh+4tiFSJmJBt+2FbjxW7Ar16M"
			crossorigin="anonymous"></script>
		<script src="/follw.js" crossorigin="anonymous"></script>
		<script>
			document.ontouchmove = function(e) {e.preventDefault()};
			
			function resizeMap() {
				$("#follwMap").height($("body").innerHeight() - ($("#header").outerHeight() + $("#footer").outerHeight()));
				follw.invalidateSize();
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

 			var follw = new Follw("follwMap", "/<?=bin2hex($id)?>", 12);
 			follw.onLocationChange(onLocationChange);
 			follw.onIDDeleted(onDelete);
 			
 			$().ready(function() {
	 			follw.setMarker(<?= $_location ?>, <?= $_accuracy ?>);
 				resizeMap()
 			});
		</script>
	</head>
	<body>
		<div id="header">
			<h1 id="title"><?= $title ?></h1>
		</div>
		<div id="follwMap"></div>
		<div id="footer">
			<p><a href="#" target="_blank" id="navigate">Navigate to this location</a></p>
		</div>
	</body>
</html>