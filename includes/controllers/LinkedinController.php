<?php
namespace App\includes\controllers;
use App\includes\Controller;

use GuzzleHttp\Client;

class LinkedinController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
    $this->config = $config;
    $this->db = $db;

    $this->model = $this->model('Linkedin', $config, $db);
  }

  public function index($params = ''){
      $params = func_get_args();
      $this->model->text['params'] = $params;

      //Step 1 = Get the access code
      // Access ->  https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=78d8f003tog4p3&redirect_uri=https://doc-site.de/linkedin&scope=r_emailaddress,r_liteprofile,w_member_social
    // $this->model->checkLinkedin();

    //Step 2 = Get the profile Id
    //$this->model->checkLinkedinApi();

    //Step 3 = TEST
    //$this->model->shareTest();

  }
 

   




  
}