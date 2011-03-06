<div id="page-content">
	<!--<div id="or-overlay">or</div>-->
	<div id="left-side" class="two-column">
		<div id="left-side-inner">
			<h1>Log In</h1>
			<form action="<?= URL::getCurrent() ?>" method="post" id="frm-login" class="validated">
				<?php if (isset($error)) : ?>
					<div class="error">
					<?= $error ?>
				</div>
				<?php endif; ?>
					<fieldset class="hidden">
						<input type="hidden" name="action" value="login" />
					</fieldset>
					<fieldset>
						<label for="fld-login-username">Username:</label>
						<input type="text" name="email" id="fld-login-username" class="required" />

						<label for="fld-login-password">Password:</label>
						<input type="password" name="password" id="fld-login-password" class="required" />
					</fieldset>
					<fieldset class="buttons">
						<button type="submit">Submit</button>
					</fieldset>
				</form>
			</div>
		</div>
		<div class="gap-15"></div>
		<!--
			<div id="right-side" class="two-column">
				<div id="right-side-inner">
					<h1>Reset Password</h1>
					<form action="<?php URL::getCurrent() ?>" method="post" id="frm-reset-password" class="validated">
				<fieldset class="hidden">
				<input type="hidden" name="action" value="reset_password" />
				</fieldset>
				<fieldset>
					<label for="fld-login-username">Username:</label>
				<input type="text" name="username" id="fld-reset-password-username" class="required" />
				</fieldset>
				<fieldset class="buttons">
					<button type="submit">Submit</button>
				</fieldset>
			</form>
			<br /><br />
		<p class="light">If you have forgotten your password we can reset it for you. Once submitted you will receive an email from us that contains your new password.</p>
		<p class="light">Should you not receive this email shortly please contact your representative for assistance.</p>
		</div>
	-->
</div>
</div>