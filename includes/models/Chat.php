<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Chat extends Model {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['kandidat'];

    $this->text = ["title" => e('Chat'), "pgname" => e('Chat'), "bottomPic"=>"/static/images/banners/nurse.png", "bottomBackground"=>"transparent"];


  }



  public function getChatsTalks($params = false){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");

      if(!$this->user = Main::user()){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"1 ".e("Fehler, versuche es erneut!"))));
      }


      if(!$userReceive = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('isdel'=>2, 'banned'=>2, 'unique_key'=>$params),'limit'=>1, 'return_type'=>'single'))){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      } 

      if(!$theChatList = $this->db->get("chats", array('select'=>'chatmsg, fromid, created', 'where'=>array('fromid'=>$this->user[0], 'toid'=>$userReceive['crt']), 'where-or-and'=>array('fromid'=>$userReceive['crt'], 'toid'=>$this->user[0]), 'limit'=>10, 'order_by'=>'crt DESC', 'return_type'=>'all'))){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Keine Nachrichten!"))));
      } 

      $chat=[];
      $currDate=false;
      foreach(array_reverse($theChatList)  as $key => $value){
        if($value['fromid'] == $this->user[0]) {$send = 1;}else{ $send=2;}
        $chat[] = array("sended"=>$send, "text"=>$value['chatmsg'], "texttime"=>Main::timeAgo($value['created']));  // "currDate"=>$value['created']
        if(Main::dateTomorow($value['created'], $currDate)){ $chat[$key]['currDate'] = Main::nice_date($value['created'], TRUE); }//else{$chat['currDate'] = '';}

        $currDate = Main::dateTomorow($value['created']);
      }
      return array("user_error"=>1, "user_reason"=>1, "msg"=>$chat);
      
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>"5 ".e("Fehler, versuche es erneut!"));
    }
  }




  




}