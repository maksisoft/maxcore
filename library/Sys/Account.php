<?php
namespace Maxcore\Sys;

use \Maxcore\Sys\Session;
use \Maxcore\Db\Model;

/**
 * @author Maksisoft
 */
class Account extends Model {
    
    
    public function getDetails($account_no){

        $sql="SELECT * FROM hesap_detaylari WHERE account_id = ?";
        
         $query = $this->getConnection()->prepare($sql);

        $query->execute([$account_no]);

        return $query->fetch();
        
        
    }
    
    
}