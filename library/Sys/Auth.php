<?php
namespace Maxcore\Sys;
/**
 *
 * @author Maksisoft
 */
class Auth extends Session {

    private $necessaryAuthority;
    private $user_has_authority;
    private $die;
    private $loggenIn;
    private $redirectUrl;
    private $userData;
    private $account;

    public function __construct($authority = false, $redirectUrl = NULL, $die = false) {

        $this->necessaryAuthority = $authority;

        $this->die = $die;

        $this->redirectUrl = $redirectUrl;
        
        $this->loggenIn = $this->inLogged();
        
    }

    public function control() {

        if ($this->loggenIn) {
            
            $this->userData = new \Maxcore\Sys\User();

            $this->user_has_authority = $this->userData ->info['auths'] != NULL ? unserialize($this->userData->info['auths']) : FALSE;

            $account = new \Maxcore\Sys\Account();
            
            $this->account = $account->getDetails($this->getAccountNo());
            
            if ($this->necessaryAuthority) {

                
                /*
                 * Yetki kontrol sistemi
                 */
                
                return true;
                
            } else {

                return true;
            }
        } else {
            
            if($this->redirectUrl != NULL){
                
                (new \Maxcore\Http\Header)->result("fail","Oturum Açılmamış")->to($this->redirectUrl);
                
                   die();
                
            }else{
                
                if ($this->die) {

                    die();
                    
                } else {

                    return false;
                } 
            
            }
    
        }
    }
    
    public function getUser(){
        
        
        return $this->userData->info;
    }
    
    public function getAccountNo(){
        
        return $this->get("account_no");
    }

    
    public function getAccountDetails(){
        
        return $this->account;
        
    }
    
    
    public function login($userData,$account_no = 0) {

        $hash= md5(rand(1000, 9999));
        $this->set("panel_login", true);
        $this->set("panel_user_id", $userData['id']);
        $this->set("panel_username", $userData['name']);

        $this->set("account_no", $account_no);
        $this->set("panel_user_key", $hash);
        
        return true;
    }
    
    public function logout($redirectUrl){
        
        $this->clean();
        
        \Maxcore\Http\Header::to($redirectUrl);
        
    }

    public function inLogged() {

        if ($this->has("panel_login") && $this->has("panel_user_id") && $this->has("panel_username")) {

            return true;
        } else {

            return false;
        }
    }

}
