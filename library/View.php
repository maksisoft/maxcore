<?php

namespace Maxcore;

use \Maxcore\App;
use \Maxcore\Sys\Session;

/**
 *
 * @author Maksisoft
 * 
 */
class View {

    public static $account_no;

    public static function render($view, $params = [], $templateName = null, $noCache = false, $token = "", $user = NULL, $account_no = 0,$hesap_detaylari = NULL) {


        self::$account_no = $account_no;


        if ($noCache) {

            $params['noCache'] = "?r=" . time();
        } else {

            $params['noCache'] = "";
        }


        if (!isset($params['title'])) {
            $params['title'] = App::getConfig("title");
        }
        $params['url'] = App::getConfig("url");


        if (App::getConfig("form", "csrfToken")) {

            if (!isset($_SESSION["csrftoken"])) {

                $token = \Maxcore\Http\Request::set_csrf();
            } else {
                $token = $_SESSION["csrftoken"];
            }
        }

        if (Session::has("session_message")) {

            $params['session']['session_message'] = Session::get("session_message");

            Session::reset("session_message");
        }

        $params['csrf_token'] = $token;
        $params['csrf'] = "<input type=\"hidden\" name=\"csrftoken\" value=\"$token\" />";
        $params['user'] = $user;
        $params['hesap_detay'] = $hesap_detaylari;

        $templateName = $templateName == null ? App::getConfig("activeTemplate") : $templateName;

        $loader = new \Twig_Loader_Filesystem(APP_DIR . DS . 'View' . "/" . $templateName);

        $twig = new \Twig_Environment($loader);

        $urlFilter = new \Twig_Filter('url', function ($url) {
            return App::getConfig("url") . $url;
        });

        $routeFilter = new \Twig_Filter('route', function ($route) {
            return App::getConfig("url") . "/" . app('route')->getRoute($route, []);
        });


        $mediaFunction = new \Twig_Function('media', function ($mediaName, $mediaName2 = NULL) {

            if ($mediaName2 == NULL) {
                return App::getConfig("url") . "/public/media/"  . $mediaName;
            } else {

                return App::getConfig("url") . "/public/media/" .  $mediaName . $mediaName2;
            }
        });

        $get_config_function = new \Twig_Function('get_config', function ($configKey, $val = NULL) {
            return App::getConfig($configKey, $val);
        });



        $asset_url_function = new \Twig_Function('asset', function ($asset, $defaultcacheid = True, $noncache = False, $type = NULL) {

            $assetUrl = App::getConfig("assetsUrl") . "/" . App::getConfig("activeTemplate") . "/" . $asset;

            if ($noncache) {

                $assetUrl = $assetUrl . "?v=" . time();
            } else {

                if ($defaultcacheid) {

                    $assetUrl = $assetUrl . "?v=" . App::getConfig("mediacacheid");
                }
            }

            return $assetUrl;
        });



        $kdv_hesapla_function = new \Twig_Function('kdv_hesapla', function ($birim_fiyat, $adet, $kdv_oran, $islem,$format = null) {

            if ($kdv_oran > 0) {
                

                if ($kdv_oran > 9) {

                    $kdv_duzelt = "1." . $kdv_oran;
                    
                } else {

                    $kdv_duzelt = "1.0" . $kdv_oran;
                }
                
                

                if($islem == "ekle"){

                     $fiyat = $birim_fiyat * $kdv_duzelt;


                 }else if($islem == "cikart"){

                     $fiyat = $birim_fiyat / $kdv_duzelt;



                 }else{
                     
                       $fiyat =   $birim_fiyat;
                 }
            
            
            }else{
              $fiyat =   $birim_fiyat;
                
            }
            
            
            


      
      if($format == null){
           return  number_format($fiyat * $adet, 2, ',', '.');
      }else{
          
          return  number_format($fiyat * $adet, 2, '.', '');
      }
            
           


        });

        
        $tl_funcition = new \Twig_Function('Tl', function ($fiyat) {

            
            return  number_format($fiyat, 2, ',', '.');


        });




        $paginate_func = new \Twig_Function('paginate', function ($paginate_data, $url = null, $external_data = null) {

            $paginate = "";

            if ($paginate_data != NULL) {

                $ex_url = "csrftoken={$_SESSION["csrftoken"]}&";

                if ($external_data != NULL) {

                    if (is_array($external_data)) {

                        foreach ($external_data as $key => $value) {

                            $ex_url .= $key . "=" . $value . "&";
                        }
                    }
                }

                $ex_url = rtrim($ex_url, "&");

                if ($url == null) {
                    $domain = App::getConfig("url") . "/";
                } else {
                    $domain = App::getConfig("url") . "/" . $url . "/";
                }


                for ($i = 0; $i < $paginate_data["total_page"]; $i++) {

                    $iyaz = $i + 1;

                    if ($iyaz == $paginate_data["now_page"]) {

                        $activate = " active";
                    } else {
                        $activate = "";
                    }

                    $paginate .= "<li class=\"page-item{$activate}\"><a class=\"page-link\" href=\"{$domain}?page={$iyaz}&{$ex_url}\">{$iyaz}</a></li>";
                }
            }

            return $paginate;
        });
        
        

        $twig->addFunction($tl_funcition);
        $twig->addFunction($kdv_hesapla_function);
        $twig->addFunction($paginate_func);
        $twig->addFunction($get_config_function);
        $twig->addFunction($asset_url_function);
        $twig->addFunction($mediaFunction);
        $twig->addFilter($routeFilter);
        $twig->addFilter($urlFilter);
        
        echo $twig->load($view . ".twig")->render($params);
    }

}
