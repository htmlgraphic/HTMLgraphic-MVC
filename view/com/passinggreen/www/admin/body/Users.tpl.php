<script type="text/javascript">
	$(function() {
		$('#user-table').dataTable({
			bPaginate: true,
			sPaginationType: "full_numbers",
			bLengthChange: true,
			bFilter: true,
			bSort: true,
			bInfo: true,
			bProcessing: true,
			bServerSide: true,
			sAjaxSource: "/admin-new/rpc/Admin/User/GetAll",
			bJQueryUI: true
		});
	});
</script>
<div id="page-content">
	<div id="button-panel">
		<button onclick="newUser();"><span class="ui-icon ui-icon-plusthick"></span>User</button>
	</div>
	<h1>Users</h1>
	<table id="user-table" class="datatable sortable">
		<thead>
			<tr>
				<th width="35">ID</th>
				<th width="125">Last Name</th>
				<th width="125">First Name</th>
				<th>Email</th>
				<th width="75">Level</th>
				<th width="75">Status</th>
				<th width="110">Last Login</th>
				<th width="30"></th>
			</tr>
		</thead>
		<tbody>
			<?php /* foreach ($users as $user): ?>
			  <tr id="user_<?=$user->getID()?>">
			  <td><?= $user->getID() ?></td>
			  <td><?= $user->getFirstname() ?></td>
			  <td><?= $user->getLastname() ?></td>
			  <td><?= $user->getEmail() ?></td>
			  <td><?= $user->getLevel() ?></td>
			  <td><?= $user->getIsEnabled() ?></td>
			  <td><?= $user->getLastLogin() ?></td>
			  <td>
			  <a onclick="editUser('<?= $user->getID() ?>');"><span class="ui-icon ui-icon-wrench"></span></a>
			  <a onclick="deleteUser('<?= $user->getID() ?>');"><span class="ui-icon ui-icon-trash"></span></a>
			  </td>
			  </tr>
			  <?php endforeach; */ ?>
		</tbody>
	</table>
</div>
<?php $this->loadView('admin/parts/UserEditForm'); ?>
<?php $this->loadView('admin/parts/UserDeleteForm'); ?>