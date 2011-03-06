<script type="text/javascript">
	$(function() {
		$('#frm-user-new input[name="useremail"]').rules('add',
		{
			remote:
				{
				url: '/admin-new/users',
				data:
					{
					action: 'validate_email_address'
				}
			},
			messages:
				{
				remote: 'There is already a user with that email address.'
			}
		});
		/*$('input[name="agent_id"]').autocomplete({
			minLength: 2,
			source: function(request, response) {
				$.ajax({
					url: '/admin-new/users',
					dataType: 'json',
					data: {
						action: 'ac_agents',
						query: request.term
					},
					success: function(data)
					{
						response($.map(data, function(item)
						{
							return { 'label': item.name, 'value': item.id };
						}))
					}
				})
			}
		});*/
	});

	function populateForm(form, data) {
		$.each(data, function(key, val) {
			var fld = $(form + ' [name="' + key + '"]');

			if (fld.length > 0)
			{
				switch (fld.attr('type'))
				{
					case 'hidden':
					case 'text':
					case 'textarea':
						fld.val(val).change();
						break;
					case 'select-one':
						$(form + ' select[name="' + key + '"] option').each(function(i)
						{
							option = $(this);

							if (option.val() == val)
							{
								option.attr('selected', true);
							}
							else
							{
								option.attr('selected', false);
							}
						});
						fld.change();
						break;
					case 'select-multiple':
						$(form + ' select[name="' + key + '"] option').each(function(i)
						{
							option = $(this);

							if ($.inArray(option.val(), val) >= 0)
							{
								option.attr('selected', true);
							}
							else
							{
								option.attr('selected', false);
							}
						});
						fld.change();
						break;
					case 'checkbox':
						if (fld.val() == val)
						{
							fld.attr('checked', true).change();
						}
						break;
				};
			};
		});
	}

	function editUser(id)
	{
		$.ajax({
			type: 'GET',
			url: '/admin-new/rpc/Admin/User/Get',
			data: {
				id: id
			},
			dataType: 'json',
			success: function(data) {
				populateForm('#frm-user-edit', data.user);
				$('#frm-user-edit').dialog('option', 'title', 'Edit User').dialog('option', 'buttons',
				{
					'Submit': function()
					{
						$(this).submit();
					},
					'Cancel': function()
					{
						$(this).dialog('close');
					},
					'Reset Pass': function()
					{
						resetUserPassword(id);
					}
				}).dialog('open');
			}
		});
	}

	function newUser() {
		//$('#frm-user-edit').clear();
		$('#frm-user-edit').dialog('option', 'title', 'New User').dialog('open');
	}

	function deleteUser(id) {
		$('#frm-user-delete input[name="id"]').val(id);
		$('#frm-user-delete').dialog('option', 'title', 'Delete User').dialog('open');
	}

	function resetUserPassword(id) {
		$('#frm-user-resetpass input[name="id"]').val(id);
		$('#frm-user-resetpass').dialog('option', 'title', 'Reset User Password').dialog('open');
	}
</script>
<div id="page-content">
	<div id="button-panel">
		<button onclick="newUser();"><span class="ui-icon ui-icon-plusthick"></span>User</button>
	</div>
	<h1>Users</h1>
	<table class="datatable sortable">
		<thead>
			<tr>
				<th width="20">ID</th>
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
			<?php foreach ($members as $member): ?>
				<tr>
					<td><?= $member->getID() ?></td>
					<td><?= $member->getFirstname() ?></td>
					<td><?= $member->getLastname() ?></td>
					<td><?= $member->getEmail() ?></td>
					<td><?= $member->getLevel() ?></td>
					<td><?= $member->getIsEnabled() ?></td>
					<td><?= $member->getLastLogin() ?></td>
					<td>
						<a onclick="editUser('<?= $member->getID() ?>');"><span class="ui-icon ui-icon-wrench"></span></a>
						<a onclick="deleteUser('<?= $member->getID() ?>');"><span class="ui-icon ui-icon-trash"></span></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php $this->loadView('admin/parts/UserEditForm'); ?>
<?php $this->loadView('admin/parts/UserDeleteForm'); ?>