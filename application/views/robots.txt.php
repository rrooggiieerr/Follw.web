<?php
global $configuration;

header('Content-Type: text/plain');
if($configuration['mode'] === 'production') {
	print('User-agent: *
Disallow: /generatesharingid');
} else {
	// Allow social media sites and deny everyone else
	print('User-agent: facebookexternalhit
Allow: /
Disallow:

User-agent: Twitterbot
Allow: /
Disallow:

User-agent: WhatsApp
Allow: /
Disallow:

User-agent: SkypeUriPreview Preview
Allow: /
Disallow:

User-agent: *
Disallow: /');
}