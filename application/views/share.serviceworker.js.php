<?php
global $configuration;

if(isset($configuration['features']['follow']['pwa']) &&
		$configuration['features']['follow']['pwa'] == TRUE) {
	http_response_code(404);
	exit();
}

header('Content-Type: text/javascript');
?>
