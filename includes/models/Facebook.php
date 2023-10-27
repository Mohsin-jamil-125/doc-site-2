<?php
namespace App\includes\models;
use App\includes\Model;


class Facebook extends Model  {

  protected $config = [], $db;
  public $text = [];
  protected $socialsConfig;

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
  }


  public function checkFacebook(){

    // $fb = new \Facebook\Facebook([
    //   'app_id' => '750148829465198',
    //   'app_secret' => '52c8d2a7090a78fb173e15de38e5b8de',
    //   'default_graph_version' => 'v2.2',
    //  ]);


    //     //Post property to Facebook
    //       $linkData = [
    //         'link' => 'https://doc-site.de/einzelheiten/SFUyNCtnMDJiQTNNbnpsRmxHcDh1dz09',
    //         'message' => 'Wir suchen ab sofort, in unserer  Salinen Klinik, einen Assistenzart (m/w/d) in der Orthopädie oder Physikalischen und Rehabilitativen Medizin in Voll- oder Teilzeit.'
    //       ];
    //       $pageAccessToken ='EAAKqQaKCSm4BACcmCtavT2NQWZCqDMtMZAWT9x0LqJIRVHV4qgaVzrNfzXNsmNZBQnno5OBaTsZBHSapJyVwTu4UVs4ljZCpXFcBBqoae6BwGtFDPLY4KehZB1AMJrrVsVsYVvSEX0vlP1DsWBXQ5gCIf8TLHmD7H343LRYRpXFbGAkTeGIShn';
          
    //       try {
    //         $response = $fb->post('/me/feed', $linkData, $pageAccessToken);
    //       } catch(Facebook\Exceptions\FacebookResponseException $e) {
    //         echo 'Graph returned an error: '.$e->getMessage();
    //         exit;
    //       } catch(Facebook\Exceptions\FacebookSDKException $e) {
    //         echo 'Facebook SDK returned an error: '.$e->getMessage();
    //         exit;
    //       }
    //       $graphNode = $response->getGraphNode(); 


  }


  // $pageAccessToken ='EAAKqQaKCSm4BAEZC3DkoB5Tdu9S81VyZBe9FZB0io4RRFZBUfdvLzvPcGn7AMhZA42WNZAEkqSLVrEaeBg1eEBh5qtxZCIm3iQO70xGxbpjk1QmvezRhyBsKcQJr4SX9vVab2cvoMi179c7pgxOlKd6c3v5HehFSCuRPAgaN7kWPAZDZD';

    

     



}
