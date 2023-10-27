<?php
namespace App\includes\models;
use GuzzleHttp\Client;

use App\includes\Model;



class Linkedin extends Model  {

  protected $config = [], $db;
  public $text = [];
  protected $socialsConfig;

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
  }


  public function checkLinkedin(){
    try {
        $client = new Client(['base_uri' => 'https://www.linkedin.com']);
        $response = $client->request('POST', '/oauth/v2/accessToken', [
            'form_params' => [
                    "grant_type" => "authorization_code",
                    "code" => $_GET['code'],
                    "redirect_uri" => REDIRECT_URL,
                    "client_id" => CLIENT_ID,
                    "client_secret" => CLIENT_SECRET,
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        $access_token = $data['access_token']; // store this token somewhere

        echo "Access   tt  ".$access_token;
    } catch(Exception $e) {
        echo $e->getMessage();
    }

  }


  public function checkLinkedinApi(){

    $access_token = 'AQVcr2ZtyKY9X0Tti0gUrXiAK38fY4_wi7_pEwMgHfAlQhG-k5MJIMZbqz_YKy340On3_9qPBCMIOtXcEseI19D5jbY3PYkMbBKpZmu07f9nvtaTmoB86smzA6OwdrPYCEUulezq_k83UTsFVCuf6Eudg0GlISDXzyjVPMSbzksolA8rydrEh1IyjzViuF_SGP4QQPer1SalGZIKtPB2825VomyxVzmMo2xYJsRROXhyysxDrOG7Pn8rwVp1ZeYRyHQ0sM_V7KJjuMCrrqTQim2YHTqj0kBrE2zCZ-W8aZv2WKz7CqWT2SEe_PztcQhQg53jxzkvThIXeK9W8CCRDBfHFJbL3g';
    try {
        $client = new Client(['base_uri' => 'https://api.linkedin.com']);
        $response = $client->request('GET', '/v2/me', [
            'headers' => [
                "Authorization" => "Bearer " . $access_token,
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        $linkedin_profile_id = $data['id']; // store this id somewhere

        echo "PROFILE.:   ".$linkedin_profile_id;
    } catch(Exception $e) {
        echo $e->getMessage();
    }


  }



  public function shareTest(){

      $link = 'https://doc-site.de/einzelheiten/SFUyNCtnMDJiQTNNbnpsRmxHcDh1dz09';
      $access_token = LINKEDIN_SHARE_ACCESS_TOKEN;
      $linkedin_id = LINKEDIN_SHARE_ID;
      $body = new \stdClass();
      $body->content = new \stdClass();
      $body->content->contentEntities[0] = new \stdClass();
      $body->text = new \stdClass();
      $body->content->contentEntities[0]->thumbnails[0] = new \stdClass();
      $body->content->contentEntities[0]->entityLocation = $link;
      $body->content->contentEntities[0]->thumbnails[0]->resolvedUrl = "https://doc-site.de/static/uploads/anzeige/36/1652789004_SALINEN.JPG";
      $body->content->title = 'Assistenzarzt (m/w/d) in der Orthopädie od. Physikalischen u. Rehabilitativen Medizin';
      $body->owner = 'urn:li:person:'.$linkedin_id;
      $body->text->text = 'Wir suchen ab sofort, in unserer  Salinen Klinik, einen Assistenzart (m/w/d) in der Orthopädie oder Physikalischen und Rehabilitativen Medizin in Voll- oder Teilzeit.';
      $body_json = json_encode($body, true);
        
      try {
          $client = new Client(['base_uri' => 'https://api.linkedin.com']);
          $response = $client->request('POST', '/v2/shares', [
              'headers' => [
                  "Authorization" => "Bearer " . $access_token,
                  "Content-Type"  => "application/json",
                  "x-li-format"   => "json"
              ],
              'body' => $body_json,
          ]);
        
          if ($response->getStatusCode() !== 201) {
              echo 'Error: '. $response->getLastBody()->errors[0]->message;
          }
        
          echo 'Post is shared on LinkedIn successfully.';
      } catch(Exception $e) {
          echo $e->getMessage(). ' for link '. $link;
      }


  }



     



}
