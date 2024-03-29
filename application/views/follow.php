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
<?php // Unregister Service Worker ?>
			if(window.navigator && navigator.serviceWorker) {
				navigator.serviceWorker.getRegistration("/<?= $id->encode() ?>/").then((registration) => {
					if(registration){
						console.debug("Unregistering Service Worker");
						registration.unregister();
					} else {
						console.debug("No Service Worker to unregister");
					}
				});
			}

<?php // Delete Local Cache ?>
			if(window.caches) {
				caches.delete("<?= $id->encode() ?>").then((success) => {
					if(success) {
						console.debug("Deleted Local Cache");
					} else {
						console.debug("No Local Cache to be deleted");
					}
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
<?php // Twitter
	  if(isset($configuration['twitterhandle'])) {
?>
		<meta name="twitter:site" content="<?= $configuration['twitterhandle'] ?>">
<?php } ?>
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
		<link rel="stylesheet" href="https://unpkg.com/font-awesome@4.7.0/css/font-awesome.css"
			integrity="sha384-FckWOBo7yuyMS7In0aXZ0aoVvnInlnFMwCv77x9sZpFgOonQgnBj1uLwenWVtsEj"
			crossorigin="anonymous"/>
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
			integrity="sha384-VzLXTJGPSyTLX6d96AxgkKvE/LRb7ECGyTxuwtpjHnVWVZs2gp5RDjeM/tgBnVdM"
			crossorigin="anonymous"/>
		<link rel="stylesheet" href="https://unpkg.com/leaflet.locatecontrol@0.73.0/dist/L.Control.Locate.min.css"
			integrity="sha384-vPNGCZwbWwO+u7VXCcmLEJRcz/YmtXGdC3LOF8O4/IvddhfpYZI1O0tJszYkbsD2"
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

			.modal-body > :last-child {
				margin-bottom: 0 !important;
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
		<script src="https://unpkg.com/leaflet.locatecontrol@0.73.0/dist/L.Control.Locate.min.js"
			crossorigin="anonymous"></script>
		<script src="/follw.js" crossorigin="anonymous"></script>
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
			<div id="static-modal" class="modal">
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
							<button type="button" class="btn btn-primary" data-dismiss="modal"><span class="fa fa-times-circle"></span> <?= $tl->get('close', 'html') ?></button>
						</div>
					</div>
				</div>
			</div>
		</main>
		<script>
			'use strict';

			// 
			function showStaticModal(title, content) {
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

			$(".privacylink").click(function(event) {
				event.preventDefault();
				showStaticModal($(this).text(), $(this).attr("href"));
			});

			var id = "<?=$id->encode()?>";

			// Local settings is stored in local storage and not shared with the server,
			// it contains settings like map zoom level etc.
			var localSettings = null;

			function readLocalSettings() {
				localSettings = JSON.parse(window.localStorage.getItem(id));
				if(!localSettings) {
					localSettings = {"type": "follow"};
					storeLocalSettings();
				}
			}

			function storeLocalSettings() {
				window.localStorage.setItem(id, JSON.stringify(localSettings));
			}

			readLocalSettings();

			// Set zoom level in local settings if not set yet
			if(!("zoomlevel" in localSettings)) {
				localSettings["zoomlevel"] = 14;
			}

			// Stop "bouncing" behaviour when scrolling page
			document.ontouchmove = function(e) {e.preventDefault()};

			function resizeMap() {
				$("#follwMap").height($("body").innerHeight() - ($("header").outerHeight() + $("footer").outerHeight()));
				follw.invalidateSize();
			}

			// Resize the map whenever the browser window is resized
			window.addEventListener("resize", resizeMap);

			function getExternalMapURL(data) {
				if(navigator.userAgent.toUpperCase().indexOf("ANDROID") !== -1) {
					// Android
					return "geo:0,0?q=" + data.geometry.coordinates[1] + "," + data.geometry.coordinates[0];
				} 

				if(navigator.userAgent.toUpperCase().indexOf("IPHONE") !== -1
						|| navigator.userAgent.toUpperCase().indexOf("IPAD") !== -1
						|| navigator.userAgent.toUpperCase().indexOf("IPOD") !== -1) {
					// iOS
					return "geo:" + data.geometry.coordinates[1] + "," + data.geometry.coordinates[0];
				}

				switch(window.localStorage.getItem("externalmapservice")) {
				case "duckduckgo":
					return "https://duckduckgo.com/?q=" + data.geometry.coordinates[1] + "%2C" + data.geometry.coordinates[0] + "&iaxm=maps";
				case "googlemaps":
					return "https://maps.google.com/?q=" + data.geometry.coordinates[1] + "," + data.geometry.coordinates[0];
				case "herewego":
					return "https://wego.here.com/directions/mix//" + data.geometry.coordinates[1] + "," + data.geometry.coordinates[0];
				case "bingmaps":
					return "https://www.bing.com/maps?q=" + data.geometry.coordinates[1] + "," + data.geometry.coordinates[0];
				case "applemaps":
					return "https://maps.apple.com/?q=" + data.properties.alias + "&ll=" + data.geometry.coordinates[1] + "," + data.geometry.coordinates[0];
				}

				return null;
			}

			function showExternalMapSelectorModal() {
				// Modal with external map selector
				event.preventDefault();
				var content = `<?= $tl->get('externalmapserviceintro') ?>
					<a href="" id="openinduckduckgo" class="externalmapselector" target="_blank" rel="noopener noreferrer">DuckDuckGo</a><br/>
					<a href="" id="openingooglemaps" class="externalmapselector" target="_blank" rel="noopener noreferrer">Google Maps</a><br/>
					<a href="" id="openinherewego" class="externalmapselector" target="_blank" rel="noopener noreferrer">HERE WeGo</a><br/>
					<a href="" id="openinbingmaps" class="externalmapselector" target="_blank" rel="noopener noreferrer">Bing Maps</a><br/>`
				if(navigator.platform.toUpperCase().indexOf("MAC") !== -1) {
					content += `<a href="" id="openinapplemaps" class="externalmapselector" target="_blank" rel="noopener noreferrer">Apple Maps</a>`;
				}
				content = $(content);
				showStaticModal(<?= $tl->get('externalmapservicetitle', 'js') ?>, content);
			}

			function updateHeader(data) {
				var resize = false;

				if(data == null) {
					s = follw.translations['nolocation'];
					if($("title").text() != s) {
						$("title").text(s);
						$("#title").text(s);
						resize = true;
					}

					s = "&nbsp;";
					if($("#coordinates").html() != s) {
						$("#coordinates").html(s);
						resize = true;
					}
				} else {
					var s = data.properties.alias + <?= $tl->get('ishere', 'js', '') ?>;
					if($("title").text() != s) {
						$("title").text(s);
						$("#title").text(s);
						resize = true;
					}

					var externalMapURL = getExternalMapURL(data);
					var h = null;
					if(externalMapURL != null) {
						h = $("<a href=\"" + externalMapURL + "\" target=\"_blank\" rel=\"noopener noreferrer\"></a>");
					} else {
						h = $("<a href=\"\" target=\"_blank\" rel=\"noopener noreferrer\"></a>");

						h.click(function(event) {
							showExternalMapSelectorModal();

							$(".externalmapselector").click((event) => {
								switch(event.target.id) {
								case "openinduckduckgo":
									window.localStorage.setItem("externalmapservice", "duckduckgo");
									break;
								case "openingooglemaps":
									window.localStorage.setItem("externalmapservice", "googlemaps");
									break;
								case "openinherewego":
									window.localStorage.setItem("externalmapservice", "herewego");
									break;
								case "openinbingmaps":
									window.localStorage.setItem("externalmapservice", "bingmaps");
									break;
								case "openinapplemaps":
									window.localStorage.setItem("externalmapservice", "applemaps");
									break;
								}

								event.target.href = getExternalMapURL(data);
								$("#static-modal").modal("hide");
								updateHeader(data);
							});
						});
					}
					
					h.text(follw.prettyPrintCoordinates(data.geometry.coordinates[1], data.geometry.coordinates[0]));

					if($("#coordinates")[0] != $(h).html()) {
						$("#coordinates").empty().append(h);
						resize = true;
					}
				}

				if(resize) {
					resizeMap();
				}
			}

			var follw = new Follw("follwMap", `/${id}`, localSettings["zoomlevel"]);
			follw.translations["nolocation"] = <?= $tl->get('nolocation', 'js') ?>;
			follw.translations["offline"] = <?= $tl->get('offline', 'js') ?>;
			follw.translations["iddeleted"] = <?= $tl->get('iddeleted', 'js') ?>;
			follw.addEventListener("locationchanged", (follw, data) => {
				updateHeader(data);
			});
			follw.addEventListener("offline", () => {
				$("title").text(follw.translations["offline"]);
				$("#title").text(follw.translations["offline"]);
				resizeMap();
				$("#coordinates").html("&nbsp;");
			});
			follw.addEventListener("iddeleted", () => {
				// Reload the browser window when the ID is deleted,
				// it will then show the ID deleted screen
				location.reload();
			});
			follw.addEventListener("zoomchanged", (follw, zoomlevel) => {
				// Store zoom level in local settings
				localSettings["zoomlevel"] = zoomlevel;
				storeLocalSettings();
			});

			$(() => {
				var lc = L.control.locate({setView: false, showCompass: true, showPopup: false}).addTo(follw.map);
				follw.map.on("locateactivate", () => {
					// Store locateactivate in local settings
					localSettings["locateactivate"] = true;
					storeLocalSettings();
				});
	 			follw.map.on("locatedeactivate", () => {
					// Store locatedeactivate in local settings
	 				localSettings["locateactivate"] = false;
	 				storeLocalSettings();
	 			});
	 			if("locateactivate" in localSettings && localSettings["locateactivate"]) {
	 				lc.start();
	 			}
			});

			$(window).scroll(() => {
				try {
					document.body.requestFullscreen();
				} catch(error) {
					console.error(error);
				}
			});

			$(() => {
				resizeMap()
<?php if(isset($location)) { ?>
				follw.setMarker(<?= $_location ?>, <?= $_accuracy ?>);
				updateHeader(<?= $location->geoJson() ?>);
<?php } ?>
				follw.startUpdate();
			});
		</script>
	</body>
</html>