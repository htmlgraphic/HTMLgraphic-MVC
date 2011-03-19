<?php

Loader::load('controller', 'AdminPageController');

class AdminDashboardController extends AdminPageController
{

  function activate()
  {
    $this->show_home_page();
  }

  public function useSSL()
  {
    return true;
  }

  private function show_home_page()
  {
    $this->setPageData('header/title', 'Admin Home');
    $this->setPageView('admin/AdminPage');
    $this->setBodyView('admin/body/Dashboard');

    $this->addPageData('header/meta', array(
        'description' => 'Passing Green :: Administration Home'
    ));

    $this->loadPage();
    exit;
  }

}

?>