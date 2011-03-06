<script type="text/javascript">
	function userCreate() {
		$('#frm-user-new').dialog('option','title','New User').dialog('open');
	}
	
	function userDelete(id) {
		$.ajax({
			url: '/admin/rpc/UserDelete',
			dataType: 'json',
			data: { 'id': id }
		});
	}
	$("#admin-users-add-button").live('click',
	function() { alert("HI");})
</script>
<div id="page-content">
	<div id="button-panel">
		<button onclick="newUser();"><span class="ui-icon ui-icon-plusthick"></span>User</button>
	</div>
	<h1>Users</h1>
</div>
<?php $this->loadView('admin/parts/UserAddForm'); ?>
<?php $this->loadView('admin/parts/UserEditForm'); ?>
<?php $this->loadView('admin/parts/UserDeleteForm'); ?>