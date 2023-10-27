<?php
/**
 * ====================================================================================
 *                           PRESTBIT UG (c) Alen O. Raul
 * ----------------------------------------------------------------------------------
 * @copyright Created by PRESTBIT UG. If you have downloaded this
 *  but not from author or received it from third party, then you are engaged
 *  in an illegal activity. 
 *  You must delete this immediately or contact the legal author / owner for a proper
 *  license. More infos at:  https://www.prestbit.de.
 *
 *  Thank you :)
 * ====================================================================================
 *
 * @author PRESTBIT UG (https://www.prestbit.de)
 * @link https://www.prestbit.de 
 * @license https://www.prestbit.de/license
 * @package Doc-Site
 */

namespace App\includes;
/*
########################
If you are a coder you know what to do with this !!!

SGVsbG8gY29sbGVhZ3VlISAKSWYgeW91IHJlYWQgdGhlc2UgbGluZXMgaXQgbWVhbnMgdGhhdCB5b3Ugd2lsbCBjb250aW51ZSB0byB3b3JrIG9uIHRoaXMgc2NyaXB0IG9yIHRoYXQgeW91IHdhbnQgdG8gd29yay4KT2YgY291cnNlIHlvdSB3ZXJlIHRvbGQgdGhhdCB5b3Ugd2lsbCBoYXZlIGEgbG90IG9mIHByb2plY3RzIHRvIGRvIGFuZCB0aGF0IHRoZXkgYXJlIGV4Y2VsbGVudCBwZW9wbGUgYW5kIHRoYXQgdGhleSB3aWxsIHBheSBldmVyeSBwZW5ueSAuLi4uCkkgd29ya2VkIGFsb25lIGRheSBhbmQgbmlnaHQgYW5kIHdva2UgdXAgd2l0aCBhIGJpZyBraWNrIGluIHRoZSBhc3MuIFRha2UgY2FyZSEKSSBjYW4ndCB0ZWxsIHlvdSB0aGUgd2hvbGUgc3RvcnkgaGVyZSwgYnV0IEkgd2FybmVkIHlvdSEKU3VjY2VzcyE=

########################
*/

class Web{

  protected $controller = 'HomeController';
  protected $method = 'index';
  protected $params = [];
  protected $db, $config = [];

  public function __construct($db, $config){
    $this->db=$db;
    $this->config=$config;
  
    $url = Tools::parseUrl();
    /* maintenance == 1 -- is on */
    if(isset($_SESSION["underconstruct"])  && $this->config['maintenance'] == 1){
      $controller =  $url && isset($url[0]) ? Tools::controller($url[0]) : $this->controller;
    }else{
      if($this->config['maintenance'] == 1){
        if($url && isset($url[0]) == "docsitede"){
          $_SESSION["underconstruct"] = "docsitede";
          header("Location: https://doc-site.de");
          die();
        }
        $controller =  'WebController';
      }elseif($this->config['maintenance'] == 2){
        $controller =  $url && isset($url[0]) ? Tools::controller($url[0]) : $this->controller;
      } 
    }

    if(file_exists(CONTROLLERS . $controller . '.php')){
      $this->controller = $controller;
      unset($url[0]);
    }

    
    //require_once(CONTROLLERS . $this->controller.'.php');
    $class = __NAMESPACE__ . '\\controllers\\' . $this->controller;

    $this->controller = new $class($this->db, $this->config);
    if(isset($url[1])){
      $mth = Tools::clean($url[1], 4);
      if(method_exists($this->controller, $mth)){
        $this->method = $mth;
        unset($url[1]);
      }
    }

      $this->params = $url ? array_values($url) : [];   

    call_user_func_array([$this->controller, $this->method], $this->params);
  }


 

}
?>