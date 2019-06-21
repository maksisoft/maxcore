<?php
namespace Maxcore\Support;

class Hash {
  
    public $type;

    
    public static function generate($type,$data,$hashPassword=NULL,$withkey = TRUE){
        
        switch ($type) {
            
            case "password":
                
                if($hashPassword != NULL){
                    
                   return md5($data.$hashPassword); 
                   
                }else{
                    
                    return md5($data);
                }
                
                break;
            
            case "text":
                
                return md5($data);
                
           break;

            default:
                
                return false;
                
            break;
        }
        
        
    }
    
    
    
    
    
    
}
