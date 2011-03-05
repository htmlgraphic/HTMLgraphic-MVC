
<div id="login-wrapper">
	<form id="login" method="post" action="<?= URL::getCurrent() ?>">
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
	</form>
</div>