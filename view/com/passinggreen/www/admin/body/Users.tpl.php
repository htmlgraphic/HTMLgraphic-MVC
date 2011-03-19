<script type="text/javascript">
  var UserDataTable;
    
  $(function() {
    UserDataTable = $("#user-table").dataTable({
      bPaginate: true,
      bLengthChange: true,
      bFilter: false,
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
        <th width="75">Enabled?</th>
        <th width="110">Last Login</th>
        <th width="30"></th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
</div>
<?php $this->loadView("admin/parts/UserEditForm", $UserEditForm); ?>
<?php $this->loadView("admin/parts/UserDeleteForm"); ?>