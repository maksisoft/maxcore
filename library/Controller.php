<?php
namespace Maxcore;

class Controller {

    public $templateName = null;
    public $result = null;
    public $authSystem;
    public static $userInfo = NULL;
    public $request = NULL;
    public static $http_request = NULL;
    public $header = NULL;
    public static $account_no = NULL;
    public static $account_details = NULL;
    
    public function __construct($authControl = FALSE, $authority = FALSE, $redirectUrl = "login", $die = FALSE) {
        if ($this->templateName = app::getConfig("activeTemplate"));

        if ($authControl) {

            $auth = new \Maxcore\Sys\Auth($authority, $redirectUrl, $redirectUrl, $die);

            $auth->control();

            $this->authSystem = true;

            self::$userInfo = $auth->getUser();
            
            self::$account_no = $auth->getAccountNo();
            
            self::$account_details = $auth->getAccountDetails();
            
            if(self::$account_details["durum"] == 0){

                 echo $this->view("error/disable_account");
                
                die();
                
            }

        }
        
        

        if ($_POST || $_GET) {

            $this->request = new Http\Request();
            
            self::$http_request = $this->request;

            $this->header = new Http\Header();
        }
    }

    public function view($viewFile, $params = [], $noCache = false, $token = "") {
        
        return \Maxcore\View::render($viewFile, $params, $this->templateName, $noCache, $token, self::$userInfo,self::$account_no,self::$account_details);
    }

    public function model($mfolder = null, $model, $args = null) {

        $mfolder = $mfolder == null ? "" : "/" . $mfolder;
        
        $file = APP_DIR . DS . 'Model' . "{$mfolder}/{$model}.php";
        

        if (file_exists($file)) {

            require_once $file;

            if (class_exists($model)) {

                return $model == null ? new $model() : new $model($args);
            } else {
                exit("Model dosyasında sınıf tanımlı değil: $model");
            }
        } else {
            exit("Model dosyası bulunamadı: {$model}.php");
        }
    }
    
    public static function include_model($mfolder = null, $model, $args = null) {

        $mfolder = $mfolder == null ? "" : "/" . $mfolder;

        if (file_exists($file = APP_DIR . DS . 'Model' . "{$mfolder}/{$model}.php")) {

            require_once $file;

            if (class_exists($model)) {

                return $model == null ? new $model() : new $model($args);
            } else {
                exit("Model dosyasında sınıf tanımlı değil: $model");
            }
        } else {
            exit("Model dosyası bulunamadı: {$model}.php");
        }
    }

    public function helper($hfolder = null, $helper, $args = null) {
        $hfolder = $hfolder == null ? "" : "/" . $hfolder;

        if (file_exists($helperFile = APP_DIR . DS . 'Helper' . "{$hfolder}/{$helper}.php")) {

            require_once $helperFile;

            if (class_exists($helper)) {

                return $args == null ? new $helper() : new $helper($args);
            } else {

                exit("Helper dosyasında sınıf tanımlı değil: $helper");
            }
        } else {
            exit("Helper dosyası bulunamadı: {$helper}.php");
        }
    }

    function __destruct() {
        $this->templateName = null;
        $this->result = null;
    }

}
