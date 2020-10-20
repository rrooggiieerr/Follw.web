<?php
// Fixes false "Variable is not defined" validation errors for variables created in other files
/* @var String $protocol */
/* @var Integer $id */
?>
<!doctype html>
<html lang="en">
	<head>
		<title>Follw - Sharing your location with privacy</title>
		<link rel="manifest" href="/<?=bin2hex($id)?>/manifest.webmanifest">
<?php // Styles ?>
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
			integrity="sha384-VzLXTJGPSyTLX6d96AxgkKvE/LRb7ECGyTxuwtpjHnVWVZs2gp5RDjeM/tgBnVdM"
			crossorigin="anonymous"/>
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"
			crossorigin="anonymous">
		<style>
			#enablegeolocation {
				display: none;
			}
			
			#shareLocationMap {
				height: 250px;
			}
			
			#followurls tbody td.followurl {
				font-family: monospace;
			}

			code {
				display: block;
				white-space: pre;
				background-color: #F8F8F8;
			}
		</style>
<?php // Scripts ?>
		<script src="https://unpkg.com/jquery@3.5.1/dist/jquery.js"
			integrity="sha384-/LjQZzcpTzaYn7qWqRIWYC5l8FWEZ2bIHIz0D73Uzba4pShEcdLdZyZkI4Kv676E"
			crossorigin="anonymous"></script>
		<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"  crossorigin="anonymous"></script>
		<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
			integrity="sha384-RFZC58YeKApoNsIbBxf4z6JJXmh+geBSgkCQXFyh+4tiFSJmJBt+2FbjxW7Ar16M"
			crossorigin="anonymous"></script>
		<script src="/follw.js" crossorigin="anonymous"></script>
		<script>
			function onDelete() {
				location.reload();
			}

 			var shareLocationMap = new Follw("shareLocationMap", "/<?=bin2hex($id)?>", 12);
 			shareLocationMap.onIDDeleted(onDelete);

			$(function() {
				$("#tabs").tabs({
					activate: function( event, ui ) {
						switch(ui.newPanel.attr('id')) {
							case 'sharelocation':
								shareLocationMap.invalidateSize();
								break;
						}
					}
				});
			});
			
			function setMyLocation(location) {
				$.post("/<?=bin2hex($id)?>", location, function() {
					shareLocationMap.getLocation(true);
				});
			}
		
			function updateFollowURLs() {
				$.get("/<?=bin2hex($id)?>/followers.json", function(data) {
					var rows = $('<tbody></tbody>');
					data.forEach(function(entry) {
 						var row = $('<tr></tr>');

 						if(entry['enabled'] && !entry['expired'])
							$(row).append(`<td class="followurl enabled"><a href="${entry['url']}" target="_blank">${entry['url']}</a></td>`);
 						else
							$(row).append(`<td class="followurl disabled">${entry['url']}</td>`);

						if(entry['reference'] != null)
							$(row).append(`<td class="reference">${entry['reference']}</td>`);
						else
							$(row).append('<td class="reference"></td>');

						if(entry['alias'] != null)
							$(row).append(`<td class="alias">${entry['alias']}</td>`);
						else
							$(row).append('<td class="alias"></td>');

						if(entry['enabled'])
							$(row).append('<td><button class="disablefollowid">Disable</button></td>');
						else
							$(row).append('<td><button class="enablefollowid">Enable</button></td>');
						$('.disablefollowid', row).click(entry['id'], function(event) {
							disableFollowID(event.data);
						});
						$('.enablefollowid', row).click(entry['id'], function(event) {
							enableFollowID(event.data);
						});

						if(entry['expired'])
							$(row).append('<td class="expires">Expired</td>');
						else if(entry['expires'] != null)
							$(row).append(`<td class="expires">${entry['expires']}</td>`);
						else
							$(row).append('<td class="expires">Never</td>');

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
				$.post("/<?=bin2hex($id)?>/generatefollowid", { reference:null }, function() {
					updateFollowURLs();
				});
			}

			function updateFollowID(followid) {
				$.post("/<?=bin2hex($id)?>/followid/" + follower, { }, function() {
					updateFollowURLs();
				});
			}

			function enableFollowID(followid) {
				$.get("/<?=bin2hex($id)?>/follower/" + followid + "/enable", function() {
					updateFollowURLs();
				});
			}
			
			function disableFollowID(followid) {
				$.get("/<?=bin2hex($id)?>/follower/" + followid + "/disable", function() {
					updateFollowURLs();
				});
			}

			function deleteFollowID(followid) {
				$.get("/<?=bin2hex($id)?>/follower/" + followid + "/delete", function() {
					updateFollowURLs();
				});
			}
			
			$().ready(function() {
				shareLocationMap.map.on('click', function(event) {
					setMyLocation({latitude: event.latlng.lat, longitude: event.latlng.lng});
				});

				// JavaScript Geolocation API can only be used when using SSL
				if(window.location.protocol == 'https:' && 'geolocation' in navigator) {
					$('#enablegeolocation').show();
				
					$('#devicelocation').click(function() {
						$('#devicelocation').prop('disabled', true);
						navigator.geolocation.getCurrentPosition(function(position) {
							setMyLocation(position.coords);
							$('#devicelocation').prop('disabled', false);
						});
					});
				}

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
							setMyLocation({latitude: latitude, longitude: longitude});
						}

						event.preventDefault();
					}
				});

				// Configuration
				$('#configuration input[name="alias"]').on("keydown", function(e) {
					if(e.keyCode == 13) { // Enter
						var val = $(this).val();
						
						console.log(val);

						return false;
					}
				});
				
				// Followers
				updateFollowURLs();

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

					$.post("/<?=bin2hex($id)?>/generatefollowid", { reference:reference, alias:alias, enabled:enabled, expires:expires }, function() {
						updateFollowURLs();
					});
					event.preventDefault();
				});
			});
		</script>
	</head>
	<body>
		<h1>Follw</h1>
		<h2>Sharing your location with privacy</h2>
		<div  id="tabs">
			<ul>
				<li><a href="#welcome">Welcome to Follw</a></li>
				<li><a href="#sharelocation">Share your location</a></li>
				<li><a href="#followers">Followers</a></li>
				<li><a href="#apps">Use an app</a></li>
				<li><a href="#integration">Integration</a></li>
				<li><a href="#privacy">Privacy</a></li>
			</ul>
			<div id="welcome">
				<p>Your Location Sharing URL: <?= $protocol . $_SERVER['HTTP_HOST'] ?>/<?=bin2hex($id)?></p>
				<p>Bookmark this Location Sharing URL to always get back to your location sharing environment.</p>
				<p>Because Follw doesn't have your contact details this Location Sharing URL can not be recovered if
				you lose it.</p>
				<p>To share your location with followers generate a Location Follow URL, <b>don't share this Location
				Sharing URL with your followers</b>.</p>
				<h2>Configuration</h2>
				<p>You can configure an alias which your Followers see so they know who they are following. This is not
				required and can be anything, it does not have to be your name or anything that gives away who you
				are.</p>
				<form action="#" id="configuration">
					Alias: <input name="alias" type="text"/>
				</form>
			</div>
			<div id="sharelocation">
				<h3>Set your location</h3>
				<p>Different methods for sharing your location are available.</p>
				<div id="enablegeolocation">
					<h4>Get your location from your device</h4>
					<p>The device you're using to access this Location Sharing URL is capable to share it's
					location.</p>
					<button id="devicelocation">Request device location</button>
				</div>
				<h4>Select your location on the map</h4>
				<p>Select a location on the map, just click on the position you like to share the location of.</p>
				<div id="shareLocationMap"></div>
				<h4>Text input</h4>
				<p>You can type the latitude and longitude of the location you like to share.</p>
				<form action="#">
					<input type="text" id="textlocation"/>
				</form>
			</div>
			<div id="followers">
				<h3>Share your location with followers</h3>
				<p>To have your location followed by others you can generate Follow URLs and manage who is allowed to
				see your location.</p>
				<h4>Your Follow URLs</h4>
				<table id="followurls">
					<thead>
						<tr>
							<td>Follow URL</td>
							<td>Reference</td>
							<td>Alias</td>
							<td>Disabled</td>
							<td>Expires</td>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
				<p>Create a new unique Follow URL</p>
				<form action="#" id="generatefollowurl">
					<input name="reference" type="text" placeholder="Reference for your convenience"/>
					<input name="alias" type="text" placeholder="Alias override"/>
					<input name="enabled" type="checkbox"/>
					<input name="expires" type="datetime-local"/>
					<input type="submit" value="Create Follow URL"/>
				</form>
			</div>
			<div id="apps">
				<!--
				<h4>Install the Follw app</h4>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore
				et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut
				aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
				dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui
				officia deserunt mollit anim id est laborum.</p>
				<p>Install the <a href="#">Follw app from the Google Play Store</a></p>
				<p>Install the <a href="#">Follw app from the Apple App Store</a></p>
				-->
				<h4>Configure OsmAnd</h4>
				<p>Install OsmAnd from the Google Play Store or Apple App Store and use the logging functionality to
				share your location.</p>
				<p>You can configure OsmAnd to automatically log your current position when you are online</p>
				<p><?= $protocol . $_SERVER['HTTP_HOST'] ?>/<?=bin2hex($id)?>?la={0}&amp;lo={1}&amp;hd={3}&amp;al={4}&amp;sp={5}</p>
				<p>Set the <i>time buffer</i> to the lowest value of 1 minute.</p>
			</div>
			<div id="integration">
				<h4>Integrate Follow URL in your HTML Website</h4>
				<p>You can embed a map with your location in any website by including the following code in you HTML
				header.</p>
<code>&lt;style&gt;
	#follwMap {
		height: 250px;
	}
&lt;/style&gt;
&lt;link rel=&quot;stylesheet&quot; href=&quot;//unpkg.com/leaflet@1.7.1/dist/leaflet.css&quot;
	integrity=&quot;sha384-VzLXTJGPSyTLX6d96AxgkKvE/LRb7ECGyTxuwtpjHnVWVZs2gp5RDjeM/tgBnVdM&quot;
	crossorigin=&quot;anonymous&quot;/&gt;
&lt;script src=&quot;//unpkg.com/leaflet@1.7.1/dist/leaflet.js&quot;
	integrity=&quot;sha384-RFZC58YeKApoNsIbBxf4z6JJXmh+geBSgkCQXFyh+4tiFSJmJBt+2FbjxW7Ar16M&quot;
	crossorigin=&quot;anonymous&quot;&gt;&lt;/script&gt;
&lt;script src=&quot;//<?= $_SERVER['HTTP_HOST'] ?>/follw.js&quot; crossorigin=&quot;anonymous&quot;&gt;&lt;/script&gt;
&lt;script&gt;
	new Follw(&quot;follwMap&quot;, &quot;<?= $protocol . $_SERVER['HTTP_HOST'] ?>/followid&quot;, 12);
&lt;/script&gt;</code>
				<p>And include <code>&lt;div id=&quot;follwMap&quot;&gt;&lt;/div&gt;</code> wherever you want to show
				the map with your location.</p>
				<h4>Integrate Follow URL in your WordPress blog</h4>
				<p>Follw.app WordPress plugin is yet to be developped.</p>
				<h4>Share locations from your Python compatible devices</h4>
				<p>Get the Follw.app Python 3 client on
				<a href="https://github.com/rrooggiieerr/Follw.py" target="_blank">GitHub</a>.</p>
			</div>
			<div id="privacy">
				<p>Privacy statement goes here.</p>
			</div>
		</div>
	</body>
</html>