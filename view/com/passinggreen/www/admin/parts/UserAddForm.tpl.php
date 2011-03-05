<form action="/admin/rpc/Admin/User/Add." method="post" id="frm-user-new" class="dialog validated">
	<fieldset class="hidden">
		<input type="hidden" name="action" value="user_create" />
	</fieldset>
	<fieldset>
		<label>First Name:</label><input type="text" name="name_first" class="required" />
		<label>Last Name:</label><input type="text" name="name_last" class="required" />
		<label>Email Address:</label><input type="text" name="email_address" class="required email" />
		<label>Agent ID:</label><input type="text" name="agent_id" />
		<label>Status:</label>
		<select name="is_active">
			<option value="1">Active</option>
			<option value="0">Inactive</option>
		</select>
		<label>Type:</label>
		<select name="is_admin">
			<option value="1">Administrator</option>
			<option value="0">Agent</option>
		</select>
	</fieldset>
</form>