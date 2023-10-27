<?php
namespace App\includes\models;
use App\includes\Model;

include SOCIALAUTH.'autoload.php';

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;
use Hybridauth\Storage\Session;

class Socials extends Model  {

  protected $config = [], $db;
  public $text = [];
  protected $socialsConfig;

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->socialsConfig = $this->config['socialsAuth'];
  }


  public function checkSocials(){
      try {
          $hybridauth = new Hybridauth($this->socialsConfig);

          $storage = new Session();

          if (isset($_GET['provider'])) {
            $strProvider = filter_var($_GET['provider'], FILTER_SANITIZE_STRING);
            $storage->set('provider', $strProvider);
          }
      
          if ($provider = $storage->get('provider')) {
              $hybridauth->authenticate($provider);
              $storage->set('provider', null);
          }
      
          HttpClient\Util::redirect('https://doc-site.de/anmeldung/socials');
      } catch (Exception $e) {
          echo $e->getMessage();
      }

  }

    

     



}
