<!doctype html>
<html>
	<head>
		<title><?= htmlspecialchars($header['title']) ?></title>

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="Content-Language" content="en-us">

		<?php foreach ($header['meta'] as $name => $content) : ?>
		<?php if ($name == 'description') : ?>

				<meta name="description" content="<?= htmlspecialchars((strlen($content) > 252) ? substr(($content = wordwrap($content, 252, '|$|')), 0, strpos($content, '|$|')) . '...' : $content) ?>">

		<?php else : ?>

					<meta name="<?= $name ?>" content="<?= htmlspecialchars($content) ?>">

		<?php endif ?>
		<?php endforeach; ?>
		<?php foreach ((array) $header['metaProperties'] as $property => $content) : ?>

						<meta property="<?= $property ?>" content="<?= htmlspecialchars($content) ?>">

		<?php endforeach; ?>

						<link rel="shortcut icon" href="/favicon.ico">

		<?php Loader::loadAssets($header['assets']); ?>

		<?php if ($header['ga_load']) : ?>
		<?php Loader::loadGoogleAnalytics($header['ga_load']); ?>
		<?php endif; ?>

						</head>
						<body>
							<div id="top-line"></div>
							<div id="wrapper">
			<?php if (!isset($login_form) || !$login_form) : ?>
			<?php $this->loadView('admin/parts/TopNav'); ?>
			<?php else : ?>
									&nbsp;
			<?php endif; ?>
									<div id="page">
				<?php $this->loadView($body_view, $body); ?>
								</div>
								<div id="footer">
									<div id="copyright">Copyright &copy; <?= date('Y'); ?> by <a href="http://htmlgraphic.com/" title="Visit the HTMLgraphic.">HTMLgraphic</a></div>
			</div>
		</div>
		<div id="loading"></div>
	</body>
</html>