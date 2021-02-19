<?php
require_once(dirname(__DIR__) . '/models/Translation.php');

// Fixes false "Variable is undefined" validation errors
/* @var ShareID $shareID */
/* @var Boolean $showIntro */

global $protocol;
global $configuration;

$tl = new Translation('share');
header('Content-Language: ' . $tl->language);
?>
<!doctype html>
<html lang="<?= $tl->language ?>">
	<head>
		<title>Follw · <?= $tl->get('follwslogan', 'html') ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
		<meta name="robots" content="noindex" />
		<meta name="referrer" content="no-referrer" />
<?php if(isset($configuration['features']['share']['pwa']) &&
		$configuration['features']['share']['pwa'] == TRUE) { ?>
		<script>
			// Start Service Worker as soon as possible to also cache icons and other resource files for offline usage
			if(window.navigator && navigator.serviceWorker) {
				navigator.serviceWorker.register("/<?=$shareID->encode()?>/serviceworker.js", { scope: "/<?=$shareID->encode()?>/" }).then(function() {
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
				navigator.serviceWorker.getRegistration("/<?= $shareID->encode() ?>/").then(function(registration) {
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
		<link rel="stylesheet" href="https://unpkg.com/bootstrap@4.5.3/dist/css/bootstrap.min.css"
			integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
			crossorigin="anonymous"/>
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
			integrity="sha384-VzLXTJGPSyTLX6d96AxgkKvE/LRb7ECGyTxuwtpjHnVWVZs2gp5RDjeM/tgBnVdM"
			crossorigin="anonymous"/>
		<style>
			.geolocationenabled, .geolocationdisabled {
				display: none;
			}

			#shareLocationMap {
				height: 250px;
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

			code {
				display: block;
				white-space: pre;
				background-color: #F8F8F8;
			}
		</style>
	</head>
	<body>
		<main role="main">
			<div class="container">
				<div class="navbar">
					<div class="navbar-brand">Follw <span class="text-muted">· <?= $tl->get('follwslogan', 'html') ?></span></div>
				</div>
				<ul class="nav nav-tabs">
					<li class="nav-item"><a class="nav-link active" id="sharelocation-tab" data-toggle="tab" href="#sharelocation" role="tab" aria-controls="sharelocation" aria-selected="true"><?= $tl->get('sharelocationtab', 'html') ?></a></li>
					<li class="nav-item"><a class="nav-link" id="followers-tab" data-toggle="tab" href="#followers" role="tab" aria-controls="followers" aria-selected="false"><?= $tl->get('managefollowerstab', 'html') ?></a></li>
					<li class="nav-item"><a class="nav-link" id="configuration-tab" data-toggle="tab" href="#configuration" role="tab" aria-controls="configuration" aria-selected="false"><?= $tl->get('configurationtab', 'html') ?></a></li>
				</ul>
				<div class="tab-content">
<?php
if($showIntro) {
?>
					<div id="intro-modal" class="modal">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title"><?= $tl->get('welcometofollw', 'html') ?></h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<p><?= $tl->get('yoursharingid', NULL, $shareID->encode()) ?></p>
									<p><?= $tl->get('dontsharesharingid') ?></p>
									<p><?= $tl->get('bookmarkthissharingurl', 'html') ?></p>
									<p id="bookmarkMac" style="display: none;"><?= $tl->get('bookmarkmacos') ?></p>
									<p id="bookmarkWin" style="display: none;"><?= $tl->get('bookmarkwindows') ?></p>
									<p id="bookmarkAndroid" style="display: none;"><?= $tl->get('bookmarkandroid') ?></p>
									<p id="bookmarkIos" style="display: none;"><?= $tl->get('bookmarkios') ?></p>
									<p><?= $tl->get('sharingidcantberecoveredwarning', 'html') ?></p>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
								</div>
							</div>
						</div>
					</div>
<?php } ?>
					<div class="tab-pane active" id="sharelocation" role="tabpanel" aria-labelledby="sharelocation-tab">
						<div class="col-md" class="geolocationenabled">
							<p><?= $tl->get('usedevicelocation', 'html') ?></p>
							<button id="start_pause_devicelocation" class="btn btn-primary btn-sm"><?= $tl->get('startsharing', 'html') ?></button>
							<button id="deletelocation" class="btn btn-primary btn-sm"><?= $tl->get('deletelocation', 'html') ?></button>
						</div>
						<p class="geolocationenabled"><?= $tl->get('orselectlocationonmap', 'html') ?></p>
						<p class="geolocationdisabled"><?= $tl->get('selectlocationonmap', 'html') ?></p>
						<div id="shareLocationMap"></div>
<?php
if($configuration['mode'] == 'development') {
?>
						<div class="container">
							<div class="row">
								<div class="col-md">
									<h4>Text input</h4>
									<p>Type the latitude and longitude of the location you like to share.</p>
									<form action="#" autocomplete="off">
										<input type="text" id="textlocation"/>
									</form>
								</div>
								<div class="col-md">
									<h4>Google real-time location URL</h4>
									<p>Paste the Google real-time location URL you like to share.</p>
									<form action="#" autocomplete="off">
										<input type="text" id="googlelocation"/>
									</form>
								</div>
							</div>
						</div>
<?php } ?>
					</div>
					<div class="tab-pane" id="followers" role="tabpanel" aria-labelledby="followers-tab">
						<table id="followurls" class="table table-striped table-sm">
							<thead>
								<tr>
									<th><?= $tl->get('followidheader', 'html') ?></th>
									<th><?= $tl->get('aliasheader', 'html') ?></th>
									<!-- <th><?= $tl->get('delayheader', 'html') ?></th> -->
									<th><?= $tl->get('expiresheader', 'html') ?></th>
									<th><?= $tl->get('enabledheader', 'html') ?></th>
									<th><?= $tl->get('shareheader', 'html') ?></th>
									<th><?= $tl->get('deleteheader', 'html') ?></th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
						<div id="sharefollowid-modal" class="modal">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title"><?= $tl->get('sharefollowidtitle', 'html') ?></h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<img src="" id="sharefollowid-qrcode"/><br/>
										&nbsp;
										<div><input type="text" value="" id="sharefollowid-clipboardtext" readonly="readonly"><button type="button" id="sharefollowid-clipboard">Copy to Clipboard</button></div>
										&nbsp;
										<div id="sharefollowid-buttons">
											<input type="image" src="/email.svg" id="sharefollowid-email"/>
											<input type="image" src="/whatsapp.png" id="sharefollowid-whatsapp"/>
											<!--  input type="image" src="/skype.svg" id="sharefollowid-skype"/ -->
											<input type="image" src="/telegram.svg" id="sharefollowid-telegram"/>
											<input type="image" src="/twitter.png" id="sharefollowid-twitter"/>
											<input type="image" src="/facebook.png" id="sharefollowid-facebook"/>
											<input type="image" src="/linkedin.png" id="sharefollowid-linkedin"/>
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
									</div>
								</div>
							</div>
						</div>
						<p><?= $tl->get('managefollowersintro', 'html') ?></p>
						<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#generatefollowid-modal"><?= $tl->get('addfollowerbutton', 'html') ?></button>
						<div id="generatefollowid-modal" class="modal">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<form action="#" id="generatefollowid" autocomplete="off">
										<div class="modal-header">
											<h5 class="modal-title"><?= $tl->get('addfollowertitle', 'html') ?></h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div class="modal-body">
											<div class="row">
												<div class="col"><?= $tl->get('addfollowerreference', 'html') ?></div>
												<div class="col"><input name="reference" type="text" placeholder="<?= $tl->get('addfollowerreferenceplaceholder', 'htmlattr') ?>"/></div>
											</div>
											<div class="row">
												<div class="col"><?= $tl->get('addfolloweralias', 'html') ?></div>
												<div class="col"><input name="alias" type="text" placeholder="<?= $tl->get('addfolloweraliasplaceholder', 'htmlattr') ?>"/></div>
											</div>
											<!-- <div class="row">
												<div class="col"><?= $tl->get('addfollowerdelay', 'html') ?></div>
												<div class="col"><input name="delay" type="time"/></div>
											</div> -->
											<div class="row">
												<div class="col"><?= $tl->get('addfollowerexpires', 'html') ?></div>
												<div class="col"><input name="expires" type="datetime-local"/></div>
											</div>
											<div class="row">
												<div class="col"><?= $tl->get('addfollowerenable', 'html') ?></div>
												<div class="col"><input name="enabled" type="checkbox"/></div>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $tl->get('addfollowerclosebutton', 'html') ?></button>
											<button type="submit" class="btn btn-primary"><?= $tl->get('addfollowercreatebutton', 'html') ?></button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="configuration" role="tabpanel" aria-labelledby="configuration-tab">
						<p><?= $tl->get('yoursharingid', NULL, $shareID->encode()) ?></p>
						<p><?= $tl->get('dontsharesharingid') ?></p>
						<p><?= $tl->get('bookmarkthissharingurl', 'html') ?></p>
						<p><?= $tl->get('sharingidcantberecoveredwarning', 'html') ?></p>
						<p><?= $tl->get('configurealiasintro', 'html') ?></p>
						<form action="#" id="configuration" autocomplete="off">
							<?= $tl->get('configurealias', 'html') ?> <input name="alias" type="text" value="<?= htmlspecialchars(@$shareID['alias']) ?>"/>
						</form>
						<h3><?= $tl->get('integration', 'html') ?></h3>
						<h4><?= $tl->get('integrationapiosmand', 'html') ?></h4>
						<div>
							<p><?= $tl->get('integrationosmandintroduction') ?></p>
							<p><?= $protocol . $_SERVER['HTTP_HOST'] ?>/<?= $shareID->encode() ?>?la={0}&amp;lo={1}&amp;hd={3}&amp;al={4}&amp;sp={5}</p>
						</div>
						<h4><?= $tl->get('integrationapi', 'html') ?></h4>
						<div><a href="/apidoc" rel="noopener noreferrer"><?= $tl->get('integrationapidocumentation', 'html') ?></a></div>
					</div>
				</div>
				<div class="d-none d-sm-block">
					<footer class="pt-4 my-md-5 pt-md-5 border-top">
						<div class="row">
							<div class="col-md">
								<div class="row">
									<div class="col">
										<h5><?= $tl->get('footerabout', 'html') ?></h5>
										<ul class="list-unstyled text-small">
											<li><a class="text-muted" href="/credits" rel="noopener noreferrer"><?= $tl->get('credits', 'html') ?></a></li>
											<li><a class="text-muted" href="https://blog.follw.app/" target="_blank" rel="noopener noreferrer"><?= $tl->get('blog', 'html') ?></a></li>
										</ul>
									</div>
									<div class="col">
										<h5><?= $tl->get('footerprivacy', 'html') ?></h5>
										<ul class="list-unstyled text-small">
											<li><a class="text-muted" href="/privacy" rel="noopener noreferrer"><?= $tl->get('privacystatement', 'html') ?></a></li>
											<li><a class="text-muted" href="/terms" rel="noopener noreferrer"><?= $tl->get('termsconditions', 'html') ?></a></li>
										</ul>
									</div>
<?php if(isset($configuration['app'])) {
	$platforms = [ 'play' => 'Android',
		'itunes' => 'iOS'
	];
?>
									<div class="col">
										<h5><?= $tl->get('footerapps', 'html') ?></h5>
										<ul class="list-unstyled text-small">
<?php	foreach($configuration['app'] as $app) { ?>
											<li><a class="text-muted" href="<?= $app['url'] ?>" target="_blank" rel="noopener noreferrer"><?= $tl->get('appfor', 'html') ?> <?= $platforms[$app['platform']] ?></a></li>
<?php	} ?>
										</ul>
									</div>
<?php } ?>
								</div>
							</div>
						</div>
					</footer>
				</div>
			</div>
		</main>
<?php // Scripts ?>
		<script src="https://unpkg.com/jquery@3.5.1/dist/jquery.min.js"
			integrity="sha384-ZvpUoO/+PpLXR1lu4jmpXWu80pZlYUAfxl5NsBMWOEPSjUn/6Z/hRTt8+pR6L4N2"
			crossorigin="anonymous"></script>
		<script src="https://unpkg.com/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
			integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
			crossorigin="anonymous"></script>
		<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
			integrity="sha384-RFZC58YeKApoNsIbBxf4z6JJXmh+geBSgkCQXFyh+4tiFSJmJBt+2FbjxW7Ar16M"
			crossorigin="anonymous"></script>
		<script src="/follw.js" crossorigin="anonymous"></script>
		<script>
			'use strict';

			// Configuration
			function submitConfig(config) {
				$.post("/<?=$shareID->encode()?>/config", config, function() {
				});
			}

			$(function() {
				$('#configuration input[name="alias"]').on("keydown", function(e) {
					if(e.keyCode == 13) { // Enter
						event.preventDefault();
						var config = {};
						config['alias'] = $(this).val();
						submitConfig(config);
					}
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
					this.map = new Follw("shareLocationMap", "/<?=$shareID->encode()?>", 12);
					this.map.nolocation = <?= $tl->get('nolocation', 'js') ?>;
					this.map.addEventListener('locationchanged', onLocationChange);
					this.map.addEventListener('iddeleted', function () { location.reload(); });
					this.map.startUpdate();

					// See if DOM is already available
					if (document.readyState === 'complete' || document.readyState === 'interactive') {
						// call on next available tick
						var _this = this;
						setTimeout(function() {
							_this.init();
						}, 1);
					} else {
						var _this = this;
						document.addEventListener('DOMContentLoaded', function() {
							_this.init();
						});
					}

				}

				init() {
					var _this = this;
					this.map.map.on('click', function(event) {
						if(!_this.isSharing) { 
							_this.updateLocation({latitude: event.latlng.lat, longitude: event.latlng.lng});
						}
					});
				}

				startSharing() {
					if(!this.isSharing) {
						this.isSharing = true;
						this.map.pauseUpdate();

						var _this = this;
						this.watchLocationID = navigator.geolocation.watchPosition(function(position) {
							_this.updateLocation(position.coords);
						});

						$('#start_pause_devicelocation').text(<?= $tl->get('pausesharing', 'js') ?>);
						$('#deletelocation').text(<?= $tl->get('stopsharing', 'js') ?>);
						$("#deletelocation").prop("disabled", false);
					}
				}
				
				stopSharing() {
					this.isSharing = false;
					this.map.resumeUpdate();
					navigator.geolocation.clearWatch(this.watchLocationID);
					$('#start_pause_devicelocation').text(<?= $tl->get('startsharing', 'js') ?>);
					$('#deletelocation').text(<?= $tl->get('deletelocation', 'js') ?>);
				}

				deleteLocation() {
					var _this = this;
					_this.stopSharing();
					$.get("/<?=$shareID->encode()?>/deletelocation", function(data) {
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
					$.post("/<?=$shareID->encode()?>", location, function() {
						_this.map.setMarker([location.latitude, location.longitude], location.accuracy);
						_this.previousLocation = location;
						_this.lastShare = Date.now();
					});
				}

				prettyPrintCoordinates(latitude, longitude) {
					if(this.map != null) {
						return map.prettyPrintCoordinates(latitude, longitude);
					}

					return '';
				}
			}

			var shareLocation = null;

			$(function() {
				var shareLocation = new ShareLocation();

				// Share location map
				$('a#sharelocation-tab').on('shown.bs.tab', function (e) {
					shareLocation.map.invalidateSize();
				});

				// Share location Geolocation API
				// JavaScript Geolocation API can only be used when using SSL
				if(window.location.protocol == 'https:' && 'geolocation' in navigator) {
					$('.geolocationenabled').show();
					$('.geolocationdisabled').hide();

					$('#start_pause_devicelocation').click(function() {
						if(shareLocation.isSharing) {
							shareLocation.stopSharing();
						} else {
							shareLocation.startSharing();
						}
					});
				}

				// Share location text input
				$('#textlocation').on("keydown", function(e) {
					if(e.keyCode == 13) { // Enter
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

				$('#deletelocation').click(function(event) {
					event.preventDefault();
					shareLocation.deleteLocation();
				});
			});

			// Managing followers
			function updateFollowIDs() {
				$.get("/<?=$shareID->encode()?>/followers.json", function(data) {
					var rows = $('<tbody></tbody>');
					data.forEach(function(entry) {
						var row = $('<tr></tr>');

						var reference = entry['id'];
						if(entry['reference'] != null)
							reference = entry['reference'];

						if(entry['enabled'] && !entry['expired'])
							$(row).append(`<td class="followurl enabled"><a href="${entry['url']}" target="_blank" rel="noopener noreferrer">${reference}</a></td>`);
						else
							$(row).append(`<td class="followurl disabled">${reference}</td>`);

						if(entry['alias'] != null)
							$(row).append(`<td class="alias">${entry['alias']}</td>`);
						else
							$(row).append('<td class="alias"></td>');

						/*if(entry['delay'])
							$(row).append('<td class="delay"></td>');
						else
							$(row).append('<td class="delay">Realtime</td>');*/

						if(entry['expired'])
							$(row).append('<td class="expires">Expired</td>');
						else if(entry['expires'] != null) {
							entry['expires'] = new Date(entry['expires'] * 1000).toLocaleString();
							$(row).append(`<td class="expires">${entry['expires']}</td>`);
						} else
							$(row).append('<td class="expires">Never</td>');

						if(entry['expired'])
							$(row).append(`<td><input type="checkbox" disabled="disabled"/></td>`);
						else if(entry['enabled'])
							$(row).append(`<td><input type="checkbox" id="disable${entry['id']}" checked="checked"/></td>`);
						else
							$(row).append(`<td><input type="checkbox" id="enable${entry['id']}"/></td>`);
						$(`#disable${entry['id']}`, row).click(entry['id'], function(event) {
							disableFollowID(event.data);
						});
						$(`#enable${entry['id']}`, row).click(entry['id'], function(event) {
							enableFollowID(event.data);
						});

						$(row).append('<td class="sharefollowid"><img src="/share_macos.png"/></td>');
						$('.sharefollowid', row).click(entry, function(event) {
							shareFollowID(event.data);
						});

						$(row).append('<td><span class="deletefollowid">&#x1F5D1;</span></td>');
						$('.deletefollowid', row).click(entry['id'], function(event) {
							deleteFollowID(event.data);
						});

						$(rows).append(row);
					});
					$('table#followurls tbody').replaceWith(rows);
				});
			}

			function generateFollowID(followid) {
				$.post("/<?=$shareID->encode()?>/generatefollowid", { reference:null }, function() {
					updateFollowIDs();
				});
			}

			function updateFollowID(followid) {
				$.post("/<?=$shareID->encode()?>/followid/" + follower, { }, function() {
					updateFollowIDs();
				});
			}

			function enableFollowID(followid) {
				$.get("/<?=$shareID->encode()?>/follower/" + followid + "/enable").always(function() {
					updateFollowIDs();
				});
			}

			function disableFollowID(followid) {
				$.get("/<?=$shareID->encode()?>/follower/" + followid + "/disable").always(function() {
					updateFollowIDs();
				});
			}

			function shareFollowID(entry) {
				console.debug(entry);
				var message = `Follow my location ${entry['url']}`;

				// QR code
				$("#sharefollowid-qrcode").attr("src", entry['url'] + "qrcode.svg");

				// Twitter
				$("#sharefollowid-twitter").click(entry, function(event) {
					event.preventDefault();
					window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(event.data['url'])}&text=${encodeURIComponent("Follow my location")}`, "_blank", "noopener,noreferrer");
				});

				// Facebook
				$("#sharefollowid-facebook").click(entry, function(event) {
					event.preventDefault();
					window.open("https://facebook.com/sharer.php?u=" + encodeURIComponent(event.data['url']), "_blank", "noopener,noreferrer");
				});

				// eMail
				$("#sharefollowid-email").click(message, function(event) {
					event.preventDefault();
					window.open(`mailto:?subject=${encodeURIComponent("Follow my location")}&body=${encodeURIComponent(event.data)}`, "_blank", "noopener,noreferrer");
				});

				// LinkedIn
				$("#sharefollowid-linkedin").click(entry, function(event) {
					event.preventDefault();
					window.open("https://www.linkedin.com/sharing/share-offsite/?url=" + encodeURIComponent(event.data['url']), "_blank", "noopener,noreferrer");
				});

				// WhatsApp
				$("#sharefollowid-whatsapp").click(message, function(event) {
					event.preventDefault();
					window.open("whatsapp://send?text=" + encodeURIComponent(event.data), "_blank", "noopener,noreferrer");
				});

				// Telegram
				$("#sharefollowid-telegram").click(entry, function(event) {
					event.preventDefault();
					window.open(`https://t.me/share/url?url=${encodeURIComponent(event.data['url'])}&text=${encodeURIComponent("Follow my location")}`, "_blank", "noopener,noreferrer");
				});

				// Clipboard
				$("#sharefollowid-clipboardtext").val(message);
				$("#sharefollowid-clipboard").click(function(event) {
					event.preventDefault();
					var copyText = document.getElementById("sharefollowid-clipboardtext");
					copyText.select();
					copyText.setSelectionRange(0, 99999);
					document.execCommand("copy");
					alert("Copied the text: " + copyText.value);
				});

				$("#sharefollowid-modal").modal()
			}

			function deleteFollowID(followid) {
				$.get("/<?=$shareID->encode()?>/follower/" + followid + "/delete").always(function() {
					updateFollowIDs();
				});
			}

			$().ready(function() {
				updateFollowIDs();

				$('#generatefollowid').submit(function() {
					event.preventDefault();

					var reference = $('input[name="reference"]', this).val();
					if(reference == "")
						reference = null;
					var alias = $('input[name="alias"]', this).val();
					if(alias == "")
						alias = null;
					var enabled = $('input[name="enabled"]', this).is(':checked');
					var expires = $('input[name="expires"]', this).val();
					if(expires) {
						expires = new Date(expires).getTime()/1000;
						/*console.log(expires);
						console.log(new Date(expires).getTime()/1000);
						// Add timezone to expires timestamp
						var timeOffsetH = Math.floor(new Date().getTimezoneOffset()/60);
						var timeOffsetM = new Date().getTimezoneOffset() % 60;
						if(timeOffsetH == 0)
							expires += "+00:00";
						else {
							if(timeOffsetH > 0)
								expires += "-";
							else {
								expires += "+";
								timeOffsetH = -timeOffsetH;
							}
							if(timeOffsetH < 10)
								expires += "0";
							expires += timeOffsetH;
							if(timeOffsetM > 10)
								expires += ":" + timeOffsetM
							else if(timeOffsetM > 0)
								expires += ":0" + timeOffsetM
							else
								expires += ":00"
						}*/
						console.log(expires);
					} else
						expires = null;

					$.post("/<?=$shareID->encode()?>/generatefollowid", { reference:reference, alias:alias, enabled:enabled, expires:expires }, function() {
						updateFollowIDs();
						$('#generatefollowid-modal').modal('hide');
						$('#generatefollowid').get(0).reset();
					});
				});
			});
<?php
if($showIntro) {
?>

			$(function() {
				if(navigator.platform.toUpperCase().indexOf('MAC') !== -1)
					$('#bookmarkMac').show();
				else if(navigator.platform.toUpperCase().indexOf('WIN') !== -1)
					$('#bookmarkWin').show();
				else if(navigator.userAgent.toUpperCase().indexOf('ANDROID') !== -1)
					$('#bookmarkAndroid').show();
				else if(navigator.userAgent.toUpperCase().indexOf('IPHONE') !== -1
					|| navigator.userAgent.toUpperCase().indexOf('IPAD') !== -1
					|| navigator.userAgent.toUpperCase().indexOf('IPOD') !== -1)
					$('#bookmarkIos').show();
				$('#intro-modal').modal('show');
			});
<?php } ?>
		</script>
	</body>
</html>