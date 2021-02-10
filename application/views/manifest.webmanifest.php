<?php
// Fixes false "Variable is undefined" validation errors
/* @var String $protocol */
/* @var ID $id */

$name = 'Follw';
$shortname = 'Follw';
if($id instanceof ShareID) {
	$name = 'Follw' . $id['alias'];
	$shortname = $id['alias'];
}

header('Content-Type: application/manifest+json');
?>
{
	"name": "<?= htmlspecialchars($name) ?>",
	"short_name": "<?= htmlspecialchars($shortname) ?>",
	"description": "Follw is a privacy focused location sharing service",
	"start_url": "<?= $protocol . $_SERVER['HTTP_HOST'] ?>/<?= $id->encode() ?>/",
	"background_color": "#1d9016",
	"display": "standalone",
	"scope": "<?= $protocol . $_SERVER['HTTP_HOST'] ?>/<?= $id->encode() ?>/",
	"theme_color": "#ffffff",
	"icons": [
		{
			"src": "\/android-icon-36x36.png",
			"sizes": "36x36",
			"type": "image\/png",
			"density": "0.75"
		},
		{
			"src": "\/android-icon-48x48.png",
			"sizes": "48x48",
			"type": "image\/png",
			"density": "1.0"
		},
		{
			"src": "\/android-icon-72x72.png",
			"sizes": "72x72",
			"type": "image\/png",
			"density": "1.5"
		},
		{
			"src": "\/android-icon-96x96.png",
			"sizes": "96x96",
			"type": "image\/png",
			"density": "2.0"
		},
		{
			"src": "\/android-icon-144x144.png",
			"sizes": "144x144",
			"type": "image\/png",
			"density": "3.0"
		},
		{
			"src": "\/android-icon-192x192.png",
			"sizes": "192x192",
			"type": "image\/png",
			"density": "4.0"
		}
	]
}