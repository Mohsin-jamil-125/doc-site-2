<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Adminkandidaten extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    if($usr = Main::user()){
      $this->users = $usr;
    }
    $this->accessLevel = $this->config['accessLevel']['admins'];

    $this->text = ["title" =>e("Kandidaten"), "pgname" => e("Kandidaten"), "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token'],
    "stoPP"=>["titel"=>e('Diesen Kandidaten löschen?'), "body"=>e('Hier bestätigen Sie die Löschung für diesen Kandidat'), "pbnt"=>e('Löschen')]];

  }


  public function doDeleteKandidate($params = ''){
    if($params){
      header("Content-Type: application/json; charset=UTF-8"); 

      if(!$this->user = Main::user()){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }

      $frm = explode('-', $params->doit);  /// '.$val['crt'].'-'.$inputs['ai_token'].'-'.$inputs['public_token'].'

      if(!$crt = Main::decrypt($frm[0])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }

      if(!$thisUser = $this->prufUser($frm[1])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if(!$user = $this->db->get("allusers", array('select'=>'leveltype, avatar', 'where'=>array('crt'=>$crt, 'isdel'=>2), 'limit'=>1, 'return_type'=>'single'))){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Fehler, versuche es erneut!').";adminkandidaten"))); 
      } 

      $pdo =  $this->db->getConnection(); 
        try {
          $pdo->beginTransaction();

          if($user['leveltype'] == 20){
            $userDelete = $this->db->delete("allusers", array('crt'=>$crt));
          }elseif($user['leveltype'] == 3){
           // $userUpd = $this->db->update("allusers", array('isdel'=>2), array('crt'=>$crt));
            $usersDelete = $this->db->delete("allusers", array('crt'=>$crt));
            
             if($user['avatar']){ 
              $targetDirAvatar = UPLOAD_PATH_AVATARS.$crt."/";
              @Main::empty_directory(ROOT."/".$targetDirAvatar);
             }

           // if( $stmt = $this->db->get('usrpreffs', array('select'=>'usrvideo', 'where' => array('usid'=>$crt), 'limit' => 1, 'return_type' => 'single'))  ){ 
              $targetDir = UPLOAD_PATH_USRVIDEO.$crt."/";  
              @Main::empty_directory(ROOT."/".$targetDir);
          //  }


          //  if( $stmt = $this->db->get('lebens', array('select'=>'crt', 'where' => array('usid'=>$crt), 'limit'=>1, 'return_type'=>'single'))){ 
              $targetDirLebens = UPLOAD_PATH_LEBENS.$crt."/";  
              @Main::empty_directory(ROOT."/".$targetDirLebens);
           // } 


           // if( $stmt = $this->db->get('optionals', array('select'=>'crt', 'where' => array('usid'=>$crt), 'limit'=>1, 'return_type'=>'single'))){ 
              $targetDirOpts = UPLOAD_PATH_OPTS.$crt."/";  
              @Main::empty_directory(ROOT."/".$targetDirOpts);
          //  } 

            $delOptsextras = $this->db->delete("optextra", array("usid"=>$crt));

          }else{

          }

          $pdo->commit();
        } catch (\PDOException $e) {
          $pdo->rollBack();
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Fehler, versuche es erneut!').";adminkandidaten")));  
        }

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg!').";adminkandidaten")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
    }
  }

  


}