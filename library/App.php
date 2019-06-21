<?php

namespace Maxcore;

/**
 * @author Maksisoft
 */
class App {

    public $route;
    public static $appConfig;

    public function __construct() {
        $this->setConfig();
        $app = \System\App::instance();
        $app->request = \System\Request::instance();
        $app->route = \System\Route::instance($app->request);
        $this->route = $app->route;
    }

    private function setConfig() {

        if ($configs = include(BASE_PATH . "private/config.php")) {

            empty($configs) ? self::$appConfig = [] : self::$appConfig = $configs;

            if (\Maxcore\App::getConfig("auth", "login_with_account_number")) {

                if (isset($_SESSION["extra_config"])) {
                    
                    if(isset($_SESSION["extra_config"]["db"]["database"])){
                        self::$appConfig["db"]["database"] = $_SESSION["extra_config"]["db"]["database"];
                    }
               
                    
                }
            }
        }
    }

    public static function editConfig($key, $sub_key = null, $val = null) {

        if ($sub_key == NULL) {

            self::$appConfig[$key] = $val;
        } else {

            self::$appConfig[$key][$sub_key] = $val;
        }
    }

    public static function getConfig($key, $val = null) {

        if ($val == NULL) {

            return isset(self::$appConfig[$key]) ? self::$appConfig[$key] : false;
        } else {

            return isset(self::$appConfig[$key][$val]) ? self::$appConfig[$key][$val] : false;
        }
    }

    public function run() {

        require BASE_PATH . 'private' . DS . 'routerList.php';

        $this->route->any('/*', function() {
            \Maxcore\View::render("error/404");
        });


        if (self::getConfig("form", "csrfToken")) {

            if ($_POST || $_GET) {

                (new Http\Request)->csrf_control();
            }
        }


        $this->route->end();
    }

    function __destruct() {
        $this->routeMenager = null;
        self::$appConfig = null;
    }

}
