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
<?php // Unregistering Service Worker ?>
		if(window.navigator && navigator.serviceWorker) {
			navigator.serviceWorker.getRegistration("/<?= $id->encode() ?>/").then((registration) => {
				if(registration){
					console.debug("Unregistering Service Worker");
					registration.unregister()
				} else {
					console.debug("No Service Worker to unregister");
				}
			});
		}

<?php // Delete Local Cache ?>
		if(window.caches) {
			caches.delete("<?= $id->encode() ?>").then((success) => {
				if(success) {
					console.debug("Deleted Local Cache");
				} else {
					console.debug("No Local Cache to be deleted");
				}
			});
		}

<?php // Delete local storage ?>
		window.localStorage.removeItem("<?= $id->encode() ?>");
	</script>
</html>