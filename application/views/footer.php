<?php
global $configuration;
?>
				<footer class="pt-4 my-md-5 pt-md-5 border-top">
					<div class="row">
						<div class="col-md">
							<div class="row">
								<div class="col">
									<h5>About</h5>
									<ul class="list-unstyled text-small">
										<li><a class="text-muted" href="https://blog.follw.app/" target="_blank" rel="noopener noreferrer">Blog</a></li>
										<li><a class="text-muted" href="https://www.instagram.com/follwapp/" target="_blank" rel="noopener noreferrer">Follw on Instagram</a></li>
										<li><a class="text-muted" href="https://www.facebook.com/follwapp" target="_blank" rel="noopener noreferrer">Follw on Facebook</a></li>
										<li><a class="text-muted" href="https://twitter.com/follw_app" target="_blank" rel="noopener noreferrer">Follw on Twitter</a></li>
									</ul>
								</div>
								<div class="col">
									<h5>Privacy</h5>
									<ul class="list-unstyled text-small">
										<li><a class="text-muted" href="/privacy" rel="noopener noreferrer">Privacy statement</a></li>
										<li><a class="text-muted" href="/terms" rel="noopener noreferrer">Terms &amp; conditions</a></li>
									</ul>
								</div>
							</div>
						</div>
						<div class="col-md">
							<div class="row">
<?php if(isset($configuration['app'])) {
	$platforms = [ 'play' => 'Android',
		'itunes' => 'iOS'
	];
?>
								<div class="col">
									<h5>Apps</h5>
									<ul class="list-unstyled text-small">
<?php	foreach($configuration['app'] as $app) { ?>
										<li><a class="text-muted" href="<?= $app['url'] ?>" target="_blank" rel="noopener noreferrer">Follw for <?= $platforms[$app['platform']] ?></a></li>
<?php	} ?>
									</ul>
								</div>
<?php } ?>
								<div class="col">
									<h5>Integration</h5>
									<ul class="list-unstyled text-small">
										<li><a class="text-muted" href="/apidoc" rel="noopener noreferrer">API documentation</a></li>
<?php //								<li><a class="text-muted" href="/wordpress" rel="noopener noreferrer">WordPress plugin</a></li>  ?>
										<li><a class="text-muted" href="/htmlsnippet" rel="noopener noreferrer">HTML snippet</a></li>
										<li><a class="text-muted" href="https://github.com/rrooggiieerr/Follw.py" target="_blank" rel="noopener noreferrer">Python client on GitHub</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</footer>