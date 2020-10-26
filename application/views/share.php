<?php
// Fixes false "Variable is not defined" validation errors for variables created in other files
/* @var String $protocol */
/* @var Integer $shareid */
?>
<!doctype html>
<html lang="en">
	<head>
		<title>Follw · Sharing your location with privacy</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
		<link rel="manifest" href="/<?=$shareid?>/manifest.webmanifest" />
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
					<li class="nav-item"><a class="nav-link active" id="welcome-tab" data-toggle="tab" href="#welcome" role="tab" aria-controls="welcome" aria-selected="true">Welcome to Follw</a></li>
					<li class="nav-item"><a class="nav-link" id="sharelocation-tab" data-toggle="tab" href="#sharelocation" role="tab" aria-controls="sharelocation" aria-selected="false">Share your location</a></li>
					<li class="nav-item"><a class="nav-link" id="followers-tab" data-toggle="tab" href="#followers" role="tab" aria-controls="followers" aria-selected="false">Followers</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="welcome" role="tabpanel" aria-labelledby="welcome-tab">
						<p>Your Location Sharing ID: <b><?= $shareid ?></b></p>
						<p>To share your location generate a Follow ID, <b>don't share this Location
						Sharing ID with your followers</b>.</p>
						<p>Bookmark this Location Sharing URL to always get back to your location sharing environment.</p>
						<p>Because Follw doesn't have your contact details this Location Sharing ID can not be recovered if
						you lose it.</p>
						<h2>Configuration</h2>
						<p>You can configure an alias which your Followers see so they know who they are following. This is not
						required and can be anything, it does not have to be your name or anything that gives away who you
						are.</p>
						<form action="#" id="configuration">
							Alias: <input name="alias" type="text" value="<?= $config['alias'] ?>"/>
						</form>
					</div>
					<div class="tab-pane" id="sharelocation" role="tabpanel" aria-labelledby="sharelocation-tab">
						<p>Select the location you like to share on the map. <button id="deletelocation" class="btn btn-primary btn-sm">Delete location</button></p>
						<div id="shareLocationMap"></div>
						<div class="container">
							<div class="row">
								<div class="col-md" id="enablegeolocation">
									<h4>Get your location from your device</h4>
									<p>The device you're using is capable to share it's location.</p>
									<button id="devicelocation" class="btn btn-primary btn-sm">Request device location</button>
								</div>
								<div class="col-md">
									<h4>Text input</h4>
									<p>Type the latitude and longitude of the location you like to share.</p>
									<form action="#">
										<input type="text" id="textlocation"/>
									</form>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="followers" role="tabpanel" aria-labelledby="followers-tab">
						<table id="followurls" class="table table-striped table-sm">
							<thead>
								<tr>
									<th>Follow ID/Reference</th>
									<th>Alias Override</th>
									<th>Delay</th>
									<th>Expires</th>
									<th>Enabled</th>
									<th>Delete</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
						<p>Create a Follow ID and manage who is allowed to see your location.</p>
						<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#generatefollowid-modal">Create Follow ID</button>
						<div id="generatefollowid-modal" class="modal">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title">Create a Follow ID</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<form action="#" id="generatefollowurl">
											<span>Reference for your convenience</span><input name="reference" type="text" placeholder="Reference"/><br/>
											<span>Alias override</span><input name="alias" type="text" placeholder="Alias override"/><br/>
											<span>Delay</span><input name="delay" type="checkbox"/><br/>
											<span>Expires</span><input name="expires" type="datetime-local"/><br/>
											<span>Enabled</span><input name="enabled" type="checkbox"/><br/>
											<input type="submit" value="Create Follow ID"/><br/>
										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
										<button type="button" class="btn btn-primary">Create Follow ID</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
<?php include_once('footer.php');?>
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
// 			function onDelete() {
// 				location.reload();
// 			}

			// Configuration
			function submitConfig(config) {
				console.log(config);
				$.post("/<?=$shareid?>/config", config, function() {
					console.log("Yay!");
				});
			}

			$(function() {
				$('#configuration input[name="alias"]').on("keydown", function(e) {
					if(e.keyCode == 13) { // Enter
						var config = {};
						config['alias'] = $(this).val();
						submitConfig(config);
						event.preventDefault();
					}
				});
			});

			// Location sharing
			// Create the share location map
			var shareLocationMap = new Follw("shareLocationMap", "/<?=$shareid?>", 12);
			shareLocationMap.onIDDeleted(function () { location.reload(); });

			function setLocation(location) {
				$.post("/<?=$shareid?>", location, function() {
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

						event.preventDefault();
					}
				});
			});
			
			// Managing followers
			function updateFollowIDs() {
				$.get("/<?=$shareid?>/followers.json", function(data) {
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

						if(entry['delay'])
							$(row).append('<td class="delay"></td>');
						else
							$(row).append('<td class="delay">Realtime</td>');

						if(entry['expired'])
							$(row).append('<td class="expires">Expired</td>');
						else if(entry['expires'] != null)
							$(row).append(`<td class="expires">${entry['expires']}</td>`);
						else
							$(row).append('<td class="expires">Never</td>');

						if(entry['enabled'])
							$(row).append(`<td><input class="form-check-input" type="checkbox" id="disable${entry['id']}" checked="checked"/></td>`);
						else
							$(row).append(`<td><input class="form-check-input" type="checkbox" id="enable${entry['id']}"/></td>`);
						$(`#disable${entry['id']}`, row).click(entry['id'], function(event) {
							disableFollowID(event.data);
						});
						$(`#enable${entry['id']}`, row).click(entry['id'], function(event) {
							enableFollowID(event.data);
						});

						$(row).append(`<td><span class="deletefollowid">&#x1F5D1;</span></td>`);
						$('.deletefollowid', row).click(entry['id'], function(event) {
							deleteFollowID(event.data);
						});

						$(rows).append(row);
					});
					$('table#followurls tbody').replaceWith(rows);
				});
			}
			
			function generateFollowID(followid) {
				$.post("/<?=$shareid?>/generatefollowid", { reference:null }, function() {
					updateFollowIDs();
				});
			}

			function updateFollowID(followid) {
				$.post("/<?=$shareid?>/followid/" + follower, { }, function() {
					updateFollowIDs();
				});
			}

			function enableFollowID(followid) {
				$.get("/<?=$shareid?>/follower/" + followid + "/enable", function() {
					updateFollowIDs();
				});
			}
			
			function disableFollowID(followid) {
				$.get("/<?=$shareid?>/follower/" + followid + "/disable", function() {
					updateFollowIDs();
				});
			}

			function deleteFollowID(followid) {
				$.get("/<?=$shareid?>/follower/" + followid + "/delete", function() {
					updateFollowIDs();
				});
			}
			
			$(function() {
				updateFollowIDs();

				$('#generatefollowurl').submit(function() {
					var reference = $('input[name="reference"]', this).val();
					if(reference == "")
						reference = null;
					var alias = $('input[name="alias"]', this).val();
					if(alias == "")
						alias = null;
					var enabled = $('input[name="enabled"]', this).is(':checked');
					var expires = $('input[name="expires"]', this).val();
					if(expires == "")
						expires = null;
					else {
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
						}
					}

					$.post("/<?=$shareid?>/generatefollowid", { reference:reference, alias:alias, enabled:enabled, expires:expires }, function() {
						updateFollowIDs();
					});
					event.preventDefault();
				});
			});
		</script>
	</body>
</html>