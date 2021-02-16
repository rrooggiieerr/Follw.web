<?php
// Fixes false "Variable is undefined" validation errors
/* @var ID $id */
?>
<html>
	<head>
		<title>ID has been deleted</title>
	</head>
	<body>
		ID has been deleted
	</body>
	<script>
		if(window.navigator && navigator.serviceWorker) {
			navigator.serviceWorker.getRegistration("/<?= $id->encode() ?>/").then(function(registration) {
				if(registration){
					console.debug("Unregistering Service Worker");
					registration.unregister()
				} else {
					console.debug("No Service Worker to unregister");
				}
			});
		}
	</script>
</html>