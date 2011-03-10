<?php

class AdminRpcController extends Controller {

    function __construct() {
        parent::__construct();
        if (!Config::get('RPC')) {
            Config::set('RPC', array(
                        'class' => 3,
                        'method' => 4,
                        'action' => 5,
                        'path' => 'procedure'
                    ));
        }
    }

    function activate() {
        $setup = Config::get('RPC');
        $class = URL::getPathPart($setup['class']);
        $method = URL::getPathPart($setup['method']);
        list($action, $type) = explode('.', URL::getPathPart($setup['action']), 2);
        $path = $setup["path"] . "/" . $class . "/" . ucwords($method) . ucwords($action) . "Controller";
        $controller_class = ucwords($class) . ucwords($method) . ucwords($action) . "Controller";

        Debugger::log("CONTROLLER: <span style=\"color: #990000\">$controller_class</span>");

        if (file_exists(Loader::getPath("controller", "$setup[path]/$controller_class"))) {
            $controller = Loader::loadNew("controller", "$setup[path]/$controller_class");
            $controller->activate();

            if (is_callable(array($controller, $type))) {
                echo $controller->$type();
            }
        } else {
            Loader::load("utility", "Server");
            $ip = Server::getIP();
            $self = Server::getSelf();
            Debugger::log("Possible RPC Injection: RPC call to non-existent controller at path {$setup["path"]}/$controller_class $ip $self");
            error_log("Possible RPC Injection: RPC call to non-existent controller at path $setup[path]/$controller_class $ip $self");
        }
    }

}

?>