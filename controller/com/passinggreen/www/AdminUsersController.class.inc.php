<?php

Loader::load('controller', 'AdminPageController');
Loader::load('model', 'com/passinggreen/BusinessCategory');
Loader::load('model', 'com/passinggreen/Country');
Loader::load('model', 'com/passinggreen/Province');

class AdminUsersController extends AdminPageController {

    function activate() {
        $this->setPageData('header/title', 'Users');
        $this->setPageView('admin/AdminPage');
        $this->setBodyView('admin/body/Users');

        $this->addPageData('header/meta', array(
            'description' => 'Passing Green :: Administration :: Users'
        ));

        $this->addPageData('header/assets/js', array(
            '/admin-new/js/user.js'
        ));

        $business_categories = DBObject::collection('BusinessCategory', DBObject::CONSISTENCY_ABSOLUTE);
        $business_categories->applySort("category");

        $countries = DBObject::collection('Country', DBObject::CONSISTENCY_ABSOLUTE);
        $countries->applySort("country");

        $provinces = DBObject::collection('Province', DBObject::CONSISTENCY_ABSOLUTE);
        $provinces->applySort("name");

        $this->addPageData('body/UserEditForm/business_categories', $business_categories->getBusinessCategories());
        $this->addPageData('body/UserEditForm/countries', $countries->getCountries());
        $this->addPageData('body/UserEditForm/provinces', $provinces->getProvinces());

        $this->loadPage();
    }

}

?>