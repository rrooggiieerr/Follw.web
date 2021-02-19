<?php
// Fixes false "Variable is undefined" validation errors
/* @var ID $id */

global $configuration;

if(isset($configuration['features']['follow']['pwa']) &&
		$configuration['features']['follow']['pwa'] == TRUE) {
	http_response_code(404);
	exit();
}

header('Content-Type: text/javascript');
?>
// Incrementing OFFLINE_VERSION will kick off the install event and force
// previously cached resources to be updated from the network.
const OFFLINE_VERSION = 2021021703;
const CACHE_NAME = "<?= $id->encode() ?>";
const URLS = ["/<?= $id->encode() ?>/", "/follw.js", "/<?= $id->encode() ?>.json"];

self.addEventListener("install", (event) => {
	console.debug("Installing Service Worker");

	event.waitUntil(caches.open(CACHE_NAME));

	// Force the waiting service worker to become the active service worker.
	self.skipWaiting();
});

self.addEventListener("activate", (event) => {
	console.debug("Activating Service Worker");

	event.waitUntil(
		// Delete unused cache entries
		caches.keys().then((keys) => {
			keys.filter((key) => {
				if(key === CACHE_NAME) {
					console.log(key);
				}
			});
		})
	);

	// Tell the active service worker to take control of the page immediately.
	self.clients.claim();
});

self.addEventListener("fetch", (event) => {
	event.respondWith(
		fetch(event.request).then((request) => {
			console.debug("Ignoring Cache for", event.request.url);
			caches.open(CACHE_NAME).then((cache) => {
				cache.add(request.url);
			});
			//return cache.addAll(URLS, { cache: "reload" });
			return request;
		}).catch(() => {
			console.debug("Using Cache for", event.request.url);
			return caches.match(event.request);
		})
	);
});