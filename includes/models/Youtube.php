<?php
namespace App\includes\models;
use App\includes\Model;

require_once 'includes/library/youtube/vendor/autoload.php';


class Youtube extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];


  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];

    $this->text =  ["title" => "TTB tesst", "color" => "#dark"];

   // $this->ytb();
  }


  public function ytb(){


    $client = new Google_Client();
    $client->setClientId("416484406276-5t9po3mc7nd5fet3pbkpdk11sqcrlfo7.apps.googleusercontent.com"); 
    $client->setClientSecret("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"); 
   // $client->setAuthConfig(__DIR__ . 'includes/library/client_secret_416484406276-5t9po3mc7nd5fet3pbkpdk11sqcrlfo7.apps.googleusercontent.com.json');
    $client->setScopes('https://www.googleapis.com/auth/youtube'); 
    $client->setRedirectUri('https://doc-site.de/youtube'); 


    // Define an object that will be used to make all API requests. 
    $youtube = new Google_Service_YouTube($client); 

    // Check if an auth token exists for the required scopes
    $tokenSessionKey = 'token-' . $client->prepareScopes();
    if (isset($_GET['code'])) {
      $client->authenticate($_GET['code']);
      $_SESSION[$tokenSessionKey] = $client->getAccessToken();
    }

    if (isset($_SESSION[$tokenSessionKey])) {
      $client->setAccessToken($_SESSION[$tokenSessionKey]);
    }


    if ($client->getAccessToken()) {

      try{
        $videoPath = 'Prestbithosting.mp4';
    
        $snippet = new Google_Service_YouTube_VideoSnippet();
        $snippet->setTitle('Title Of Video');
        $snippet->setDescription('Description Of Video');
        $snippet->setTags(array('Tag 1', 'Tag 2'));
        $snippet->setCategoryId(22);
    
        // Numeric video category. See
        // https://developers.google.com/youtube/v3/docs/videoCategories/list
        $snippet->setCategoryId("22");
    
        // Set the video's status to "public". Valid statuses are "public",
        // "private" and "unlisted".
        $status = new Google_Service_YouTube_VideoStatus();
        $status->privacyStatus = "private";
    
        // Associate the snippet and status objects with a new video resource.
        $video = new Google_Service_YouTube_Video();
        $video->setSnippet($snippet);
        $video->setStatus($status);
    
        // Specify the size of each chunk of data, in bytes. Set a higher value for
        // reliable connection as fewer chunks lead to faster uploads. Set a lower
        // value for better recovery on less reliable connections.
        $chunkSizeBytes = 1 * 1024 * 1024;
        
        // Setting the defer flag to true tells the client to return a request which can be called
        // with ->execute(); instead of making the API call immediately.
        $client->setDefer(true);
    
        // Create a request for the API's videos.insert method to create and upload the video.
        $insertRequest = $youtube->videos->insert("status,snippet", $video);
        
        // Create a MediaFileUpload object for resumable uploads.
        $media = new Google_Http_MediaFileUpload(
            $client,
            $insertRequest,
            'video/*',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($videoPath));
        
        
        // Read the media file and upload it chunk by chunk.
        $status = false;
        $handle = fopen($videoPath, "rb");
        while (!$status && !feof($handle)) {
          $chunk = fread($handle, $chunkSizeBytes);
          $status = $media->nextChunk($chunk);
        }
        fclose($handle);
        
        // If you want to make other calls after the file upload, set setDefer back to false
        $client->setDefer(false);
                
        // Delete video file from local server
        // @unlink("videos/".$videoData['file_name']);
        
        // uploaded video data
        $videoTitle = $status['snippet']['title'];
        $videoDesc = $status['snippet']['description'];
        $videoTags = implode(",",$status['snippet']['tags']);
        $videoId = $status['id'];
        
        // uploaded video embed html
        $youtubeURL = 'https://youtu.be/'.$videoId; /// aici este save la DB
        $htmlBody .= "<p class='succ-msg'>Video Uploaded to YouTube</p>";
        $htmlBody .= '<embed width="400" height="315" src="https://www.youtube.com/embed/'.$videoId.'"></embed>';
        $htmlBody .= '<ul><li><b>YouTube URL: </b><a href="'.$youtubeURL.'">'.$youtubeURL.'</a></li>';
        $htmlBody .= '<li><b>Title: </b>'.$videoTitle.'</li>';
        $htmlBody .= '<li><b>Description: </b>'.$videoDesc.'</li>';
        $htmlBody .= '<li><b>Tags: </b>'.$videoTags.'</li></ul>';
        $htmlBody .= '<a href="logout.php">Logout</a>';
    
      } catch (Google_Exception $e) {
        $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
            htmlspecialchars($e->getMessage()));
        $htmlBody .= 'Please reset session <a href="logout.php">Logout</a>';
      }


    }else{
      return false; /// NU AM ACCESS FARA AUTORIZATIE
    }





  }




  public function videoToYT(){





  }






















}


