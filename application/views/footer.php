<?php
require_once(dirname(__DIR__) . '/models/Translation.php');

global $configuration;

$footertl = new Translation('footer');
?>
				<footer class="pt-4 border-top">
					<div class="row">
						<div class="col-md">
							<div class="row">
								<div class="col">
									<h5><?= $footertl->get('aboutheader', 'html') ?></h5>
									<p><?= $footertl->get('aboutintro') ?></p>
									<p><?= $footertl->get('blogintro') ?></p>
									<ul class="list-unstyled text-small">
										<li><a href="https://www.instagram.com/follwapp/" target="_blank" rel="noopener noreferrer"><?= $footertl->get('onsocialmedia', 'html', 'Instagram') ?></a></li>
										<li><a href="https://www.facebook.com/follwapp" target="_blank" rel="noopener noreferrer"><?= $footertl->get('onsocialmedia', 'html', 'Facebook') ?></a></li>
										<li><a href="https://twitter.com/follw_app" target="_blank" rel="noopener noreferrer"><?= $footertl->get('onsocialmedia', 'html', 'Twitter') ?></a></li>
									</ul>
								</div>
								<div class="col">
									<h5><?= $footertl->get('privacyheader', 'html') ?></h5>
									<p><?= $footertl->get('privacyintro') ?></p>
									<p><?= $footertl->get('termsintro') ?></p>
								</div>
<?php if(isset($configuration['features']['share']['app'])) { ?>
							</div>
						</div>
						<div class="col-md">
							<div class="row">
								<div class="col">
									<h5><?= $footertl->get('appsheader', 'html') ?></h5>
									<p><?= $footertl->get('appsintro', 'html') ?></p>
									<ul class="list-unstyled text-small">
<?php	foreach($configuration['features']['share']['app'] as $app) { ?>
										<li><a href="<?= $app['url'] ?>" target="_blank" rel="noopener noreferrer"><?= $footertl->get('appfor', 'html', $footertl->get('appplatform' . $app['platform'])) ?></a></li>
<?php	} ?>
									</ul>
								</div>
<?php } ?>
								<div class="col">
									<h5><?= $footertl->get('integrationheader', 'html') ?></h5>
									<p><?= $footertl->get('integrationintro') ?></p>
									<p><?= $footertl->get('htmlintro') ?></p>
									<ul class="list-unstyled text-small">
										<li><a href="https://github.com/rrooggiieerr/Follw.py" target="_blank" rel="noopener noreferrer">Python client on GitHub</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</footer>