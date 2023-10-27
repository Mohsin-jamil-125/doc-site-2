<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
 

include SOCIALAUTH.'autoload.php';

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;
use Hybridauth\Storage\Session;

class Logout extends Model  {

 
  protected $socialsConfig;

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->socialsConfig = $this->config['socialsAuth'];

    Main::pagetitle('LogOut');
  }

  public function ausloggen(){
    if($info = Main::user()){ 
      $usrIns = $this->db->update("allusers", array("on_line"=>2), array("crt"=>$info[0]));
    } 

    if(isset($_SESSION['social_name']) && !empty($_SESSION['social_name'])){
      $hybridauth = new Hybridauth($this->socialsConfig);
      $adapter = $hybridauth->getAdapter($_SESSION['social_name']);
      $adapter->disconnect();
    }

  
    unset($_COOKIE['loowt']); 
  //  setcookie("loowt", "", time() - 3600, "/", DOMAIN, true, true);
    setcookie("loowt","", [
			'expires' => time() - 3600,
			'path' => '/',
			'domain' => DOMAIN,
			'secure' => TRUE,
			'httponly' => TRUE,
			'samesite' => 'None',
	  ]);  
    if(isset($_SESSION["login"])) unset($_SESSION["login"]);   
   // if(isset($_SESSION["logi"])) unset($_SESSION["logi"]);
    if(isset($_SESSION["loggedin"])) unset($_SESSION["loggedin"]);
    if(isset($_SESSION["csrf_token"])) unset($_SESSION["csrf_token"]);
  
    session_destroy();

    return Main::redirect("anmeldung"); 
  }

}