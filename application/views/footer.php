<?php
require_once(dirname(__DIR__) . '/models/Translation.php');

global $configuration;

$tl = new Translation('footer');
?>
				<footer class="pt-4 my-md-5 pt-md-5 border-top">
					<div class="row">
						<div class="col-md">
							<div class="row">
								<div class="col">
									<h5><?= $tl->get('footeraboutheader', 'html') ?></h5>
									<ul class="list-unstyled text-small">
										<li><a href="https://blog.follw.app/" target="_blank" rel="noopener noreferrer">Blog</a></li>
										<li><a href="https://www.instagram.com/follwapp/" target="_blank" rel="noopener noreferrer"><?= $tl->get('onsocialmedia', 'html', 'Instagram') ?></a></li>
										<li><a href="https://www.facebook.com/follwapp" target="_blank" rel="noopener noreferrer"><?= $tl->get('onsocialmedia', 'html', 'Facebook') ?></a></li>
										<li><a href="https://twitter.com/follw_app" target="_blank" rel="noopener noreferrer"><?= $tl->get('onsocialmedia', 'html', 'Twitter') ?></a></li>
									</ul>
								</div>
								<div class="col">
									<h5><?= $tl->get('footerprivacyheader', 'html') ?></h5>
									<p><?= $tl->get('footerprivacyintro') ?></p>
									<ul class="list-unstyled text-small">
										<li></li>
										<li><a href="/terms" rel="noopener noreferrer">Terms &amp; conditions</a></li>
									</ul>
								</div>
<?php if(isset($configuration['features']['share']['app'])) { ?>
							</div>
						</div>
						<div class="col-md">
							<div class="row">
								<div class="col">
									<h5><?= $tl->get('footerappsheader', 'html') ?></h5>
									<p><?= $tl->get('footerappsintro', 'html') ?></p>
									<ul class="list-unstyled text-small">
<?php	foreach($configuration['features']['share']['app'] as $app) { ?>
										<li><a href="<?= $app['url'] ?>" target="_blank" rel="noopener noreferrer"><?= $tl->get('appfor', 'html', $tl->get('appplatform' . $app['platform'])) ?></a></li>
<?php	} ?>
									</ul>
								</div>
<?php } ?>
								<div class="col">
									<h5><?= $tl->get('footerintegrationheader', 'html') ?></h5>
									<p><?= $tl->get('footerintegrationintro') ?></p>
									<ul class="list-unstyled text-small">
<?php //								<li><a href="/wordpress" rel="noopener noreferrer">WordPress plugin</a></li>  ?>
										<li><a href="/htmlsnippet" rel="noopener noreferrer">HTML snippet</a></li>
										<li><a href="https://github.com/rrooggiieerr/Follw.py" target="_blank" rel="noopener noreferrer">Python client on GitHub</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</footer>