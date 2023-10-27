<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Pruf extends Model  {
  
  protected $allowed = array("prufMail", "newMail");

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;

    $this->accessLevel = $this->config['accessLevel']['open'];
    $this->text = [];
    

    Main::pagetitle('Überprüfung');
  }



  public function getPruf($params = false){
    if($params){
      if(!$params[0] || !in_array($params[0], $this->allowed)) {
        return false;
      }else{$actiune = $params[0];}
 
     if(is_string($actiune) &&  method_exists($this, $actiune)){
       return $this->{$actiune}( $params[1] );
     }
     return false;
    }else{
     return false;
    }
  }


  public function checkKundin($id){
    if($theKlient = $this->db->get("allusers", array("select"=>"crt, leveltype, userfstname, userlstname, usermail", "where"=>array("unique_key"=>$id, 'activated'=>0), "limit"=>1, "return_type"=>"single"))){
     return $theKlient;
    }
    return false;
  }



  public function prufMail($param){
    if($param){
      if($checkKundin = $this->checkKundin($param)){
        if($upd = $this->db->update("allusers", array('activated'=>1), array('crt'=>$checkKundin['crt']))){
          if( $checkKundin['leveltype'] == 3 ){$mailType = "confirm";}else{$mailType = "registerConfirm";}

          $emailDo = Main::emailCreate($mailType, array('username'=>$checkKundin['userfstname'].' '.$checkKundin['userlstname'], 'b1link'=>'https://doc-site.de/anmeldung', 'b2link'=>'https://doc-site.de/kontakt'));

          @$this->sendmail($checkKundin['usermail'], $emailDo['subject'], $emailDo['message'],  $emailDo['template']);

          return true;
        }
        return false;
      }
      return false;
    }else{
      return false;
     }
  }




  public function newMail($param){
    if($param){
      if($checkKundin = $this->checkKundin($param)){




        return $checkKundin;
      }
      return false;
    }else{
      return false;
     }
  }



  



}