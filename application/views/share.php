<?php
require_once(dirname(__DIR__) . '/models/Translation.php');

// Fixes false "Variable is undefined" validation errors
/* @var ShareID $shareID */
/* @var Boolean $showIntro */

global $protocol;
global $configuration;

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
if(isset($configuration['features']['share']['pwa']) &&
		$configuration['features']['share']['pwa'] == TRUE) {
	header('Service-Worker-Allowed: /' . $shareID->encode() . '/');
}

$tl = new Translation('share');
header('Content-Language: ' . $tl->language);
?>
<!doctype html>
<html lang="<?= $tl->language ?>">
	<head>
		<title>Follw · <?= $tl->get('follwslogan', 'html') ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="robots" content="noindex" />
		<meta name="referrer" content="no-referrer" />
<?php if(isset($configuration['features']['share']['pwa']) &&
		$configuration['features']['share']['pwa'] == TRUE) { ?>
		<script>
			// Start Service Worker as soon as possible to also cache icons and other resource files for offline usage
			if(window.navigator && navigator.serviceWorker) {
				navigator.serviceWorker.register("/<?=$shareID->encode()?>/serviceworker.js", { scope: "/<?=$shareID->encode()?>/" }).then(() => {
					console.debug("Registered Service Worker");
					//TODO Try to unregister Service Worker and Cache?
				}).catch((error) => {
					console.error("Failed to register Service Worker:", error);
				});
			} else {
				console.info("Service Worker not available");
			}
		</script>
<?php } else { ?>
		<script>
			if(window.navigator && navigator.serviceWorker) {
				navigator.serviceWorker.getRegistration("/<?= $shareID->encode() ?>/").then((registration) => {
					if(registration){
						console.debug("Unregistering Service Worker");
						registration.unregister()
					} else {
						console.debug("No Service Worker to unregister");
					}
				});
			}

			if(window.caches) {
				caches.open("<?= $shareID->encode() ?>").then((cache) => {
					console.log(cache);
					//TODO caches.delete("<?= $shareID->encode() ?>");
				});
			}
		</script>
<?php } ?>
		<link rel="manifest" href="/<?=$shareID->encode()?>/manifest.webmanifest" />
<?php // Icons ?>
		<link rel="icon" href="/favicon-96x96.png" sizes="96x96" type="image/png">
		<link rel="icon" href="/favicon-64x64.png" sizes="64x64" type="image/png">
		<link rel="icon" href="/favicon-48x48.png" sizes="48x48" type="image/png">
		<link rel="icon" href="/favicon-32x32.png" sizes="32x32" type="image/png">
		<link rel="icon" href="/favicon-16x16.png" sizes="16x16" type="image/png">
		<link rel="icon" href="/favicon.svg" sizes="any" type="image/svg+xml">
		<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#006400">
		<meta name="msapplication-config" content="/<?=$shareID->encode()?>/browserconfig.xml">
		<meta name="msapplication-TileColor" content="#006400">
		<meta name="msapplication-TileImage" content="/mstile-144x144.png">
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
			}

			.container {
				padding: 0;
				overflow: hidden;
			}

			@media (max-width: 575px) {
				.navbar-brand {
					font-size: 1rem;
				}

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

			.tab-pane {
				padding-left: 5px;
				padding-right: 5px;
			}

			.nav-link {
				padding: .5rem;
			}

			.geolocationenabled, .geolocationdisabled {
				display: none;
			}

			#sharelocationbuttons {
				text-align: center;
			}

			#sharelocationbuttons button {
				width: 4rem;
				height: 4rem;
				border-radius: 50%;
			}

			#shareLocationMap {
				height: 250px;
			}

			#textlocation {
				width: 100%;
			}

			.createfollower, .updatefollower {
				display: none;
			}

			#sharefollowid-modal .modal-body {
				text-align: center;
			}

			#sharefollowid-qrcode {
				width: 222px;
				height: 222px;
			}

			#sharefollowid-modal input[type=image] {
				width: 40px;
				height: 40px;
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
		<main role="main">
			<div class="container">
				<header class="navbar">
					<div class="navbar-brand">Follw <span class="text-muted">· <?= $tl->get('follwslogan', 'html') ?></span></div>
				</header>
				<ul class="nav nav-tabs" role="tablist">
					<li class="nav-item"><a class="nav-link active" id="sharelocation-tab" data-toggle="tab" href="#sharelocation" role="tab" aria-controls="sharelocation" aria-selected="true"><?= $tl->get('sharelocationtab', 'html') ?></a></li>
					<li class="nav-item"><a class="nav-link" id="followers-tab" data-toggle="tab" href="#followers" role="tab" aria-controls="followers" aria-selected="false"><?= $tl->get('managefollowerstab', 'html') ?></a></li>
					<li class="nav-item"><a class="nav-link" id="settings-tab" data-toggle="tab" href="#settings" role="tab" aria-controls="settings" aria-selected="false"><span class="d-inline d-sm-none fa fa-cog" title="<?= $tl->get('settingstab', 'htmlattr') ?>"></span> <span class="d-none d-sm-inline"><?= $tl->get('settingstab', 'htmlattr') ?></span></a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="sharelocation" role="tabpanel" aria-labelledby="sharelocation-tab">
						<div class="geolocationenabled">
							<p><?= $tl->get('usedevicelocation', 'html') ?></p>
							<div id="sharelocationbuttons">
								<button id="start_pause_devicelocation" class="btn btn-primary btn-sm"><span class="fa fa-play fa-2x" title="<?= $tl->get('startsharing', 'htmlattr') ?>"></span></button>
								<button id="deletelocation" class="btn btn-primary btn-sm"><span class="fa fa-trash fa-2x" title="<?= $tl->get('deletelocation', 'htmlattr') ?>"></span></button>
							</div>
							<p><?= $tl->get('orselectlocationonmap', 'html') ?></p>
						</div>
						<p class="geolocationdisabled"><?= $tl->get('selectlocationonmap', 'html') ?></p>
						<div id="shareLocationMap"></div>
<?php
if($configuration['mode'] == 'development') {
?>
						<div class="container">
							<div class="row">
								<div class="col-md">
									<form action="#" autocomplete="off">
										<input type="text" id="textlocation" placeholder="Latitude, longitude"/>
									</form>
								</div>
							</div>
						</div>
<?php } ?>
					</div>
					<div class="tab-pane" id="followers" role="tabpanel" aria-labelledby="followers-tab">
						<div style="overflow: auto">
							<table id="followurls" class="table table-striped table-sm">
								<thead>
									<tr>
										<th></th>
										<th><?= $tl->get('followidheader', 'html') ?></th>
										<th><?= $tl->get('aliasheader', 'html') ?></th>
										<!-- <th><?= $tl->get('delayheader', 'html') ?></th> -->
										<th><?= $tl->get('startsheader', 'html') ?></th>
										<th><?= $tl->get('expiresheader', 'html') ?></th>
										<th><?= $tl->get('enabledheader', 'html') ?></th>
										<th colspan="2"><span id="refreshfollowers" class="fa fa-refresh"></span></th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
						<div id="sharefollowid-modal" class="modal">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title"><?= $tl->get('sharefollowidtitle', 'html') ?></h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="<?= $tl->get('close', 'htmlattr') ?>">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<img id="sharefollowid-qrcode" alt=""/><br/>
										&nbsp;
										<div><input type="text" value="" id="sharefollowid-clipboardtext" readonly="readonly"><button type="button" id="sharefollowid-clipboard"><span class="fa fa-clipboard"></span></button></div>
										&nbsp;
										<div id="sharefollowid-buttons">
											<input type="image" src="/email.svg" id="sharefollowid-email" alt="eMail"/>
											<input type="image" src="/whatsapp.png" id="sharefollowid-whatsapp" alt="WhataApp"/>
											<!--  input type="image" src="/skype.svg" id="sharefollowid-skype" alt="Skype"/ -->
											<input type="image" src="/telegram.svg" id="sharefollowid-telegram" alt="Telegram"/>
											<input type="image" src="/twitter.png" id="sharefollowid-twitter" alt="Twitter"/>
											<input type="image" src="/facebook.png" id="sharefollowid-facebook" alt="Facebook"/>
											<input type="image" src="/linkedin.png" id="sharefollowid-linkedin" alt="LinkedIn"/>
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class="fa fa-times-circle"></span>  <?= $tl->get('close', 'html') ?></button>
									</div>
								</div>
							</div>
						</div>
						<p><?= $tl->get('managefollowersintro', 'html') ?></p>
						<button type="button" id="createfollowerbutton" class="btn btn-primary btn-sm"><span class="fa fa-plus-circle"></span>  <?= $tl->get('createfollowerbutton', 'html') ?></button>
						<div id="createupdatefollowermodal" class="modal">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<form id="createupdatefollowerform" action="#" autocomplete="off">
										<div class="modal-header">
											<h5 class="modal-title"><span class="createfollower"><?= $tl->get('createfollowertitle', 'html') ?></span><span class="updatefollower"><?= $tl->get('updatefollowertitle', 'html') ?></span></h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="<?= $tl->get('close', 'htmlattr') ?>">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div class="modal-body">
											<div class="form-group">
												<label for="referenceInput"><?= $tl->get('createupdatefollowerreferencelabel', 'html') ?></label>
												<input name="reference" id="referenceInput" type="text" class="form-control" placeholder="<?= $tl->get('createupdatefollowerreferenceplaceholder', 'htmlattr') ?>"/>
												<small class="form-text text-muted"><?= $tl->get('createupdatefollowerreferencehelp', 'htmlattr') ?></small>
											</div>
											<div class="form-group">
												<label for="aliasInput"><?= $tl->get('createupdatefolloweraliaslabel', 'html') ?></label>
												<input name="alias" id="aliasInput" type="text" class="form-control" placeholder="<?= $tl->get('createupdatefolloweraliasplaceholder', 'htmlattr') ?>"/>
												<small class="form-text text-muted"><?= $tl->get('createupdatefolloweraliashelp', 'htmlattr') ?></small>
											</div>
											<!-- <div class="form-group">
												<label for="delayInput"><?= $tl->get('createupdatefollowerdelaylabel', 'html') ?></label>
												<input name="delay" id="delayInput" type="time" class="form-control"/>
												<small class="form-text text-muted"><?= $tl->get('createupdatefollowerdelayhelp', 'htmlattr') ?></small>
											</div> -->
											<div class="form-group">
												<label for="startsInput"><?= $tl->get('createupdatefollowerstartslabel', 'html') ?></label>
												<input name="starts" id="startsInput" type="datetime-local" class="form-control"/>
												<small class="form-text text-muted"><?= $tl->get('createupdatefollowerstartshelp', 'htmlattr') ?></small>
											</div>
											<div class="form-group">
												<label for="expiresInput"><?= $tl->get('createupdatefollowerexpireslabel', 'html') ?></label>
												<input name="expires" id="expiresInput" type="datetime-local" class="form-control"/>
												<small class="form-text text-muted"><?= $tl->get('createupdatefollowerexpireshelp', 'htmlattr') ?></small>
											</div>
											<div class="form-group">
												<label for="enabledInput"><?= $tl->get('createupdatefollowerenablelabel', 'html') ?></label>
												<input name="enabled" id="enabledInput" type="checkbox" class="form-control"/>
												<small class="form-text text-muted"><?= $tl->get('createupdatefollowerenablehelp', 'htmlattr') ?></small>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class="fa fa-times-circle"></span> <?= $tl->get('createupdatefollowerclosebutton', 'html') ?></button>
											<button type="submit" class="createfollower btn btn-primary"><span class="fa fa-plus-circle"></span> <?= $tl->get('createfollowerbutton', 'html') ?></button>
											<button type="submit" class="updatefollower btn btn-primary"><span class="fa fa-pencil"></span> <?= $tl->get('updatefollowerbutton', 'html') ?></button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="settings" role="tabpanel" aria-labelledby="settings-tab">
						<p><?= $tl->get('yoursharingid', NULL, $shareID->encode()) ?></p>
						<p><?= $tl->get('dontsharesharingid') ?></p>
						<p><?= $tl->get('bookmarkthissharingurl', 'html') ?></p>
						<p><?= $tl->get('sharingidcantberecoveredwarning', 'html') ?></p>
						<p><?= $tl->get('configurealiasintro', 'html') ?></p>
						<form action="#" id="settingsform" autocomplete="off">
							<?= $tl->get('configurealias', 'html') ?> <input name="alias" type="text" value="<?= htmlspecialchars(@$shareID['alias']) ?>"/>
						</form>
					</div>
				</div>
<?php $footertl = new Translation('footer'); ?>
				<footer class="d-block d-sm-none">
					<a href="<?= $protocol ?><?= $_SERVER['HTTP_HOST'] ?>" target="_blank" rel="noopener noreferrer"><?= $footertl->get('sharinglocationwith', 'html') ?></a> · <a href="/privacy" class="privacylink" rel="noopener noreferrer"><?= $footertl->get('privacystatement', 'html') ?></a>
				</footer>
				<footer class="pt-4 d-none d-sm-block">
					<div class="row">
						<div class="col-md">
							<div class="row">
								<div class="col">
									<h5><?= $footertl->get('aboutheader', 'html') ?></h5>
									<p><?= $footertl->get('aboutintro') ?></p>
									<p><?= $footertl->get('blogintro') ?></p>
								</div>
								<div class="col">
									<h5><?= $footertl->get('privacyheader', 'html') ?></h5>
									<p><?= $footertl->get('privacyintro') ?></p>
									<p><?= $footertl->get('termsintro') ?></p>
								</div>
<?php if(isset($configuration['features']['share']['app'])) { ?>
								<div class="col">
									<h5><?= $footertl->get('appsheader', 'html') ?></h5>
									<p><?= $footertl->get('appsintro', 'html') ?></p>
									<ul class="list-unstyled text-small">
<?php	foreach($configuration['features']['share']['app'] as $app) { ?>
										<li><a class="text-muted" href="<?= $app['url'] ?>" target="_blank" rel="noopener noreferrer"><?= $footertl->get('appfor', 'html', $footertl->get('appplatform' . $app['platform'])) ?></a></li>
<?php	} ?>
									</ul>
								</div>
<?php } ?>
							</div>
						</div>
					</div>
				</footer>
			</div>
		</main>
<?php // Scripts ?>
		<script src="https://unpkg.com/jquery@3.5.1/dist/jquery.min.js"
			integrity="sha384-ZvpUoO/+PpLXR1lu4jmpXWu80pZlYUAfxl5NsBMWOEPSjUn/6Z/hRTt8+pR6L4N2"
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
			var shareID = "<?=$shareID->encode()?>";

			// Global settings
			// Global settings are stored on the server.
			// Currently only the alias.
			var globalSettings = {};
			function getGlobalSettings(callback = null) {
				$.get(`/${shareID}/settings.json`, (data) => {
					if(data) {
						globalSettings = data;
						if(callback) {
							callback(data);
						}
					}
				});
			}
			getGlobalSettings();

			function storeGlobalSettings() {
				$.post(`/${shareID}/config`, globalSettings, () => {
				});
			}

			$(() => {
				// Refresh global settings when settings tab is shown.
				$("a#settings-tab").on("shown.bs.tab", (e) => {
					getGlobalSettings((data) => {
						if("alias" in data) {
							$("#settingsform input[name=alias]").val(data["alias"]);
						}
					});
				});

				// Store global settings when settings have been modified on the settings tab.
				$("#settingsform input[name=alias]").on("keydown", function(event) {
					if(event.keyCode == 13) { // Enter
						event.preventDefault();
						globalSettings["alias"] = $(this).val();
						storeGlobalSettings();
					}
				});
			});

			// Local settings
			// Local settings is stored in local storage and not shared with the server,
			// it contains settings like which tab is shown, map zoom level etc.
			var localSettings = JSON.parse(window.localStorage.getItem(shareID));
			console.log(localSettings);
			if(!localSettings) {
				localSettings = {};
			}

			function storeLocalSettings() {
				window.localStorage.setItem(shareID, JSON.stringify(localSettings));
			}

			$(() => {
				// Show tab that was open before
				if(localSettings["tab"]) {
					$(`.nav-tabs a[href='${localSettings["tab"]}']`).tab("show");
				} else {
					$(".nav-tabs a[href='#sharelocation']").tab("show");
				}
	
				// Store open tab in local settings
				$("a[data-toggle=tab]").on("shown.bs.tab", (event) => {
					localSettings["tab"] = $(event.target).attr("href");
					storeLocalSettings();
				});
			});

			// Location sharing
			function onLocationChange(follw, data) {
				if(data != null) {
					$("#deletelocation").prop("disabled", false);

					var s = follw.prettyPrintCoordinates(data.latitude, data.longitude);
					if($("#coordinates").text() != s) {
						$("#coordinates").text(s);
					}
					$("#deletelocation").prop("disabled", false);
				} else {
					$("#deletelocation").prop("disabled", true);
					follw.map.zoomControl.enable();;
					follw.map.dragging.enable();
					follw.map.touchZoom.enable();
					follw.map.doubleClickZoom.enable();
					follw.map.scrollWheelZoom.enable();
				}
			}

			class ShareLocation {
				constructor() {
					this.isSharing = false;
					this.watchLocationID = null;
					this.previousLocation = null;
					this.lastShare = null;

					// Create the share location map
					this.map = new Follw("shareLocationMap", `/${shareID}`, 12);
					this.map.nolocation = <?= $tl->get('nolocation', 'js') ?>;
					this.map.addEventListener("locationchanged", onLocationChange);
					this.map.addEventListener("iddeleted", () => { location.reload(); });
					this.map.startUpdate();

					// See if DOM is already available
					if (document.readyState === "complete" || document.readyState === "interactive") {
						// call on next available tick
						var _this = this;
						setTimeout(() => {
							_this.init();
						}, 1);
					} else {
						var _this = this;
						document.addEventListener("DOMContentLoaded", () => {
							_this.init();
						});
					}

				}

				init() {
					var _this = this;
					this.map.map.on("click", (event) => {
						if(!_this.isSharing) { 
							_this.updateLocation({latitude: event.latlng.lat, longitude: event.latlng.lng});
						}
					});

					// Resume updating if that was what we were doing
					if(localSettings["sharing"] == true) {
						this.startSharing();
					}
				}

				startSharing() {
					if(!this.isSharing) {
						this.isSharing = true;

						this.map.pauseUpdate();
						localSettings["sharing"] = true;
						storeLocalSettings();
						var _this = this;
						this.watchLocationID = navigator.geolocation.watchPosition((position) => {
							_this.updateLocation(position.coords);
						});

						$("#start_pause_devicelocation").html('<span class="fa fa-pause fa-2x" title="<?= $tl->get('pausesharing', 'htmlattr') ?>"></span>');
						$("#deletelocation").html('<span class="fa fa-stop fa-2x" title="<?= $tl->get('stopsharing', 'htmlattr') ?>"></span>');
						$("#deletelocation").prop("disabled", false);
					}
				}

				stopSharing() {
					this.isSharing = false;

					navigator.geolocation.clearWatch(this.watchLocationID);
					localSettings["sharing"] = false;
					storeLocalSettings();
					this.map.resumeUpdate();

					$("#start_pause_devicelocation").html('<span class="fa fa-play fa-2x" title="<?= $tl->get('startsharing', 'htmlattr') ?>"></span>');
					$("#deletelocation").html('<span class="fa fa-trash fa-2x" title="<?= $tl->get('deletelocation', 'htmlattr') ?>"></span>');
				}

				deleteLocation() {
					var _this = this;
					_this.stopSharing();
					$.get(`/${shareID}/deletelocation`, (data) => {
						_this.map.getLocation(true);
						$("#deletelocation").prop("disabled", true);
					});
				}

				equals(location1, location2) {
					if(location1 === null && location2 === null) return true;
					if(location1 === null) return false;
					if(location2 === null) return false;
					if(location1.latitude !== location2.latitude) return false;
					if(location1.longitude !== location2.longitude) return false;
					if(location1.accuracy !== location2.accuracy) return false;
					if(location1.altitude !== location2.altitude) return false;
					if(location1.altitudeAccuracy !== location2.altitudeAccuracy) return false;
					if(location1.heading !== location2.heading) return false;
					if(location1.speed !== location2.speed) return false;
					return true;
				}

				updateLocation(location) {
					console.log(location);
					var _this = this;
					$.post(`/${shareID}/`, location, () => {
						_this.map.setMarker([location.latitude, location.longitude], location.accuracy);
						_this.previousLocation = location;
						_this.lastShare = Date.now();
					});
				}

				prettyPrintCoordinates(latitude, longitude) {
					if(this.map != null) {
						return map.prettyPrintCoordinates(latitude, longitude);
					}

					return "";
				}
			}

			var shareLocation = null;

			$(() => {
				var shareLocation = new ShareLocation();

				// Share location map
				$("a#sharelocation-tab").on("shown.bs.tab", () => {
					shareLocation.map.invalidateSize();
				});

				// Share location Geolocation API
				// JavaScript Geolocation API can only be used when using SSL
				if(window.location.protocol == "https:" && "geolocation" in navigator) {
					$(".geolocationenabled").show();
					$(".geolocationdisabled").hide();

					$("#start_pause_devicelocation").click(() => {
						if(shareLocation.isSharing) {
							shareLocation.stopSharing();
						} else {
							shareLocation.startSharing();
						}
					});
				}

				// Share location text input
				$("#textlocation").on("keydown", (event) => {
					if(event.keyCode == 13) { // Enter
						event.preventDefault();

						var numericRegex = /^\s*([0-9]*[\.,]?[0-9]*)\s([0-9]*[\.,]?[0-9]*)\s*$/;

						var val = $(this).val();

						var latitude = null;
						var longitude = null;

						// Recognise formatting and act accordingly
						if(numericRegex.test(val)) {
							match = val.match(numericRegex);
							latitude = match[1].replace(",", ".");
							longitude = match[2].replace(",", ".");
						} else
							console.log("Invalid location");

						if(latitude < -90 || latitude > 90) {
							console.log("Latitude out of range");
							latitude = null;
						}

						if(longitude < -180 || longitude > 180) {
							console.log("Longitude out of range");
							longitude = null;
						}

						if(latitude != null && longitude != null) {
							shareLocation.updateLocation({latitude: latitude, longitude: longitude});
						}
					}
				});

				$("#deletelocation").click((event) => {
					event.preventDefault();
					shareLocation.deleteLocation();
				});
			});

			// Managing followers
			function refreshFollowIDs() {
				$("#refreshfollowers").addClass("fa-spin");

				$.get(`/${shareID}/followers.json`, (data) => {
					var rows = $("<tbody></tbody>");
					data.forEach((entry) => {
						var row = $("<tr></tr>");

						var reference = entry["id"];
						if(entry["reference"] != null)
							reference = entry["reference"];

						$(row).append('<td><span class="fa fa-pencil editfollowid"></span></td>');
						$(".editfollowid", row).click(entry["id"], (event) => {
							editFollowID(event.data);
						});

 						if(entry["enabled"] && entry["started"] && !entry["expired"]) {
 	 						if(entry["reference"])
								$(row).append(`<td class="followurl enabled text-muted"><a href="${entry["url"]}" target="_blank" rel="noopener noreferrer">${entry["reference"]}</a></td>`);
 	 						else
								$(row).append(`<td class="followurl enabled text-muted"><a href="${entry["url"]}" target="_blank" rel="noopener noreferrer">${entry["id"]}</a></td>`);
 						} else if(entry["reference"]) {
							$(row).append(`<td class="followurl disabled">${entry["reference"]}</td>`);
 						} else {
							$(row).append(`<td class="followurl disabled text-muted">${entry["id"]}</td>`);
 						}

						if(entry["alias"] != null)
							$(row).append(`<td class="alias">${entry["alias"]}</td>`);
						else if("alias" in globalSettings)
							$(row).append(`<td class="alias text-muted">${globalSettings["alias"]}</td>`);
						else
							$(row).append(`<td class="alias text-muted"></td>`);

						/*if(entry["delay"])
							$(row).append('<td class="delay"></td>');
						else
							$(row).append('<td class="delay">Realtime</td>');*/

						if(entry["started"])
							$(row).append('<td class="starts">Active</td>');
						else if(entry["starts"] != null) {
							entry["starts"] = new Date(entry["starts"] * 1000).toLocaleString();
							$(row).append(`<td class="starts">${entry["starts"]}</td>`);
						} else
							$(row).append('<td class="starts"></td>');

						if(entry["expired"])
							$(row).append('<td class="expires">Expired</td>');
						else if(entry["expires"] != null) {
							entry["expires"] = new Date(entry["expires"] * 1000).toLocaleString();
							$(row).append(`<td class="expires">${entry["expires"]}</td>`);
						} else
							$(row).append('<td class="expires">Never</td>');

						if(entry["expired"])
							$(row).append('<td><input type="checkbox" disabled="disabled"/></td>');
						else if(entry["enabled"])
							$(row).append(`<td><input type="checkbox" id="disable${entry["id"]}" checked="checked"/></td>`);
						else
							$(row).append(`<td><input type="checkbox" id="enable${entry["id"]}"/></td>`);
						$(`#disable${entry["id"]}`, row).click(entry["id"], (event) => {
							disableFollowID(event.data);
						});
						$(`#enable${entry["id"]}`, row).click(entry["id"], (event) => {
							enableFollowID(event.data);
						});

						$(row).append('<td><span class="fa fa-share sharefollowid"></span></td>');
						$(".sharefollowid", row).click(entry, (event) => {
							shareFollowID(event.data);
						});

						$(row).append('<td><span class="fa fa-trash deletefollowid"></span></td>');
						$(".deletefollowid", row).click(entry["id"], (event) => {
							deleteFollowID(event.data);
						});

						$(rows).append(row);
					});
					$("table#followurls tbody").replaceWith(rows);

					$("#refreshfollowers").removeClass("fa-spin");

				});
			}

			function enableFollowID(followid) {
				$.get(`/${shareID}/follower/${followid}/enable`).always(() => {
					refreshFollowIDs();
				});
			}

			function disableFollowID(followid) {
				$.get(`/${shareID}/follower/${followid}/disable`).always(() => {
					refreshFollowIDs();
				});
			}

			function deleteFollowID(followid) {
				$.get(`/${shareID}/follower/${followid}/delete`).always(() => {
					refreshFollowIDs();
				});
			}

			// Show the alias as the placeholder text for the alias override form field
			$("#createupdatefollowermodal").on("shown.bs.modal", () => {
				getGlobalSettings((data) => {
					var alias = "";
					if("alias" in data)
						alias = data["alias"];
					$("#createupdatefollower input[name=alias]").prop("placeholder", data["alias"]);
				});
			});

			$("#createfollowerbutton").click(() => {
				$("#createupdatefollowerform").prop("action", `/${shareID}/generatefollowid`);
				$(".createfollower").show();

				$("#createupdatefollowermodal").modal("show");
			});


			function editFollowID(followid) {
				$.get(`/${shareID}/follower/${followid}.json`, (data) => {
					$(`#createupdatefollowermodal input[name=reference]`).prop("placeholder", data["id"]);
					$(`#createupdatefollowermodal input[name=reference]`).val(data["reference"]);
					$(`#createupdatefollowermodal input[name=alias]`).val(data["alias"]);
					if(data["starts"]) {
						var starts = new Date(new Date(data["starts"] * 1000).toString().split('GMT')[0]+' UTC').toISOString().slice(0,16);
						$(`#createupdatefollowermodal input[name=starts]`).val(starts);
					}
					if(data["expires"]) {
						var expires = new Date(new Date(data["expires"] * 1000).toString().split('GMT')[0]+' UTC').toISOString().slice(0,16);
						$(`#createupdatefollowermodal input[name=expires]`).val(expires);
					}
					$(`#createupdatefollowermodal input[name=enabled]`).prop("checked", data["enabled"]);

					$("#createupdatefollowerform").prop("action", `/${shareID}/follower/${followid}`);
					$(".updatefollower").show(); 

					$("#createupdatefollowermodal").modal("show");
				});
			}

			// Reset generate Follow ID form on modal close
			$("#createupdatefollowermodal").on("hidden.bs.modal", () => {
				$("#createupdatefollowerform").get(0).reset();
				$("#createupdatefollower input[name=reference]").prop("placeholder", <?= $tl->get('createupdatefollowerreferenceplaceholder', 'js') ?>);
				$("#createupdatefollowerform").prop("action", "#");
				$(".createfollower").hide();
				$(".updatefollower").hide();
			});

			$("#createupdatefollowerform").submit(function(event) {
				event.preventDefault();

				var reference = $("input[name=reference]", this).val();
				if(reference == "")
					reference = null;
				var alias = $("input[name=alias]", this).val();
				if(alias == "")
					alias = null;
				var enabled = $("input[name=enabled]", this).is(":checked");
				var starts = $("input[name=starts]", this).val();
				if(starts) {
					console.debug(starts);
					starts = new Date(starts).getTime()/1000;
					console.log(starts);
				} else
					starts = null;
				var expires = $("input[name=expires]", this).val();
				if(expires) {
					console.debug(expires);
					expires = new Date(expires).getTime()/1000;
					console.log(expires);
				} else
					expires = null;

				$.post($("#createupdatefollowerform").prop("action"), { reference:reference, alias:alias, enabled:enabled, starts:starts, expires:expires }, () => {
					refreshFollowIDs();
					$("#createupdatefollowermodal").modal("hide");
				});
			});

			// Sharing Follow ID
			function shareFollowID(entry) {
				var message = `Follow my location ${entry["url"]}`;

				// QR code
				$("#sharefollowid-qrcode").prop("src", `${entry["url"]}qrcode.svg`);

				// Twitter
				$("#sharefollowid-twitter").click(entry, (event) => {
					event.preventDefault();
					window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(event.data["url"])}&text=${encodeURIComponent("Follow my location")}`, "_blank", "noopener,noreferrer");
				});

				// Facebook
				$("#sharefollowid-facebook").click(entry, (event) => {
					event.preventDefault();
					window.open(`https://facebook.com/sharer.php?u=${encodeURIComponent(event.data["url"])}`, "_blank", "noopener,noreferrer");
				});

				// eMail
				$("#sharefollowid-email").click(message, (event) => {
					event.preventDefault();
					window.open(`mailto:?subject=${encodeURIComponent("Follow my location")}&body=${encodeURIComponent(event.data)}`, "_blank", "noopener,noreferrer");
				});

				// LinkedIn
				$("#sharefollowid-linkedin").click(entry, (event) => {
					event.preventDefault();
					window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(event.data["url"])}`, "_blank", "noopener,noreferrer");
				});

				// WhatsApp
				$("#sharefollowid-whatsapp").click(message, (event) => {
					event.preventDefault();
					window.open(`whatsapp://send?text=${encodeURIComponent(event.data)}`, "_blank", "noopener,noreferrer");
				});

				// Telegram
				$("#sharefollowid-telegram").click(entry, (event) => {
					event.preventDefault();
					window.open(`https://t.me/share/url?url=${encodeURIComponent(event.data["url"])}&text=${encodeURIComponent("Follow my location")}`, "_blank", "noopener,noreferrer");
				});

				// Clipboard
				$("#sharefollowid-clipboardtext").val(message);
				$("#sharefollowid-clipboard").off("click").click((event) => {
					event.preventDefault();
					var copyText = document.getElementById("sharefollowid-clipboardtext");
					copyText.select();
					copyText.setSelectionRange(0, 99999);
					document.execCommand("copy");
					alert(`Copied the text: ${copyText.value}`);
				});

				$("#sharefollowid-modal").modal("show")
			}

			$().ready(() => {
				refreshFollowIDs();

				$("#refreshfollowers").click(() => {
					refreshFollowIDs();
				});
			});

			// 
			function showStaticModal(title, content) {
				if($("#static-modal").length == 0) {
					var staticModal = $(`<div id="static-modal" class="modal">
	<div class="modal-dialog" role="document">
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
				$(".creditslink,.privacylink,.termslink").click(function(event) {
					event.preventDefault();
					showStaticModal($(this).text(), $(this).attr("href"));
				});
			});
<?php if($showIntro) { ?>

			$(() => {
				var content = $(`<p><?= $tl->get('yoursharingid', NULL, $shareID->encode()) ?></p>
<p><?= $tl->get('dontsharesharingid') ?></p>
<p><?= $tl->get('bookmarkthissharingurl', 'html') ?></p>
<p id="bookmarkMac" style="display: none;"><?= $tl->get('bookmarkmacos') ?></p>
<p id="bookmarkWin" style="display: none;"><?= $tl->get('bookmarkwindows') ?></p>
<p id="bookmarkAndroid" style="display: none;"><?= $tl->get('bookmarkandroid') ?></p>
<p id="bookmarkIos" style="display: none;"><?= $tl->get('bookmarkios') ?></p>
<p><?= $tl->get('sharingidcantberecoveredwarning', 'html') ?></p>`);
				showStaticModal(<?= $tl->get('welcometofollw', 'js') ?>, content);
				if(navigator.platform.toUpperCase().indexOf("MAC") !== -1)
					$("#static-modal .modal-body #bookmarkMac").show();
				else if(navigator.platform.toUpperCase().indexOf("WIN") !== -1)
					$("#static-modal .modal-body #bookmarkWin").show();
				else if(navigator.userAgent.toUpperCase().indexOf("ANDROID") !== -1)
					$("#static-modal .modal-body #bookmarkAndroid").show();
				else if(navigator.userAgent.toUpperCase().indexOf("IPHONE") !== -1
					|| navigator.userAgent.toUpperCase().indexOf("IPAD") !== -1
					|| navigator.userAgent.toUpperCase().indexOf("IPOD") !== -1)
					$("#static-modal .modal-body #bookmarkIos").show();
			});
<?php } ?>
		</script>
	</body>
</html>