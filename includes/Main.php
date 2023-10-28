<?php 
/**
 * ====================================================================================
 *                           PRESTBIT UG (c) Alen O. Raul
 * ----------------------------------------------------------------------------------
 * @copyright Created by PRESTBIT UG. If you have downloaded this
 *  but not from author or owner website or received it from third party, then you are engaged
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
use App\includes\Tools;
use App\includes\library\PasswordsHash;
use App\includes\library\PHPMailer\PHPMailer;
use App\includes\library\PHPMailer\SMTP;
use App\includes\library\PHPMailer\Exception;

use App\includes\library\Recaptcha;
use \EasyCSRF\Exceptions\InvalidCsrfTokenException;

/*
########################
If you are a coder you know what to do with this !!!

SGVsbG8gY29sbGVhZ3VlISAKSWYgeW91IHJlYWQgdGhlc2UgbGluZXMgaXQgbWVhbnMgdGhhdCB5b3Ugd2lsbCBjb250aW51ZSB0byB3b3JrIG9uIHRoaXMgc2NyaXB0IG9yIHRoYXQgeW91IHdhbnQgdG8gd29yay4KT2YgY291cnNlIHlvdSB3ZXJlIHRvbGQgdGhhdCB5b3Ugd2lsbCBoYXZlIGEgbG90IG9mIHByb2plY3RzIHRvIGRvIGFuZCB0aGF0IHRoZXkgYXJlIGV4Y2VsbGVudCBwZW9wbGUgYW5kIHRoYXQgdGhleSB3aWxsIHBheSBldmVyeSBwZW5ueSAuLi4uCkkgd29ya2VkIGFsb25lIGRheSBhbmQgbmlnaHQgYW5kIHdva2UgdXAgd2l0aCBhIGJpZyBraWNrIGluIHRoZSBhc3MuIFRha2UgY2FyZSEKSSBjYW4ndCB0ZWxsIHlvdSB0aGUgd2hvbGUgc3RvcnkgaGVyZSwgYnV0IEkgd2FybmVkIHlvdSEKU3VjY2VzcyE=

########################
*/



class Main extends Tools{
    protected static $doOriginCheck = false;
    protected static $title=""; 
    protected static $canonical="";
    protected static $description="";
    protected static $webkeywords="";
    protected static $url="";
    protected static $image="";
    protected static $video="";
    protected static $body_class="main-body";
    protected static $lang="";
    private static $config=array();

  /**
  * Generate meta title
  * @param none
  * @return title
  */
  public static function config($val = FALSE){      
    return $val ? self::$config[$val] : self::$config;
  }
  /**
  * Return lang array
  * @param none
  * @return array
  */
  public static function getTransArray(){      
    return  isset(Main::$lang) ? Main::$lang : false; 
  }
  /**
  * Generate meta title
  * @param none
  * @return title
  */
  public static function title(){      
    if(empty(self::$title)){
      return self::$config["title"];
    }else{
      return self::$title." - ".self::$config["title"];
    }
  }
  /**
  * Generate meta title extra
  * @param none
  * @return title
  */
  public static function pagetitle($title){  
    if(!empty($title)){
      self::$config["title"] = "doc-site - ".$title;
    }else{
      self::$config["title"] = $title;
    }
  }

  /**
  * Generate canonical href
  * @param none
  * @return title
  */
  public static function canonical(){      
    if(empty(self::$canonical)){
      return '<link rel="canonical" href="'.self::$config["url"].self::$config["canonical"].'"/>';
    }else{
      return '<link rel="canonical" href="'.self::$config["url"].self::$canonical.'"/>';
    }
  }

  public static function canonicalhref($href, $ogg = ''){ 
    if($ogg && array_key_exists('eizellId', $ogg)){
      $art = array_key_exists('art', $ogg) ? $ogg['art'] : $ogg['crt'];
      $href = "/einzelheitenansehen" ? "/einzelheiten" : $href;
      self::$description = filter_var($ogg['beschreibung'], FILTER_SANITIZE_STRING);
      self::$title = "doc-site - ". filter_var($ogg['titel'], FILTER_SANITIZE_STRING);
      self::$image = $ogg['anzlogo'] ? self::url().UPLOAD_PATH_ANZEIGE.$art.'/'.$ogg['anzlogo'] :  self::url().UPLOAD_PATH_ANZEIGE.'no-avatars/firmen-no-logo.svg';
      self::$canonical = $href.'/'.$ogg['eizellId'];
      self::$url = self::$config["url"].$href.'/'.$ogg['eizellId'];

    } 
    
    if(empty(self::$canonical)){
       self::$config["canonical"] = $href;
    } 
  }


  /**
	 * Returns Maintenance mode
	 **/
	public static function isMaintenance(){
		return self::$config["maintenance"]==1?true:false;
	}	
/**
* Generate meta description
* @param none
* @return description
*/
  public static function description(){      
    if(empty(self::$description)){
      return self::$config["description"];
    }else{
      return self::$description;
    }
  }
  /**
* Generate meta keywords
* @param none
* @return description
*/
public static function webkeywords(){      
  if(empty(self::$webkeywords)){
    return self::$config["webkeywords"];
  }else{
    return self::$webkeywords;
  }
}
/**
* Generate URL
* @param none
* @return description
*/
  public static function url(){      
    if(empty(self::$url)){
      return self::$config["url"];
    }else{
      return self::$url;
    }
  } 
  /**
  * Body Class inject
  * @param none
  * @return message
  */     
  public static function body_class(){
    if(!empty(self::$body_class)) return " class='".self::$body_class."'";
  }    
/**
* Generate URL
* @param none
* @return description
*/
  public static function image(){      
    if(empty(self::$image)){
      return "https://doc-site.de/static/images/docsite-logo.png";
    }else{
      return self::$image;
    }
  }  
/**
* Set meta info
* @param none
* @return Formatted array
*/
  public static function set($meta, $value){
    if(!empty($value)){
      self::$$meta = $value;
    }
  }   
/**
* Generate Open-graph tags
* @param none
* @return string
*/  
  public static function ogp(){
    $l = !empty(self::$config["canonical"]) ? self::$config["canonical"] : ''; // static/images/docsite-logo.png  static/images/brand/socials_logo.png
    $meta="<meta property='og:type' content='website' />\n\t";      
    $meta.="<meta property='og:url' content='".self::url()."".$l."' />\n\t"; 
    $meta.="<meta property='og:title' content='".self::title()."' />\n\t";
    $meta.="<meta property='og:description' content='".self::description()."' />\n\t"; 
    $meta.="<meta property='og:image' content='".self::image()."' />\n\t"; 
    $meta.="<meta property='og:locale' content='de_DE' />\n\t"; 
    if(!empty(self::$video)){
      $meta.=self::$video; 
    }
    echo $meta; 
  }
/**
* Show analitics codes ( Foofle FB etc)
* @param none
* @return string
*/
  public static function analitics($do = FALSE){
    if(!$do){
      if(file_exists(ANALTIC) && file_get_contents(ANALTIC) !== false) {
        return file_get_contents(ANALTIC);
      } else {
        return '';
      }
    }elseif($do){
      if($myfile = fopen(ANALTIC, "w")){
        fwrite($myfile, $do);
        fclose($myfile); 

        return true;
      }else{
        return false;
      }
    }
  }


public static function mitigatevalues($conf, $dropdownvalue){
  switch($conf){
    case "prefix":
      $keyp = explode('-#@@#-', $dropdownvalue); 
      $ret = $keyp[0];
    break;
    case "days":
      if($dropdownvalue){
        $keyp = explode('-', $dropdownvalue); 
        $ret = is_array($keyp) && array_key_exists(2, $keyp)? $keyp[0] : false;
      }else{
        $ret = false;
      }
    break;
    case "months":
      if($dropdownvalue){
        $keyp = explode('-', $dropdownvalue); 
        $ret = is_array($keyp) && array_key_exists(1, $keyp)? $keyp[1] : false;
      }else{
        $ret = false;
      }
    break;
    case "years":
      if($dropdownvalue){
        $keyp = explode('-', $dropdownvalue); 
        $ret = is_array($keyp) && array_key_exists(2, $keyp) ? $keyp[2] : false;
      }else{
        $ret = false;
      }
    break;

    default:
    $ret = false;
  }
  return isset($ret) ? $ret : false;
} 
/**
* Return dropdowns value  ($inputs['u']['choosejob'], 'positionjob', $inputs['u']['positionjob'])
*/
public static function relreturnValue($conf, $what, $value){  
  $web = json_decode(self::$config['dropselect'], true);
  if(array_key_exists($conf, $web)){
    return !$value || $value == "#" ? '-- -' : $web[$conf][$what][$value];
  }
return false;
}

public static function returnValue($where, $what, $value){  
  if(!$value || empty($value)) return '';
  $web = json_decode(self::$config[$where], true);
  if(array_key_exists($what, $web)){
    return $web[$what][$value];
  }
return false;
}

public static function returnValueee($where, $what, $value){
  if(!$value || empty($value)) return '';  
  if($web = json_decode(self::$config[$where], true)){
    if(array_key_exists($what, $web)){
      if($a = $web[$what][$value]){
        return $a;
      }else{
        return false;
      }
    }else{
      return false;
    }
  }else{
    return false;
  }
  
}

public static function filtersReturnValue(){  
    $web = json_decode(self::$config['dropdowns'], true);
    $dropselect = json_decode(self::$config['dropselect'], true);
    if($berufsFeld = $web['berufsfeld']){
      $checks = '';
      foreach($berufsFeld as $key => $val){ 
        if(isset($dropselect[$key]['subjectjob'])  && !empty($dropselect[$key]['subjectjob'])){

          foreach($dropselect[$key]['subjectjob'] as $k => $v){
            $checks .= '<label class="custom-control custom-checkbox mb-3 sbjcks  '.$key.'" style="display: none;">
                        <input type="checkbox" class="custom-control-input  '.str_replace(' ', '', $k).'  sbjckd" data-id="'.str_replace(' ', '', $k).'"  name="subjectfilter[]" value="'.$k.'">
                        <span class="custom-control-label">
                          <a href="#" class="text-dark">'.e($v).'</a>
                        </span>
                      </label>';
          }
        }
      }
      return $checks;
    }
  return false;
}
/**
* Get related dropdowns($conf)  {"doit":"altenpflege","woo":"positionjob"}
*/
public static function reldropdowns($conf, $wheres, $wherestoo = FALSE, $dropdownvalue= FALSE){ 
  $response = [];
  $web = json_decode(self::$config['dropselect'], true);
  if(array_key_exists($conf, $web)){
      $theDrop = $web[$conf];
      $dropdown='';
     if($wherestoo){ $dropdownSec = ''; } 
      if($wheres && array_key_exists($wheres, $theDrop)){
        foreach($theDrop[$wheres] as $key => $val){ 
          if(trim($dropdownvalue) &&  ($key == $dropdownvalue)){ $selected = 'selected'; }else{ $selected = ''; }  
          $dropdown .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
        }
        $response[$wheres] = $dropdown; 
      }else{
        $response[$wheres] = false;
      }

      if($wherestoo && array_key_exists($wherestoo, $theDrop)){
        foreach($theDrop[$wherestoo] as $key => $val){  
          if(trim($dropdownvalue) &&  ($key == $dropdownvalue)){ $selected = 'selected'; }else{ $selected = ''; } 
          $dropdownSec .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
        }
        $response[$wherestoo] = $dropdownSec; 
      }else{
        $response[$wherestoo] = false;
      }

    return !$dropdownvalue ? $response: $response[$wheres];
  }
  return false;
}
/**
* Get web settings dropdowns($conf)
*/
public static function dropdowns($conf, $dropdownvalue = FALSE){ //  if($val == 'userphone'){$val = explode('-', $val); return $val[1];}
  $web = json_decode(self::$config['dropdowns'], true);
  if(array_key_exists($conf, $web)){
    //if (!$dropdownvalue && !in_array($conf, $types)) return $web[$conf];
      $types = array("prefix", "days", "months", "years");
      $dropdown='';
      if(in_array($conf, $types)){
        if( $keyp = self::mitigatevalues($conf, $dropdownvalue) ){ $dropdownvalue = $keyp; }
      }

      if(is_array($dropdownvalue) && array_filter($dropdownvalue)){
        foreach($web[$conf] as $key => $val){   
          if(in_array($key, $dropdownvalue)){ $selected = 'selected'; }else{ $selected = ''; }
          $dropdown .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
        }
        return $dropdown;
      }


      foreach($web[$conf] as $key => $val){   
        if(trim($dropdownvalue) &&  ($key == $dropdownvalue)){ $selected = 'selected'; }else{ $selected = ''; }
        $dropdown .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
      }
    return $dropdown;
  }
  return false;
}
/**
* Set inputs values
* @param string
* @return string
*/
public static function inputsvalues($val, $type = FALSE){
  $types = array("selected", "checked", "none");   

    if($type && $type == "ansprechbild"){
      $vals = explode('/', $val);
      if(trim($vals[1])){ 
        return Main::config('url').UPLOAD_PATH_ANSPRECHPARTNERS.$val;
      }else{
        return Main::config('url').UPLOAD_PATH_ANSPRECHPARTNERS."no-avatars/male-no-avatar.png";
      }
    }

    if($type && $type == "display"){
      if(trim($val)){ 
        return "";
      }else{
        return "none";
      }
    }

  if(!$type && !is_array($val)){
    if(isset($val) && !empty($val)){
      if(strpos($val, "-#@@#-")){$val = explode('-#@@#-', $val); return $val[1];}
      return $val;
    } else{
      return ''; /// '.Main::inputsvalues(["value"=>"prof.dr", "dbval"=>$inputs["u"]['titeljob']]).'
    }
  }elseif($type  && in_array($type, $types) && is_array($val)){
    if(isset($val['value']) && !empty($val['dbval'])){
        if($val['value'] == $val['dbval']){
          return $type;
        }elseif($val['value'] === "1ja" && !empty($val['dbval'])){
          return  $type;
        }else{
          return '';
        }
    }elseif($val['value'] === "1nix" && empty($val['dbval'])){
      return  $type;  
    }elseif($val['value'] === "2nein" && empty($val['dbval'])){
      return  $type;    
    }elseif($val['value'] === "1hide" && empty($val['dbval'])){
      return  $type;  
    }elseif($type == "checked" && !$val['value'] && !$val['dbval']){
      return  $type;  
    } 
  }else{
    return '';
  }
  return '';
}
/**
* Set checks and radios 
* @param config web
* @return string
*/
public static function radiochecks($fields, $form, $isJson = FALSE){
  $frm = [];
  foreach($fields as $val){
    if(array_key_exists($val, $form)){ 
      $frm[$val] = $form[$val];
     }else{
      $frm[$val] = 2;
     }
  }
  return $isJson ? json_encode($frm) : $frm;
}

/**
* Set Socials links login / register
* @param config web
* @return string
*/  
public static function socialsLinksRegister(){
  if($websettings = json_decode(self::$config['websettings'], true)){
    foreach($websettings['socialsAuth'] as $key => $soc){
       //if(!$soc || $soc == "") self::$config['socialsAuth']['providers'][$key]['enabled'] = null;
    }
  }
 self::$config['socialsAuth']['providers']['Google']['enabled'] = false;   
}  

/**
* Generate Socials links
* @param config web
* @return string
*/  
public static function socialsLinks(){
  $sc='';
  if($websettings = json_decode(self::$config['linkSocials'], true)){
    foreach($websettings as $key => $soc){
      if($soc != ''){
        $sc .= '<li class="list-inline-item">
                <a href="'.$soc.'" class="btn-floating btn-sm mr-1" target="_blank" style="background-color: #00527f!important;">
                  <i class="fa fa-'.$key.' text-white"></i>
                </a>
              </li>';
      }
    }
  }else{
    return null;
  }
  return $sc?$sc:null;
} 

/**
* Get All Socials shares
* @param
* @return string
*/  
public static function socialsDoShares(){  
  $sc=[];
  if($websettings = json_decode(self::$config['doshare'], true)){
    foreach($websettings as $key => $soc){
      if($soc != '2'){
        array_push($sc, $key);
      }
    }
  }else{
    return null;
  }
  return $sc?$sc:null;
} 
/**
* Payments visibility container
* @param config web
* @return string
*/
public static function paymentsLinks(){
  if($websettings = json_decode(self::$config['websettings'], true)){
    if($websettings['payments'] == 1) return '';
      return 'none;';
  }
  return 'none;';
}
  /**
* Validate and sanitize email
* @param string
* @return email
*/  
public static function email($email){
  $email=trim($email);
  if (preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$/i', $email) && strlen($email)<=50 && filter_var($email, FILTER_SANITIZE_EMAIL)){
      return filter_var($email, FILTER_SANITIZE_EMAIL);
  }
  return FALSE;
}
/**
 * Read user cookie and extract user info   
 * @param 
 * @return array of info
 */
public static function user(){
  $res = array();
  if(isset($_SESSION["login"])){
    if($data = json_decode(base64_decode($_SESSION["login"]), TRUE)){     
      if(isset($data["loggedin"]) && !empty($data["key"]) && !empty($data["uniq"])){  
        $res = array(self::clean(substr($data["key"], 60)), self::clean(substr($data["key"], 0, 60)), self::clean($data["uniq"]), self::clean($data["membership"]), self::clean($data["leveltype"]));
        if(isset($data["ansprecheralias"]) && !empty($data["ansprecheralias"]) && !empty($data["frmalias"])){
          array_push($res, self::clean($data["ansprecheralias"]), self::clean($data["frmalias"]));
        }
      } 
      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', $res, FILE_APPEND);
      return $res ? $res : FALSE;
    }
    return FALSE;  
  }   
  return FALSE;  
} 
/**
 * Protect users name   
 * @param 
 * @return string
 */
public static function userProtect($vorname, $name, $privacy = 1){ // 1privat 2open
  $private = self::websettings('userprivate');
  if($private && $private == 1){ // Raul O.
    $fullname = $vorname.' '.ucfirst($name[0]).'.';
  }elseif($private && $private == 2){ // Raul O****
    $fullname = $vorname.' '.ucfirst($name[0]).'****';  
  }elseif($private && $private == 3){ // RO
    $fullname = ucfirst($vorname[0]).ucfirst($name[0]);
  }else{ // RO
    $fullname = ucfirst($vorname[0]).ucfirst($name[0]);
  }
  return $fullname;
}  
/**
* Function Create actions in ajax callback
* @param object
* @return array
*/
public static function actCreate($arr = array()){
  file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', " -- actCreate Function Start ---- "."\n", FILE_APPEND);

  if($arr){
    $act=array();
    $actions = explode(';', $arr['action']);
    $where = explode(';', $arr['action_do']);

    foreach($actions as $key => $a){
      $act[] = array("act"=>$a, "do"=> (int)$where[$key] >=1 ? self::convertLevelLink($where[$key]) : $where[$key]);
      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', " -- i am inside foreach loop with value ---- ".$a."---- §where--".$where[$key]."\n", FILE_APPEND);
    }
    file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', " -- i am returnning false before end  ---- "."\n", FILE_APPEND);
    file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', " -- the size of array is  ---- ".count($act)."\n", FILE_APPEND);
    return $act?$act:false;
  }
  file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', " -- false reutrned from  actCreate Function end  ---- "."\n", FILE_APPEND);
  return false;
}
/**
* Function copy pictures
* @returns string
**/
public static function empty_directory($dir){
  if($files = glob($dir.'/*')){
    foreach($files as $file){
      if(is_file($file)) unlink($file); 
    }
    return true;
  }
  return false;
}



/**
* Do forms function multiSelect
* @param object
* @return array
*/
public static function formMultiSelect($obj, $elem = 'selectsAnsprechers'){
  if (property_exists($obj, $elem) || array_key_exists($elem, get_object_vars($obj))){
    $r=array();
    foreach ($obj->{$elem} as $key => $value) {
      array_push($r, $value);
    }
    return $r;
  }
return false;
}
/**
* Do forms function 
* @param object
* @return array
*/
public static function forms($frm){
  if($frm){
    $fields = array();
    foreach($frm as $key=>$val){
      if(isset($val->required)){ 
        if($val->required == 1 && !empty($val->errmessage) && empty($val->name) && !strlen($val->name)) return array("_er"=>1, "message"=>isset($val->errmessage) && $val->errmessage?$val->errmessage:"Fehler! Füllen Sie alle erforderlichen Felder aus!");

        if(isset($val->type) && ($val->required == 1 && $val->type == "email")){
          if(!$eml = self::email($val->name)){
            return array("_er"=>1, "message"=>"Geben Sie eine gültige E-Mail-Adresse ein!");
          }else{
            $val->name = self::email($val->name);
          }
        }  
      }
      
      $fields[$key] = !empty($val->name) && strlen($val->name) ? $val->name : null;
    }
    return $fields;
  }
  return false;
}  
/**
* Compare passwords
* @param string
* @return array
*/
public static function comparePasswords($pass, $passrep){
  if($pass){
    if(strlen($passrep) <= 7 ){
      return array('err'=>1, 'msg'=>'Passwort mind. 8 Zeichen!');
    }elseif($passrep != $pass){
      return array('err'=>1, 'msg'=>'Ihre Passwörter stimmen nicht überein!');
    }
    return array('err'=>0);
  }
  return array('err'=>1, 'msg'=>'Error!Try again!');
}  
/**
* Get resources for page
*/
public static function resources($conf, $t, $a){
  $t = $t."_".$a;
  if($conf[$t] || !empty($conf[$t])) {
    return $t;
  }
  return false;
}
/**
* Get web settings  
*/
public static function websettings($conf){
  $web = json_decode(self::$config['websettings'], true);

  file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "Websettings- ". $web."\n", FILE_APPEND);

  if(array_key_exists($conf, $web)){
   return $web[$conf];
  }
  return false;
}
/**
  * Is Set and Equal to
  * @param key, value
  * @return boolean
  */ 
  public static function is_set($key,$value=NULL,$method="GET"){
    if(!in_array($method, array("GET","POST"))) return FALSE;
    if($method=="GET") {
      $method=$_GET;
    }elseif($method=="POST"){
      $method=$_POST;
    }
    if(!isset($method[$key])) return FALSE;
    if(!is_null($value) && $method[$key]!==$value) return FALSE;
    return TRUE;
 }
/**
* Validate and sanitize username
* @param string
* @return username
*/  
  public static function username($user){
    if(preg_match('/^\w{4,}$/', $user) && strlen($user)<=20 && filter_var($user,FILTER_SANITIZE_STRING)) {
      return filter_var(trim($user),FILTER_SANITIZE_STRING);
    }
    return false;    
  }

  /**
* Generate uniq username
* @param string
* @return username
*/ 
  public static function generate_username($string_name, $rand_no = 200){
	while(true){
		$username_parts = array_filter(explode(" ", strtolower($string_name))); //explode and lowercase name
		$username_parts = array_slice($username_parts, 0, 2); //return only first two arry part
	
		$part1 = (!empty($username_parts[0]))?substr($username_parts[0], 0,8):""; //cut first name to 8 letters
		$part2 = (!empty($username_parts[1]))?substr($username_parts[1], 0,5):""; //cut second name to 5 letters
		$part3 = ($rand_no)?rand(0, $rand_no):"";
		
		$username = $part1. str_shuffle($part2). $part3; //str_shuffle to randomly shuffle all characters 
		
	//	$username_exist_in_db = username_exist_in_database($username); //check username in database
		//if(!$username_exist_in_db){
			return $username?$username:false;
	//	}
	}
}
/**
* Validate Date
* @param string
*/  
  public static function validatedate($date, $format = 'Y-m-d H:i:s'){
    if(!class_exists("DateTime")){
      if(!preg_match("!(.*)-(.*)-(.*)!",$date)) return false;
      return true;
    }
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
  }

/**
* Function split by new line
* @returns string
**/
public static function splitNewLine($text) {
  $code=preg_replace('/\n$/','',preg_replace('/^\n/','',preg_replace('/[\r\n]+/',"\n",$text)));
  return explode("\n",$code);
}
/**
* Function encrypt
* @returns string
**/
public static function encrypt($string){
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'A1AIugiuiogyuyT!uytrtryiuytyi6r@i765ri5#546ft4!5Yryu5y74(CDCC2BBRT#2935136HH7B63C27'; // user define private key
    $secret_iv = '5fgf5HJ5H75HB63C2723QasWeliydxKUyruyUYRy567R47ukyu77'; // user define secret key
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);

    return $output;
}
/**
* Function decrypt
* @returns string
**/
public static function decrypt($string){
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'A1AIugiuiogyuyT!uytrtryiuytyi6r@i765ri5#546ft4!5Yryu5y74(CDCC2BBRT#2935136HH7B63C27'; // user define private key
    $secret_iv = '5fgf5HJ5H75HB63C2723QasWeliydxKUyruyUYRy567R47ukyu77'; // user define secret key
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
				
    return $output;
}
/**
 * Get IP 
 **/
public static function ip(){
  $ipaddress = '';
   if (getenv('HTTP_CLIENT_IP'))
       $ipaddress = getenv('HTTP_CLIENT_IP');
   else if(getenv('HTTP_X_FORWARDED_FOR'))
       $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
   else if(getenv('HTTP_X_FORWARDED'))
       $ipaddress = getenv('HTTP_X_FORWARDED');
   else if(getenv('HTTP_FORWARDED_FOR'))
       $ipaddress = getenv('HTTP_FORWARDED_FOR');
   else if(getenv('HTTP_FORWARDED'))
      $ipaddress = getenv('HTTP_FORWARDED');
   else if(getenv('REMOTE_ADDR'))
       $ipaddress = getenv('REMOTE_ADDR');
   else
       $ipaddress = 'UNKNOWN';

   return $ipaddress; 
}
/**
 * Validate URLs extended
 **/
public static function is_url_extended($url){
  if(!self::is_url($url)){
    if(self::is_url("http://".$url)){
      return "http://".$url;
    }else{
      return false;
    }
  }else{
    return $url;
  }   
}
/**
 * Validate URLs
 **/
public static function is_url($url){
  if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url) && filter_var($url, FILTER_VALIDATE_URL)){
    return true;
  }    
  return false;     
}
/**
* Create menu and Avatar links
**/
public static function avaratLinks( $leveltype ) { 
  $ansprehen = FALSE;
  if($ansprech = self::user()){
    if(isset($ansprech[5]) && isset($ansprech[6])){
      $ansprehen = TRUE;
    }
  }

  $link = array();
  switch ($leveltype) {
    case "1":
      $link = array("dashboard"=>"Mein Profil", "inbox"=>"Posteingang", "websettings"=>"Settings");
      break;
    case "2":
      $link = array("company"=>"Mein Profil", "merkliste"=>"Meine Merkliste");
      if(!$ansprehen){ $link["meine-anzeigen"] = "Meine Anzeigen"; $link["company-einstellungen"] = "Settings";  $link["rechnungen"] = "Rechnungen"; $link["freunde-einladen"] = "Freunde Einladen";}
      break;
    case "3":
      $link = array("dash"=>"Mein Profil", "liebste"=>"Meine Merkliste", "freunde-einladen"=>"Freunde Einladen", "einstellungen"=>"Settings");
      break;
    case "20":
      $link = false;
      break; 
    default:
      $link = false;
  }
  if($link){
    $content = '';
    foreach($link as $key => $val){
      $content .= '<a class="dropdown-item" '.(( $key == "freunde-einladen" ) ? 'id="sharefreunds"' : '').' href="/'.$key.'">'.e($val).'</a>';
    }
    $content .= '<a class="dropdown-item" href="/logout">'.e('Ausloggen').'</a>';
  }else{
    $content = '
                <a class="dropdown-item" href="willkommen">
                  <i class="dropdown-icon  icon icon-settings"></i> '.e('Profilanlegen').' 
                </a>
                <a class="dropdown-item" href="logout">
                  <i class="dropdown-icon icon icon-power"></i> '.e('Ausloggen').' 
                </a>';
  }
  return  $content;
}
/**
  * Get is in 24Hours
  * @param Facebook page
  * @return number of likes
  */  
  public static function is24h($time_ago){
    $time_ago = strtotime($time_ago);
    $cur_time   = time();
    $time_elapsed   = $cur_time - $time_ago;
    $hours      = round($time_elapsed / 3600);

    if($hours >= 24){
      return false;
    }
  return true;
  }
/**
* Aplly discount to price
* @param string
*/  
public static function rabatPrice($price, $discount){
  
  $newWert = $price-(((int)$price/100)*$discount);
   
  return number_format((float)$newWert, 2, '.', '');
}
/**
* Add VAT to pay value
*/ 
public static function addVAT($val){
  if($vatRate = self::$config["vatvalue"]){
    $tv = (((int)$vatRate/100)*$val)+$val;

    return number_format((float)$tv, 2, '.', '');
  }

  $tv = ((int)$val*1.19); // if the VAT is not in DB = default 19%
  return number_format((float)$val, 2, '.', ''); 
}
/**
  * converts payPlans
  */ 
  public static function plansNames($tip){
    switch ($tip) {
      case "card":
        $pp = e('Karte');
        break;
      case "sofort":
        $pp = "Sofort";
        break;
      case "giro":
        $pp = "Giro";
        break;
      case "sepa":
        $pp = "SEPA";
        break;
      case "paypal":
        $pp = "PayPal";
        break;
      case "uberweiss":
        $pp = e('Überweisung/Auf Rechnung');
        break;
      default:
        $pp = "";
    }
    return $pp;
  }

  public static function revPlansName($tip){
    switch ($tip) {
      case "card":
        $pp = 1;
        break;
      case "sofort":
        $pp = 2;
        break;
      case "giro":
        $pp = 3;
        break;
      case "sepa":
        $pp = 4;
        break;
      case "paypal":
        $pp = 5;
        break;
      case "uberweiss":
        $pp = 6;
        break;
      default:
        $pp = "";
    }
    return $pp;
  }
/**
* Convert levels types
**/
public static function convertLevel($level = 20){
  switch ($level) {
    case "20":
      return "register";
      break;
    case "1":
      return "admin";
      break;
    case "2":
      return "firma";
      break;
    case "3":
      return "kandidat";
      break;
    default:
      return "--";
  }
}
/**
* Convert levels to links
**/
public static function convertLevelLink($level = 20){
  switch ($level) {
    case "20":
      $k = 'willkommen';
      break;
    case "1":
      $k =  'dashboard';
      break;
    case "2":
      $k =  'kandidaten';
      break;
    case "3":
      $k =  'stellenangebote';
      break;
    default:
      $k =  false;
  }
  return $k;
}
/**
* Make password
* @param string, encode= MD5, SHA1 or SHA256 
* @return hash
*/ 
public static function chatStaus($stat){
  if($stat == 1){
    return array("chatmsg"=>e('Online'), "chatclass"=>"online_icon");
  }
  return array("chatmsg"=>e('Offline'), "chatclass"=>"offline_icon");
}




/**
* Make password
* @param string, encode= MD5, SHA1 or SHA256 
* @return hash
*/ 
public static function passEncode($p){ 
  $p = trim($p);    
  $salt = hash('sha512',uniqid());
  $password_sha = openssl_digest($p, 'sha512');
  $encP = hash('sha512', $password_sha . $salt);

  return array("p"=>$encP, "s"=>$salt);  
}
/**
  * Encode string
  * @param string, encode= MD5, SHA1 or SHA256 
  * @return hash
  */   
  public static function encode($string,$encoding="phppass"){      
    if($encoding=="phppass"){
      if(!class_exists("PasswordsHash")) require_once(ROOT."/includes/library/phpPass.class.php");
      $e = new PasswordsHash(8, FALSE);
      return $e->HashPassword($string.self::$config["security"]);
    }else{
      return hash($encoding,$string.self::$config["security"]);
    }
  }
   /**
  * Generate api or random string
  * @param length, start
  * @return 
  */    
  public static function strrand($length=12,$api=""){    
    $use = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"; 
    srand((double)microtime()*1000000); 
    for($i=0; $i<$length; $i++) { 
      $api.= $use[rand()%strlen($use)]; 
    } 
  return $api; 
}

/**
* Unset unwanted from array
* @param array
* @return array
*/   
public static function unsetarray($arr, $allowed){
  foreach($arr as $key => $val){
    if (!in_array($key, $allowed)){
      unset($arr[$key]);
    }
  }
  return $arr;
}

/**
* Get extension
* @param file name
* @return extension
*/   
public static function extension($file){
    return strrchr($file, "."); 
}

 
  /**
  * Clean cookie
  * @param cookie
  * @return cleaned cookie
  */  

    public static function get_cookie($cookie){
      return Main::clean($cookie, 1);
    }


/**
* Redirect function
* @param url/path (not including base), message and header code
* @return nothing
*/   
  public static function redirect($url, $message=array(), $header="", $fullurl=FALSE){      

    //if($url) $url =  preg_replace('/[^a-z]/i','',$url);
    if(!empty($message)){      
      $_SESSION["popsmsg"] = self::clean("{$message[0]}::{$message[1]}",3,FALSE,"<span>");
    }
    switch ($header) {
      case '403':
        header('HTTP/1.1 301 Moved Permanently');
        break;
      case '404':
        header('HTTP/1.1 404 Not Found');
        break;
      case '503':
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 60');
        break;
    }
    if($fullurl){
      header("Location: $url");
      exit;
    }    
    header("Location: ".self::$config["url"]."/$url");
    exit;
  }
  /**
  * Truncate a string
  * @param string, delimiter, append string
  * @return truncated message
  */  
  public static function truncate($string,$del,$limit="...") {
    $len = strlen($string);
      if ($len > $del) {
         $new = substr($string,0,$del).$limit;
          return $new;
      }
      return $string;
  } 

/**
* Format Number
* @param number, decimal
* @return formatted number
*/  
  public static function formatnumber($number,$decimal="0") {
    if($number>1000000000000) $number= round($number /1000000000000, $decimal)."T";
    if($number>1000000000) $number= round($number /1000000000, $decimal)."B";
    if($number>1000000) $number= round($number /1000000, $decimal)."M";
    if($number>10000) $number= round($number /10000, $decimal)."K";

    return $number;
  }  
  /**
  * Ajax Button
  * @param type, max number of page, current page, url, text, class
  * @return formatted button
  */ 
    public static function ajax_button($type, $max, $current,$url,$text='',$class="ajax_load"){
      if($current >= $max) return FALSE;
      return "<a href='$url' data-page='$current' data-type='$type' class='button fullwidth $class'>Load More $text</a>";
    }

/**
  * Main page reditrect Error
  */ 
    public static function message(){
      if(isset($_SESSION["popsmsg"]) && !empty($_SESSION["popsmsg"])) {
        $message=explode("::",self::clean($_SESSION["popsmsg"],3,FALSE,"<span>"));
          $message="<div class='alert alert-{$message[0]}' role='alert'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>{$message[1]}</div>";
          unset($_SESSION["popsmsg"]);
      }else {
        $message="";
      }
      return stripslashes($message); 
    }

   /**
  * Generates url based on settings (seo or not)
  * @param default (non-seo), pretty urls (seo)
  * @return url
  */ 
  public static function href($default, $seo="", $base=TRUE){      
    if(empty($seo)){
      if(self::$config["mod_rewrite"]){
        return (!$base)?"$default":"".self::$config["url"]."/$default";
      }else{
        return (!$base)?"index.php?a=$default":"".self::$config["url"]."/index.php?a=$default";
      }
    }else{
      if(self::$config["mod_rewrite"]){
        return (!$base)?"$seo":"".self::$config["url"]."/$seo";
      }else{
        return (!$base)?"$default":"".self::$config["url"]."/$default";
      }
    }
  }

/**
* Generates admin url based on settings (seo or not)
* @see Main::href()
* @param default (non-seo), pretty urls (seo)
* @return url
*/ 
  public static function ahref($default,$seo="",$base=TRUE){
    if(!empty($seo)) return Main::href("ad/index.php?a=$default","ad/$seo",$base);
    return Main::href("ad/index.php?a=$default","ad/$default",$base);
  }
  /**  
  * Validates the captcha based G server response
  * @param data
  * @return ok
  */  
  public static function checkcaptcha($array, $private_key){      
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$private_key.'&response='.$array["g-recaptcha-response"]);
      $responseData = json_decode($verifyResponse);
      if($responseData->success){
        return true;
      }
      return false;
  }
  /**  
  * Validates the captcha based on settings
  * @param data
  * @return ok
  */  
  public static function check_captcha($capcha){      
    if(empty($capcha) || !self::$config["captcha_private"] || !self::$config["captcha_public"]) return false;

    $reCaptcha = new Recaptcha(self::$config["captcha_private"]);
    $resp = $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $capcha);
      if ($resp == null || !$resp->success) {
        return false;
      }
    return true;    
  }
  /**
   * Generates a random string of given $length.
   *
   * @param Integer $length The string length.
   * @return String The randomly generated string.
   */
  public static function randomString( $length ){
    $seed = 'ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijqlmnpqrtsuvwxyz123456789';
    $max = strlen( $seed ) - 1;

    $string = '';
    for ( $i = 0; $i < $length; ++$i ){
        $string .= $seed[intval( mt_rand( 0.0, $max ))];
    }
    return $string;
} 
  /**  
  * Generates CAPTCHA html based on settings
  * @param none
  * @return captcha
  */     
  public static function captcha(){      
    if(self::$config["captcha"] == 1 && self::$config["captcha_private"] && self::$config["captcha_public"]){
        // echo '<div class="g-recaptcha" data-sitekey="'.$public.'"></div><script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=en"></script>';
      return '<div class="text-center"><div class="g-recaptcha" style="display: inline-block;" data-sitekey="'.self::$config["captcha_public"].'"></div></div>';
    }
    return false;
  }

/**  
  * Generates CSRF token
  * @param none
  * @return token
  */  
  public static function generate(){  
    $sessionProvider = new \EasyCSRF\NativeSessionProvider();
    $easyCSRF = new \EasyCSRF\EasyCSRF($sessionProvider);
   
    if($token = $easyCSRF->generate('my_token')) return "<input type='hidden' name='token' value='$token'/>";
  }


  /**  
  * Check csrf token
  * @param none
  * @return boolean
  */ 
  
  // Make token reusable
  //$easyCSRF->check('my_token', $_POST['token'], null, true);
  // Example 1 hour expiration
  //$easyCSRF->check('my_token', $_POST['token'], 60 * 60);
  public static function csrfcheck($token){  
      
      return false;
      /*
    try {
      $sessionProvider = new \EasyCSRF\NativeSessionProvider();
      $easyCSRF = new \EasyCSRF\EasyCSRF($sessionProvider);
      $easyCSRF->check('my_token', $token, 60, true);
    } catch(InvalidCsrfTokenException $e) {
      return $e->getMessage();
    } */
  }
  
/**
   * Adds extra useragent and remote_addr checks to CSRF protections.
   */
  public static function enableOriginCheck(){
    self::$doOriginCheck = self::$config["origin_check"] ? true : false;
}


/**
  * Create Nonce
  * @param none
  * @return token
  */   
  public static function nonce_create($action="",$duration="5"){
    $i = ceil( time() / ( $duration*60 / 2 ) );
    return md5( $i . $action . $action);
  }
/**
* Return Nonce
* @param none
* @return token
*/   
  public static function nonce($action="",$key="_nonce"){
    return $key."=".substr(self::nonce_create($action), -12, 10);
  }
/**
* Validate Nonce Token
* @param token
* @return boolean
*/   
  public static function validate_nonce($action="",$key="_nonce"){
    if(isset($_GET[$key]) && substr(self::nonce_create($action), -12, 10) == $_GET[$key]){
      return true;
    }
    return false;
  }  
/**
   * Set ucookie  name, value, expire, path, domain, secure,  httponly   Main::cookie("pruff", "PruffEmail", FALSE);
   * @param Name, value
   */  
  public static function cookie($name, $value="", $flag = TRUE, $time=1){
    if(empty($value)){
      if(isset($_COOKIE[$name])){
        return Main::clean($_COOKIE[$name], 3, FALSE);
      }else{
        return FALSE;
      }
    }
  //setcookie($name, $value, time()+($time*60), "/",DOMAIN,TRUE,TRUE);
    setcookie($name, $value, [
			'expires' => time()+($time*60),
			'path' => '/',
			'domain' => DOMAIN,
			'secure' => TRUE,
			'httponly' => $flag,
			'samesite' => 'None',
	  ]);
  }

/**
 * Enqueue scripts to header and footer
 */
  public static function enqueue($where="header"){
    if($where=="footer"){  
      global $enqueue_footer;
      echo $enqueue_footer;
    }else{
      global $enqueue_header;
      echo $enqueue_header;
    } 
  }
/**  || strpos($url, "jquery.toast.css") || strpos($url, "select2.min.css") || strpos($url, "cookie.css") || strpos($url, "sweetalert.css")
 * Add scripts to header and footer  defer /js.pusher.com/7.0/pusher.min.js?v=7.0   
 */  
  public static function add($url, $type="script", $footer=TRUE, $deffer = ''){  
    if(strpos($url, "pusher.com")){ 
      $deffer = 'defer';  
    } else if(strpos($url, "query.touchSwipe.min.js")){ 
      $deffer = 'defer';
    } else if(strpos($url, "js/swipe.js")){ 
      $deffer = 'defer';
      
      
    } else if(strpos($url, "select2.full.min.js")){   
      $deffer = 'defer';
    } else if(strpos($url, "select2.js")){    
      $deffer = 'defer';
    } else if(strpos($url, "sticky.js")){    
      $deffer = 'defer';
    } else if(strpos($url, "bootstrap.min.css")){ 
      $deffer = "media=\"all\" onload=\"this.media='all'\""; 
    } else if(strpos($url, "icons.css")){ 
      $deffer = "media=\"all\" onload=\"this.media='all'\""; 
    } else if(strpos($url, "owl.carousel.css")){ 
      $deffer = "media=\"all\" onload=\"this.media='all'\""; 
    } else if(strpos($url, "jquery.mCustomScrollbar.css")){ 
      $deffer = "media=\"all\" onload=\"this.media='all'\"";  
    } else if(strpos($url, "recaptcha/api.js") ){ 
      $deffer = "defer"; 
    } else if(strpos($url, "jquery.toast.css") ){ 
      $deffer = "media=\"all\" onload=\"this.media='all'\"";  
    } else if(strpos($url, "select2.min.css") ){ 
      $deffer = "media=\"all\" onload=\"this.media='all'\""; 
    } else if(strpos($url, "cookie.css") ){ 
      $deffer = "media=\"all\" onload=\"this.media='all'\""; 
    } else if(strpos($url, "sweetalert.css") ){ 
      $deffer = "media=\"all\" onload=\"this.media='all'\""; 
    }else{ 
      $deffer = "";
    }
    if($type=="style"){   
      $tag='<link rel="stylesheet" type="text/css" href="'.$url.'" '.$deffer.'>';
    }elseif($type=="custom"){
      $tag=$url;
    }else{
      $tag='<script '.$deffer.' src="'.$url.'"></script>';
    }
    if($footer){
      global $enqueue_footer;
      $enqueue_footer.=$tag."\n\t";
    }else{
      global $enqueue_header;
      $enqueue_header.=$tag."\n\t";
    }
  } 
/**
 * Enqueue scripts to header and footer
 */
  public static function admin_enqueue($where="header"){
    if($where=="footer"){  
      global $admin_enqueue_footer;
      echo $admin_enqueue_footer;
    }else{
      global $admin_enqueue_header;
      echo $admin_enqueue_header;
    } 
  }
/**
 * Add scripts to header and footer
 */  
  public static function admin_add($url, $type="script", $footer=TRUE){
    if($type=="style"){
      $tag='<link rel="stylesheet" type="text/css" href="'.$url.'">';
    }elseif($type=="custom"){
      $tag=$url;
    }else{
      $tag='<script type="text/javascript" src="'.$url.'"></script>';
    }
    if($footer){
      global $admin_enqueue_footer;
      $admin_enqueue_footer.=$tag."\n\t";
    }else{
      global $admin_enqueue_header;
      $admin_enqueue_header.=$tag."\n\t";
    }
  }
  /**
    * List of JS CDNs
    **/  
    public static function cdn($cdn, $version="", $admin=FALSE){      
      $cdns = array(  
        "recapcha"=> array(
          "src" => "https://www.google.com/recaptcha/api.js",
          "latest" =>"1.0"
        ),
        "pusher"=> array(
          "src" => "https://js.pusher.com/[version]/pusher.min.js",  
          "latest" =>"7.0"
        ),
        "jquery"=> array(
            "src" => "https://cdnjs.cloudflare.com/ajax/libs/jquery/[version]/jquery.min.js",
            "latest" =>"3.6.0"
          ),
        "jquery-ui"=> array(
            "src" => "https://cdnjs.cloudflare.com/ajax/libs/jqueryui/[version]/jquery-ui.min.js",
            "latest" =>"1.10.3"
          ),
        "ace"=>array(
            "src"=>"https://cdnjs.cloudflare.com/ajax/libs/ace/[version]/ace.js",
            "latest" => "1.1.01"
          ),
        "icheck"=>array(
            "src"=>"https://cdnjs.cloudflare.com/ajax/libs/iCheck/[version]/icheck.min.js",
            "latest" => "1.0.1"
          ),
        "ckeditor"=>array(
            "src"=>"https://cdnjs.cloudflare.com/ajax/libs/ckeditor/[version]/ckeditor.js",
            "latest" => "4.3.2"
          ),
        "selectize"=>array(
            "src"=>"https://cdnjs.cloudflare.com/ajax/libs/selectize.js/[version]/js/standalone/selectize.min.js",
            "latest"=>"0.8.5"
          ),
        "zlip"=>array(
            "src"=>"https://cdnjs.cloudflare.com/ajax/libs/zclip/[version]/jquery.zclip.min.js",
            "latest"=>"1.1.2"
          ),
        "flot"=>array(
            "src"=>"https://cdnjs.cloudflare.com/ajax/libs/flot/[version]/jquery.flot.min.js",
            "latest"=>"0.8.2"
          ),
        "less"=>array(
            "src"=>"https://cdnjs.cloudflare.com/ajax/libs/less.js/[version]/less.min.js",
            "latest"=>"1.6.2"
          ),
        "ckeditor"=>array(
            "src"=>"https://cdnjs.cloudflare.com/ajax/libs/ckeditor/[version]/ckeditor.js",
            "latest"=>"4.3.2"
          )        
        );
      if(array_key_exists($cdn, $cdns)){
        if(!empty($version)  || ($version <= $cdns[$cdn]["latest"])){
          $js=str_replace("[version]", $version, $cdns[$cdn]["src"])."?v=$version";
        }else{
          $js=str_replace("[version]", $cdns[$cdn]["latest"], $cdns[$cdn]["src"])."?v={$cdns[$cdn]["latest"]}";
        }
        if($admin){
          return Main::admin_add($js,"script",TRUE);
        }else{
          return Main::add($js,"script", TRUE);
        }
      }else{
        if($admin){
          return Main::admin_add(self::$config['url'].'/'.$cdn,"script",TRUE);
        }else{
          return Main::add(self::$config['url'].'/'.$cdn,"script",TRUE);
        }
      }
      return FALSE;
    }
    /**
    * List of CSS CDNs & most requested styles
    **/  
    public static function cdncss($cdn, $admin=FALSE){      
      $cdns=array(
        "bootstrap"=> array(
            "src" => "link la dcnul de la nbootstrap"
          )
      
        );
      if(array_key_exists($cdn, $cdns)){
        if($admin){
          return Main::admin_add($cdns[$cdn]["src"], "style", $admin);
        }else{
          return Main::add($cdns[$cdn]["src"], "style", $admin);
        }
      }else{
        if($admin){
          return Main::admin_add(self::$config['url'].'/'.$cdn, "style", $admin);
        }else{
          return Main::add(self::$config['url'].'/'.$cdn, "style", $admin);
        }
      }
      return FALSE;
    }
  /**
   * Get default Language or now
   */   
  public static function defaultLang(){
    if(isset($_COOKIE["lang"]) && !empty($_COOKIE["lang"])){
      $_lang = $_COOKIE["lang"]; 
    } else{
      $_lang = self::$config["lang"]; 
    }
    return $_lang;    
  }
  /* At least one is checked
  * @param array
  * @return array
  */
  public static function atLeastOneChecked($frm, $arr){
    $countElem = count($arr);
    $el=0;
    foreach($arr as $key => $val){
      if(!array_key_exists($val, $frm)) $el++;
    }
    
    if($el < $countElem){
      return true;
    }
    return false;
  }
  /* Is allowed to work
  * @param array
  * @return array   154 056
  */
  public static function makeJobId($id, $count = 3){
    $zeros ='';
    $arr = str_split($id, $count);

    $last = end($arr);
    $strnr = strlen($last); //2 
    $zz = ($count-$strnr);       ////$arr =  125 789   LAST 12

    if($zz >= 1){
      for($q = 0; $q < $zz; $q++){
        $zeros .= '0'; 
      }
     $fullLastId = $zeros.$last;
    }else{
      $fullLastId = $last;
    }

    if(count($arr) > 1){
      $firsts = '';
      for($w=0; $w < (count($arr)-1) ; $w++){
        $firsts .= $arr[$w].' '; 
      }
      $fullId = $firsts.$fullLastId;
    }else{
      $fullId = $fullLastId;
    }
     
   return $fullId;
  }
  /* Is allowed to work
  * @param array
  * @return array
  */
  public static function isAllowedWork($frm){

    $approbat = array_key_exists('mustsjobapp', $frm) ? $frm['mustsjobapp'] : 0;
    $erlaubnis = array_key_exists('mustsjoberlaub', $frm) ? $frm['mustsjoberlaub'] : 0;
    $annerkung = array_key_exists('mustsjobaank', $frm) ? $frm['mustsjobaank'] : 0;

    if($approbat == 0 && $erlaubnis == 0 && $annerkung == 0){
      return false;
    }
    return array('mustsjobapp'=>$approbat, 'mustsjoberlaub'=>$erlaubnis, 'mustsjobaank'=>$annerkung);
  }
  /**
* Create birth
* @param string
* @return string
*/
public static function doBirthdate($showBirth, $userday, $usermonth, $useryear){
  if($showBirth == 2 || $showBirth == '' || !isset($showBirth)){
    return '';
  }
  $day = isset($userday) ? $userday : 0;
  $month = isset($usermonth) ? $usermonth : 0;
  $year = isset($useryear) ? $useryear : 0;
  if($day >=1 && $month >= 1 && $year >= 1){
    $datumBirth = $day."-".$month."-".$year;
  }elseif($day == 0 && $month == 0 && $year == 0){
    $datumBirth = "";
  }elseif($year >= 1){
    $datumBirth = $day."-".$month."-".$year;
  }
  return $datumBirth;
}

/**
   * Self add to translate
   */
  public static function selfAddTranslations($lang, $key){
    $destinationPath = ROOT."/includes/languages/".Main::config('lang').".php";
    $array = file_get_contents($destinationPath);
    $lang[$key] = $key;

    if(file_put_contents($destinationPath, '<?php'.  PHP_EOL.' $lang ='.var_export($lang, true).';'.PHP_EOL.'?>')){
      return true;
    }
    return false;
  }


  public static function bearbeitenTranslations($key, $wortes){
    $language = Main::$lang;
    $destinationPath = ROOT."/includes/languages/".Main::config('lang').".php";
    $array = file_get_contents($destinationPath);
    $language[$key] = $wortes;

    if(file_put_contents($destinationPath, '<?php'.  PHP_EOL.' $lang ='.var_export($language, true).';'.PHP_EOL.'?>')){
      return true;
    }
    return false;
  }


  public static function checkCheckuri($val, $all){
    if($val){
      $res = (array)$val;
        if(!is_array($all)){
          if($res && !in_array($all, $res)){
            return true;
          }
        return false;
        }elseif(is_array($all)){
          foreach($res as $val){
            if(in_array($val, $all)){
              return false;
            }
          }
          return true;
        }
    }
  return false;
  }

  /**
   * Share to
   */   
  public static function shareit($vals=''){
    $cont = '';
    if($curr = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']){
      $cont .= '<a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u='.$curr.'" class="dropdown-item mt-0 mr-2 text-muted"><i class="fa fa-facebook-square" aria-hidden="true"></i>&nbsp;Facebook</a>
             <a target="_blank" href="http://pinterest.com/pin/create/bookmarklet/?url='.$curr.'&is_video=false&description='.self::description().'&media='.Main::config('url').$vals['pic'].'" class="dropdown-item mt-0 mr-2 text-muted"><i class="fa fa-pinterest-square" aria-hidden="true"></i>&nbsp;Pinterest</a>
             <a target="_blank" href="https://www.linkedin.com/shareArticle?mini=true&url='.$curr.'&title='.$vals['titel'].'" class="dropdown-item mt-0 mr-2 text-muted"><i class="fa fa-linkedin-square" aria-hidden="true"></i>&nbsp;Linkedin</a>
             <a title="Reddit" target="_blank" href="http://www.reddit.com/submit?url='.urlencode($curr).'" onClick="javascript:window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600\');return false;" class="dropdown-item mt-0 mr-2 text-muted"><i class="fa fa-reddit-square" aria-hidden="true"></i>&nbsp;Reddit</a>

             <a title="Reddit" target="_blank" href="https://www.xing.com/spi/shares/new?url='.urlencode($curr).'" class="dropdown-item mt-0 mr-2 text-muted"><i class="fa fa-xing-square" aria-hidden="true"></i>&nbsp;Xing</a>

             <a target="_blank" href="http://twitter.com/share?text='.$vals['titel'].'&url='.$curr.'" class="dropdown-item mt-0 mr-2 text-muted"><i class="fa fa-twitter" aria-hidden="true"></i>&nbsp;Twitter</a>
             <a target="_blank" href="https://telegram.me/share/url?url='.$curr.'" class="dropdown-item mt-0 mr-2"><i class="fa fa-telegram" aria-hidden="true"></i>&nbsp;Telegram</a>
             <a target="_blank" title="WhatsApp" href="whatsapp://send?text='.$curr.'" data-action="share/whatsapp/share" onClick="javascript:window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600\');return false;" class="dropdown-item mt-0 mr-2 text-muted"><i class="fa fa-whatsapp" aria-hidden="true"></i>&nbsp;WhatsApp</a>
             <a title="Share on E-Mail" target="_blank" href="mailto:?subject='.$vals['titel'].'&body='.$curr.'"  onClick="javascript:window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600\');return false;" class="dropdown-item mt-0 mr-2 text-muted"><i class="fa fa-envelope" aria-hidden="true"></i>&nbsp;E-mail</a>';
    } 
    return $cont;    
  }

  /**
   * Translate strings
   */   
    public static function e($text){
      if(!is_array(Main::$lang)) return $text;
      if(isset(Main::$lang[$text]) && !empty(Main::$lang[$text])) {
        return Main::$lang[$text];
      }else{
        @self::selfAddTranslations(Main::$lang, $text);
      }
      return $text;    
    }
  /**
   * Check if user agent is bot
   */  
  public static function bot($ua=""){
    if(empty($ua)){
      if(!isset($_SERVER['HTTP_USER_AGENT']) || empty($_SERVER['HTTP_USER_AGENT']) || is_null($_SERVER['HTTP_USER_AGENT'])){
        return TRUE;
      }
      $ua=$_SERVER['HTTP_USER_AGENT'];
    }
    $list = array("facebookexternalhit","Teoma", "alexa", "froogle", "Gigabot", "inktomi",
    "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
    "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot",
    "crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp",
    "msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz",
    "Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot",
    "Mediapartners-Google", "Sogou web spider", "WebAlta Crawler","TweetmemeBot",
    "Butterfly","Twitturls","Me.dium","Twiceler");
    foreach($list as $bot){
      if(strpos($ua,$bot)!==false)
      return true;
    }
    return false; 
  }
  /**
   * List of Countries and ISO Code
   * @since 1.0
   **/
  public static function countries($code=""){
    $countries=array('AF'=>'Afghanistan','AX'=>'Aland Islands','AL'=>'Albania','DZ'=>'Algeria','AS'=>'American Samoa','AD'=>'Andorra','AO'=>'Angola','AI'=>'Anguilla','AQ'=>'Antarctica','AG'=>'Antigua And Barbuda','AR'=>'Argentina','AM'=>'Armenia','AW'=>'Aruba','AU'=>'Australia','AT'=>'Austria','AZ'=>'Azerbaijan','BS'=>'Bahamas','BH'=>'Bahrain','BD'=>'Bangladesh','BB'=>'Barbados','BY'=>'Belarus','BE'=>'Belgium','BZ'=>'Belize','BJ'=>'Benin','BM'=>'Bermuda','BT'=>'Bhutan','BO'=>'Bolivia','BA'=>'Bosnia And Herzegovina','BW'=>'Botswana','BV'=>'Bouvet Island','BR'=>'Brazil','IO'=>'British Indian Ocean Territory','BN'=>'Brunei Darussalam','BG'=>'Bulgaria','BF'=>'Burkina Faso','BI'=>'Burundi','KH'=>'Cambodia','CM'=>'Cameroon','CA'=>'Canada','CV'=>'Cape Verde','KY'=>'Cayman Islands','CF'=>'Central African Republic','TD'=>'Chad','CL'=>'Chile','CN'=>'China','CX'=>'Christmas Island','CC'=>'Cocos (Keeling) Islands','CO'=>'Colombia','KM'=>'Comoros','CG'=>'Congo','CD'=>'Congo, Democratic Republic','CK'=>'Cook Islands','CR'=>'Costa Rica','CI'=>'Cote D\'Ivoire','HR'=>'Croatia','CU'=>'Cuba','CY'=>'Cyprus','CZ'=>'Czech Republic','DK'=>'Denmark','DJ'=>'Djibouti','DM'=>'Dominica','DO'=>'Dominican Republic','EC'=>'Ecuador','EG'=>'Egypt','SV'=>'El Salvador','GQ'=>'Equatorial Guinea','ER'=>'Eritrea','EE'=>'Estonia','ET'=>'Ethiopia','FK'=>'Falkland Islands (Malvinas)','FO'=>'Faroe Islands','FJ'=>'Fiji','FI'=>'Finland','FR'=>'France','GF'=>'French Guiana','PF'=>'French Polynesia','TF'=>'French Southern Territories','GA'=>'Gabon','GM'=>'Gambia','GE'=>'Georgia','DE'=>'Germany','GH'=>'Ghana','GI'=>'Gibraltar','GR'=>'Greece','GL'=>'Greenland','GD'=>'Grenada','GP'=>'Guadeloupe','GU'=>'Guam','GT'=>'Guatemala','GG'=>'Guernsey','GN'=>'Guinea','GW'=>'Guinea-Bissau','GY'=>'Guyana','HT'=>'Haiti','HM'=>'Heard Island & Mcdonald Islands','VA'=>'Holy See (Vatican City State)','HN'=>'Honduras','HK'=>'Hong Kong','HU'=>'Hungary','IS'=>'Iceland','IN'=>'India','ID'=>'Indonesia','IR'=>'Iran, Islamic Republic Of','IQ'=>'Iraq','IE'=>'Ireland','IM'=>'Isle Of Man','IL'=>'Israel','IT'=>'Italy','JM'=>'Jamaica','JP'=>'Japan','JE'=>'Jersey','JO'=>'Jordan','KZ'=>'Kazakhstan','KE'=>'Kenya','KI'=>'Kiribati','KR'=>'Korea','KW'=>'Kuwait','KG'=>'Kyrgyzstan','LA'=>'Lao People\'s Democratic Republic','LV'=>'Latvia','LB'=>'Lebanon','LS'=>'Lesotho','LR'=>'Liberia','LY'=>'Libyan Arab Jamahiriya','LI'=>'Liechtenstein','LT'=>'Lithuania','LU'=>'Luxembourg','MO'=>'Macao','MK'=>'Macedonia','MG'=>'Madagascar','MW'=>'Malawi','MY'=>'Malaysia','MV'=>'Maldives','ML'=>'Mali','MT'=>'Malta','MH'=>'Marshall Islands','MQ'=>'Martinique','MR'=>'Mauritania','MU'=>'Mauritius','YT'=>'Mayotte','MX'=>'Mexico','FM'=>'Micronesia, Federated States Of','MD'=>'Moldova','MC'=>'Monaco','MN'=>'Mongolia','ME'=>'Montenegro','MS'=>'Montserrat','MA'=>'Morocco','MZ'=>'Mozambique','MM'=>'Myanmar','NA'=>'Namibia','NR'=>'Nauru','NP'=>'Nepal','NL'=>'Netherlands','AN'=>'Netherlands Antilles','NC'=>'New Caledonia','NZ'=>'New Zealand','NI'=>'Nicaragua','NE'=>'Niger','NG'=>'Nigeria','NU'=>'Niue','NF'=>'Norfolk Island','MP'=>'Northern Mariana Islands','NO'=>'Norway','OM'=>'Oman','PK'=>'Pakistan','PW'=>'Palau','PS'=>'Palestinian Territory, Occupied','PA'=>'Panama','PG'=>'Papua New Guinea','PY'=>'Paraguay','PE'=>'Peru','PH'=>'Philippines','PN'=>'Pitcairn','PL'=>'Poland','PT'=>'Portugal','PR'=>'Puerto Rico','QA'=>'Qatar','RE'=>'Reunion','RO'=>'Romania','RU'=>'Russian Federation','RW'=>'Rwanda','BL'=>'Saint Barthelemy','SH'=>'Saint Helena','KN'=>'Saint Kitts And Nevis','LC'=>'Saint Lucia','MF'=>'Saint Martin','PM'=>'Saint Pierre And Miquelon','VC'=>'Saint Vincent And Grenadines','WS'=>'Samoa','SM'=>'San Marino','ST'=>'Sao Tome And Principe','SA'=>'Saudi Arabia','SN'=>'Senegal','RS'=>'Serbia','SC'=>'Seychelles','SL'=>'Sierra Leone','SG'=>'Singapore','SK'=>'Slovakia','SI'=>'Slovenia','SB'=>'Solomon Islands','SO'=>'Somalia','ZA'=>'South Africa','GS'=>'South Georgia And Sandwich Isl.','ES'=>'Spain','LK'=>'Sri Lanka','SD'=>'Sudan','SR'=>'Suriname','SJ'=>'Svalbard And Jan Mayen','SZ'=>'Swaziland','SE'=>'Sweden','CH'=>'Switzerland','SY'=>'Syrian Arab Republic','TW'=>'Taiwan','TJ'=>'Tajikistan','TZ'=>'Tanzania','TH'=>'Thailand','TL'=>'Timor-Leste','TG'=>'Togo','TK'=>'Tokelau','TO'=>'Tonga','TT'=>'Trinidad And Tobago','TN'=>'Tunisia','TR'=>'Turkey','TM'=>'Turkmenistan','TC'=>'Turks And Caicos Islands','TV'=>'Tuvalu','UG'=>'Uganda','UA'=>'Ukraine','AE'=>'United Arab Emirates','GB'=>'United Kingdom','US'=>'United States','UM'=>'United States Outlying Islands','UY'=>'Uruguay','UZ'=>'Uzbekistan','VU'=>'Vanuatu','VE'=>'Venezuela','VN'=>'Viet Nam','VG'=>'Virgin Islands, British','VI'=>'Virgin Islands, U.S.','WF'=>'Wallis And Futuna','EH'=>'Western Sahara','YE'=>'Yemen','ZM'=>'Zambia','ZW'=>'Zimbabwe');
    if(!empty($code)) return $countries[$code];
    return $countries;
  }
  /**
   * Custom cURL Function
   **/  
  public static function curl($url,$option=array()){
    if(in_array('curl', get_loaded_extensions())){    
      $option=!empty($options)?$option:array(CURLOPT_RETURNTRANSFER => 1,CURLOPT_URL => $url,CURLOPT_USERAGENT => 'Premium Poll Script');
      $curl = curl_init();
      curl_setopt_array($curl,$option);
      $resp = curl_exec($curl);
      curl_close($curl);    
      return $resp;
    }

    if(ini_get('allow_url_fopen')){
      return @file_get_contents($url);
    }
  } 


  public static function emailCreate($subj, $array = []){
    if($emailsFormulare = self::emailsFormulare($subj, $array)){
      if($sendArr = array_merge($emailsFormulare['lines'], $emailsFormulare['statics'])){
        return array("subject"=>$emailsFormulare['subject'], "message"=>$sendArr, "template"=>$emailsFormulare['template']);
      } 
    }
  return false;  
  }

  public static function emailsFormulare($subject, $array){
    $SLinks=[];
    if($socLinks = json_decode(self::$config['linkSocials'], true)){
      foreach($socLinks as $k => $lnk){
        if(!empty($lnk) && $lnk != null) {
          $SLinks['socialink'.$k] = $lnk;  
        }else{
          $SLinks['socialink'.$k] = '#'; 
        }
      }
    }	

    $emailSubject = array(
      'email'=> array_key_exists('customsubject', $array) ? $array['customsubject'] : Main::config('title'),
      'register'=>e('Herzlich willkommen bei doc-site!'),
      'registerConfirm'=>e('E-Mail ist bestätigt!'),
      'confirm'=>e('Account Bestätigung'),
      'recovery'=>e('Passwort zurücksetzen'),
    );

    $emailStatics = array(
      'cannotsee'=>e('Sollte diese Email nicht einwandfrei dargestellt werden, klicken Sie bitte'),
      'kontakt'=>e('Kontakt'),
      'kontaktEmail'=>self::$config['email'],
      'kontaktWebsite'=>self::$config['url'],
      'impressum'=>e('Impressum'),
      'webname'=>'<a href="https://doc-site.de/impressum" style="text-decoration: none;">'.self::$config['title'].'</a>',
      'webaddress'=>self::$config['companyaddress'],
      'weblocation'=>self::$config['companycity'],
      'webfolgen'=>e('Folgen Sie uns schon?'),
      'currjahr'=>date('Y'),
      'kontaktSocials'=>$SLinks
    );

    

    switch($subject){
      case "email":
       $template = '0';
       $lines['l0'] = $array['username'] ? $array['username'] : " ";
       $lines['l1']= $array['l1'];
       $lines['l2']= array_key_exists('l2', $array) ? $array['l2'] : '';

       $lines['b1'] = array_key_exists('b1link', $array) || array_key_exists('b1', $array) ? $array['b1'] : '';
       $lines['b1link'] = array_key_exists('b1link', $array) || array_key_exists('b1', $array) ? $array['b1link'] : '';
       $lines['b1display'] = array_key_exists('b1link', $array) || array_key_exists('b1', $array) ? 'block' : 'none';

       $lines['l45display'] = array_key_exists('l4', $array) || array_key_exists('l5', $array) ? 'block' : 'none';
       $lines['l4']= array_key_exists('l4', $array) ? $array['l4'] : '';
       $lines['l5']= array_key_exists('l5', $array) ? $array['l5'] : '';
      break;
      case "recovery":
        $template = 3;
        $lines['l0'] = e('Passwort zurücksetzen');
        $lines['l1']=e('Für Ihr Konto wurde eine Passwortwiederherstellung angefordert.');
        $lines['l2']=e('Geben Sie diesen Code ein, um Ihr Kontopasswort zurückzusetzen.');
        $lines['l3']=e('Hier ist Ihr Geheimcode');
        $lines['code']=$array['code'];
        $lines['b1'] = e("Wiederherstellen Ihre Passwort");
        $lines['b1link'] = $array['b1link'];
      break;
      case "confirm":
       $template = 2;
       $lines['l0'] = e('Herzlich willkommen bei doc-site!');
       $lines['username'] = $array['username'] ? $array['username'] : "...";
       $lines['l1']=e('Schön , dass Sie bei uns sind!');
       $lines['l2']=e('Ein gutes und vollständiges Profil ist für Ihre Stellensuche sehr wichtig.Die Angaben in ihrem Profil werden dazu beitragen, dass sie gute und vor allem passgenaue Angebote von doc-site bekommen.Ausserdem können Sie auch von den Unternehmen direkt kontaktiert werden.');
       $lines['b1'] = e("Gleisch Profil erstellen");
       $lines['b1link'] = $array['b1link'];
       $lines['l3'] = e("Bei fragen zu Stellenangeboten, Ihren Account-Einstellungen oder sonstigen Fragen, stehen wir Ihnen gerne mit Rat und Rat zur Seite.Sie können dazu gerne direkt Kontakt zu uns aufnehmen.");
       $lines['b2'] = e("Bei Fragen - fragen!");
       $lines['b2link'] = $array['b2link'];
      break;
      case "registerConfirm":
        $template = 2;
        $lines['l0'] = e('Ihre E-Mail-Adresse ist jetzt bestätigt!');
        $lines['l1']=e('Schön , dass Sie bei uns sind!');
        $lines['l2']= "<br/>";
        $lines['b1']=e("Gleisch Profil erstellen");
        $lines['b1link'] = $array['b1link'];

        $lines['l3'] = e("Bei fragen zu Stellenangeboten, Ihren Account-Einstellungen oder sonstigen Fragen, stehen wir Ihnen gerne mit Rat und Rat zur Seite.Sie können dazu gerne direkt Kontakt zu uns aufnehmen.");
        $lines['b2'] = e("Bei Fragen - fragen!");
        $lines['b2link'] = $array['b2link'];
      break;
      case "register":
        $template = 1;
        $lines['l0'] = e('Herzlich willkommen bei doc-site!');
        $lines['l1']=e('Vielen dank, dass Sie sich bei doc-site registriert haben.</bold> Bitte bestätigen Sie bitte Ihre Email-Adresse.');
        $lines['b1']=e('Email-Adresse bestätigen');
        $lines['b1link'] = $array['b1link'];
        $lines['l2']=e('Falls Sie nicht weitergeleitet werden sollten, kopieren Sie bitte die URL und fügen Sie diese in die Adressziele eines neuen Browsersfensters ein');
        $lines['link']=$array['b1link'];

        $lines['l3']= '';
        $lines['b2']='';
        $lines['b2link'] = $array['b2link'];
         

        // $lines['l3']=e('Wenn die Weiterleitung nicht funktioniert, können Sie hier eine neue Bestatigungs-Email anfordern');
        // $lines['b2']=e('Neue Bestätigungsmail anfordern');
        // $lines['b2link'] = $array['b2link'];
        $lines['l4']=e('Der Bestätigungsmail läuft in 48 Stunden ab.');
        $lines['l5']=e('Haben Sie diese Email erhalten, ohne dass Sie sich registriert haben? Dann klicken Sie bitte hier.');
       break;
       case "registerBewerb":
        $template = 1;
        $lines['l0'] = e('Herzlich willkommen bei doc-site!');
        $lines['l1']=e('Vielen dank, dass Sie sich bei doc-site registriert haben.</bold> Bitte bestätigen Sie bitte Ihre Email-Adresse.');
        $lines['b1']=e('Email-Adresse bestätigen');
        $lines['b1link'] = $array['b1link'];
        $lines['l2']=e('Falls Sie nicht weitergeleitet werden sollten, kopieren Sie bitte die URL und fügen Sie diese in die Adressziele eines neuen Browsersfensters ein');
        $lines['link']=$array['b1link'];

        $lines['l3']=e('Wenn die Weiterleitung nicht funktioniert, können Sie hier eine neue Bestatigungs-Email anfordern');
        $lines['b2']=e('Neue Bestätigungsmail anfordern');
        $lines['b2link'] = $array['b2link'];
        $lines['l4']=e('Der Bestätigungsmail läuft in 48 Stunden ab.');
        $lines['l5']=e('Haben Sie diese Email erhalten, ohne dass Sie sich registriert haben? Dann klicken Sie bitte hier.');
       break;
       default:
        $template = 0;
    }


    return array("subject"=>$emailSubject[$subject] ? $emailSubject[$subject] : Main::$config['title'], "statics"=>$emailStatics, "lines"=>$lines, "template"=>$template);
  }

/**
   * Send Email
   * @param array
   * @return boolean
   */  
  public static function send(array $array, $template){         
    if(!empty(self::$config["smtp"]["host"])){
      $mail = new PHPMailer(true);
      $mail->isSMTP();
      $mail->SMTPDebug = 0;
      $mail->Debugoutput = 'html';
			$mail->Host = self::$config["smtp"]["host"];
			$mail->SMTPAuth = true;
      $mail->CharSet   = 'UTF-8';
      $mail->Encoding  = 'base64';
      $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
      );      

			$mail->Username = self::$config["smtp"]["user"];
			$mail->Password = self::$config["smtp"]["pass"];
			$mail->SMTPSecure = "tls";
      $mail->SMTPKeepAlive = true;
			$mail->Port = self::$config["smtp"]["port"];

      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "*************************** "."\n", FILE_APPEND);
      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "  Mail Sending Part "."\n", FILE_APPEND);
      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "*************************** "."\n", FILE_APPEND);
      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "EmailHost- ". $mail->Host."\n", FILE_APPEND);
      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "username- ". $mail->Username."\n", FILE_APPEND);
      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "Password- ". $mail->Password."\n", FILE_APPEND);
      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "Port- ". $mail->Port."\n", FILE_APPEND);
      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "Template- ". $template."\n", FILE_APPEND);


    }
    $mail->isHTML(true);
    $mail->addCustomHeader('Content-type: text/html;charset=UTF-8');
    $mail->SetFrom(self::$config["smtp"]["noreply"], self::$config["title"]);
    $mail->AddReplyTo(self::$config["email"], self::$config["title"]);
    $mail->AddAddress($array["to"]);   
    $mail->Subject =  $array["subject"]; 
    $mail->DKIM_domain = 'doc-site.de';
    $mail->DKIM_selector = 'default';
    $mail->DKIM_passphrase = '';
    $mail->DKIM_identity = $mail->From;

    if($content=file_get_contents(TEMPLATE."/email_{$template}.php")){

      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "  *****  Start File contents part *****  "."\n", FILE_APPEND);
      if(array_key_exists("message", $array)){
        foreach($array["message"] as $key => $value){
          if($key != 'kontaktSocials') $content=str_replace("[{$key}]", $value, $content);
        }

        if($allLinks = $array["message"]['kontaktSocials']){
          foreach($allLinks as $key => $value){
            if($value == '#'){
              $k = 'show'.$key;
              $content = str_replace("[{$k}]", 'none', $content); 
            }else{
              $k = 'show'.$key;
              $content = str_replace("[{$k}]", 'block', $content); 
            }
            $content = str_replace("[{$key}]", $value, $content); 
          }
        }

        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "  *****  Ending  File contents part *****  "."\n", FILE_APPEND);
      }else{

        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "  ### Failure! File contents part ###  "."\n", FILE_APPEND);
        return FALSE;
      }

    }else{

      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "---------------------Jumped to false part of mail sending-----------------  "."\n", FILE_APPEND);
      return FALSE;
    }

    $mail->Body = $content;
    // if(!$mail->Send()) {
      // if(true) {
        if(!empty($content)) {
       $mail->ClearAllRecipients();
        $headers  = 'From:  '.self::$config["title"].' <'.self::$config["email"].'>' . "\r\n";
        $headers  .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        mail($array["to"], $array["subject"], $content, $headers);
        
        return TRUE;
    } else {

      $mail->ClearAllRecipients();
      return TRUE;
    }          
  }  
  /**
   * Country Codes
   **/
  public static function derbundes($code, $reverse=FALSE){

    $array = array("BS"=> "Bundesweit", "BW"=>"Baden-Wuerttemberg","BY"=>"Bayern","BE"=>"Berlin","BB"=>"Brandenburg","HB"=>"Bremen","HH"=>"Hamburg","HE"=>"Hessen","MV"=>"Mecklenburg-Vorpommern","NI"=>"Niedersachsen","NW"=>"Nordrhein-Westfalen","RP"=>"Rheinland-Pfalz","SL"=>"Saarland","SN"=>"Sachsen","ST"=>"Sachsen-Anhalt", "SH"=>"Schleswig-Holstein", "TH"=>"Thueringen");

    if($reverse){
      $array=array_flip($array);
      if(isset($array[$code])) return $array[$code];      
    }
    $code=strtoupper($code);
    if(isset($array[$code])) return $array[$code];

  }

  /**
   * Country Codes
   **/
 public static function ccode($code,$reverse=FALSE){
    $array=array('AF' => 'Afghanistan', 'AX' => 'Aland Islands', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa', 'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla', 'AQ' => 'Antarctica', 'AG' => 'Antigua And Barbuda', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BA' => 'Bosnia And Herzegovina', 'BW' => 'Botswana', 'BV' => 'Bouvet Island', 'BR' => 'Brazil', 'IO' => 'British Indian Ocean Territory', 'BN' => 'Brunei Darussalam', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CV' => 'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CX' => 'Christmas Island', 'CC' => 'Cocos (Keeling) Islands', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CG' => 'Congo', 'CD' => 'Congo, Democratic Republic', 'CK' => 'Cook Islands', 'CR' => 'Costa Rica', 'CI' => 'Cote D\'Ivoire', 'HR' => 'Croatia', 'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands (Malvinas)', 'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'GF' => 'French Guiana', 'PF' => 'French Polynesia', 'TF' => 'French Southern Territories', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GR' => 'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' => 'Guam', 'GT' => 'Guatemala', 'GG' => 'Guernsey', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HM' => 'Heard Island & Mcdonald Islands', 'VA' => 'Holy See (Vatican City State)', 'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran, Islamic Republic Of', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IM' => 'Isle Of Man', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JE' => 'Jersey', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'KR' => 'Korea', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Lao People\'s Democratic Republic', 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libyan Arab Jamahiriya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macao', 'MK' => 'Macedonia', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MU' => 'Mauritius', 'YT' => 'Mayotte', 'MX' => 'Mexico', 'FM' => 'Micronesia, Federated States Of', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'ME' => 'Montenegro', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands', 'AN' => 'Netherlands Antilles', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria', 'NU' => 'Niue', 'NF' => 'Norfolk Island', 'MP' => 'Northern Mariana Islands', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau', 'PS' => 'Palestinian Territory, Occupied', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn', 'PL' => 'Poland', 'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'RE' => 'Reunion', 'RO' => 'Romania', 'RU' => 'Russian Federation', 'RW' => 'Rwanda', 'BL' => 'Saint Barthelemy', 'SH' => 'Saint Helena', 'KN' => 'Saint Kitts And Nevis', 'LC' => 'Saint Lucia', 'MF' => 'Saint Martin', 'PM' => 'Saint Pierre And Miquelon', 'VC' => 'Saint Vincent And Grenadines', 'WS' => 'Samoa', 'SM' => 'San Marino', 'ST' => 'Sao Tome And Principe', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovakia', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'GS' => 'South Georgia And Sandwich Isl.', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard And Jan Mayen', 'SZ' => 'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syrian Arab Republic', 'TW' => 'Taiwan', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TL' => 'Timor-Leste', 'TG' => 'Togo', 'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad And Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TC' => 'Turks And Caicos Islands', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 'US' => 'United States', 'UM' => 'United States Outlying Islands', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VE' => 'Venezuela', 'VN' => 'Viet Nam', 'VG' => 'Virgin Islands, British', 'VI' => 'Virgin Islands, U.S.', 'WF' => 'Wallis And Futuna', 'EH' => 'Western Sahara', 'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe');
    if($reverse){
      $array=array_flip($array);
      if(isset($array[$code])) return $array[$code];      
    }
    $code=strtoupper($code);
    if(isset($array[$code])) return $array[$code];
  }  
  /**
   * Current currency
   **/
 public static function currency($code="",$amount=""){
    $array = array('AUD' => array('label'=>'Australian Dollar','format' => '$ %s'),'CAD' => array('label' => 'Canadian Dollar','format' => '$ %s'),'EUR' => array('label' => 'Euro','format' => '€ %s'),'GBP' => array('label' => 'Pound Sterling','format' => '£ %s'),'JPY' => array('label' => 'Japanese Yen','format' => '¥ %s'),'USD' => array('label' => 'U.S. Dollar','format' => '$ %s'),'NZD' => array('label' => 'N.Z. Dollar','format' => '$ %s'),'CHF' => array('label' => 'Swiss Franc','format' => '%s Fr'),'HKD' => array('label' => 'Hong Kong Dollar','format' => '$ %s'),'SGD' => array('label' => 'Singapore Dollar','format' => '$ %s'),'SEK' => array('label' => 'Swedish Krona','format' => '%s kr'),'DKK' => array('label' => 'Danish Krone','format' => '%s kr'),'PLN' => array('label' => 'Polish Zloty','format' => '%s zł'),'NOK' => array('label' => 'Norwegian Krone','format' => '%s kr'),'HUF' => array('label' => 'Hungarian Forint','format' => '%s Ft'),'CZK' => array('label' => 'Czech Koruna','format' => '%s Kč'),'ILS' => array('label' => 'Israeli New Sheqel','format' => '₪ %s'),'MXN' => array('label' => 'Mexican Peso','format' => '$ %s'),'BRL' => array('label' => 'Brazilian Real','format' => 'R$ %s'),'MYR' => array('label' => 'Malaysian Ringgit','format' => 'RM %s'),'PHP' => array('label' => 'Philippine Peso','format' => '₱ %s'),'TWD' => array('label' => 'New Taiwan Dollar','format' => 'NT$ %s'),'THB' => array('label' => 'Thai Baht','format' => '฿ %s'),'TRY' => array('label' => 'Turkish Lira','format' => 'TRY %s'));
    if(empty($code)) return $array;
    
    $code=strtoupper($code);
    if(isset($array[$code])) return sprintf($array[$code]["format"],$amount);
  } 
/**
* Create download buttons   
* @returns string   
**/
public static function btndownload($buttOne, $buttTwo, $id){
  $ret ='';
  if(!$buttOne && !$buttTwo){
    return '';
  }elseif($buttOne && $buttTwo){ 
    $btwidth = '48';
  }elseif($buttOne xor $buttTwo){
    $btwidth = '100';
  }

  $vita = '<a download="doc-site-vita.pdf" href="'.Main::config('url').UPLOAD_PATH_LEBENS.$id.'/'.$buttOne.'" class="btn btn-outline-primary btn-sm btn-pill mr-2" style="width: '.$btwidth.'%;border-radius: 15px;height: 25px;"><i class="fa fa-download"></i> Download Vita</a>';
  $weiters = '<a download="doc-site-document.pdf" href="'.Main::config('url').UPLOAD_PATH_OPTS.$id.'/'.$buttTwo.'" class="btn btn-outline-primary btn-sm btn-pill" style="width: '.$btwidth.'%;border-radius: 15px;height: 25px;"><i class="fa fa-download"></i> Download Weitere Dokumente</a>';

  if($buttOne){
    $ret .= $vita;
  }
  if($buttTwo){
    $ret .= $weiters;
  }
  return $ret;
}  
/**
* Convert date to DB date
* @returns string
**/
public static function dbdate($timestamp){
  $dbdate = explode('-', $timestamp);  
  return $dbdate[2]."-".$dbdate[1]."-".$dbdate[0];
} 

/**
* Convert date to DB date
* @returns string
**/
public static function unidate($timestamp){
 return date('d-m-Y', strtotime($timestamp)); 
}
/**
* Convert timestamp to nice date
* @returns string
**/
public static function nice_date($timestamp, $today = FALSE){
  if(!$timestamp) return '--';
  if($today){
    if (substr($timestamp, 0, 10) === date('Y-m-d')) { 
      return e('Heute'); 
    }else{
      $nicedate = date('F j, Y', strtotime($timestamp)); 
      return $nicedate;
    }
  }
  $nicedate = date('F j, Y', strtotime($timestamp)); 
  return $nicedate;
}  
/**
* Convert timestamp to numeric date
* @returns string
**/
public static function nice_datum($timestamp){
  $nicedate = date('d-m-Y  H:i', strtotime($timestamp));  
  return $nicedate;
} 

/**
* Get coordinates from address
* @returns string
**/
public static function get_lat_long($address,  $region){
  $geocode = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($address)."&region=" . $region . "&key=".GOOGLEKY;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $geocode);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  $response = curl_exec($ch);
  if (curl_errno($ch)) {
    return curl_error($ch);
  }
  curl_close($ch);
  $output = json_decode($response);
  $dataarray = get_object_vars($output);
  if ($dataarray['status'] != 'ZERO_RESULTS' && $dataarray['status'] != 'INVALID_REQUEST') {
      if (isset($dataarray['results'][0]->geometry->location->lat)) {
         $address = str_replace(",",".", $dataarray['results'][0]->geometry->location->lat).", ".str_replace(",",".", $dataarray['results'][0]->geometry->location->lng);
      } else {
          $address =  '2.37287, 0.00016'; //not founded  $dataarray['error_message']
      }
  } else {
      $address = '2.37287, 0.00016'; //not founded
  }
  return $address;
} 

/**
* Get address from coordinates
* @returns string
**/
public function geoLocationAddress($lat, $long){
  $geocode = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&key=".GOOGLEKY;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $geocode);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  $response = curl_exec($ch);
  curl_close($ch);
  $output = json_decode($response);
  $dataarray = get_object_vars($output);
  if ($dataarray['status'] != 'ZERO_RESULTS' && $dataarray['status'] != 'INVALID_REQUEST') {
      if (isset($dataarray['results'][0]->formatted_address)) {
          $address = $dataarray['results'][0]->formatted_address;
      } else {
          $address = FALSE; //not founded
      }
  } else {
      $address = FALSE; //not founded
  }
  return $address;
} 

 /**
  * Format Font
  **/  
 public static function fontf($fonts){
   return json_encode(explode("\n",str_replace("\r", "", $fonts)));
 }

 /**
 * Send debug code to the Javascript console
 */ 
  public static function myDebug($data) {
    if(is_array($data) || is_object($data)) {
      echo("<script>console.log('arr-PHP: ".json_encode($data)."');</script>");
    } else {
      echo("<script>console.log('PHP: $data');</script>");
    }
  }

  public static function logvisits() {
    if($ipaddress = self:: ip()){
    $iplogfile = 'includes/ip-address-mainsite.txt';
    $file = file_get_contents($iplogfile);

    $webpage = $_SERVER['SCRIPT_NAME'];
    $timestamp = date('d/m/Y h:i:s');
    $browser = $_SERVER['HTTP_USER_AGENT'];
    $fp = fopen($iplogfile, 'a+');
    fwrite($fp, '['.$timestamp.']: '.$ipaddress.' '.$browser. "\r\n");
    fclose($fp);
    }
    return true;
  }

}
?>