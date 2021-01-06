<?php
// Fixes false "Variable is undefined" validation errors
/* @var String $protocol */
/* @var ID $id */
?>
{
	"short_name": "Follw",
	"name": "Follw - Sharing your location with privacy",
	"description": "Follw is a privacy focused location sharing service",
	"start_url": "<?= $protocol . $_SERVER['HTTP_HOST'] ?>/<?=$id->encode()?>/",
	"background_color": "#3367D6",
	"display": "standalone",
	"scope": "<?= $protocol . $_SERVER['HTTP_HOST'] ?>/<?=$id->encode()?>/",
	"theme_color": "#3367D6",
	"icons": [
		{
			"src": "/icons8-location-48.png",
			"sizes": "48x48"
		},
		{
			"src": "/icons8-location-96.png",
			"sizes": "96x96",
			"purpose": "maskable"
		}
	]
}