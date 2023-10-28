<?php
namespace App\includes\middlewares;
use App\includes\library\phpjwt\JWT;
use App\includes\library\phpjwt\JWK;
use App\includes\Main;
use App\includes\Tools;


class Middleware{
  protected $config = [], $db;
  protected $user;
  protected $userauth;
  protected $id, $email, $membership, $expires, $firstname, $lastname, $password, $salz, $auth_key, $unique_key, $userverified, $userbanned, $activated;
  public $logged, $loggedLevel;

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
  }
 
  public function checkAnmeldung(){ 
    if($info=Main::user()){
      if($user = $this->db->get("allusers", array('select'=>'leveltype', 'where'=>array('crt'=>$info[0], 'isdel'=>2, 'unique_key'=>$info[2]), 'limit'=>1, 'return_type'=>'single'))){								
        $redir = Main::convertLevelLink($user['leveltype']);
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', $user['leveltype'], FILE_APPEND);
        Main::redirect($redir);
        die();
      }
    }
    return FALSE;
  }

  public function usersRegister($allProfile, $socials = FALSE){ 
    if(!$this->emailExists($allProfile['emailVerified'], $socials)){ 
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('E-Mail bereits registriert oder nicht korrekt!')))); /// am incercat Login dar emailul exista!!!
    }

    $ps = !$socials ? Main::passEncode($this->email) : Main::passEncode($allProfile['password']);
    $auth_key=Main::encode($this->config["security"].Main::strrand());
    $unique=Main::strrand(20);

    $generate_username = !$socials ? Main::generate_username($allProfile['firstName']." ".$allProfile['lastName']) : Main::randomString(10);

    $ip = Main::ip();
    $registeredIp = $ip?ip2long($ip):0;


    $pdo =  $this->db->getConnection();
      try {
        $pdo->beginTransaction();
        $frm = []; 
        $frm["crt"] = $this->db->getLastInsertId('allusers') + 1;
        $frm["leveltype"] = $this->config['accessLevel']['register'];
        $frm["usernames"] = $generate_username;
        $frm["userfstname"] = $allProfile['firstName'];
        $frm["userlstname"] = $allProfile['lastName'];   /// membership
        $frm["usermail"] = $this->email;
        $frm["userphone"] = $allProfile['phone'];
        $frm["userpass"] = $ps["p"];
        $frm["usersalt"] = $ps["s"];
        $frm["auth_key"] = $auth_key;
        $frm["unique_key"] = $unique;
        $frm["uip"] = $registeredIp;


        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "leveltype - ". $frm["leveltype"]."\n", FILE_APPEND);
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "username - ".$frm["usernames"]."\n", FILE_APPEND);
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "firstname - ".$frm["userfstname"]."\n", FILE_APPEND);
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "lastname - ".$frm["userlstname"]."\n", FILE_APPEND);
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "usermail - ".$frm["usermail"]."\n", FILE_APPEND);
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "userPhone - ".$frm["userphone"]."\n", FILE_APPEND);
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "userPass - ".$frm["userpass"]."\n", FILE_APPEND);
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "usersalt- ".$frm["usersalt"]."\n", FILE_APPEND);
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "auth_key - ".$frm["auth_key"]."\n", FILE_APPEND);
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "unique_key - ".$frm["unique_key"]."\n", FILE_APPEND);
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "uip - ".$frm["uip"]."\n", FILE_APPEND);
        


        $insertUser = $this->db->insert("allusers", $frm);
       /* $prefferencesUsers = $this->config['uspreffs'];
        $insertUserPreffs = $this->db->insert("usrpreffs", array("usid"=>$insertUser, "preffs"=>$prefferencesUsers)); */

        if(!$socials) {
          $allProfile = Main::unsetarray($allProfile, array("identifier", "photoURL", "firstName", "lastName", "gender", "email", "emailVerified", "phone", "country",  "city", "zip"));
          $allProfile['usid'] = $insertUser;
          $insertUserSocial = $this->db->insert("allsocials", $allProfile); 
        }

        if(!array_key_exists("bewerbt", $allProfile)){
          $this->id = $insertUser;
          $this->auth_key = $frm["auth_key"];
          $this->unique_key = $frm["unique_key"]; 
          $this->membership = "register";
          $this->loggedLevel = $frm["leveltype"];
          $this->password = $frm["userpass"];
        }

        $pdo->commit();
      } catch (\PDOException $e) {
        $pdo->rollBack();
        die($e->getMessage());
      }

      if(!array_key_exists("bewerbt", $allProfile)){
        $uIn = $this->setUserIN();

        return $uIn ? ['uniq'=>$frm["unique_key"], 'level'=>$this->loggedLevel] : false;   /// 
      }else{
        return $insertUser ? $insertUser : false;
      }
  }

  public function emailExists($mail, $socials = FALSE){  // traditional Register =>$socials = TRUE
      if(!$emailVerified = Main::email($mail)){
        if(!$socials){
          $this->email = Main::randomString(10);
          return true;
        }
        return false;
      }
     
      $this->email = $emailVerified;
      if(!$user = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('isdel'=>2, 'usermail'=>$this->email),'limit'=>1, 'return_type'=>'single'))){
        return true;
      } 
      return false;
  }


  public function altAdminRegister($allProfile){   
    if(!$this->emailExists($allProfile['nwadmemail'], TRUE)){ 
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('E-Mail bereits registriert oder nicht korrekt!'))));
    } 

    

    $ps = Main::passEncode($allProfile['nwadmepasswd']);
    $ip = Main::ip();
    $registeredIp = $ip?ip2long($ip):0;
    $auth_key=Main::encode($this->config["security"].Main::strrand());
    
    $phone = $allProfile['addphcode']."-#@@#-".$allProfile['nwadmphone'];  
    $generate_username = Main::generate_username($allProfile['admname']." ".$allProfile['lstadmname']);
    $unique=Main::strrand(20); 

    $pdo =  $this->db->getConnection();
      try {
        $pdo->beginTransaction();
        $frma = []; 
        $frma["usernames"] = $generate_username;
        $frma["userfstname"] = $allProfile['admname'];
        $frma["userlstname"] = $allProfile['lstadmname'];   
        $frma["usermail"] = $allProfile['nwadmemail'];
        $frma["userphone"] = $phone;
        
        $frma["admin"] = 2;
        $frma["membership"] = "admin";  
        $frma["leveltype"] = 1;
        
        $frma["userpass"] = $ps["p"];
        $frma["usersalt"] = $ps["s"];
        $frma["unique_key"] = $unique;
        $frma["auth_key"] = $auth_key;
        $frma["activated"] = $allProfile['isihnaktiv'];
        $frma["uip"] = $registeredIp;

        $insertUser = $this->db->insert("allusers", $frma);

        $insertRoles = $this->db->insert("sa_roles", array("usid"=>$insertUser, "iskontakt"=>$allProfile['alskontactsprechner'], "roles"=>$allProfile['insertRoles'])); 
        
        $prefferencesUsers = $this->config['uspreffs'];
        $insertUserPreffs = $this->db->insert("usrpreffs", array("usid"=>$insertUser, "preffs"=>$prefferencesUsers));

        $pdo->commit();
      } catch (\PDOException $e) {
        $pdo->rollBack();
        die($e->getMessage());
      }

    return $insertUser ? $insertUser : false;
  }

  public function altAdminEdit($allProfile){   
    if(!$this->butEmailExists($allProfile['nwadmemail'], $allProfile['token_ai'])){ 
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('E-Mail bereits registriert oder nicht korrekt!'))));
    } 

    if(array_key_exists("nwadmepasswd", $allProfile)){  
      $ps = Main::passEncode($allProfile['nwadmepasswd']);
      $auth_key=Main::encode($this->config["security"].Main::strrand());
      $unique=Main::strrand(20); 
    }else{
      $ps = FALSE;
      $auth_key = FALSE;
      $unique=FALSE;
    }
    
    $ip = Main::ip();
    $registeredIp = $ip?ip2long($ip):0;

    $phone = $allProfile['addphcode']."-#@@#-".$allProfile['nwadmphone'];  

    $pdo =  $this->db->getConnection();
      try {
        $pdo->beginTransaction();
        $frma = []; 
        $frma["userfstname"] = $allProfile['admname'];
        $frma["userlstname"] = $allProfile['lstadmname'];   
        $frma["usermail"] = $allProfile['nwadmemail'];
        $frma["userphone"] = $phone;

       if( $ps) {
         $frma["userpass"] =  $ps["p"];
         $frma["usersalt"] = $ps["s"];
         $frma["unique_key"] = $unique;
         $frma["auth_key"] = $auth_key;
       }
       
        $frma["activated"] = $allProfile['isihnaktiv'];
        $frma["uip"] = $registeredIp;

        $updateUser = $this->db->update("allusers", $frma, array("crt"=>$allProfile['token_ai'], "isdel"=>2));

        $insertRoles = $this->db->update("sa_roles", array("iskontakt"=>$allProfile['alskontactsprechner'], "roles"=>$allProfile['insertRoles']), array("usid"=>$allProfile['token_ai']));  

        $pdo->commit();
      } catch (\PDOException $e) {
        $pdo->rollBack();
        die($e->getMessage());
      }

    return $updateUser ? true : false;
  }


  public function ansprechPartnerRegister($allProfile){ 
    if(!$this->ansprechEmailExists($allProfile['ansprechmail'])){ 
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('E-Mail bereits registriert oder nicht korrekt!'))));
    }

    if(!$this->emailExists($allProfile['ansprechmail'], TRUE)){ 
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('E-Mail bereits registriert oder nicht korrekt!'))));
    }

    
      $chPasswords = Main::comparePasswords($allProfile['ansppassword'], $allProfile['ansppasswordrep']);
      if($chPasswords['err'] == 1){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$chPasswords['msg'])));
      }

      $ps = Main::passEncode($allProfile['ansppassword']);
      $ip = Main::ip();
      $registeredIp = $ip?ip2long($ip):0;
      $auth_key=Main::encode($this->config["security"].Main::strrand());

    
    $phone = $allProfile['jobphcode']."-#@@#-".$allProfile['jobphone'];  
    $generate_username = Main::generate_username($allProfile['jobvorname']." ".$allProfile['jobname']);
    $unique=Main::strrand(20); 

    $pdo =  $this->db->getConnection();
      try {
        $pdo->beginTransaction();
        $frma = []; 
        $frma["usid"] = $allProfile['usids'];
       // $frma["islogin"] = $isLogin ? $isLogin : 2;
      //  $frma["isedits"] = $isEdit ? $isEdit : 2;
        $frma["usernames"] = $generate_username;
        $frma["userfstname"] = $allProfile['jobvorname'];
        $frma["userlstname"] = $allProfile['jobname'];   
        $frma["usermail"] = $allProfile['ansprechmail'];
        $frma["userphone"] = $phone;
        $frma["unique_key"] = $unique;

        $insertAnsprech = $this->db->insert("ansprechpartners", $frma);

        $insertAnsprechpreffs = $this->db->insert("ansprechpreffs", array('usid'=>$insertAnsprech, 'herrjob'=>$allProfile['herrjob'], 'jobstreet'=>$allProfile['jobstreet'], 'jobstreetnr'=>$allProfile['jobstreetnr'], 'jobspostcode'=>$allProfile['jobspostcode'], 'jobsort'=>$allProfile['jobsort'], 'preffs'=>'{}'));

        $insertAnsprecher = $this->db->insert("ansprecher", array('usid'=>$insertAnsprech, 'einrichtung'=>$allProfile['einrichtungname'], 'typefrm'=>$allProfile['chooseeinrichtung'], 'positionjob'=>$allProfile['jobposition']));

        //  $frma = Main::unsetarray($frma, array("usid")); // "islogin", "isedits"
          unset($frma['usid']);
          array_values($frma);

          $frma["ansprech"] = $insertAnsprech;
          $frma["membership"] = "firma";
          $frma["leveltype"] = 3;
          $frma["uip"] = $registeredIp;
          $frma["userpass"] = $ps["p"];
          $frma["usersalt"] = $ps["s"];
          $frma["auth_key"] = $auth_key;
          $frma["uip"] = $registeredIp;

          $insertUser = $this->db->insert("allusers", $frma);

        $pdo->commit();
      } catch (\PDOException $e) {
        $pdo->rollBack();
       // die($e->getMessage());
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$e->getMessage())));
      }

    return $insertAnsprech ? $unique : false;
  }


  public function ansprechPartnerEdit($allProfile){ 
    if(!$this->ansprechEmailExists($allProfile['ansprechmail'], $allProfile['ansprechID'])){ 
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('E-Mail bereits registriert oder nicht korrekt!')." a")));
    }

    // if(!$this->emailExists($allProfile['ansprechmail'], TRUE)){ 
    //   return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('E-Mail bereits registriert oder nicht korrekt!')." b")));
    // }  ansprechID 0 crt

    if($user = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('isdel'=>2, 'usermail'=>$allProfile['ansprechmail']), 'where-not'=>array('ansprech'=>$allProfile['ansprechID']), 'limit'=>1, 'return_type'=>'single'))){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('E-Mail bereits registriert oder nicht korrekt!')." b")));
    } 

    if( $allProfile['ansppassword'] && $allProfile['ansppasswordrep']){
      $chPasswords = Main::comparePasswords($allProfile['ansppassword'], $allProfile['ansppasswordrep']);
      if($chPasswords['err'] == 1){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$chPasswords['msg'])));
      }
      $auth_key=Main::encode($this->config["security"].Main::strrand());
    }

    $ps = Main::passEncode($allProfile['ansppassword']);
    $ip = Main::ip();
    $registeredIp = $ip?ip2long($ip):0;
    $phone = $allProfile['jobphcode']."-#@@#-".$allProfile['jobphone'];  

    $pdo =  $this->db->getConnection();
      try {
        $pdo->beginTransaction();
        $frma = []; 
      //  $frma["islogin"] = 1; 
       // $frma["isedits"] = 2;
        $frma["userfstname"] = $allProfile['jobvorname'];
        $frma["userlstname"] = $allProfile['jobname'];   
        $frma["usermail"] = $allProfile['ansprechmail'];
        $frma["userphone"] = $phone;

        $insertAnsprech = $this->db->update("ansprechpartners", $frma, array("crt"=>$allProfile['ansprechID'])); // ok 30

        $insertAnsprecher = $this->db->update("ansprecher", array('einrichtung'=>$allProfile['einrichtungname'], 'typefrm'=>$allProfile['chooseeinrichtung'], 'positionjob'=>$allProfile['jobposition']), array("usid"=>$allProfile['ansprechID'])); // ok

         $insertAnsprechpreffs = $this->db->update("ansprechpreffs", array('herrjob'=>$allProfile['herrjob'], 'jobstreet'=>$allProfile['jobstreet'], 'jobstreetnr'=>$allProfile['jobstreetnr'], 'jobspostcode'=>$allProfile['jobspostcode'], 'jobsort'=>$allProfile['jobsort'], 'preffs'=>'{}'),  array("usid"=>$allProfile['ansprechID'])); //ok

         // $frma = Main::unsetarray($frma, array("usid", "islogin", "isedits"));
        // unset( $frma['usid']);
        // $frma = array_values($frma);

         // $frma["membership"] = "firma";
        //  $frma["leveltype"] = 3;
          $frma["uip"] = $registeredIp;
          if( $allProfile['ansppassword'] && $allProfile['ansppasswordrep']){
            $frma["userpass"] = $ps["p"];
            $frma["usersalt"] = $ps["s"];
            $frma["auth_key"] = $auth_key;
          }

         $insertUser = $this->db->update("allusers",  $frma,  array("ansprech"=>$allProfile['ansprechID']));


        $pdo->commit();
      } catch (\PDOException $e) {
        $pdo->rollBack();
        //die($e->getMessage());
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$e->getMessage())));
      }

    return $insertAnsprech ? $insertAnsprech : false;
  }


  public function ansprechEmailExists($mail, $edits = FALSE){ 
    if(!$emailVerified = Main::email($mail)){
      return false;
    }
    $this->email = $emailVerified;
    if(!$edits){
      if(!$user = $this->db->get("ansprechpartners", array('select'=>'crt', 'where'=>array('usermail'=>$this->email),'limit'=>1, 'return_type'=>'single'))){
        return true;
      } 
    }else{  
      if(!$user = $this->db->get("ansprechpartners", array('select'=>'crt', 'where'=>array('usermail'=>$this->email), 'where-not'=>array('crt'=>$edits),  'limit'=>1, 'return_type'=>'single'))){
        return true;
      } 
    }
    return false;
  }


  public function butEmailExists($mail, $id){ 
    if(!$emailVerified = Main::email($mail)){
      return false;
    }

    $this->email = $emailVerified;

    if(!$user = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('usermail'=>$this->email), 'where-not'=>array('crt'=>$id),  'limit'=>1, 'return_type'=>'single'))){
      return true;
    } 

    return false;
  }


  public function checkSocialAnmeldung($allProfile){ 
      if($user = $this->db->get("allsocials", array('select'=>'usid, gender, email, emailVerified', 'where'=>array('identifier'=>$allProfile['identifier']),'limit'=>1,'return_type'=>'single'))){
        if(!$userSocial = $this->checkUserById($user['usid'])){
          $usersRegister = $this->usersRegister($allProfile);
          return  array("action"=>1, "datas"=>$usersRegister);
        }else{
          $setUserIN = $this->setUserIN();
          return  array("action"=>2, "datas"=>$setUserIN);
        }
      }
      $usersRegister = $this->usersRegister($allProfile);
      return  array("action"=>1, "datas"=>$usersRegister); /// aici trebuie sa fac REGISTER
  }


  public function checkLevel($level, $depth = ''){
    if(!$this->userauth = Main::user()){
      if($level != 10){
        return $this->destroiy();
      } 
      return false;
    } else {
      if($level == 20 && ($this->userauth[4] != $level || $this->userauth[3] != 'register')){
        return $this->destroiy();
      }elseif($level == 3 && ($this->userauth[4] != $level || $this->userauth[3] != 'kandidat')){
        return $this->destroiy();
      }elseif($level == 2 && ($this->userauth[4] != $level || $this->userauth[3] != 'firma')){
        return $this->destroiy(); 
      }elseif($level == 1){
        if( ($this->userauth[4] != 1 && $this->userauth[4] != 2)  || (!$this->userauth[3] == 'admin' xor !$this->userauth[3] == 'firma')){
          return $this->destroiy();
        }
      }
    }
    return $this->check($this->userauth, $depth);
  }


  public function checkAvatar($id, $noch = FALSE){ 
    if($userAvatar = $this->db->get("allsocials", array('select'=>'photoURL', 'where'=>array('usid'=>$id),'limit'=>1,'return_type'=>'single'))){
      if($userAvatar['photoURL'] && (!filter_var($userAvatar['photoURL'], FILTER_VALIDATE_URL) === false))	{
        return $userAvatar['photoURL'];
      }
     return false;
    }
    return false;
  }

  public function checkSocialsEmail($id){ 
    if($userSoc = $this->db->get("allsocials", array('select'=>'crt, emailVerified', 'where'=>array('usid'=>$id),'limit'=>1,'return_type'=>'single'))){
      return $userSoc;
    }
    return false;
  }


  public function checkAccountProgress($id, $type){ 
    $total = array();
    if(!$type){
      return false;
    }
    if(!$progress = Main::config("progress")){
      return false;
    }
    $progress = json_decode($progress, true);
    if(!array_key_exists($type, $progress)){
      return false;
    }

    foreach($progress[$type] as $key => $val){
      $p = $this->db->get($key, array('where'=>array('usid'=>$id),'limit'=>1,'return_type'=>'single'));
      foreach($val as $key => $value){
        if($p && $value === 1 && (int)$p[$key] === 2) array_push($total, $key);
        if($p && $value === 2 && !$p[$key] || empty($p[$key])) array_push($total, $key);
      }
    }
    return $total;
  }


  public function getStatsPayimentFirms(){
    if(!$info = Main::user()){
      return false;
    }

    if(isset($info[5]) && isset($info[6])){
      $info[0] = $info[6];
    }

    $results = [];
    if($anszKondit = $this->db->get("rechnungen", array("select"=>"rechnungen.crt, rechnungen.planid, rechnungen.nrstellen, rechnungen.gebucht, rechnungen.rechstat, rechnungen.created, payplans.planname, payplans.planstellen, payplans.planprice, payplans.planweeks, payplans.planmonths, payplans.planlanger",
      "join"=>array(array("type"=>"join", "table"=>"payplans", "on"=>array("payplans.crt"=>"rechnungen.planid"))), 
      'where'=>array('rechnungen.usid'=>$info[0], 'rechnungen.verlng'=>0), 'where-not'=>array('rechstat'=>6), 'return_type'=>'all'))){
        foreach($anszKondit as $key => $val){
          if($val['gebucht']){
            $gebucht = unserialize($val['gebucht']);
            $manyGebucht  = count($gebucht);  // stiu cate s rezervate
            $nr = ($val['nrstellen']-$manyGebucht);
            if( $nr == 0 ){
              unset($anszKondit[$key]);
            }
          }
        }

        $results['plans'] =  array_values($anszKondit);
    }else{ return false;} 
    
    if($statAbvailabs = $this->getNrStellensAvailable($info[0])){
      $results['statAbvailabs'] = $statAbvailabs;
    }else{ return false;} 

    return $results;
  }


  public function getNrStellensAvailable($myId = FALSE){  // $myId = FALSE, $planID = FALSE, $stellnr = FALSE
    if(!$myId){
      if(!$info = Main::user()){
        return false;
      }else{
        $myId = $info[0];
      } 
    }

    if($anzRechnung = $this->db->get("rechnungen", array('select'=>'nrstellen, gebucht', 'where'=>array('usid'=>$myId, 'verlng'=>0), 'where-not'=>array('rechstat'=>6), 'return_type'=>'all'))){
     
      $available = 0; $gebuch = 0; $gebuchEach = [];

      foreach($anzRechnung as $key => $val){
        
        if($val['gebucht']){
          $gebucht = unserialize($val['gebucht']); //2
          $manyGebucht  = count($gebucht);  // stiu cate s rezervate  2
          $nr = ($val['nrstellen']-$manyGebucht); //3
          if( $nr >= 1  ){
            $available = $nr; // 3 
          }
          $gebuch += $manyGebucht; // 2
        }elseif(!$val['gebucht']){

           $available += $val['nrstellen']; // 3
        }
          

          $gebuchtA = unserialize($val['gebucht']);
          if (is_array($gebuchtA)) {
            $manyGebuchtA  = count($gebuchtA);  // stiu cate s rezervate
          }else{ $manyGebuchtA = 0; }

          $gebuchEach[$key] = ($val['nrstellen']-$manyGebuchtA);
      }



     foreach($gebuchEach as $k => $val){ if($val == 0){ unset($gebuchEach[$k]);} }

      return ["gebucht"=>$gebuch, "remains"=>($available-$gebuch), "available"=>$available, "availableEach"=>array_values($gebuchEach)];
    }
    return false;
  }


  public function getAnsprechers($id){
    if(!$info = Main::user()){
      return false;
    } 

    if($id && !empty($id)){
      if($ansprechpartner = $this->db->get("ansprechpartners", 
        array("select"=>"ansprechpartners.crt, ansprechpartners.avatar, ansprechpartners.usernames, ansprechpartners.userfstname, ansprechpartners.userlstname, ansprechpartners.usermail, ansprechpartners.userphone, ansprechpartners.unique_key, ansprechpartners.modified, ansprecher.einrichtung, ansprecher.typefrm, ansprecher.positionjob, ansprechpreffs.herrjob, ansprechpreffs.jobstreet, ansprechpreffs.jobstreetnr, ansprechpreffs.jobspostcode, ansprechpreffs.jobsort, ansprechpreffs.preffs", "join"=>array(
          array("type"=>"join", "table"=>"ansprecher", "on"=>array("ansprecher.usid"=>"ansprechpartners.crt")), 
          array("type"=>"join", "table"=>"ansprechpreffs", "on"=>array("ansprechpreffs.usid"=>"ansprechpartners.crt"))),
          'where'=>array('ansprechpartners.usid'=>$info[0], 'ansprechpartners.unique_key'=>$id), 'return_type'=>'single'))){							
          return $ansprechpartner;
        }
        Main::redirect("company");
        exit;
    }else{
      if($ansprechpartners = $this->db->get("ansprechpartners", array("select"=>"ansprechpartners.crt, ansprechpartners.avatar, ansprechpartners.usernames, ansprechpartners.userfstname, ansprechpartners.userlstname, ansprechpartners.usermail, ansprechpartners.userphone, ansprechpartners.unique_key, ansprechpartners.activated, ansprechpartners.modified, ansprecher.einrichtung, ansprecher.typefrm, ansprecher.positionjob", "join"=>array(array("type"=>"join", "table"=>"ansprecher", "on"=>array("ansprechpartners.crt"=>"ansprecher.usid"))), 'where'=>array('ansprechpartners.usid'=>$info[0]), 'order_by'=>'ansprechpartners.crt DESC', 'return_type'=>'all'))){	
        return $ansprechpartners;
      }
      return false;
    }
  return false;
  }

  public function getFavKandidaten($bewerbt){   
    if($info = Main::user()){
      if(!$bewerbt){

        $kandidates = $this->db->get("firmsliebste", 
                array("select"=>"firmsliebste.crt as lbst, allusers.crt, allusers.on_line, allusers.avatar, allusers.userfstname, allusers.userlstname, allusers.usermail, allusers.userphone, kandidate.isalowed, kandidate.choosejob, kandidate.positionjob, kandidate.subjectjob, usrpreffs.typeprivat, usrpreffs.titeljob, usrpreffs.herrjob, lebens.leben", "join"=>array(
                array("type"=>"join", "table"=>"allusers", "on"=>array("allusers.crt"=>"firmsliebste.kandidat")), 
                array("type"=>"join", "table"=>"kandidate", "on"=>array("kandidate.usid"=>"firmsliebste.kandidat")), 
                array("type"=>"join", "table"=>"usrpreffs", "on"=>array("usrpreffs.usid"=>"firmsliebste.kandidat")), 
                array("type"=>"left-join", "table"=>"lebens", "on"=>array("lebens.usid"=>"firmsliebste.kandidat")),
              ), 'where'=>array('firmsliebste.usid'=>$info[0]), 'return_type'=>'all'));

      }elseif($bewerbt){

        $kandidates = $this->db->get("bewerbeliste", 
                array("select"=>"bewerbeliste.crt as lbst, allusers.crt, allusers.on_line, allusers.avatar, allusers.userfstname, allusers.userlstname, allusers.usermail, allusers.userphone, kandidate.isalowed, kandidate.choosejob, kandidate.positionjob, kandidate.subjectjob, usrpreffs.typeprivat, usrpreffs.titeljob, usrpreffs.herrjob, lebens.leben", "join"=>array(
                array("type"=>"join", "table"=>"allusers", "on"=>array("allusers.crt"=>"bewerbeliste.usid")), 
                array("type"=>"join", "table"=>"kandidate", "on"=>array("kandidate.usid"=>"bewerbeliste.usid")), 
                array("type"=>"join", "table"=>"usrpreffs", "on"=>array("usrpreffs.usid"=>"bewerbeliste.usid")), 
                array("type"=>"left-join", "table"=>"lebens", "on"=>array("lebens.usid"=>"bewerbeliste.usid")),
              ), 'where'=>array('bewerbeliste.firma'=>$info[0]), 'return_type'=>'all'));

      }
      
    if(isset($kandidates) && is_array($kandidates)){
      foreach($kandidates as $key => $user){
        if(!$user['avatar'] || empty($user['avatar'])){
          if($avatar = $this->checkAvatar($user['crt'])){
            $kandidates[$key]['avatar'] = $avatar; 
          }else{
            $kandidates[$key]['avatar'] = Main::config('url').'/static/images/users/no-avatars/male-no-avatar.png';
          } 
        }else{
          $kandidates[$key]['avatar'] = Main::config('url').'/static/images/users/'.$user['crt'].'/'.$user['avatar'];
        }

        $kandidates[$key]['fullname'] = Main::userProtect($user['userfstname'], $user['userlstname'], $user['typeprivat']);
        if(array_key_exists('leben', $user) && !empty($user['leben'])){
          $kandidates[$key]['lebenname'] = str_replace(' ', '', $kandidates[$key]['fullname']);
          $kandidates[$key]['lebenslauf'] = Main::config('url').UPLOAD_PATH_LEBENS.$user['crt'].'/'.$user['leben']; 
        }else{
          $kandidates[$key]['lebenname'] = '';
          $kandidates[$key]['lebenslauf'] = '';
        }

        if(!$user['usermail'] || empty($user['usermail'])){
          $userSocialEmail = $this->checkSocialsEmail($user['crt']);
          if($userSocialEmail && $userSocialEmail['emailVerified']){
            $userEmail = $userSocialEmail['emailVerified']; 
          }else{
            $userEmail = '';
          }
        }else{
          $userEmail = $user['usermail'];
        }

        $kandidates[$key]['anzliebste'] = $user['lbst'];
        $kandidates[$key]['chatid'] = Main::encrypt($user['crt']);
        $kandidates[$key]['themail'] = $userEmail;
        $kandidates[$key]['thephone'] = $user['userphone'] ? '+'.str_replace('-#@@#-', '', $user['userphone']) : '';
        $kandidates[$key]['kid'] = "kandidatansehen/".Main::encrypt($user['crt']);

        $kandidates[$key]['titeljob'] =  $user['titeljob'] ? ucwords($user['titeljob']) : Main::returnValue("dropdowns", "anredes", $user['herrjob']);
        $kandidates[$key]['choosejobs'] = Main::returnValue("dropdowns", "berufsfeld", $user['choosejob']);
        $kandidates[$key]['positionjobs'] = Main::relreturnValue($user['choosejob'], "positionjob", $user['positionjob']);
        $kandidates[$key]['subjectjobs'] = Main::relreturnValue($user['choosejob'], "subjectjob", $user['subjectjob']);

        if(array_key_exists('leben', $kandidates[$key])) unset($kandidates[$key]['leben']);
        unset($kandidates[$key]['userfstname'], $kandidates[$key]['userlstname'], $kandidates[$key]['choosejob'], $kandidates[$key]['positionjob'], $kandidates[$key]['subjectjob']);  
      }
      return array('allkandidates' => $kandidates, 'allowedLogged'=>TRUE);
    }
    return false;
   }
   return $this->destroiy();
  }


  public function getOpenKandidaten($logged, $id){
    $leveltype = FALSE;
    $allowedLogged = FALSE;
    $info=[];
      if(!$info = Main::user()){
        $allowedLogged = FALSE;
      }else{
        if(($logged && array_key_exists('u', $logged)) && !empty($logged['u'])){
          $leveltype = $logged['u']['leveltype'];
          if($info && ($info[4] == 2) && ($leveltype == 2)){
            $allowedLogged = TRUE;
          }else{
            $allowedLogged = FALSE;
          }
        }
      }
 
      if($id && !empty($id)){
        $id = Main::decrypt($id);
        if(!$id || !$id >= 1){
          Main::redirect("");
          exit;
        }

        $kandidates = $this->db->get("allusers", 
                array("select"=>"allusers.crt, allusers.on_line, allusers.avatar, allusers.userfstname, allusers.userlstname, allusers.usermail, allusers.userphone, allusers.unique_key, allusers.modified,  kandidate.isalowed, kandidate.choosejob, kandidate.positionjob, kandidate.subjectjob, kandidate.arts, kandidate.vertrag, kandidate.suzatz, kandidate.landsjob, usrpreffs.typeprivat, usrpreffs.titeljob, usrpreffs.herrjob, usrpreffs.usrvideo, lebens.leben, optextra.optextra, optionals.opts", "join"=>array(
                array("type"=>"join", "table"=>"kandidate", "on"=>array("kandidate.usid"=>"allusers.crt")), 
                array("type"=>"join", "table"=>"usrpreffs", "on"=>array("usrpreffs.usid"=>"allusers.crt")), 
                array("type"=>"left-join", "table"=>"lebens", "on"=>array("lebens.usid"=>"allusers.crt")),
                array("type"=>"left-join", "table"=>"optextra", "on"=>array("optextra.usid"=>"allusers.crt")),
                array("type"=>"left-join", "table"=>"optionals", "on"=>array("optionals.usid"=>"allusers.crt")), 
              ), 'where'=>array('allusers.crt'=>$id, 'allusers.isdel'=>2), 'limit'=>1, 'return_type'=>'all'));
      }else{
        $kandidates = $this->db->get("allusers", 
                array("select"=>"allusers.crt, allusers.on_line, allusers.avatar, allusers.userfstname, allusers.userlstname, allusers.usermail, allusers.userphone, kandidate.isalowed, kandidate.choosejob, kandidate.positionjob, kandidate.subjectjob, kandidate.arts, kandidate.vertrag, kandidate.suzatz, usrpreffs.typeprivat, usrpreffs.titeljob, usrpreffs.herrjob, lebens.leben", "join"=>array(
                array("type"=>"join", "table"=>"kandidate", "on"=>array("kandidate.usid"=>"allusers.crt")), 
                array("type"=>"join", "table"=>"usrpreffs", "on"=>array("usrpreffs.usid"=>"allusers.crt")), 
                array("type"=>"left-join", "table"=>"lebens", "on"=>array("lebens.usid"=>"allusers.crt")),
              ), 'limit'=>8, 'return_type'=>'all'));
      }

      if($kandidates && !empty($kandidates)){
                foreach($kandidates as $key => $user){

                  if($info){                                       
                    if($lbs = $this->db->get('firmsliebste', array('select'=>'crt', 'where' => array('usid'=>$info[0], 'kandidat'=>$user['crt']), 'limit' => 1, 'return_type' => 'single'))){    
                      $kandidates[$key]['anzliebste'] = $lbs['crt'];
                    }else{
                      $kandidates[$key]['anzliebste'] = FALSE;
                    }
                  }  
                

                  if(!$user['avatar'] || empty($user['avatar'])){
                    if($avatar = $this->checkAvatar($user['crt'])){
                      $kandidates[$key]['avatar'] = $avatar; 
                    }else{
                      $kandidates[$key]['avatar'] = Main::config('url').'/static/images/users/no-avatars/male-no-avatar.png';
                    } 
                  }else{
                    $kandidates[$key]['avatar'] = Main::config('url').'/static/images/users/'.$user['crt'].'/'.$user['avatar'];
                  }

                  $kandidates[$key]['chatid'] = '';

                  if($leveltype == 3 && $info[4] == 3){
                    if($user['crt'] === $info[0]){
                      $allowedLogged = TRUE;
                    }else{
                      $allowedLogged = FALSE;
                    }
                  }elseif($leveltype && ($leveltype == 2) && ($info[4] == 2)){
                    $kandidates[$key]['chatid'] = Main::encrypt($user['crt']);
                  }elseif($leveltype && $leveltype == 1 && $info[4] == 1 && $info[3] == 'admin'){
                    $allowedLogged = TRUE;
                    $kandidates[$key]['chatid'] = Main::encrypt($user['crt']);
                  }

                  $kandidates[$key]['fullname'] = Main::userProtect($user['userfstname'], $user['userlstname'], $user['typeprivat']);
                  if($user['leben'] || trim($user['leben'])){
                    $kandidates[$key]['lebenname'] = $allowedLogged ? str_replace(' ', '', $kandidates[$key]['fullname']) : '';
                    $kandidates[$key]['lebenslauf'] = $allowedLogged ? Main::config('url').UPLOAD_PATH_LEBENS.$user['crt'].'/'.$user['leben'] : ''; 
                  }else{
                    $kandidates[$key]['lebenname'] = '';
                    $kandidates[$key]['lebenslauf'] = '';
                  }

                  if($id && $id >= 1){
                    if($user['opts'] || trim($user['opts'])){
                      $kandidates[$key]['optsname'] = $allowedLogged ? str_replace(' ', '', $kandidates[$key]['fullname']) : '';
                      $kandidates[$key]['lebeweoitnname'] = $allowedLogged ? Main::config('url').UPLOAD_PATH_OPTS.$user['crt'].'/'.$user['opts'] : 'javascript::void(0);return;'; 
                    }else{
                      $kandidates[$key]['optsname'] = '';
                      $kandidates[$key]['lebeweoitnname'] = 'javascript::void(0);return;';
                    }
                  }
                  

                  if(!$user['usermail'] || empty($user['usermail'])){
                    $userSocialEmail = $this->checkSocialsEmail($user['crt']);
                    if($userSocialEmail && $userSocialEmail['emailVerified']){
                      $userEmail = $userSocialEmail['emailVerified']; 
                    }else{
                      $userEmail = FALSE;
                    }
                  }else{
                    $userEmail = $user['usermail'];
                  }


                  $kandidates[$key]['themail'] = $allowedLogged && $userEmail ? $userEmail : '';

                  $kandidates[$key]['thephone'] = $allowedLogged && $user['userphone'] ? '+'.str_replace('-#@@#-', '', $user['userphone']) : '';
                  $kandidates[$key]['kid'] = $allowedLogged ? "kandidatansehen/".Main::encrypt($user['crt']) : '';
                  

                  $kandidates[$key]['titeljob'] =  $user['titeljob'] ? ucwords($user['titeljob']) : Main::returnValue("dropdowns", "anredes", $user['herrjob']);
                  $kandidates[$key]['choosejobs'] = Main::returnValue("dropdowns", "berufsfeld", $user['choosejob']);
                  $kandidates[$key]['positionjobs'] = Main::relreturnValue($user['choosejob'], "positionjob", $user['positionjob']);
                  $kandidates[$key]['subjectjobs'] = Main::relreturnValue($user['choosejob'], "subjectjob", $user['subjectjob']);

                  if(array_key_exists('leben', $kandidates[$key])) unset($kandidates[$key]['leben']);
                  unset($kandidates[$key]['userfstname'], $kandidates[$key]['userlstname'], $kandidates[$key]['choosejob'], $kandidates[$key]['positionjob'], $kandidates[$key]['subjectjob']);  
                }
          return array('allkandidates' => $kandidates, 'allowedLogged'=>$leveltype);

      }
   return false;
  }


  public function getFavoAnzeigen(){
    if($info = Main::user()){
      if($aallAnz = $this->db->get("kandidatliebste", array("select"=>"kandidatliebste.crt as lbrt, anzeigen.crt as art, anzeigen.ison, anzeigen.viewd, anzeigen.titel, anzeigen.berufsfeld, anzeigen.positionen, anzeigen.fachbereich, anzeigen.arts, anzeigen.filiale, anzeigen.starts, anzeigen.citycode, anzeigen.city, anzeigen.derbundes, anzeigen.derland, anzeigen.created, firmen.crt as ffrt, firmen.usid as ffid, firmen.frmavatar, firmen.frmname, bewerbeliste.crt as bwrt", "join"=>array(
        array("type"=>"join", "table"=>"anzeigen", "on"=>array("anzeigen.crt"=>"kandidatliebste.liebste")),
        array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.crt"=>"kandidatliebste.firma")),
        array("type"=>"left-join", "table"=>"bewerbeliste", "on"=>array("bewerbeliste.stellen"=>"anzeigen.crt"))
        ),'where'=>array('kandidatliebste.usid'=>$info[0]), 'order_by'=>'kandidatliebste.crt DESC', 'return_type'=>'all'))){	

        return $aallAnz;
      }
      return false;
    } 
   return $this->destroiy();
  }

  public function getOffenFirmaProfil($idd){
    if(!$info = Main::user()){
      return false;
    } 
    if($idd && !empty($idd)){
      $id = Main::decrypt($idd);
      if(!$id || !$id >= 1){
        Main::redirect("");
        exit;
      }

      if($firmaDetails = $this->db->get("allusers", array("select"=>"allusers.crt as alusr, allusers.avatar as usater,  allusers.userfstname, allusers.unique_key, allusers.userlstname,
        firmen.crt as ffrt, firmen.frmawall, firmen.frmavatar, firmen.frmname, firmen.frmbeschtitl, firmen.frmbeschreibb, firmen.ufrmlinkk, firmen.ufrmlinksocc, firmen.frmvideotitel, firmen.frmvideo, frmpreffs.anrede, frmpreffs.position, frmpreffs.telefon, frmpreffs.email, frmpreffs.hausnr,  frmpreffs.strasse,  frmpreffs.citycode,  frmpreffs.city,  frmpreffs.landd", "join"=>array(
          array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"allusers.crt")),
          array("type"=>"join", "table"=>"frmpreffs", "on"=>array("frmpreffs.usid"=>"firmen.usid"))
          ),'where'=>array('allusers.crt'=>$id, 'allusers.isdel' => 2), 'limit'=>1, 'return_type'=>'single'))){ 

            if($aallAnz = $this->db->get("anzeigen", array("select"=>"anzeigen.crt as art, anzeigen.ansprechs, anzeigen.titel, anzeigen.berufsfeld, anzeigen.positionen, anzeigen.fachbereich, anzeigen.arts,  anzeigen.filiale, anzeigen.starts, anzeigen.citycode, anzeigen.city, anzeigen.derbundes, anzeigen.derland, anzeigen.anzlogo, anzeigen.created, firmen.crt as ffrt, firmen.usid as ffid, firmen.frmawall, firmen.frmavatar, firmen.frmname, bewerbeliste.crt as bwrt", "join"=>array(
                array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"anzeigen.usid")),
                array("type"=>"left-join", "table"=>"bewerbeliste", "on"=>array("bewerbeliste.stellen"=>"anzeigen.crt"))
                ),'where'=>array('anzeigen.usid'=>$id), 'return_type'=>'all'))){	
                                              
                  foreach($aallAnz as $key => $val){
                    if($lbs = $this->db->get('kandidatliebste', array('select'=>'crt', 'where' => array('usid'=>$info[0], 'liebste'=>$val['art']), 'limit' => 1, 'return_type' => 'single'))){    
                      $aallAnz[$key]['anzliebste'] = $lbs['crt'];
                    }else{
                      $aallAnz[$key]['anzliebste'] = FALSE;
                    }
                  }  
            }
          $rch = array("firmDetails"=>$firmaDetails, "anzeigen"=>$aallAnz);
      }
     return $rch;
    }
    return false;
  }


  
  public function getOpenAnzeigen($idd){
    $info = FALSE;
    $info = Main::user();

    if($idd && !empty($idd)){
      $id = Main::decrypt($idd);
      if(!$id || !$id >= 1){
        Main::redirect("");
        exit;
      }


      if(!$this->db->doquery("UPDATE anzeigen SET viewd = viewd + 1 WHERE crt = ".$id)){
        Main::redirect("");
        exit;
      }


      
      if($anzz = $this->db->get("anzeigen", array("select"=>"anzeigen.crt as art, anzeigen.usid as ffid, anzeigen.ansprechs, anzeigen.titel, anzeigen.berufsfeld, anzeigen.positionen, anzeigen.fachbereich, anzeigen.arts, anzeigen.filiale, anzeigen.starts, anzeigen.citycode, anzeigen.city, anzeigen.derbundes, anzeigen.derland, anzeigen.anznr, anzeigen.anzstr, anzeigen.beschreibung, anzeigen.besherwartet, anzeigen.beshbieten, anzeigen.beshwirerw,  anzeigen.urlink, anzeigen.soclink, anzeigen.anzlogo, anzeigen.anzvideo, anzeigen.created, firmen.crt as ffrt, firmen.usid, firmen.frmawall, firmen.frmavatar, firmen.frmname, firmen.ufrmlinksocc, frmpreffs.email, bewerbeliste.crt as bwrt", "join"=>array(
        array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"anzeigen.usid")),
        array("type"=>"join", "table"=>"frmpreffs", "on"=>array("frmpreffs.usid"=>"firmen.usid")),
        array("type"=>"left-join", "table"=>"bewerbeliste", "on"=>array("bewerbeliste.stellen"=>"anzeigen.crt"))
        ),'where'=>array('anzeigen.crt'=>$id, 'anzeigen.ison'=>0), 'limit'=>1, 'return_type'=>'single'))){
          
          $anzz['eizellId'] = $idd;

          if($iStatus = $this->db->get("paystats", array('select'=>'datactivat', 'where'=>array('anzid'=>$anzz['art']), 'limit'=>1, 'return_type'=>'single'))){
            $anzz['activaeted'] = $iStatus['datactivat'];
          }

          if(!empty($anzz['ansprechs'])  && ($anspr = unserialize($anzz['ansprechs']))){  
            foreach($anspr as $val){
              $ansprechers[] = $this->db->get("ansprechpartners", 
                array("select"=>"ansprechpartners.crt, ansprechpartners.avatar, ansprechpartners.userfstname, ansprechpartners.userlstname, ansprechpartners.usermail, ansprechpartners.userphone, ansprechpartners.unique_key, ansprecher.positionjob, ansprechpreffs.herrjob, ansprechpreffs.jobstreet, ansprechpreffs.jobstreetnr, ansprechpreffs.jobspostcode, ansprechpreffs.jobsort, firmen.frmname", "join"=>array(
                array("type"=>"join", "table"=>"ansprecher", "on"=>array("ansprecher.usid"=>"ansprechpartners.crt")), 
                array("type"=>"join", "table"=>"ansprechpreffs", "on"=>array("ansprechpreffs.usid"=>"ansprechpartners.crt")), // herrjob 
                array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"ansprechpartners.usid"))
              ),'where'=>array('ansprechpartners.unique_key'=>$val), 'return_type'=>'single'));
            }
            $anzz['ansprechpartners'] = $ansprechers;  
          }else{
            $ansprechers[] = $this->db->get("allusers", array("select"=>"allusers.crt, allusers.avatar, allusers.unique_key, firmen.frmname, frmpreffs.anrede as herrjob, frmpreffs.vorname as userfstname, frmpreffs.name as userlstname, frmpreffs.position as positionjob, frmpreffs.telefon as userphone, frmpreffs.email as usermail, frmpreffs.hausnr as jobstreetnr, frmpreffs.strasse as jobstreet, frmpreffs.citycode as jobspostcode, frmpreffs.city as jobsort", "join"=>array(
                array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"allusers.crt")),
                array("type"=>"join", "table"=>"frmpreffs", "on"=>array("frmpreffs.usid"=>"allusers.crt"))
              ),
                'where'=>array('allusers.crt'=>$anzz['usid']), 'limit'=>1, 'return_type'=>'single'));
            $anzz['ansprechAdmin'] = "alias";  
            $anzz['ansprechpartners'] = $ansprechers;
          }	

          return $anzz;   
        }
        Main::redirect("");  
        exit;
    }else{

      if($aallAnz = $this->db->get("anzeigen", array("select"=>"anzeigen.crt as art, anzeigen.ansprechs, anzeigen.titel, anzeigen.berufsfeld, anzeigen.positionen, anzeigen.fachbereich, anzeigen.arts, anzeigen.filiale, anzeigen.starts, anzeigen.citycode, anzeigen.city, anzeigen.derbundes, anzeigen.derland, anzeigen.anzlogo, anzeigen.created, firmen.crt as ffrt, firmen.usid as ffid, firmen.frmawall, firmen.frmavatar, firmen.frmname, bewerbeliste.crt as bwrt", "join"=>array(
        array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"anzeigen.usid")),
        array("type"=>"left-join", "table"=>"bewerbeliste", "on"=>array("bewerbeliste.stellen"=>"anzeigen.crt"))
        ),'where'=>array('anzeigen.ison'=>0), 'return_type'=>'all'))){	
        if($info){                                       
          foreach($aallAnz as $key => $val){
            if($lbs = $this->db->get('kandidatliebste', array('select'=>'crt', 'where' => array('usid'=>$info[0], 'liebste'=>$val['art']), 'limit' => 1, 'return_type' => 'single'))){    
              $aallAnz[$key]['anzliebste'] = $lbs['crt'];
            }else{
              $aallAnz[$key]['anzliebste'] = FALSE;
            }
          }  
        }                                        
        return $aallAnz;
      }
      return false;
    }
  return false;
  }


  
  public function getMeineRecgnungens($id){
    if(!$info = Main::user()){
      return false;
    } 

    if($id && !empty($id)){
      $id = Main::decrypt($id);
      if(!$id || !$id >= 1){
        Main::redirect("company");
        exit;
      }

      if($info[3] == "admin" && $info[4] == 1) {
        $rch = $this->db->get("rechnungen", 
                array("select"=>"*",  "join"=>array(
                array("type"=>"join", "table"=>"payplans", "on"=>array("payplans.crt"=>"rechnungen.planid"))),
                'where'=>array('rechnungen.crt'=>$id), 'return_type'=>'single'));


        $firmen = $this->db->get("firmen", array('select'=>'frmawall, frmavatar, frmname, frmsteuer, frmregister, frmeinricht, intersartze, intersnurse, interspharma, frmbeschtitl, frmbeschreibb, ufrmlinkk, ufrmlinksocc, videotube, frmvideotitel, frmvideo, created', 'where'=>array('usid'=>$rch['usid']),'limit'=>1,'return_type'=>'single'));

        $firmapreffs = $this->db->get("frmpreffs", array('select'=>'anrede, vorname, name, position, telefon, email, hausnr, strasse, citycode, city, landd, inhaber, bankname, swift, iban, telpref, chatpref, mailpref, preffs', 'where'=>array('usid'=>$rch['usid']),'limit'=>1,'return_type'=>'single'));

        $userul = $this->db->get("allusers", array('select'=>'unique_key', 'where'=>array('crt'=>$rch['usid']),'limit'=>1,'return_type'=>'single'));


        $rch = array_merge($rch, $firmapreffs, $firmen, $userul);

      }elseif($info[3] == "firma" && $info[4] == 2){ 
        //$rch = $this->db->get("rechnungen", array('where'=>array('crt'=>$id, 'usid'=>$info[0]), 'return_type'=>'single'));

        $rch = $this->db->get("rechnungen", 
                array("join"=>array(
                array("type"=>"join", "table"=>"payplans", "on"=>array("payplans.crt"=>"rechnungen.planid"))),
                'where'=>array('rechnungen.crt'=>$id, 'rechnungen.usid'=>$info[0]), 'limit'=>1, 'return_type'=>'single'));
      }

      if($rch){		
          return $rch;
        }
      Main::redirect("company");
      exit;
    }else{
      if($info[3] == "admin" && $info[4] == 1) {

        $allRch = $this->db->get("rechnungen", 
      array("select"=>"rechnungen.crt, rechnungen.rechnunr, rechnungen.planid, rechnungen.verlng, rechnungen.planname, rechnungen.rechval, rechnungen.rabat, rechnungen.rabatval, rechnungen.rechstat, payments.paymetod, payments.timepay, payments.gultigbis, payments.sichtbarbis, payments.created, firmen.frmname",  "join"=>array(
      array("type"=>"join", "table"=>"payments", "on"=>array("payments.rechnnr"=>"rechnungen.crt")),
      array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"rechnungen.usid"))
      ), 'order_by'=>'rechnungen.crt DESC', 'return_type'=>'all'));

      }elseif($info[3] == "firma" && $info[4] == 2){ 
        $allRch = $this->db->get("rechnungen", 
      array("select"=>"rechnungen.crt, rechnungen.rechnunr, rechnungen.planid, rechnungen.verlng, rechnungen.planname, rechnungen.rechval, rechnungen.rabat, rechnungen.rabatval, rechnungen.rechstat, payments.paymetod, payments.timepay, payments.gultigbis, payments.sichtbarbis, payments.created, firmen.frmname",  "join"=>array(
      array("type"=>"join", "table"=>"payments", "on"=>array("payments.rechnnr"=>"rechnungen.crt")),
      array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"rechnungen.usid"))
      ),
      'where'=>array('rechnungen.usid'=>$info[0]), 'order_by'=>'rechnungen.crt DESC', 'return_type'=>'all'));

      }
        if($allRch){
          foreach($allRch as $key => $val){
            $allRch[$key]['crt'] = Main::encrypt($val['crt']);
            $allRch[$key]['timepay'] = Main::nice_date($val['timepay']);
            $allRch[$key]['gultigbis'] = Main::nice_date($val['gultigbis']);
            $allRch[$key]['sichtbarbis'] = Main::nice_date($val['sichtbarbis']);  
            $allRch[$key]['created'] = Main::nice_date($val['created']);            
          }
        return $allRch;
      }
      return false;
    }
  return false;
  }


  public function getKandidatAlerts(){
    if(!$info = Main::user()){
      return false;
    }
    if( $ka = $this->db->get("kandidatealerts", array("select"=>"crt, mails, beruf, fach, created", 'where'=>array('usid'=>$info[0]), 'return_type'=>'all')) ){
      foreach($ka as $k => $v){
        $ka[$k]['created'] = Main::unidate($v['created']);
      }
    }
  return $ka ? $ka : false;
  }


  public function getFrmAlerts(){
    if(!$info = Main::user()){
      return false;
    }
    if( $ka = $this->db->get("frmsealerts", array("select"=>"crt, mails, beruf, fach, created", 'where'=>array('usid'=>$info[0]), 'return_type'=>'all')) ){
      foreach($ka as $k => $v){
        $ka[$k]['created'] = Main::unidate($v['created']);
      }
    }
  return $ka ? $ka : false;
  }


  public function getMeineAnzeigen($idd){
    if(!$info = Main::user()){
      return false;
    } 

    if(isset($info[5]) && isset($info[6])){
      $info[0] = $info[6];
    }

    if($idd && !empty($idd)){
      $id = Main::decrypt($idd);
      if(!$id || !$id >= 1){
        Main::redirect("company");
        exit;
      }


      if($anzz = $this->db->get("anzeigen", array("select"=>"anzeigen.crt, anzeigen.viewd, anzeigen.ansprechs, anzeigen.titel, anzeigen.berufsfeld, anzeigen.positionen, anzeigen.fachbereich, anzeigen.arts, anzeigen.vertrag, anzeigen.filiale, anzeigen.starts, anzeigen.citycode, anzeigen.city, anzeigen.derbundes, anzeigen.derland, anzeigen.latit, anzeigen.longit, anzeigen.anznr, anzeigen.anzstr, anzeigen.beschreibung, anzeigen.besherwartet, anzeigen.beshbieten, anzeigen.beshwirerw, anzeigen.urlink, anzeigen.soclink, anzeigen.anzlogo, anzeigen.anzvideo, anzeigen.ytpromo, anzeigen.ytdid, anzeigen.modified, anzeigen.created, paystats.ison as paystat, paystats.planid, paystats.billnr, paystats.datactivat as activaeted,
      payplans.planname, payplans.planstellen, payplans.planweeks, payplans.planmonths, payplans.planlanger, rechnungen.nrstellen, rechnungen.gebucht",  "join"=>array(
                array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt")), 
                array("type"=>"join", "table"=>"payplans", "on"=>array("payplans.crt"=>"paystats.planid")),
                array("type"=>"join", "table"=>"rechnungen", "on"=>array("rechnungen.crt"=>"paystats.billnr"))),
                'where'=>array('anzeigen.crt'=>$id, 'anzeigen.usid'=>$info[0]), 'limit'=>1, 'return_type'=>'single'))){

            $anzz['eizellId'] = $idd;       

            if($anzz['gebucht'] && !empty($anzz['gebucht'])){
              $gebucht = unserialize($anzz['gebucht']);
              $manyGebucht  = count($gebucht); 
              $nr = ($anzz['nrstellen']-$manyGebucht);
              if($nr && $nr >= 1){
                $anzz['ramase'] = $nr;
              }else{
                $anzz['ramase'] = 0;
              }
            }else{
              $anzz['ramase'] = $anzz['nrstellen'];
            }

            if($anspr = unserialize($anzz['ansprechs'])){
              foreach($anspr as $val){
                $ansprechers[] = $this->db->get("ansprechpartners", 
                  array("join"=>array(
                  array("type"=>"join", "table"=>"ansprecher", "on"=>array("ansprecher.usid"=>"ansprechpartners.crt")), 
                  array("type"=>"join", "table"=>"ansprechpreffs", "on"=>array("ansprechpreffs.usid"=>"ansprechpartners.crt"))),
                  'where'=>array('ansprechpartners.unique_key'=>$val), 'return_type'=>'single'));
              }
              $anzz['ansprechpartners'] = $ansprechers;  
            }	
            return $anzz;
          }
      Main::redirect("company"); // ishidd   paystats
      exit;
    }else{
      if($aallAnz = $this->db->get("anzeigen", array("select"=>"anzeigen.crt, anzeigen.data_acktiv, anzeigen.viewd,  anzeigen.ansprechs,  anzeigen.titel, anzeigen.berufsfeld,
      anzeigen.positionen, anzeigen.fachbereich, anzeigen.arts, anzeigen.vertrag, anzeigen.filiale, anzeigen.starts, anzeigen.citycode, anzeigen.city, anzeigen.derbundes, anzeigen.derland, anzeigen.anzlogo, 
      paystats.ison, paystats.planid, paystats.payverlng, paystats.datactivat, payplans.crt as planNr, payplans.planname, payplans.planstellen, payplans.planprice, payplans.planweeks, payplans.planmonths, payplans.planlanger, payplans.planlangerval, payplans.planlangerwoch", "join"=>array(
        array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt", "paystats.usid"=>"anzeigen.usid")),
        array("type"=>"join", "table"=>"payplans", "on"=>array("payplans.crt"=>"paystats.planid")),
      ),'where'=>array('anzeigen.usid'=>$info[0], 'paystats.ishidd' => 2), 'order_by'=>'anzeigen.crt DESC', 'return_type'=>'all'))){	
        if($aallAnz){
          foreach($aallAnz as $key => $val){
            if($bewerberi = $this->db->get("bewerbeliste", array('where'=>array('crt'=>$val['crt'], 'firma'=>$info[0]), 'return_type'=>'count'))){
              $aallAnz[$key]['bewerberi'] = $bewerberi;
            }else{
              $aallAnz[$key]['bewerberi'] = 0;
            }

            if($isPayedVerl = $this->db->get("payments", array('select'=>'prt', 'where'=>array('usid'=>$info[0], 'anzeigeid'=>$val['crt'], 'paystatus'=>3), 'limit'=>1, 'return_type'=>'single'))){
              $aallAnz[$key]['onwaitvelang'] = 3;
            }

          }
        }
        return $aallAnz;
      }
      return false;
    }
  return false;
  }


  public function countDeleted($leveltype = 3){
    if($leveltype == 3){
      if(!$rez = $this->db->get("kandidate", array('select'=>'crt', 'where'=>array('isdel'=>1), 'return_type'=>'count'))){
        return FALSE;
      }
    }elseif($leveltype == 2){
      if(!$rez = $this->db->get("firmen", array('select'=>'crt', 'where'=>array('isdel'=>1), 'return_type'=>'count'))){
        return FALSE;
      }
    }
    return $rez;
  }

  public function countRegisteredToday($leveltype = 3){
    if($leveltype == 3){
      $result = $this->db->doQueryCount("SELECT COUNT(*) crt FROM kandidate WHERE created > now() - interval 24 hour");
    }elseif($leveltype == 2){
      $result = $this->db->doQueryCount("SELECT COUNT(*) crt FROM firmen WHERE created > now() - interval 24 hour");
    }
      if(!$result){
        return false;
      } 
    return $result;
  } 
  

  public function getAdminKandidatenList(){
    $deleted = array(); $heute = array();
    if($kandidat = $this->db->get("allusers", 
                array("select"=>"allusers.crt, allusers.avatar, allusers.userfstname, allusers.userlstname, allusers.usermail, allusers.userphone, allusers.unique_key, allusers.modified, kandidate.isdel, kandidate.choosejob, kandidate.positionjob, kandidate.subjectjob, kandidate.created", "join"=>array(
                array("type"=>"join", "table"=>"kandidate", "on"=>array("kandidate.usid"=>"allusers.crt"))), 'where'=>array('allusers.leveltype'=>3), 'return_type'=>'all'))){

            foreach($kandidat as $key => $user){
              if(!$user['avatar'] || empty($user['avatar'])){
                if($avatar = $this->checkAvatar($user['crt'])){
                  $kandidat[$key]['avatar'] = $avatar; 
                }else{
                  $kandidat[$key]['avatar'] = Main::config('url').'/static/images/users/no-avatars/male-no-avatar.png';
                } 
              }else{
                $kandidat[$key]['avatar'] = Main::config('url').'/static/images/users/'.$user['crt'].'/'.$user['avatar'];
              }

              if($user['isdel'] == 1){
                array_push($deleted, $user['crt']);
              }
      
              if (substr($user['created'], 0, 10) == date('Y-m-d')) {
                array_push($heute, $user['crt']);
              }
            }  
            
       $kandidatJust = $this->db->get("allusers", array("select"=>"crt, usermail, unique_key, modified, created", 'where'=>array('leveltype'=>20), 'return_type'=>'all'));        

      return ['all'=>$kandidat, 'heute'=>$heute,'deleted'=>$deleted, 'justregistered'=>$kandidatJust];
    }
   return false;
  }


  public function getAdminAnzeigensList(){
    $aktiv=array();$reaktiv=array();$heute=array();
    if($all = $this->db->get("anzeigen", 
                array("select"=>"anzeigen.crt, anzeigen.viewd, anzeigen.titel, anzeigen.berufsfeld, anzeigen.positionen, anzeigen.fachbereich, anzeigen.anzlogo, anzeigen.created, firmen.frmname, paystats.ison, paystats.ison, paystats.planid, paystats.payverlng, paystats.datactivat", "join"=>array(
                array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"anzeigen.usid")),
                array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt")) 
              ), 'return_type'=>'all'))){

            foreach($all as $key => $val){
              if($planns = $this->db->get("payplans", array('select'=>'planname', 'where'=>array('crt'=>$val['planid']), 'limit'=>1, 'return_type'=>'single'))){
                $all[$key]['planid'] = $planns['planname'];
              }

              if($plannsBewer = $this->db->get("bewerbeliste", array('select'=>'crt', 'where'=>array('stellen'=>$val['crt']), 'return_type'=>'count'))){
                $all[$key]['bewerbet'] = $plannsBewer;
              }else{
                $all[$key]['bewerbet'] = 0;
              }

              if($val['ison'] == 1){
                $all[$key]['planPlan'] = e('Inaktiv');
              }elseif($val['ison'] == 2){
                $all[$key]['planPlan'] = e('Inaktiv');
              }elseif($val['ison'] == 3){
                $all[$key]['planPlan'] = e('Aktiv');
                $all[$key]['planDate'] = Main::unidate($val['datactivat']);
              }elseif($val['ison'] == 4){
                $all[$key]['planPlan'] = e('Aktiv');
                $all[$key]['planDate'] = e('Eingeschränkt');
              }else{
                $all[$key]['planPlan'] = '-';
              }


              if($val['ison'] == 3 && $val['payverlng'] == 0){
                array_push($aktiv, $val['crt']);
              }

              if($val['ison'] == 3 && $val['payverlng'] >= 1){
                array_push($reaktiv, $val['crt']);
              }
      
              if (substr($val['created'], 0, 10) == date('Y-m-d')) {
                array_push($heute, $val['crt']);
              }

            }
        return ['all'=>$all, 'heute'=>$heute,'aktiv'=>$aktiv, 'reaktiv'=>$reaktiv];
    }
  return false;
  }


  public function getAdminBewerbt(){
    $heute=array();
    if($all = $this->db->get("anzeigen", 
                        array("select"=>"anzeigen.crt, anzeigen.viewd, anzeigen.titel, firmen.frmname, bewerbeliste.usid, bewerbeliste.created", "join"=>array(
                        array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"anzeigen.usid")),
                        array("type"=>"join", "table"=>"bewerbeliste", "on"=>array("bewerbeliste.stellen"=>"anzeigen.crt"))), 'return_type'=>'all'))){
        foreach($all as $key => $val){
          if($getUser = $this->db->get("allusers", array("select"=>"userfstname, userlstname", "where"=>array("crt"=>$val['usid']), "limit"=>1, "return_type"=>"single"))){
            $all[$key]['kandidat'] = $getUser['userfstname'].' '.$getUser['userlstname']; 
          }else{
            $all[$key]['kandidat'] = "";
          }

          if (substr($val['created'], 0, 10) == date('Y-m-d')) {
            array_push($heute, $val['crt']);
          }
        }
        return ['all'=>$all, 'heute'=>$heute];
    }
  return false;
  }

  
  public function getPublicTicker(){

    if($allTicker = $this->db->get("anzeigen", array("select"=>"anzeigen.crt, anzeigen.titel, anzeigen.derbundes, anzeigen.anzlogo, firmen.usid as alusr, firmen.frmavatar", "join"=>array(
      array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"anzeigen.usid")),
      array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt"))
      ),'where'=>array('paystats.ison'=>3), 'order_by'=>'anzeigen.modified DESC', 'limit'=>20, 'return_type'=>'all'))){  /// anzeigen.viewd
        $url = Main::config('url');
        foreach($allTicker as $key => $val){

          if(!$val['anzlogo']) { 
            if(!$val['frmavatar']) { 
              $allTicker[$key]['anzlogo'] = $url.UPLOAD_PATH_ANZEIGE.'no-avatars/firmen-no-logo.svg';
            }else{
              $allTicker[$key]['anzlogo'] = $url.UPLOAD_PATH_LOGO.$val['alusr'].'/'.$val['frmavatar'];
            }
          }else{  
            $allTicker[$key]['anzlogo'] = $url.UPLOAD_PATH_ANZEIGE.$val['crt'].'/'.$val['anzlogo'];
          }
          $allTicker[$key]['titel'] = mb_substr($val['titel'], 0, 30)." ...";
          $allTicker[$key]['link'] = Main::encrypt($val['crt']);
        }                   
      return $allTicker;
    }
    return false;
  }

  
  public function getAdminFirmenList(){
    $deleted = array(); $heute = array();
    if($all = $this->db->get("firmen", 
                array("select"=>"firmen.crt, firmen.usid, firmen.frmavatar, firmen.frmname, firmen.frmsteuer, firmen.frmeinricht, firmen.intersartze, firmen.intersnurse, firmen.interspharma, firmen.created, firmen.isdel, allusers.unique_key, allusers.modified, frmpreffs.telefon,frmpreffs.email", "join"=>array(
                array("type"=>"join", "table"=>"allusers", "on"=>array("allusers.crt"=>"firmen.usid")),
                array("type"=>"join", "table"=>"frmpreffs", "on"=>array("frmpreffs.usid"=>"firmen.usid")) 
              ), 'return_type'=>'all'))
                ){
        foreach($all as $k => $val){
          $rechnungen = $this->db->get("rechnungen", array('select'=>'rechstat, planname', 'where'=>array('usid'=>$val['usid']), 'order_by'=>'crt DESC', 'limit'=>1, 'return_type'=>'single'));
          if($rechnungen){
            if($rechnungen['rechstat'] == 1){
              $all[$k]['bezahlt'] = e('Bezahlt');
            }elseif($rechnungen['rechstat'] == 3){
              $all[$k]['bezahlt'] = e('Nicht bezahlt');
            }elseif($rechnungen['rechstat'] == 6){
              $all[$k]['bezahlt'] = e('Storniert');
            }elseif($rechnungen['rechstat'] == 3){
              $all[$k]['bezahlt'] = e('Fehler');
            }else{
              $all[$k]['bezahlt'] = '-';
            }
            $all[$k]['planname'] = $rechnungen['planname'] ? $rechnungen['planname'] :'-- --';
          }else{
            $all[$k]['bezahlt'] = '-';
            $all[$k]['planname'] = '-- --';
          }


          if($val['isdel'] == 1){
            array_push($deleted, $val['crt']);
          }
  
          if (substr($val['created'], 0, 10) == date('Y-m-d')) {
            array_push($heute, $val['crt']);
          }
        }

        
      return ['all'=>$all, 'heute'=>$heute,'deleted'=>$deleted];
    }
   return false;
  }


  public function countRegToday(){
    if(!$anzeigen = $this->db->doQueryCount("SELECT COUNT(*) crt FROM anzeigen WHERE  created > now() - interval 24 hour")){  
      $anzeigen = $anzeigen ? $anzeigen : false;
    }

    if(!$bewerbeliste = $this->db->doQueryCount("SELECT COUNT(*) crt FROM bewerbeliste WHERE  created > now() - interval 24 hour")){
      $bewerbeliste = $bewerbeliste ? $bewerbeliste : false;
    }

    $act=0;$react=0;
    if($all = $this->db->get("paystats", 
        array("select"=>"paystats.ison, paystats.payverlng", "join"=>array(
        array("type"=>"join", "table"=>"allusers", "on"=>array("allusers.crt"=>"paystats.usid"))),
        'where'=>array('allusers.isdel'=>2), 'return_type'=>'all'))){
        foreach($all as $k => $val){
          if($val['ison'] == 3 && $val['payverlng'] == 0){   
            $act++;
          }elseif($val['ison'] == 3 && $val['payverlng'] >= 1){
            $react++;
          }
        }
    }
 
    return array('stellen'=>$anzeigen, 'bewerb'=>$bewerbeliste, 'aktiv'=>$act, 'reaktiv'=>$react);
  } 

  

  public function getAllStats(){     ////  "stellen"=>$this->countRegisteredToday('anzeigen', 2), "bewerb"=>$this->countRegisteredToday('bewerbeliste')
    $rez=[]; 
    $counts = array('anzeigen', 'bewerbeliste');
    if($rez["allusers"] = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('isdel'=>2, 'leveltype'=>3), 'return_type'=>'count'))){
      $rez["firmen"] = $this->db->get("firmen", array('select'=>'crt', 'where'=>array('isdel'=>2), 'return_type'=>'count'));

      foreach($counts as $key){
        $rez[$key] = $this->db->get($key, array('select'=>'crt', 'return_type'=>'count'));
      }

     // $rez['anzonline'] = $this->db->get('anzeigen', array('select'=>'crt', 'return_type'=>'count'));

     $rez['anzonline'] = $this->db->get("anzeigen", array("select"=>"anzeigen.crt, paystats.payverlng", 
      "join"=>array(array("type"=>"join", "table"=>"paystats", "on"=>array("anzeigen.crt"=>"paystats.anzid"))), 'where'=>array('paystats.ison'=>3), 'return_type'=>'count'));

      $rez['vermerkt'] = $this->db->get('firmsliebste', array('select'=>'crt', 'return_type'=>'count'));

       $rez['deleted'] = ["kandidate"=>$this->countDeleted(3), "firmen"=>$this->countDeleted(2)];
       $rez['registeredToday'] = ["kandidate"=>$this->countRegisteredToday(3), "firmen"=>$this->countRegisteredToday(2), "registeredHeute"=>$this->countRegToday()];

      return $rez;
    }
    return false;
  }


  public function getAdmins($id = FALSE){ 
    if(!$info = Main::user()){
      return false;
    } 

    if(!$id){
      if($all = $this->db->get("allusers", 
        array("select"=>"allusers.crt, allusers.userfstname, allusers.userlstname, allusers.usermail, allusers.userphone, allusers.activated, allusers.unique_key, sa_roles.iskontakt, sa_roles.roles", "join"=>array(
        array("type"=>"join", "table"=>"sa_roles", "on"=>array("sa_roles.usid"=>"allusers.crt"))),
        'where'=>array('allusers.admin'=>2), 'return_type'=>'all'))){
          						
        return $all;
      }
    }else{
      $id = Main::decrypt($id);
      if(!$id || !$id >= 1){
        $this->destroiy();  
        exit;
      }

      if($all = $this->db->get("allusers", 
        array("select"=>"allusers.crt, allusers.avatar, allusers.userfstname, allusers.userlstname, allusers.usermail, allusers.userphone, allusers.activated, allusers.unique_key, sa_roles.iskontakt, sa_roles.roles", "join"=>array(
        array("type"=>"join", "table"=>"sa_roles", "on"=>array("sa_roles.usid"=>"allusers.crt"))),
        'where'=>array('allusers.crt'=>$id, 'allusers.admin'=>2), 'return_type'=>'single'))){	

          if($all['avatar']){
            $all['avatar'] = Main::config('url').'/static/images/users/'.$all['crt'].'/'.$all['avatar'];; 
          }else{
            $all['avatar'] = Main::config('url').'/static/images/users/no-avatars/male-no-avatar.png';
          } 

        return $all;
      }
    }
    return false;
  }




  public function getChatKand(){
    if(!$info = Main::user()){
      return false;
    }

    if(!$myAds = $this->db->get("anzeigen", array("select"=>"anzeigen.titel, anzeigen.positionen, anzeigen.fachbereich, paystats.crt", "join"=>array(
      array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt"))), 
      'where'=>array('paystats.usid'=>$info[0]), 'big-equal'=>array('paystats.ison'=>3), 'return_type'=>'all'))){
       return false;
     }else{
      $myAdsPos = array();
      $myAdsFach = array();
      foreach($myAds as $k => $v){
        array_push($myAdsPos, $v['positionen']);

        array_push($myAdsFach, $v['fachbereich']);
      }
    }

 

    if($kandidates = $this->db->get("candidates_view", array('return_type'=>'all'))){


      foreach($kandidates as $key => $user){
        if (!in_array($user['positionjob'], $myAdsPos)){
          unset($kandidates[$key]);
        }
  
        if (!in_array($user['subjectjob'], $myAdsFach)){
          unset($kandidates[$key]);
        }
      }
  
     $kandidates = array_values($kandidates);
    
          
    }

  }

  public function getAllChatsUsers(){
    if(!$info = Main::user()){
      return false;
    } 


    if(!$myAds = $this->db->get("anzeigen", array("select"=>"anzeigen.titel, anzeigen.positionen, anzeigen.fachbereich, paystats.crt", "join"=>array(
      array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt"))), 
      'where'=>array('paystats.usid'=>$info[0]), 'big-equal'=>array('paystats.ison'=>3), 'return_type'=>'all'))){
       return false;
     }else{
      $myAdsPos = array();
      $myAdsFach = array();
      foreach($myAds as $k => $v){
        array_push($myAdsPos, $v['positionen']);

        array_push($myAdsFach, $v['fachbereich']);
      }
    }


    if($kandidates = $this->db->get("candidates_view", array('return_type'=>'all'))){
      foreach($kandidates as $key => $user){
        if (!in_array($user['positionjob'], $myAdsPos)){
          unset($kandidates[$key]);
        }
  
        if (!in_array($user['subjectjob'], $myAdsFach)){
          unset($kandidates[$key]);
        }
      }
  
     if($user = array_values($kandidates)){
      foreach($user as $k => $uusr){
        if(!$uusr['avatar'] || empty($uusr['avatar'])){
          if($avatar = $this->checkAvatar($uusr['crt'])){
            $user[$k]['avatar'] = $avatar; 
          }else{
            $user[$k]['avatar'] = "no-avatars/male-no-avatar.png";
          } 
        } 

        $user[$k]['on_line'] = Main::chatStaus($uusr['on_line']);
        if($typeprivat = $this->db->get("usrpreffs", array("select"=>"typeprivat", 'where'=>array('usid'=>$uusr['crt']),'limit'=>1, 'return_type'=>'single'))){
          $user[$k]['fullname'] = Main::userProtect($uusr['userfstname'], $uusr['userlstname'], $typeprivat['typeprivat']);
        }else{
          $user[$k]['fullname'] = Main::userProtect($uusr['userfstname'], $uusr['userlstname'], 1);
        }
      }
      return $user;
     }
    return false;      
    }

  }



  public function getAllChatsKandidates(){
    if(!$info = Main::user()){
      return false;
    } 


    if($availab = $this->db->get("ansprechsview", array("select"=>"usid, ansprechs, piid", "return_type"=>"all"))){
      $respionse = [];
      foreach($availab as $k =>$val){
        if($user = $this->db->get("allusers",  
          array("select"=>"allusers.crt, allusers.on_line, allusers.avatar, allusers.userfstname, allusers.userlstname, allusers.unique_key, firmen.frmavatar, firmen.frmname as frmname", 
          "join"=>array(array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"allusers.crt"))), 
          'where'=>array('allusers.crt'=>$val['usid'], 'allusers.isdel'=>2), 'return_type'=>'single'))){
              if(!$user['frmavatar'] || empty($user['frmavatar'])){ 
                $respionse[$k]['avatar'] = UPLOAD_PATH_LOGO.'firma-no-avatars/firmen-no-logo.svg';
              }else{ 
                $respionse[$k]['avatar'] = UPLOAD_PATH_LOGO.$user['crt'].'/'.$user['frmavatar'];
              }
              $respionse[$k]['on_line'] = Main::chatStaus($user['on_line']);
              $respionse[$k]['fullname'] = $user['userfstname'].' '.$user['userlstname'];

              $respionse[$k]['crt'] = $user['crt'];
              $respionse[$k]['unique_key'] = $user['unique_key'];
              $respionse[$k]['frmname'] = $user['frmname'];
              $respionse[$k]['ansprechers'] =[];

              if($val['ansprechs']){
                $ansprechs = unserialize($val['ansprechs']);
                if($ansprechs && count($ansprechs) >= 1){
                  foreach($ansprechs as $k => $value){
                    if($ans = $this->db->get("allusers", array("select"=>"allusers.crt, ansprechpartners.crt as ansprcrt, ansprechpartners.islogin, ansprechpartners.avatar, ansprechpartners.userfstname, ansprechpartners.userlstname, ansprechpartners.unique_key", 
                    "join"=>array(array("type"=>"join", "table"=>"ansprechpartners", "on"=>array("ansprechpartners.usid"=>"allusers.crt"))), 
                    "where"=>array("allusers.isdel"=>2, "allusers.unique_key"=>$value, "ansprechpartners.activated"=>1 ), "limit"=>1, "return_type"=>"single"))){

                      if(!$ans['avatar'] || empty($ans['avatar'])){ 
                        $respionse[$k]['ansprechers'][$k]['avatar'] = UPLOAD_PATH_ANSPRECHPARTNERS.'no-avatars/male-no-avatar.png';
                      }else{ 
                        $respionse[$k]['ansprechers'][$k]['avatar'] = UPLOAD_PATH_ANSPRECHPARTNERS.$ans['ansprcrt'].'/'.$ans['avatar'];
                      }
                      $respionse[$k]['ansprechers'][$k]['on_line'] = Main::chatStaus($ans['islogin']);
                      $respionse[$k]['ansprechers'][$k]['fullname'] = $user['userfstname'].' '.$ans['userlstname'];
                      $respionse[$k]['ansprechers'][$k]['crt'] = $ans['crt'];
                      $respionse[$k]['ansprechers'][$k]['unique_key'] = $ans['unique_key'];
                      $respionse[$k]['ansprechers'][$k]['frmname'] = $user['frmname'];
                    }
                  }
                }
              } 
        }
      }
      return $respionse;
    } 
    return false;
  }



  public function getAllChatsKandidatesAAA(){
    if(!$info = Main::user()){
      return false;
    } 


    if($availab = $this->db->get("anzeigen", array("select"=>"anzeigen.usid, anzeigen.ansprechs, paystats.crt as piid", 
    "join"=>array( array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt"))
    ), "big-equal"=>array("paystats.ison" => 3), "return_type"=>"all"))){



    }

      if($user = $this->db->get("allusers",  
        array("select"=>"allusers.crt, allusers.on_line, allusers.avatar, allusers.userfstname, allusers.userlstname, allusers.unique_key, firmen.frmavatar, firmen.frmname as frmname", 
        "join"=>array(array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"allusers.crt"))), 
        'where'=>array('allusers.isdel'=>2), 'return_type'=>'all'))){
          foreach($user as $k => $uusr){
            if(!$uusr['frmavatar'] || empty($uusr['frmavatar'])){ 
              $user[$k]['avatar'] = UPLOAD_PATH_LOGO.'firma-no-avatars/firmen-no-logo.svg';
            }else{ 
              $user[$k]['avatar'] = UPLOAD_PATH_LOGO.$uusr['crt'].'/'.$uusr['frmavatar'];
            }
            $user[$k]['on_line'] = Main::chatStaus($uusr['on_line']);
            $user[$k]['fullname'] = $uusr['userfstname'].' '.$uusr['userlstname'];
          }
          return $user;
      }
    return false;
  }


  public function getAdminsChats(){
    if(!$info = Main::user()){
      return false;
    } 

    if($user = $this->db->get("allusers",  
        array("select"=>"allusers.crt, allusers.on_line, allusers.avatar, allusers.userfstname, allusers.userlstname, allusers.unique_key, firmen.frmavatar, firmen.frmname as frmname", 
        "join"=>array(
         array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"allusers.crt"))), 
        'where'=>array('allusers.isdel'=>2), 'where-not'=>array('allusers.crt'=>$info[0]), 'return_type'=>'all'))){
          foreach($user as $k => $uusr){
            if(!$uusr['frmavatar'] || empty($uusr['frmavatar'])){ 
              $user[$k]['avatar'] = UPLOAD_PATH_LOGO.'firma-no-avatars/firmen-no-logo.svg';
            }else{ 
              $user[$k]['avatar'] = UPLOAD_PATH_LOGO.$uusr['crt'].'/'.$uusr['frmavatar'];
            }

            $user[$k]['on_line'] = Main::chatStaus($uusr['on_line']);


           $user[$k]['fullname'] = $uusr['userfstname'].' '.$uusr['userlstname'];
          }
          return $user;
      }
    return false;
  }


  





  
  
  public function check($info, $depth = ''){ 

      if($user = $this->db->get("allusers", array('where'=>array('crt'=>$info[0], 'isdel'=>2, 'banned'=>2, 'unique_key'=>$info[2]),'limit'=>1,'return_type'=>'single'))){							
        $user['ucrt'] = Main::encrypt($user['crt']);
        $user['lastlogged'] = Main::nice_datum($user['modified']);
        $user['membership'] = !empty($user['membership']) ? $user['membership'] : null;
        if(!$user['avatar'] || empty($user['avatar'])){
          if($avatar = $this->checkAvatar($info[0])){
            $user['avatar'] = $avatar; 
            $user['noavatar'] = $avatar;
          }else{
            $user['avatar'] = "no-avatars/male-no-avatar.png";
            $user['noavatar'] = '';
          } 
        }else{
          $user['noavatar'] = $user['avatar'];
        }
        
        $user['hasSocials'] = $this->checkSocialsEmail($info[0]);

        $user['progress'] = $this->checkAccountProgress($info[0], $user['membership']);

        if($depth == 'kandidat'){
          if($usrpreffs = $this->db->get("usrpreffs", array('select'=>'typeprivat, titeljob, herrjob, jobstreet, jobstreetnr, jobspostcode, jobsort, jobbirth, telefon, chat, mail, ytpromot, usrvideo, preffs', 'where'=>array('usid'=>$user['crt']),'limit'=>1,'return_type'=>'single'))){

            $kandidate = $this->db->get("kandidate", array('select'=>'isalowed, mustsjobapp, mustsjoberlaub, mustsjobaank, suzatz, choosejob, positionjob, subjectjob, arts, vertrag, landsjob', 'where'=>array('usid'=>$user['crt']),'limit'=>1,'return_type'=>'single'));

            if(!$lebens = $this->db->get("lebens", array('select'=>'crt as lebencrt, leben', 'where'=>array('usid'=>$user['crt']),'limit'=>1,'return_type'=>'single'))){
              $lebens['leben']='';
              $lebens['lebencrt'] = '';
            }else{ $lebens['lebencrt'] = Main::encrypt($lebens['lebencrt']); }
            if(!$optionals = $this->db->get("optionals", array('select'=>'crt as optscrt, opts', 'where'=>array('usid'=>$user['crt']),'limit'=>1,'return_type'=>'single'))){
              $optionals['opts']='';
              $optionals['optscrt']='';
            }else{ $optionals['optscrt'] = Main::encrypt($optionals['optscrt']); }
            if(!$optextratext = $this->db->get("optextra", array('select'=>'crt as optextracrt, optextra', 'where'=>array('usid'=>$user['crt']),'limit'=>1,'return_type'=>'single'))){
              $optextratext['optextra'] ='';
              $optextratext['optextracrt'] ='';
            }else{ $optextratext['optextracrt'] = Main::encrypt($optextratext['optextracrt']); }

            // todo UN JOIN PT FLORICICA!!
            $user = array_merge($user, $usrpreffs, $kandidate, $lebens, $optionals, $optextratext);
          }else{
            return FALSE;
          }
        }else if($depth == 'firma'){ 
          if($ansprech = Main::user()){
            if(($ansprech[4] == 2) && isset($ansprech[6])){
              $user['crt'] = $ansprech[6];
            }
          }//  }elseif($level == 22 && ($this->userauth[4] != $level || $this->userauth[3] != 'firma')){

          if($firmapreffs = $this->db->get("frmpreffs", array('select'=>'anrede, vorname, name, position, telefon, email, hausnr, strasse, citycode, city, landd, inhaber, bankname, swift, iban, telpref, chatpref, mailpref, preffs', 'where'=>array('usid'=>$user['crt']),'limit'=>1,'return_type'=>'single'))){

            $firmen = $this->db->get("firmen", array('select'=>'frmawall, frmavatar, frmname, frmsteuer, frmregister, frmeinricht, intersartze, intersnurse, interspharma, medsfachs, therafachs, verwafach, techfach, sonstiges, frmbeschtitl, frmbeschreibb, ufrmlinkk, ufrmlinksocc, videotube, frmvideotitel, frmvideo, created', 'where'=>array('usid'=>$user['crt']),'limit'=>1,'return_type'=>'single'));


            // todo UN JOIN PT FLORICICA!!
            $user = array_merge($user, $firmapreffs, $firmen); /// $lebens, $optionals, $optextratext
          }else{
            return FALSE;
          }
          
        }else if($depth == 'admin'){
          
          return $user;

        }


        if( !$x = Main::cookie("meingang")){
          if($mc = $this->countPosteingang()){
            $unreadMails = $mc['email'] >= 1 ? $mc['email'] : 0;  
            $unreadChats = $mc['chats'] >= 1 ? $mc['chats'] : 0;  
            $unreadMails = json_encode(array("email"=>$unreadMails, "chats"=>$unreadChats));
          }else{ $unreadMails = json_encode(array("email"=>"0", "chats"=>"0"));}
        }else{
           $unreadMails = $x;
        }

        $user['postEingang'] = $unreadMails;

      return $user;
    }
    return FALSE;
  }


  public function checkUserById($id){ 
    if($check = $this->db->get("allusers", array('select'=>'leveltype, membership, expires, userfstname, userlstname, userpass, usersalt, auth_key, unique_key, banned, userverified, activated', "where"=>array('crt'=>$id, 'isdel'=>2, 'banned'=>2), 'limit'=>1, 'return_type'=>'single'))){
        $this->id = $id;
        $this->loggedLevel = $check['leveltype'];
        $this->membership = $check['membership'];
        $this->expires = $check['expires'];
        $this->firstname = $check['userfstname'];
        $this->lastname = $check['userlstname'];
        $this->password = $check['userpass'];
        $this->salz = $check['usersalt'];
        $this->auth_key = $check['auth_key'];  
        $this->unique_key = $check['unique_key'];
        $this->userbanned = $check['banned'];  
        $this->userverified = $check['userverified']; 
        $this->activated = $check['activated']; 

      return true;
    }
    return false;
  }

/**
* Check the password on password changes
**/
 public function checkbrutes($userid) {
	$now = time(); 
	$valid_attempts = $now - (1 * 60 * 60);
	$trys = $this->db->get("logins_err",  array('select'=>'crf', 'where'=>array('user_id' =>$userid), 'compbig'=>array('time'=>$valid_attempts), 'return_type'=>'count'));        
		if (!$trys || ($trys && $trys <= 5)) {
			return false;
		}
  return true;
 }


  public function checkUserPassword($id, $pas){ 

    if(!$user = $this->checkUserById($id)){
      return false;
    }
    $password_sha = openssl_digest($pas, 'sha512');
    $password = hash('sha512', $password_sha . $this->salz);
    
		if ($this->password == $password && !$this->checkbrutes($id)){
				$delLogins = $this->db->delete("logins_err", array('user_id' => $id));
        return array("result"=>1, "msg"=>"success");
		}else{
			if(!$this->checkbrutes($id)){
				$this->db->insert("logins_err", array('user_id'=>$id, 'time'=>time()), TRUE);
			  return array("result"=>2, "msg"=>"Ihr Passwort ist falsch!");
			}
      return array("result"=>3, "msg"=>"Ihr Konto ist für 1 Stunde gesperrt!");
		}  
  }


  public function setUserIN(){
      $jsonArr = base64_encode(json_encode(array("loggedin"=>TRUE, "key"=>$this->auth_key.$this->id, "uniq"=>$this->unique_key, "membership"=>$this->membership, "leveltype"=>$this->loggedLevel)));

      $token = Tools::setJwt(array("id"=>$jsonArr)); 

      if($jwt = $this->unique_key.JWT::encode($token, JWTKEY)){  
      
        $user_browser = $_SERVER['HTTP_USER_AGENT'];
        $sessData['login_string'] = hash('sha512', $this->password . $user_browser); 		
        $_SESSION['sessData'] = $sessData;

        $_SESSION["login"] = $jsonArr;
       // $_SESSION["logi"] = $this->unique_key;
      //  $_SESSION['loogiwh'] = $this->loggedLevel;
        $this->logged = TRUE;	
        Main::cookie("loowt", $jwt);

        if($this->db->doquery("UPDATE allusers SET tot_visits = tot_visits + 1 WHERE crt = ".$this->id)){
          $upd = $this->db->update("allusers", array('on_line'=>1), array('crt'=>$this->id));
        }

        return true;
      }
    return false;
  }


  public function countPosteingang(){
    if(!$info = Main::user()){
      return false;
    } 
     $theMailCount = $this->db->get("mailserver", array('select'=>'crt', 
                                    'where'=>array('usid'=>$info[0], 'tosidview'=>$info[0], 'mailstat'=>2), 
                                    'where-or-and'=>array('tosid'=>$info[0], 'tosidview'=>$info[0], 'mailstat'=>2), 'return_type'=>'count'));
    $theMailCount = $theMailCount ? $theMailCount : "0"; 

    $theChatCount = $this->db->get("chats", array('select'=>'crt', 
                                    'where'=>array('isread'=>1, 'fromid'=>$info[0]), 
                                    'where-or-and'=>array('isread'=>1, 'toid'=>$info[0]), 'return_type'=>'count'));
   
    $theChatCount = $theChatCount ? $theChatCount : "0"; 

    Main::cookie("meingang", json_encode(array("email"=>$theMailCount, "chats"=>$theChatCount)), FALSE  );  

    return  array("email"=>$theMailCount, "chats"=>$theChatCount);
  }


  public function destroiy(){
    setcookie("loowt","", [
			'expires' => time() - 3600,
			'path' => '/',
			'domain' => DOMAIN,
			'secure' => TRUE,
			'httponly' => TRUE,
			'samesite' => 'None',
	  ]);    
    setcookie("meingang", "", time() - 3600, "/", DOMAIN, true, true); 
    setcookie("meingang","", [
			'expires' => time() - 3600,
			'path' => '/',
			'domain' => DOMAIN,
			'secure' => TRUE,
			'httponly' => TRUE,
			'samesite' => 'None',
	  ]); 

    if (isset($_SESSION)){
        session_destroy();
    }
    

    Main::redirect("anmeldung"); 
   //header("Location: https://doc-site.de?a=".$a."-".$b);
    die();
  }

  





}

?>