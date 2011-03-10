<?php

Loader::load('controller', 'AdminPageController');

class AdminContentController extends AdminPageController {

    function activate() {
        $module_id = str_replace('Module', '', URL::getPathPart(1));

        $module = new FormulaModule($module_id);

        if (isset($module) && $module->isValid()) {
            $this->setPageData('header/title', "{$module->getName()} - {$module->getDescription()}");

            if ($this->module_is_active($module)) {
                $this->setPageData('breadcrumb', array(
                    'Dashboard' => array('url' => '/admin/home/', 'title' => 'Passing Green Admin Dashboard'),
                    $module->getName()
                ));
                $this->setBodyView("body/Module{$module_id}Content");
                $this->addPageData('body', array(
                    'name' => $module->getName(),
                    'description' => $module->getDescription()
                ));

                // FIXME this is a hacky way of getting the next module
                if ($module->getID() < 10) {
                    $next_module = new FormulaModule($module->getID() + 1);
                    if (isset($next_module) && $next_module->is_valid()) {
                        $this->setPageData('body/next_module', $this->get_module_link($next_module));
                    }
                }
            } else {
                $module_data = new stdClass();
                $module_data->name = $module->getName();
                $module_data->description = $module->getDescription();
                $module_data->week = $module->getWeek();

                $this->setPageData('header/title', $module_data->name);
                $this->addPageData('body/module', $module_data);
                $this->setBodyView("body/Locked");
            }
        }
        else
            $this->error404();

        $this->setPageData('header/show_banner', true);

        $this->loadPage();
    }

}

?>