<?php
// Fixes false "Variable is undefined" validation errors
/* @var FollowID $id */
/* @var Location $location */

global $protocol;

header('Content-Type: application/vnd.google-earth.kml+xml');
header('Cache-Control: max-age=' . $location->refresh);
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $location->refresh));

print('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
?>
<kml xmlns="http://www.opengis.net/kml/2.2">
	<NetworkLinkControl>
		<expires><?= date(DateTime::RFC3339, time() + $location->refresh) ?></expires>
		<Update>
			<targetHref><?= $protocol ?><?= $_SERVER['HTTP_HOST'] ?>/<?= $id->encode() ?>.kml</targetHref>
			<Change>
				<Point targetId="<?= $id->encode() ?>">
					<coordinates><?= $location['longitude'] ?>,<?= $location['latitude'] ?></coordinates>
				</Point>
			</Change>
		</Update>
	</NetworkLinkControl>
</kml>