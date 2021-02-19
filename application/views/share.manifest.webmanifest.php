<?php
// Fixes false "Variable is undefined" validation errors
/* @var String $protocol */
/* @var ID $id */

global $configuration;
global $protocol;

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
	"orientation": "any",
	"categories": ["navigation", "location sharing"],
	"icons": [
		{
			"src": "/android-icon-36x36.png",
			"sizes": "36x36",
			"type": "image/png",
			"purpose": "any"
		},
		{
			"src": "/android-icon-48x48.png",
			"sizes": "48x48",
			"type": "image/png",
			"purpose": "any"
		},
		{
			"src": "/android-icon-72x72.png",
			"sizes": "72x72",
			"type": "image/png",
			"purpose": "any"
		},
		{
			"src": "/android-icon-96x96.png",
			"sizes": "96x96",
			"type": "image/png",
			"purpose": "any"
		},
		{
			"src": "/android-icon-144x144.png",
			"sizes": "144x144",
			"type": "image/png",
			"purpose": "any"
		},
		{
			"src": "/android-icon-192x192.png",
			"sizes": "192x192",
			"type": "image/png",
			"purpose": "any"
		},
		{
			"src": "/android-icon-512x512.png",
			"sizes": "512x512",
			"type": "image/png",
			"purpose": "any"
		},
		{
			"src": "/maskable-icon-196x196.png",
			"sizes": "196x196",
			"type": "image/png",
			"purpose": "maskable"
		}
	],
	"shortcuts": [],
	"related_applications": [],
	"prefer_related_applications": false
}');

$manifest->start_url = $protocol . $_SERVER['HTTP_HOST'] . '/' . $shareID->encode() . '/';
$manifest->scope = $protocol . $_SERVER['HTTP_HOST'] . '/' . $shareID->encode() . '/';

// Add links to native apps
if(isset($configuration['features']['share']['app'])) {
	foreach($configuration['features']['share']['app'] as $app) {
		$manifest->prefer_related_applications = TRUE;
		$manifest->related_applications[] = $app;
	}
}

// Add link to PWA
if(isset($configuration['features']['share']['pwa']) &&
		$configuration['features']['share']['pwa'] == TRUE) {
	$manifest->prefer_related_applications = FALSE;
	$manifest->related_applications[] = ['platform' => 'webapp', 'url' => $protocol . $_SERVER['HTTP_HOST'] . '/' . $shareID->encode() . '/manifest.webmanifest'];
}

$json = json_encode($manifest, $configuration['jsonoptions']);
header('Content-Length: ' . strlen($json));
print($json);