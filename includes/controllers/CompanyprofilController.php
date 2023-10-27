<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class CompanyprofilController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Companyprofil', $this->config, $this->db);
    }

    public function index($params = ''){

      $this->model->checkIsLogged('firma'); /// firma/company

      $this->isPage = 'firma/companyprofil';
      $params = func_get_args();
      $this->model->text['params'] = $params;


      $this->view($this->isPage, $this->model->text);
    }




    public function douploadbilds($params = ''){
        if(Main::bot()) return $this->content("_404");
        if($p = $this->model->frmUploadBild($_FILES, $_POST)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }

    public function douploadFrmAvatar($params = ''){
        if(Main::bot()) return $this->content("_404");
        if($p = $this->model->frmUploadAvatar($_FILES, $_POST)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }


    public function finfosUpdate($params = ''){
      if(Main::bot()) return $this->content("_404");
      $data = json_decode(file_get_contents("php://input"));
      if($p = $this->model->firmaEinrichtungEdit($data->doit)){
           http_response_code(200);
           echo json_encode($p);
       }else{
           http_response_code(200);
           echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
       } 
   }

    public function ansprechUpdate($params = ''){
      if(Main::bot()) return $this->content("_404");
      $data = json_decode(file_get_contents("php://input"));
      if($p = $this->model->firmAnsprechEdit($data->doit)){
          http_response_code(200);
          echo json_encode($p);
      }else{
          http_response_code(200);
          echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
      } 
    }

    
    public function uploadvid($params = ''){
        if(Main::bot()) return $this->content("_404");
        if($p = $this->model->frmUploadVid($_FILES, $_POST)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }

    


    public function douploadWall($params = ''){
        if(Main::bot()) return $this->content("_404");
        if($p = $this->model->frmUploadWall($_FILES, $_POST)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }


    public function delfrmlogo($params = ''){
      if(Main::bot()) return $this->content("_404");
      $data = json_decode(file_get_contents("php://input"));
      if($p = $this->model->deletefrmlogo($data->doit)){
           http_response_code(200);
           echo json_encode($p);
       }else{
           http_response_code(200);
           echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
       } 
    }


    public function delfrmwall($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->deletefrmwall($data->doit)){
             http_response_code(200);
             echo json_encode($p);
         }else{
             http_response_code(200);
             echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
         } 
    }


    public function delfrmavatar($params = ''){
      if(Main::bot()) return $this->content("_404");
      $data = json_decode(file_get_contents("php://input"));
      if($p = $this->model->deletefrmavatar($data->doit)){
          http_response_code(200);
          echo json_encode($p);
      }else{
          http_response_code(200);
          echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
      } 
    }


    public function delansprbild($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));

        if($p = $data->doit){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
    }



    }

 

}
?>