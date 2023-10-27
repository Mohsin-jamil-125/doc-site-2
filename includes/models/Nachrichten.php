<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Nachrichten extends Model {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];

    $this->text = ["title" => e('Posteingang'), "pgname" => e('Posteingang'), "bottomPic"=>"/static/images/banners/nurse.png", "bottomBackground"=>"transparent",
  "mdl"=>["shows"=>"", "displays"=>"", "titel"=>'doc-site', "untertitle"=>e('Sie haben derzeit keine E-Mails!'),
  "onclick"=>"data-dismiss=\"modal\""]];
  }


  public function getTheMails($params = false){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");

      if(!$this->users = Main::user()){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"1 ".e("Fehler, versuche es erneut!"))));
      }

      if($params == "getAll"){

        if(!$theMailList = $this->db->get("mailserver", array('select'=>'crt, usid, tosid, mailsubject, mailstar, mailstat,  modified', 
          //'where'=>array('usid'=>$this->users[0], 'usidview'=>$this->users[0]), 
          'where'=>array('tosid'=>$this->users[0], 'tosidview'=>$this->users[0]), 'order_by'=>'crt DESC', 'return_type'=>'all'))){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Keine Emails!"))));
        }
        
      }elseif($params == "delet"){

        if(!$theMailList = $this->db->get("mailserver", array('select'=>'crt, usid, tosid, mailsubject, mailstar, mailstat,  modified', 
          'where'=>array('usid'=>$this->users[0], 'usidview'=>0), 
          'where-or-and'=>array('tosid'=>$this->users[0], 'tosidview'=>0), 'order_by'=>'crt DESC', 'return_type'=>'all'))){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Keine gelöschten E-Mails"))));
        }
      }elseif($params == "importt"){

        if(!$theMailList = $this->db->get("mailserver", array('select'=>'crt, usid, tosid, mailsubject, mailstar, mailstat,  modified', 
          'where'=>array('usid'=>$this->users[0], 'usidview'=>$this->users[0], "mailstar"=>1), 
          'where-or-and'=>array('tosid'=>$this->users[0], 'tosidview'=>$this->users[0], "mailstar"=>1), 'order_by'=>'crt DESC', 'return_type'=>'all'))){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Keine Emails!"))));
        }

      }elseif($params == "seendsd"){
        if(!$theMailList = $this->db->get("mailserver", array('select'=>'crt, usid, tosid, mailsubject, mailstar, mailstat,  modified', 
          'where'=>array('usid'=>$this->users[0], 'usidview'=>$this->users[0]), 'order_by'=>'crt DESC', 'return_type'=>'all'))){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Keine Emails!"))));
        }
      }elseif($params == "wurfen"){
        if(!$theMailList = $this->db->get("mailentwufe", array('select'=>'crt, usid, mailsubject, mailmess, mailstar, modified', 
          'where'=>array('usid'=>$this->users[0]), 'order_by'=>'crt DESC', 'return_type'=>'all'))){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Keine Emails!"))));
        }
      }

      foreach($theMailList as $key => $val){
        if($params == "getAll" && $val['tosid'] == $this->users[0]){
          if($us = $this->db->get("allusers", array("select"=>"allusers.userfstname, allusers.userlstname, usrpreffs.typeprivat", "join"=>array(
            array("type"=>"join", "table"=>"usrpreffs", "on"=>array("usrpreffs.usid"=>"allusers.crt"))),
            "where"=>array("allusers.crt"=>$val['usid'], "allusers.isdel"=>2), "limit"=>1, "return_type"=>"single"))){

            $theMailList[$key]['fullname'] = Main::userProtect($us['userfstname'], $us['userlstname'], $us['typeprivat']);
          }
        }else{

          if($us = $this->db->get("allusers", array("select"=>"allusers.userfstname, allusers.userlstname, usrpreffs.typeprivat", "join"=>array(
            array("type"=>"join", "table"=>"usrpreffs", "on"=>array("usrpreffs.usid"=>"allusers.crt"))),
            "where"=>array("allusers.crt"=>$val['tosid'], "allusers.isdel"=>2), "limit"=>1, "return_type"=>"single"))){

            $theMailList[$key]['fullname'] = Main::userProtect($us['userfstname'], $us['userlstname'], $us['typeprivat']);
          }

        }
      }



      $theMailDels = $this->db->get("mailserver", array('select'=>'crt', 
      'where'=>array('usid'=>$this->users[0], 'usidview'=>0), 
      'where-or-and'=>array('tosid'=>$this->users[0], 'tosidview'=>0), 'return_type'=>'count'));

      $theMailList['gelost'] = $theMailDels ? $theMailDels : "";

      return array("user_error"=>1, "user_reason"=>1, "msg"=>$theMailList);


      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>"5 ".e("Fehler, versuche es erneut!"));
      }
  }


  public function delTheMails($params = false){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");

        if(!$this->users = Main::user()){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        }

        if($params->diid == "wurfen"){
          $delLogins = $this->db->delete("mailentwufe", array('usid' => $this->users[0]));
          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg!"))));
        }elseif($params->diid == "delet"){
          $theGelosgh = $this->db->update("mailserver", array("tosidview"=>$this->users[0]), array('crt'=>$params->doit));
          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg!"))));
        }

        if(!$mProp = $this->doMailsProperty($this->users[0], $params->doit)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        }

        $theMailList = $this->db->update("mailserver", array($mProp[0]=>0), array('crt'=>$params->doit));

        if($mProp[1] == 2){
          if( $x = Main::cookie("meingang")){
            $x = json_decode( $x, TRUE);
            $nrUnreads = $x['email'] >= 1 ? ($x['email']-1) : "0";
            $unreads = json_encode(array("email"=>$nrUnreads, "chats"=>$x['chats']));
          }else{
            $unreads = json_encode(array("email"=>"0", "chats"=>"0"));
          }
          Main::cookie("meingang", $unreads, FALSE);
        }
      
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg!"))));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
      }
  }


  





}
