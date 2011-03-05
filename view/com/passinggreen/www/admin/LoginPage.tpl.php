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

							<div id="login-wrapper">
								<form id="login" method="post" action="">
				<?php if (isset($error)) : ?>
								<div class="error">
					<?= $error ?>
							</div>
				<?php endif; ?>

				<label>
				Email
					<input type="text" class="text" name="email" />
				</label>

				<label>
				Password
					<input type="password" class="text" name="password" />
				</label>
				<button id="submit-login" type="submit">Log In</button>

				<!--<p class="help"><a href="/admin/forgot-password/">Register</a></p>-->
			</form>
		</div>
	</body>
</html>