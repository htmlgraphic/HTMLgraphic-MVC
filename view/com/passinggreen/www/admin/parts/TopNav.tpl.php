<div id="user-box">Logged in as <?php echo $this->member->getFirstname(), ' ', $this->member->getLastname(), ' (', $this->member->getEmail(), ')'; ?></div>
<div id="header">
	<ul id="menu" class="clear">
		<li id="nav-dashboard"><a href="/admin-new/dashboard">Dashboard</a></li>
		<li id="nav-users"><a href="/admin-new/users">Users</a></li>
		<li id="nav-settings" class="alt"><a href="javascript:void(0);">Settings</a>
			<ul>
				<li><a href="/admin-new/settings/global">Global Configuration</a></li>
			</ul>
		</li>
	</ul>
</div>