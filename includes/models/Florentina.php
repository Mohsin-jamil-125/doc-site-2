<?php

class Dashboard  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['admins'];

    $this->text = ["title" => "Dashboard", "pgname" => "Dashboard"];


  }



  public function checkIsLogged(){
    $check = new Middleware($this->config, $this->db);
    $this->user = $check->checkLevel($this->accessLevel);

    $this->text['u'] = $this->user;

  }



}
