<form action="/admin-new/rpc/Admin/User/Add" method="post" id="frm-user-edit" class="dialog validated">
	<fieldset class="hidden">
		<input type="hidden" name="id" value="" />
	</fieldset>
	<fieldset>
		<div class="tabs">
			<ul>
				<li><a href="#new-tab-1">User</a></li>
				<li><a href="#new-tab-2">Business</a></li>
				<li><a href="#new-tab-3">Payment</a></li>
				<li><a href="#new-tab-4">Referrals</a></li>
			</ul>
			<div id="new-tab-1">
				<div class="clear">
					<div class="col">
						<label>First Name:</label><input type="text" name="userFirstname" class="required" value="" />
						<label>Last Name:</label><input type="text" name="userLastname" class="required" value="" />
						<label>Email Address:</label><input type="text" name="useremail" class="required email" value="" />
						<label>Phone:</label><input type="text" name="userPhone" class="required email" value="" />
						<label>Comments:</label>
						<textarea name="comments" rows="4" cols="10"></textarea>
					</div>
					<div class="col">						<label>Status:</label>
						<select name="is_enabled">
							<option value="yes">Enabled</option>
							<option value="no">Disabled</option>
						</select>
						<label>Type:</label>
						<select name="level">
							<option value="user">Free Account</option>
							<option value="member">Paid Account</option>
						</select></div>
				</div>
			</div>
			<div id="new-tab-2">
				<div class="clear">
					<div class="col">
						<label>Company Name:</label><input type="text" name="userCompany" class="required" value="" />
						<label>Company Address 1:</label><input type="text" name="userAddr1" class="required" value="" />
						<label>Company Address 2:</label><input type="text" name="userAddr2" class="required email" value="" />
						<label>Company City:</label><input type="text" name="userCity" class="required email" value="" />
						<label>Company State:</label><input type="text" name="userState" class="required email" value="" />
						<label>Company ZIP:</label><input type="text" name="userZip" class="required email" value="" />
						<label>Company Country:</label><input type="text" name="userCountry" class="required email" value="" />
						<label>Company Phone:</label><input type="text" name="userAltPhone" class="required email" value="" />
						<label>Company Fax:</label><input type="text" name="userFax" class="required email" value="" />
					</div>
					<div class="col">&nbsp;</div>
				</div>
			</div>
			<div id="new-tab-3"></div>
			<div id="new-tab-4"></div>
		</div>
	</fieldset>
</form>