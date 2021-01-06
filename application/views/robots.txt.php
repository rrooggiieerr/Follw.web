<?php
global $configuration;

header('Content-Type: text/plain');
if($configuration['mode'] === 'production') {
	print('User-agent: *
Disallow: /generatesharingid');
} else {
	// Don't let anything be indexed by search engines
	print('User-agent: *
Disallow: /');
}