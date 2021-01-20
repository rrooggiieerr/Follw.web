<?php
// Fixes false "Variable is undefined" validation errors
/* @var ID $id */
/* @var Location $location */

global $protocol;

header('Content-Type: application/gpx+xml');
header('Cache-Control: max-age=' . $location->refresh);
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $location->refresh));

print('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
?>
<gpx version="1.0" creator="Follw">
	<metadata>
		<link href="<?= $protocol ?><?= $_SERVER['HTTP_HOST'] ?>">
			<text>Follw</text>
		</link>
		<time><?= date(DateTime::RFC3339, time()) ?></time>
	<metadata>
	<wpt lat="<?= $location['latitude'] ?>" lon="<?= $location['longitude'] ?>">
<?php if(!empty($location['altitude'])) {?>
		<ele><?= $location['altitude'] ?></ele>
<?php } ?>
		<time><?= date(DateTime::RFC3339, $location->timestamp) ?></time>
		<name><?= htmlspecialchars($id['alias']) ?></name>
		<desc>Location shared with Follw</desc>
		<src><?= $_SERVER['HTTP_HOST'] ?></src>
<?php if(!empty($location['accuracy'])) { ?>
		<hdop><?= $location['accuracy'] ?></hdop>
<?php } ?>
	</wpt>
</gpx>