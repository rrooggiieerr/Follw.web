<?php
// Fixes false "Variable is undefined" validation errors
/* @var ShareID $shareID */
/* @var Boolean $showIntro */

global $configuration
?>
<!doctype html>
<html lang="en">
	<head>
		<title>Follw · Sharing your location with privacy</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
		<meta name="robots" content="noindex" />
		<link rel="manifest" href="/<?=$shareID->encode()?>/manifest.webmanifest" />
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
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
			integrity="sha384-VzLXTJGPSyTLX6d96AxgkKvE/LRb7ECGyTxuwtpjHnVWVZs2gp5RDjeM/tgBnVdM"
			crossorigin="anonymous"/>
		<style>
			#enablegeolocation {
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
				<div class="jumbotron">
					<h1>Follw <small class="h4 text-muted">· Sharing your location with privacy</small></h1>
				</div>
				<ul class="nav nav-tabs">
					<li class="nav-item"><a class="nav-link active" id="sharelocation-tab" data-toggle="tab" href="#sharelocation" role="tab" aria-controls="sharelocation" aria-selected="true">Share your location</a></li>
					<li class="nav-item"><a class="nav-link" id="followers-tab" data-toggle="tab" href="#followers" role="tab" aria-controls="followers" aria-selected="false">Manage Followers</a></li>
					<li class="nav-item"><a class="nav-link" id="configuration-tab" data-toggle="tab" href="#configuration" role="tab" aria-controls="configuration" aria-selected="false">Configuration</a></li>
				</ul>
				<div class="tab-content">
<?php
if($showIntro) {
?>
					<div id="intro-modal" class="modal">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title">Welcome to Follw</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<p>Your Location Sharing ID: <b><?= $shareID->encode() ?></b></p>
									<p>To share your location generate a Follow ID, <b>don't share this Location
									Sharing ID with your followers</b>.</p>
									<p>Bookmark this Location Sharing URL to always get back to your location sharing environment.</p>
									<p id="bookmarkMac" style="display: none;">Press <b>⌘D</b> to bookmark this Sharing URL.</p>
									<p id="bookmarkWin" style="display: none;">Press <b>...</b> to bookmark this Sharing URL.</p>
									<p id="bookmarkAndroid" style="display: none;">Press <b>⋮</b> and then <b>Add to Home screen</b> to add this Sharing URL as a WebApp to your home screen.</p>
									<p id="bookmarkIos" style="display: none;">Press <b>Action</b> and then <b>Add to Home Screen</b> to add this Sharing URL as a WebApp to your home screen.</p>
									<p>Because Follw doesn't have your contact details this Location Sharing ID can not be recovered if
									you lose it.</p>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
								</div>
							</div>
						</div>
					</div>
<?php } ?>
					<div class="tab-pane active" id="sharelocation" role="tabpanel" aria-labelledby="sharelocation-tab">
						<p>Select the location you like to share on the map. <button id="deletelocation" class="btn btn-primary btn-sm">Delete location</button></p>
						<div id="shareLocationMap"></div>
						<div class="container">
							<div class="row">
								<div class="col-md" id="enablegeolocation">
									<h4>Get your location from your device</h4>
									<p>The device you're using is capable to share it's location.</p>
									<button id="devicelocation" class="btn btn-primary btn-sm">Request device location</button>
								</div>
<?php
if($configuration['mode'] == 'development') {
?>
								<div class="col-md">
									<h4>Text input</h4>
									<p>Type the latitude and longitude of the location you like to share.</p>
									<form action="#" autocomplete="off">
										<input type="text" id="textlocation"/>
									</form>
								</div>
<?php } ?>
<?php
if($configuration['mode'] == 'development') {
?>
								<div class="col-md">
									<h4>Google real-time location URL</h4>
									<p>Paste the Google real-time location URL you like to share.</p>
									<form action="#" autocomplete="off">
										<input type="text" id="googlelocation"/>
									</form>
								</div>
<?php } ?>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="followers" role="tabpanel" aria-labelledby="followers-tab">
						<table id="followurls" class="table table-striped table-sm">
							<thead>
								<tr>
									<th>Follow ID/Reference</th>
									<th>Alias Override</th>
									<!-- <th>Delay</th> -->
									<th>Expires</th>
									<th>Enabled</th>
									<th>Share</th>
									<th>Delete</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
						<div id="sharefollowid-modal" class="modal">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title">Share Follow ID</h5>
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
						<p>Create a Follow ID and manage who is allowed to see your location.</p>
						<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#generatefollowid-modal">Create Follow ID</button>
						<div id="generatefollowid-modal" class="modal">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<form action="#" id="generatefollowid" autocomplete="off">
										<div class="modal-header">
											<h5 class="modal-title">Create a Follow ID</h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div class="modal-body">
											<div class="row">
												<div class="col">Reference for your convenience</div>
												<div class="col"><input name="reference" type="text" placeholder="Reference"/></div>
											</div>
											<div class="row">
												<div class="col">Alias override</div>
												<div class="col"><input name="alias" type="text" placeholder="Alias override"/></div>
											</div>
											<!-- <div class="row">
												<div class="col">Delay</div>
												<div class="col"><input name="delay" type="time"/></div>
											</div> -->
											<div class="row">
												<div class="col">Expires</div>
												<div class="col"><input name="expires" type="datetime-local"/></div>
											</div>
											<div class="row">
												<div class="col">Enabled</div>
												<div class="col"><input name="enabled" type="checkbox"/></div>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
											<button type="submit" class="btn btn-primary">Create Follow ID</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="configuration" role="tabpanel" aria-labelledby="configuration-tab">
						<p>Your Location Sharing ID: <b><?= $shareID->encode() ?></b></p>
						<p>To share your location generate a Follow ID, <b>don't share this Location
						Sharing ID with your followers</b>.</p>
						<p>Bookmark this Location Sharing URL to always get back to your location sharing environment.</p>
						<p>Because Follw doesn't have your contact details this Location Sharing ID can not be recovered if
						you lose it.</p>
						<p>You can configure an alias which your Followers see so they know who they are following. This is not
						required and can be anything, it does not have to be your name or anything that gives away who you
						are.</p>
						<form action="#" id="configuration" autocomplete="off">
							Alias: <input name="alias" type="text" value="<?= htmlspecialchars(@$shareID['alias']) ?>"/>
						</form>
					</div>
				</div>
				<div class="d-none d-sm-block">
<?php include_once('footer.php');?>
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

// 			function onDelete() {
// 				location.reload();
// 			}

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
			// Create the share location map
			var shareLocationMap = new Follw("shareLocationMap", "/<?=$shareID->encode()?>", 12);
			shareLocationMap.onIDDeleted(function () { location.reload(); });

			function setLocation(location) {
				$.post("/<?=$shareID->encode()?>", location, function() {
					shareLocationMap.getLocation(true);
				});
			}

			$(function() {
				// Share location map
				$('a#sharelocation-tab').on('shown.bs.tab', function (e) {
					shareLocationMap.invalidateSize();
				});

				shareLocationMap.map.on('click', function(event) {
					setLocation({latitude: event.latlng.lat, longitude: event.latlng.lng});
				});

				// Share location Geolocation API
				// JavaScript Geolocation API can only be used when using SSL
				if(window.location.protocol == 'https:' && 'geolocation' in navigator) {
					$('#enablegeolocation').show();

					$('#devicelocation').click(function() {
						$('#devicelocation').prop('disabled', true);
						navigator.geolocation.getCurrentPosition(function(position) {
							setLocation(position.coords);
							$('#devicelocation').prop('disabled', false);
						});
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
							setLocation({latitude: latitude, longitude: longitude});
						}
					}
				});

				$('#deletelocation').click(function(event) {
					event.preventDefault();

					$.get("/<?=$shareID->encode()?>/deletelocation", function(data) {
						shareLocationMap.getLocation(true);
					});
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
							$(row).append(`<td class="followurl enabled"><a href="${entry['url']}" target="_blank">${reference}</a></td>`);
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
				$("#sharefollowid-qrcode").attr("src", entry['url'] + "/qrcode.svg");

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

			$(function() {
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