<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class DashboardController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
    $this->config = $config;
    $this->db = $db;

    $this->model = $this->model('Dashboard', $this->config, $this->db);
  }

  public function index($params = ''){

    $this->model->checkIsLogged('admin');  

    $this->isPage = 'admin/dashboard';
    $params = func_get_args();
    $this->model->text['params'] = $params;

    $this->model->getAdminStats();

    /// anzonline  vermerkt  anzeigen  bewerbeliste  $this->text['adm'] 

    $this->model->text["datain"] = ["kandidates"=>$this->model->text['adm']["allusers"], "unternehmen"=>$this->model->text['adm']["firmen"], "jobs"=>$this->model->text['adm']["anzonline"], "apply"=>$this->model->text['adm']["bewerbeliste"], "saved"=>$this->model->text['adm']["vermerkt"], "suspended"=>($this->model->text['adm']["anzeigen"]-$this->model->text['adm']["anzonline"]), "jobstxt"=>e('Stellenangebote'), "applytxt"=>e('Bewerbt'), "savedtxt"=>e('Vermerkt'), "suspendedtxt"=>e('Aktiv'), "kandidatestxt"=>e('Kandidaten'), "unternehmentxt"=>e('Unternehmen')];

    unset($this->model->text['adm']["anzonline"], $this->model->text['adm']["vermerkt"]);

    $this->view($this->isPage,  $this->model->text);
  }

  

      public function doChats($params = ''){
            if(Main::bot()) return $this->content("_404");
            $data = json_decode(file_get_contents("php://input"));
            if($p = $this->model->dodoChatSend($data)){
                  http_response_code(200);
                  echo json_encode($p);
            }else{
                  http_response_code(200);
                  echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
            } 
      }


      public function getChats($params = ''){
            if(Main::bot()) return $this->content("_404");
            $data = json_decode(file_get_contents("php://input"));
            if($p = $this->model->getChatsTalks($data->doit)){
                  http_response_code(200);
                  echo json_encode($p);
            }else{
                  http_response_code(200);
                  echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
            } 
      }



      



     public function zugangedits($params = ''){
            if(Main::bot()) return $this->content("_404");
            $data = json_decode(file_get_contents("php://input"));
            if($p = $this->model->zugangsEdit($data->doit)){
            http_response_code(200);
            echo json_encode($p);
            }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
            } 
      }


      public function dowebsetts($params = ''){
      if(Main::bot()) return $this->content("_404");
            $data = json_decode(file_get_contents("php://input"));
            if($p = $this->model->doWebSettings($data->doit)){
                  http_response_code(200);
                  echo json_encode($p);
            }else{
                  http_response_code(200);
                  echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
            } 
      }

      public function admntestemail($params = ''){
      if(Main::bot()) return $this->content("_404");
            $data = json_decode(file_get_contents("php://input"));
            if($p = $this->model->doTestEmail($data->doit)){
                  http_response_code(200);
                  echo json_encode($p);
            }else{
                  http_response_code(200);
                  echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
            } 
      }

      public function admntestchat($params = ''){
      if(Main::bot()) return $this->content("_404");
            $data = json_decode(file_get_contents("php://input"));
            if($p = $this->model->doTestChat($data->doit)){
                  http_response_code(200);
                  echo json_encode($p);
            }else{
                  http_response_code(200);
                  echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
            } 
      }


      public function zahhll($params = ''){
            if(Main::bot()) return $this->content("_404");
              $data = json_decode(file_get_contents("php://input"));
              if($p = $this->model->doZahhll($data->doit)){
                    http_response_code(200);
                    echo json_encode($p);
              }else{
                    http_response_code(200);
                    echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
              } 
      }

      public function zahhllpaypal($params = ''){
            if(Main::bot()) return $this->content("_404");
              $data = json_decode(file_get_contents("php://input"));
              if($p = $this->model->doZahhllpaypal($data->doit)){
                    http_response_code(200);
                    echo json_encode($p);
              }else{
                    http_response_code(200);
                    echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
              } 
      }



      public function zahhllrechn($params = ''){
            if(Main::bot()) return $this->content("_404");
              $data = json_decode(file_get_contents("php://input"));
              if($p = $this->model->doZahhllrechser($data->doit)){
                    http_response_code(200);
                    echo json_encode($p);
              }else{
                    http_response_code(200);
                    echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
              } 
      }



      public function rabathinzufug($params = ''){
            if(Main::bot()) return $this->content("_404");
              $data = json_decode(file_get_contents("php://input"));
              if($p = $this->model->doRabathinzufungen($data->doit)){
                    http_response_code(200);
                    echo json_encode($p);
              }else{
                    http_response_code(200);
                    echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
              } 
      }


      public function rabatloeschhen($params = ''){
            if(Main::bot()) return $this->content("_404");
              $data = json_decode(file_get_contents("php://input"));
              if($p = $this->model->doRabatDelete($data->doit)){
                    http_response_code(200);
                    echo json_encode($p);
              }else{
                    http_response_code(200);
                    echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
              } 
      }



      


      public function angebotvieed($params = ''){
            if(Main::bot()) return $this->content("_404");
              $data = json_decode(file_get_contents("php://input"));
              if($p = $this->model->doAngebotvieed($data->doit)){
                    http_response_code(200);
                    echo json_encode($p);
              }else{
                    http_response_code(200);
                    echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
              } 
      }


      public function angebsett($params = ''){
            if(Main::bot()) return $this->content("_404");
              $data = json_decode(file_get_contents("php://input"));
              if($p = $this->model->doAngebsett($data->doit)){
                    http_response_code(200);
                    echo json_encode($p);
              }else{
                    http_response_code(200);
                    echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
              } 
      }



      public function delStellen($params = ''){
            if(Main::bot()) return $this->content("_404");
            $data = json_decode(file_get_contents("php://input"));
            if($p = $this->model->doDeleteStellenangebote($data->doit)){
                  http_response_code(200);
                  echo json_encode($p);
            }else{
                  http_response_code(200);
                  echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
            } 
      }


      public function delUnternehn($params = ''){
            if(Main::bot()) return $this->content("_404");
            $data = json_decode(file_get_contents("php://input"));
            if($p = $this->model->doDeleteUnternehm($data->doit)){
                  http_response_code(200);
                  echo json_encode($p);
            }else{
                  http_response_code(200);
                  echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
            } 
      }


      
 



  
}
?>