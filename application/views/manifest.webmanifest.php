<?php
// Fixes false "Variable is undefined" validation errors
/* @var String $protocol */
/* @var ID $id */

global $configuration;

header('Content-Type: application/manifest+json');

$manifest = json_decode('{
	"name": "Follw",
	"short_name": "Follw",
	"start_url": "",
	"scope": "",
	"description": "Follw is a privacy focused location sharing service",
	"display": "standalone",
	"theme_color": "#ffffff",
	"background_color": "#006400",
	"icons": [
		{
			"src": "\/android-icon-36x36.png",
			"sizes": "36x36",
			"type": "image\/png"
		},
		{
			"src": "\/android-icon-48x48.png",
			"sizes": "48x48",
			"type": "image\/png"
		},
		{
			"src": "\/android-icon-72x72.png",
			"sizes": "72x72",
			"type": "image\/png"
		},
		{
			"src": "\/android-icon-96x96.png",
			"sizes": "96x96",
			"type": "image\/png"
		},
		{
			"src": "\/android-icon-144x144.png",
			"sizes": "144x144",
			"type": "image\/png"
		},
		{
			"src": "\/android-icon-192x192.png",
			"sizes": "192x192",
			"type": "image\/png"
		},
		{
			"src": "\/android-icon-512x512.png",
			"sizes": "512x512",
			"type": "image\/png"
		}
	]
}');

if($id instanceof FollowID) {
	$manifest->name = 'Follw ' . $id['alias'];
	$manifest->short_name = $id['alias'];
}
$manifest->start_url = $protocol . $_SERVER['HTTP_HOST'] . '/' . $id->encode() . '/';
$manifest->scope = $protocol . $_SERVER['HTTP_HOST'] . '/' . $id->encode() . '/';

// Add links to native apps
if($id instanceof ShareID && isset($configuration['app'])) {
	$manifest->prefer_related_applications = TRUE;
	$manifest->related_applications = [];
	foreach($configuration['app'] as $app) {
		$manifest->related_applications[] = $app;
	}
}

$json = json_encode($manifest, $configuration['jsonoptions']);
header('Content-Length: ' . strlen($json));
print($json);