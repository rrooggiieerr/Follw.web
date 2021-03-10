<?php
require_once(dirname(__DIR__) . '/models/Translation.php');

// Fixes false "Variable is undefined" validation errors
/* @var FollowID $id */
/* @var Location $location */

global $configuration;
global $protocol;

// Preconnect to third party domains to improve page loading speed
header('Link: <https://unpkg.com/>; rel=preconnect', FALSE);
header('Link: <https://a.tile.openstreetmap.org/>; rel=preconnect', FALSE);
header('Link: <https://b.tile.openstreetmap.org/>; rel=preconnect', FALSE);
header('Link: <https://c.tile.openstreetmap.org/>; rel=preconnect', FALSE);
header('Link: <https://unpkg.com/>; rel=dns-prefetch', FALSE);
header('Link: <https://a.tile.openstreetmap.org/>; rel=dns-prefetch', FALSE);
header('Link: <https://b.tile.openstreetmap.org/>; rel=dns-prefetch', FALSE);
header('Link: <https://c.tile.openstreetmap.org/>; rel=dns-prefetch', FALSE);

// Only allow Service Worker for this scope
if(isset($configuration['features']['follow']['pwa']) &&
		$configuration['features']['follow']['pwa'] == TRUE) {
	header('Service-Worker-Allowed: /' . $id->encode() . '/');
}

$tl = new Translation('follow');
header('Content-Language: ' . $tl->language);

$title = $tl->get('nolocation');
$_location = 'null';
$_accuracy = 'null';

if(isset($location)) {
	$title = $tl->get('ishere', NULL, $id['alias']);
	$_location = '[' . $location['latitude'] . ', ' . $location['longitude'] . ']';
	if(isset($location['accuracy'])) {
		$_accuracy = $location['accuracy'];
	}
}
?>
<!doctype html>
<html lang="<?= $tl->language ?>">
	<head>
		<title><?= htmlspecialchars($title, ENT_NOQUOTES) ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<meta name="robots" content="noindex" />
		<meta name="referrer" content="no-referrer" />
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-title" content="Follw <?= htmlspecialchars($id['alias']) ?>">
<?php if(isset($configuration['features']['follow']['pwa']) &&
		$configuration['features']['follow']['pwa'] == TRUE) { ?>
		<script>
			// Start Service Worker as soon as possible to also cache icons and other resource files for offline usage
			if(window.navigator && navigator.serviceWorker) {
				navigator.serviceWorker.register("/<?=$id->encode()?>/serviceworker.js", { scope: "/<?=$id->encode()?>/" }).then(() => {
					console.debug("Registered Service Worker");
					//TODO Try to unregister Service Worker and Cache?
				}).catch(function(error) {
					console.error("Failed to register Service Worker:", error);
				});
			} else {
				console.info("Service Worker not available");
			}
		</script>
<?php } else { ?>
		<script>
			if(window.navigator && navigator.serviceWorker) {
				navigator.serviceWorker.getRegistration("/<?= $id->encode() ?>/").then(function(registration) {
					if(registration){
						console.debug("Unregistering Service Worker");
						registration.unregister()
					} else {
						console.debug("No Service Worker to unregister");
					}
				});
			}

			if(window.caches) {
				caches.open("<?= $id->encode() ?>").then((cache) => {
					console.log(cache);
					//TODO caches.delete("<?= $id->encode() ?>");
				});
			}
		</script>
<?php } ?>
		<link rel="manifest" href="/<?=$id->encode()?>/manifest.webmanifest" />
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
<?php // Facebook Open Graph ?>
		<meta property="og:url" content="<?= $protocol . $_SERVER['HTTP_HOST'] . '/' . $id->encode() . '/'?>">
		<meta property="og:title" content="<?= htmlspecialchars($title, ENT_COMPAT) ?>">
		<meta property="og:type" content="website">
		<meta property="og:description" content="<?= htmlspecialchars($title, ENT_COMPAT) ?>">
		<meta property="og:locale" content="<?= $tl->language ?>">
<?php if(isset($location)) { ?>
		<!-- meta property="og:image" content="https://osm-static-maps.herokuapp.com/?geojson=<?= urlencode(json_encode([ 'type' => 'Point', 'coordinates'=> [ $location['longitude'], $location['latitude'] ]])) ?>&zoom=14&width=600&height=314&imagemin=true" -->
		<meta property="og:image" content="https://osm-static-maps.herokuapp.com/?center=<?= $location['longitude'] ?>,<?= $location['latitude'] ?>&zoom=14&width=600&height=314&imagemin=true">
		<meta property="og:image:secure_url" content="https://osm-static-maps.herokuapp.com/?center=<?= $location['longitude'] ?>,<?= $location['latitude'] ?>&zoom=14&width=600&height=314&imagemin=true">
		<meta property="og:image:width" content="600">
		<meta property="og:image:height" content="314">
<?php } else { ?>
		<meta property="og:image" content="/apple-touch-icon-180x180.png">
		<meta property="og:image:width" content="180">
		<meta property="og:image:height" content="180">
<?php } ?>
<?php // Twitter ?>
		<meta name="twitter:site" content="@follw_app?>">
		<meta name="twitter:title" content="<?= htmlspecialchars($title, ENT_COMPAT) ?>">
		<meta name="twitter:description" content="<?= htmlspecialchars($title, ENT_COMPAT) ?>">
<?php if(isset($location)) { ?>
		<meta name="twitter:card" content="summary_large_image">
		<meta name="twitter:image" content="https://osm-static-maps.herokuapp.com/?center=<?= $location['longitude'] ?>,<?= $location['latitude'] ?>&zoom=14&width=600&height=314&imagemin=true">
<?php } else { ?>
		<meta name="twitter:card" content="summary">
		<meta name="twitter:image" content="/apple-touch-icon-180x180.png">
<?php } ?>
<?php // Styles ?>
		<link rel="stylesheet" href="https://unpkg.com/bootstrap@4.6.0/dist/css/bootstrap.min.css"
			integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l"
			crossorigin="anonymous"/>
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
			integrity="sha384-VzLXTJGPSyTLX6d96AxgkKvE/LRb7ECGyTxuwtpjHnVWVZs2gp5RDjeM/tgBnVdM"
			crossorigin="anonymous"/>
		<link rel="stylesheet" href="https://unpkg.com/font-awesome@4.7.0/css/font-awesome.css"
			integrity="sha384-FckWOBo7yuyMS7In0aXZ0aoVvnInlnFMwCv77x9sZpFgOonQgnBj1uLwenWVtsEj"
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

			header, footer {
				text-align: center;
				padding: 4px;
			}

			header > * {
				margin-top: 0;
				margin-bottom: 0;
			}

			#coordinates {
				word-spacing: -0.1em;
			}

			#follwMap {
				height: 250px;
			}

			@media (max-width: 575px) {
				.modal-dialog {
					max-width: 100%;
					width: 100%;
					height: 100%;
					margin: 0;
					padding: 0;
				}

				.modal-content {
					height: auto;
					min-height: 100%;
					height: auto;
					border: 0;
					border-radius: 0;
				}
			}
		</style>
<?php // Scripts ?>
		<script src="https://unpkg.com/jquery@3.5.1/dist/jquery.js"
			integrity="sha384-/LjQZzcpTzaYn7qWqRIWYC5l8FWEZ2bIHIz0D73Uzba4pShEcdLdZyZkI4Kv676E"
			crossorigin="anonymous"></script>
		<script src="https://unpkg.com/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"
			integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns"
			crossorigin="anonymous"></script>
		<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
			integrity="sha384-RFZC58YeKApoNsIbBxf4z6JJXmh+geBSgkCQXFyh+4tiFSJmJBt+2FbjxW7Ar16M"
			crossorigin="anonymous"></script>
		<script src="/follw.js" crossorigin="anonymous"></script>
		<script>
			'use strict';
			var id = "<?=$id->encode()?>";

			// Local settings
			// Local settings is stored in local storage and not shared with the server,
			// it contains settings like which tab is shown, map zoom level etc.
			var localSettings = JSON.parse(window.localStorage.getItem(id));
			if(!localSettings) {
				localSettings = {"type": "follow"};
				storeLocalSettings();
			}

			function storeLocalSettings() {
				window.localStorage.setItem(id, JSON.stringify(localSettings));
			}

			document.ontouchmove = function(e) {e.preventDefault()};

			function resizeMap() {
				$("#follwMap").height($("body").innerHeight() - ($("header").outerHeight() + $("footer").outerHeight()));
				follw.invalidateSize();
			}

			window.addEventListener("resize", resizeMap);

			function onLocationChanged(follw, data) {
				if(data != null) {
					var s = data.alias + <?= $tl->get('ishere', 'js', '') ?>;
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
				} else {
					s = follw.translations['nolocation'];
					if($("title").text() != s) {
						$("title").text(s);
						$("#title").text(s);
						resizeMap();
					}

					s = "&nbsp;";
					if($("#coordinates").html() != s) {
						$("#coordinates").html(s);
					}
				}
			}

			// Restore zoom level from local settings
			var zoomlevel = 14;
			if("zoomlevel" in localSettings) {
				zoomlevel = localSettings["zoomlevel"];
			}
			var follw = new Follw("follwMap", `/${id}`, zoomlevel);
			follw.translations["nolocation"] = <?= $tl->get('nolocation', 'js') ?>;
			follw.translations["offline"] = <?= $tl->get('offline', 'js') ?>;
			follw.translations["iddeleted"] = <?= $tl->get('iddeleted', 'js') ?>;
			follw.addEventListener("locationchanged", onLocationChanged);
			follw.addEventListener("offline", () => {
					$("title").text(follw.translations["offline"]);
					$("#title").text(follw.translations["offline"]);
					resizeMap();
					$("#coordinates").html("&nbsp;");
			});
			follw.addEventListener("iddeleted", () => {
				location.reload();
			});
			follw.addEventListener("zoomchanged", (follw, zoomlevel) => {
				// Store zoom level in local settings
				localSettings["zoomlevel"] = zoomlevel;
				storeLocalSettings();
			});

			$(window).scroll(() => {
				try {
					document.body.requestFullscreen();
				} catch(error) {
					console.error(error);
				}
			});

			// 
			function showStaticModal(title, content) {
				if($("#static-modal").length == 0) {
					var staticModal = $(`<div id="static-modal" class="modal">
	<div class="modal-dialog modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="<?= $tl->get('close', 'htmlattr') ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class="fa fa-times-circle"></span>  <?= $tl->get('close', 'html') ?></button>
			</div>
		</div>
	</div>
</div>`);
					$("main").append(staticModal);
				}

				$("#static-modal .modal-title").text(title);
				if(typeof content === "object") {
					$("#static-modal .modal-body").html(content);
					$("#static-modal").modal("show");
				} else {
					$("#static-modal .modal-body").load(content + "?raw", () => {
						$("#static-modal").modal("show");
					})
				}
			}

			$().ready(() => {
				$(".privacylink").click(function(event) {
					event.preventDefault();
					showStaticModal($(this).text(), $(this).attr("href"));
				});
				
				resizeMap()
				follw.setMarker(<?= $_location ?>, <?= $_accuracy ?>);
<?php if(isset($location))  { ?>
				$("#coordinates").text(follw.prettyPrintCoordinates(<?= $location['latitude'] ?>, <?= $location['longitude'] ?>))
<?php } ?>
				follw.startUpdate();
			});
		</script>
	</head>
	<body>
		<main>
			<header>
				<h1 id="title"><?= htmlspecialchars($title, ENT_NOQUOTES) ?></h1>
				<div id="coordinates">&nbsp;</div>
			</header>
			<div id="follwMap"></div>
			<footer>
				<a href="<?= $protocol ?><?= $_SERVER['HTTP_HOST'] ?>" target="_blank" rel="noopener noreferrer"><?= $tl->get('credits', 'html') ?></a> · <a href="/privacy" class="privacylink" target="_blank" rel="noopener noreferrer"><?= $tl->get('privacystatement', 'html') ?></a>
			</footer>
		</main>
	</body>
</html>