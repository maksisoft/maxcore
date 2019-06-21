<?php
namespace Maxcore\Sys;
/**
 *
 * @author Maksisoft
 */
class Session {

    public static function set($key, $value) {

        $_SESSION[$key] = $value;

        return true;
    }

    public static function get($key) {

        return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
    }

    public static function has($key) {
        
        return isset($_SESSION[$key]) ? TRUE : FALSE;
    }

    public static function remove($key) {

        if (isset($_SESSION[$key])) {

            unset($_SESSION[$key]);
            
            return true;
            
        } else {
            
            return false;
        }
    }
    
    public static function reset($key){
        
           if (isset($_SESSION[$key])) {

            $_SESSION[$key] = NULL;
            
            return true;
            
        } else {
            
            return false;
        }
        
    }

    public function clean() {
        
        session_destroy();
        
    }

}
