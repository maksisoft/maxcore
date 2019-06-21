<?php

namespace Maxcore\Http;

/**
 *
 * @author Maksisoft
 */
class Header extends \Maxcore\Sys\Session{

    public $http_status_code;
  

    public function set_code($code) {

        $this->http_status_code = $code;

        return $this;
    }

    public function result($type, $message) {
        
        self::set("session_message", ['type'=>$type,'message'=>$message]);

        return $this;
    }

    public function to($url="") {

        header('Location: ' . \Maxcore\App::getConfig("url") . "/" . $url);
    }

    public function back() {

        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

}
