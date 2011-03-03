<title><?= htmlspecialchars($title) ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Language" content="en-us">

<?php foreach ($meta as $name => $content) : ?>
<?php if ($name == 'description') : ?>

		<meta name="description" content="<?= htmlspecialchars((strlen($content) > 252) ? substr(($content = wordwrap($content, 252, '|$|')), 0, strpos($content, '|$|')) . '...' : $content) ?>">

<?php else : ?>

			<meta name="<?= $name ?>" content="<?= htmlspecialchars($content) ?>">

<?php endif ?>
<?php endforeach; ?>
<?php foreach ((array) $metaProperties as $property => $content) : ?>

				<meta property="<?= $property ?>" content="<?= htmlspecialchars($content) ?>">
				
<?php endforeach; ?>	
<?php if (isset($redirectmeta)) : ?>
					<meta http-equiv="refresh" content="<?= $redirectmeta['time'] ?>; url=<?= $redirectmeta['url'] ?>">

<?php endif; ?>

					<link rel="shortcut icon" href="/favicon.ico">

<?php Loader::loadAssets($assets); ?>

<?php if ($ga_load) : ?>
<?php Loader::loadGoogleAnalytics($ga_options); ?>
<?php endif; ?>