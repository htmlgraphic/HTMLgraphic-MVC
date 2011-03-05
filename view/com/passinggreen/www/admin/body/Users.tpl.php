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
<div id="admin-users" style="background: #66f">
	<h1>Users</h1>
	<div id="admin-users-add"><button id="admin-users-add-button" type="button">+ User</button></div>
		-Table of Users-
</div>
