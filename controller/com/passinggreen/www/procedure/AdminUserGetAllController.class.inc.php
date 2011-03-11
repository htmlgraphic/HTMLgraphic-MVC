<?php

Loader::load("controller", "/controller/ModelController");

class AdminUserGetAllController extends ModelController {

    function __construct() {
        
    }

    function execute() {
        $return = new stdClass;
        $params = Request::getRequest();

        Config::set("HideDebugger", true); //comment this out to debug

        $users = DBObject::collection('Member', DBObject::CONSISTENCY_ABSOLUTE);
        $fullCount = $users->getMemberCount();

        $users = DBObject::collection('Member', DBObject::CONSISTENCY_ABSOLUTE);
        $users->setRange($params['iDisplayStart'], $params['iDisplayLength']);

        switch ($params['iSortCol_0']) {
            case 0:
                $users->applySort("AutoID", $params['sSortDir_0']);
                break;
            case 1:
                $users->applySort("userFirstname", $params['sSortDir_0']);
                break;
            case 2:
                $users->applySort("userLastname", $params['sSortDir_0']);
                break;
            case 3:
                $users->applySort("useremail", $params['sSortDir_0']);
                break;
            case 4:
                $users->applySort("level", $params['sSortDir_0']);
                break;
            case 5:
                $users->applySort("is_enabled", $params['sSortDir_0']);
                break;
        }

        $return = array(
            'sEcho' => $params['sEcho'],
            'iTotalRecords' => $fullCount,
            'iTotalDisplayRecords' => $params['iDisplayLength'],
            'aaData' => array()
        );

        foreach ($users->getMembers() as $_user) {
            $row = array();

            $row[] = $_user->getID();
            $row[] = $_user->getFirstname();
            $row[] = $_user->getLastname();
            $row[] = $_user->getEmail();
            $row[] = $_user->getLevel();
            $row[] = $_user->getIsEnabled();
            $row[] = $_user->getLastLogin();
            $row[] = "<a onclick=\"editUser(this, '" . $_user->getID() . "');\"><span class=\"ui-icon ui-icon-wrench\"></span></a><a onclick=\"deleteUser(this, '" . $_user->getID() . "');\"><span class=\"ui-icon ui-icon-trash\"></span></a>";

            $return['aaData'][] = $row;
        }

        echo json_encode($return);
        return;
    }

}

?>