<?php
// Fixes false "Variable is not defined" validation errors for variables created in other files
/* @var String $protocol */
/* @var Integer $id */
?>
{
	"short_name": "Follw.app",
	"name": "Follw.app: Sharing your location anonymous",
	"description": "Anonymous location sharing service",
	"start_url": "<?= $protocol . $_SERVER['HTTP_HOST'] ?>/<?=bin2hex($id)?>/",
	"background_color": "#3367D6",
	"display": "standalone",
	"scope": "<?= $protocol . $_SERVER['HTTP_HOST'] ?>/<?=bin2hex($id)?>/",
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