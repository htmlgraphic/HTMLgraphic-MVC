<form action="/admin-new/rpc/Admin/User/Add" method="post" id="frm-user-edit" class="dialog validated">
	<fieldset class="hidden">
		<input type="hidden" name="id" value="" />
	</fieldset>
	<fieldset>
		<label>First Name:</label><input type="text" name="userFirstname" class="required" value="" />
		<label>Last Name:</label><input type="text" name="userLastname" class="required" value="" />
		<label>Email Address:</label><input type="text" name="useremail" class="required email" value="" />
		<label>Comments:</label>
		<textarea name="comments" rows="4" cols="10"></textarea>
		<label>Status:</label>
		<select name="is_enabled">
			<option value="yes">Enabled</option>
			<option value="no">Disabled</option>
		</select>
		<label>Type:</label>
		<select name="level">
			<option value="user">Free Account</option>
			<option value="member">Paid Account</option>
		</select>
	</fieldset>
</form>