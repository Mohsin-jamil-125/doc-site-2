<?php
/**
 * ====================================================================================
 *                           PRESTBIT UG (c) Alen O. Raul
 * ----------------------------------------------------------------------------------
 * @copyright Created by PRESTBIT UG. If you have downloaded this
 *  but not from author or received it from third party, then you are engaged
 *  in an illegal activity. 
 *  You must delete this immediately or contact the legal author / owner for a proper
 *  license. More infos at:  https://www.prestbit.de.
 *
 *  Thank you :)
 * ====================================================================================
 *
 * @author PRESTBIT UG (https://www.prestbit.de)
 * @link https://www.prestbit.de 
 * @license https://www.prestbit.de/license
 * @package Doc-Site
 */

namespace App\includes;
use App\includes\library\IpnListener;


/*
########################
If you are a coder you know what to do with this !!!

SGVsbG8gY29sbGVhZ3VlISAKSWYgeW91IHJlYWQgdGhlc2UgbGluZXMgaXQgbWVhbnMgdGhhdCB5b3Ugd2lsbCBjb250aW51ZSB0byB3b3JrIG9uIHRoaXMgc2NyaXB0IG9yIHRoYXQgeW91IHdhbnQgdG8gd29yay4KT2YgY291cnNlIHlvdSB3ZXJlIHRvbGQgdGhhdCB5b3Ugd2lsbCBoYXZlIGEgbG90IG9mIHByb2plY3RzIHRvIGRvIGFuZCB0aGF0IHRoZXkgYXJlIGV4Y2VsbGVudCBwZW9wbGUgYW5kIHRoYXQgdGhleSB3aWxsIHBheSBldmVyeSBwZW5ueSAuLi4uCkkgd29ya2VkIGFsb25lIGRheSBhbmQgbmlnaHQgYW5kIHdva2UgdXAgd2l0aCBhIGJpZyBraWNrIGluIHRoZSBhc3MuIFRha2UgY2FyZSEKSSBjYW4ndCB0ZWxsIHlvdSB0aGUgd2hvbGUgc3RvcnkgaGVyZSwgYnV0IEkgd2FybmVkIHlvdSEKU3VjY2VzcyE=

########################
*/

class Tools{
  protected static $named = "Controller", $sandbox=FALSE;
  public $jwtkey = JWTKEY;
  private static $iss = DOMAIN;
  private static $aud = DOMAIN;
  private static $iat;
  private static $nbf;
  private static $exp;

  /**
  * Parse URL
  * @param 
  * @return array
  */
  public static function parseUrl(){
    if(isset($_GET) && isset ($_GET['url'])){
      $_GET = array_map("self::clean", $_GET);
      return explode("/", filter_var(rtrim($_GET['url'], "/" ), FILTER_SANITIZE_URL));
    }
  }
  /**
  * Parse View
  * @param string
  * @return array
  * @model home/homeView.php || something/somethig.php
  */
  public static function parseView($view){
    if($view){
      $viewArray = explode('/', $view);
      if(count($viewArray) == 3 && $viewArray[0] && $viewArray[1]) return $viewArray[0].'/'.$viewArray[1].'.php';

      return $viewArray[0] ? $viewArray[0].'/'.$viewArray[0].'View.php' : false;
    }
    return false;
  }
  /**
  * Parse Layout
  * @param string
  * @return array
  * @model home/homeView.php || something/somethig.php
  */
  public static function parseLayout($view){
    if($view){
      $viewArray = explode('/', $view);
     // return $viewArray[0] == 'admin' ? 'admin' : $viewArray[0];
       return $viewArray ? $viewArray  : FALSE;
    }
    return false;
  }
  /**
  * Clean a string
  * @param string, cleaning level (1=lowest,2,3=highest)
  * @return cleaned string
  */
  public static function clean($string, $level='1', $chars = FALSE, $leave=""){        
    if(is_array($string)) return array_map("App\includes\Tools::clean", $string);

    $string = preg_replace('/<script[^>]*>([\s\S]*?)<\/script[^>]*>/i', '', $string);     
    switch ($level) {
      case '4':
        $string=strip_tags($string, $leave);    
        $string = filter_var($string, FILTER_SANITIZE_STRING); //preg_replace('/[^a-z]/i','',$string);
        break;
      case '3':
        $string=strip_tags($string,$leave);    
        if($chars) {
          $string = htmlspecialchars($string, ENT_QUOTES | ENT_HTML5,"UTF-8");  
        }
        break;
      case '2':
        $string = strip_tags($string,'<b><i><s><u><strong>');
        break;
      case '1':
        $string = strip_tags($string, '<b><i><s><u><strong><a><pre><code><p><div>'); 
        break;
    }   
    $string=str_replace('href=','rel="nofollow" href=', $string);   
    return $string; 
  }

  /**
  * Do Controller name
  * @param string
  * @return string
  *  //// wie-es-funktioniert str_replace('-', '', $url[0] ==>> WieesfunktioniertController
  */
  public static function controller($controller){
   //  $name =str_replace('#_=_', '', $name); 
  // $kname =  str_replace(array("-", "#_=_", "_"), '', $controller);
    $controller = filter_var($controller, FILTER_SANITIZE_STRING);
    $kname = str_replace('-', '', $controller);
    return ucfirst($kname) . self::$named;
  }



 /**
	* Do JWT Stuff
	**/
	private static function jwtVal($vals = array()){
		$token = array(
			"iss" => self::$iss,
			"aud" => self::$aud,
			"iat" => self::$iat,
			"nbf" => self::$nbf,
			"exp" => self::$exp,
			"data" => $vals
		);
		return $token;
	}
  /**
  * Set JWT Token
  **/
  public static function setJwt($vals = array()){
		self::$iat = time();
		self::$nbf = strtotime("-1 hour");
		self::$exp = strtotime("+24 hours");
		
		return self::jwtVal($vals);
	}
/**
  * Get timeAgo
  */  
  public static function timeAgo($time_ago){
    $time_ago = strtotime($time_ago);
    $cur_time   = time();
    $time_elapsed   = $cur_time - $time_ago;
    $seconds    = $time_elapsed ;
    $minutes    = round($time_elapsed / 60 );
    $hours      = round($time_elapsed / 3600);
    $days       = round($time_elapsed / 86400 );
    $weeks      = round($time_elapsed / 604800);
    $months     = round($time_elapsed / 2600640 );
    $years      = round($time_elapsed / 31207680 );
    // Seconds
    if($seconds <= 60){
        return "just now";
    }else if($minutes <=60){
        if($minutes==1){
            return e('vor einer Minute');
        }
        else{
            return e('vor')." $minutes ".e('Minuten');
        }
    }else if($hours <=24){
        if($hours==1){
            return e('Vor einer Stunde');
        }else{
            return e('vor')." $hours ".e('Stunden');
        }
    }else if($days <= 7){
        if($days==1){
            return e('gestern');
        }else{
            return e('vor')." $days ".e('Tagen');
        }
    }else if($weeks <= 4.3){
        if($weeks==1){
            return e('vor einer Woche');
        }else{
            return e('vor')." $weeks ".e('Wochen');
        }
    }else if($months <=12){
        if($months==1){
            return e('vor einem Monat');
        }else{
            return e('vor')." $months ".e('Monaten');
        }
    }else{
        if($years==1){
            return e('vor einem Jahr');
        }else{
            return e('vor')." $years ".e('Jahren');
        }
    }
} 


public static function dateTomorow($d, $oldDate = FALSE){
  $timestamp = strtotime($d);
  if(!$oldDate){
    return date('d', $timestamp);
  }
  
  $nowDate = date('d', $timestamp);

  return  $nowDate-$oldDate >=1 ? true : false;
}


/**
* Convert a timestap into timeago format
* @param time
* @return timeago
*/  
public static function timeagoFormat($time){
  $time=strtotime($time);
  $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
  $lengths = array("60","60","24","7","4.35","12","10");
  $now = time();
    $difference = $now - $time;
    $tense= "ago";
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
      $difference /= $lengths[$j];
    }
    $difference = round($difference);
    if($difference != 1) {
      $periods[$j].= "s";
    }
  return "$difference $periods[$j] $tense ";
} 


  /**
  * Get Facebook Likes
  * @param Facebook page
  * @return number of likes
  */   
  public static function facebook_likes(){    
      $json_url ='https://graph.facebook.com/'.PAGE_DOC_FB.'?access_token=EAAKqQaKCSm4BACcmCtavT2NQWZCqDMtMZAWT9x0LqJIRVHV4qgaVzrNfzXNsmNZBQnno5OBaTsZBHSapJyVwTu4UVs4ljZCpXFcBBqoae6BwGtFDPLY4KehZB1AMJrrVsVsYVvSEX0vlP1DsWBXQ5gCIf8TLHmD7H343LRYRpXFbGAkTeGIShn&fields=fan_count';
      $json = file_get_contents($json_url);
      $json_output = json_decode($json);
    
      //Extract the likes count from the JSON object
      if($json_output->fan_count){
        return $json_output->fan_count;
      }else{
        return 0;
      }

  }


/**
* Get Twitter followers
* @param Twitter profile
* @return number of followers
*/ 
  public static function twitter_followers($url){
    if(preg_match('((http://|https://|www.)twitter.+[\w-\d]+/(.*))', $url,$id)) {
        $id = $id[2];
        $content = json_decode(@file_get_contents("https://api.twitter.com/1/users/lookup.json?screen_name=$id"),TRUE);
      return $content[0]["followers_count"];
    }
  }	


  /**
	 * Membership Payment - Paypal
	 **/
	public static function payPayPal($array=array()){
    if(!$array || empty($array)){
      return false;
    }

    if(!$paypal_email = Main::config("paypal_email")){
      return false;
    }
    // Generate Paypal link
		  $options = array(
				"cmd"=>"_xclick",
				"business"=>$paypal_email,
        "item_number"=>$array['payId'],
   			"currency_code"=>Main::config("currency"),
   			"item_name"=>Main::config("title") ." ".$array['plan'],
   			"custom" => json_encode($array['usrid']),
   			"amount"=>$array['amount'],
        "return"=>Main::href("paypal/verify"),
        "notify_url"=>Main::href("paypal/ipn"),
        "cancel_return"=>Main::href("paypal/cancel")
		  );

		// Build Query
		if(Main::config("paypal_sandbox") == 1){
			$paypal_url="https://www.sandbox.paypal.com/cgi-bin/webscr?";
		}else{
			$paypal_url="https://www.paypal.com/cgi-bin/webscr?";
		}
    $q = http_build_query($options);
    $paypal_url=$paypal_url.$q;

	  return $paypal_url;
		exit;
	}	
	/**
	 * Verify PayPal Payment
	 **/		
	public static function verify(){
		if(Main::config("paypal_sandbox") == 2){
			self::$sandbox = TRUE;
		} 

	//	if($this->id == "cancel") return Main::redirect("user/",array("warning",e("Your payment has been canceled.")));

   	// instantiate the IPN listener  update
    //include(ROOT.'/includes/library/Paypal.class.php');
    $listener = new IpnListener();

    // tell the IPN listener to use the PayPal test sandbox
    $listener->use_sandbox = self::$sandbox;

    // try to process the IPN POST
    try {
      $listener->requirePostMethod();
      $verified = $listener->processIpn();   
    } catch (Exception $e) {
      error_log($e->getMessage());
      // return Main::redirect("/",array("danger",e("An error has occurred. Your payment could not be verified. Please contact us for more info.")));
      return "An error has occurred. Your payment could not be verified. Please contact us for more info.";
    }
    // If Verified Purchase
    if ($verified){		    
    	// if($this->id==md5($this->config["security"]."yearly")){
    	// 	$expires=date("Y-m-d H:i:s", strtotime("+1 year"));
    	// 	$info["duration"]="1 Year";
    	// }else{
    	// 	$expires=date("Y-m-d H:i:s", strtotime("+1 month"));
    	// 	$info["duration"]="1 Month";
    	// }
    	// if(isset($_POST["custom"])){
    	// 	$data=json_decode($_POST["custom"]);
    	// 	$this->userid=$data->userid;
    	// }
    	// // Save info for future needs
    	// if(isset($_POST["pending_reason"])){
    	// 	$info["pending_reason"]=$_POST["pending_reason"];
    	// }
    	// $info["payer_email"]=$_POST["payer_email"];
    	// $info["payer_id"]=$_POST["payer_id"];
    	// $info["payment_date"]=$_POST["payment_date"];

    	// $insert=array(
    	// 	":date" =>"NOW()",
    	// 	":tid" =>$_POST["txn_id"],
    	// 	":amount" => $_POST["mc_gross"],
    	// 	":status" => $_POST["payment_status"],
    	// 	":userid" => $this->userid,
    	// 	":expires"=>$expires,
    	// 	":info"=>json_encode($info)
    	// 	);
    	
    	// // Update database
    	// if($this->db->insert("payment",$insert) && $this->db->update("user",array("last_payment"=>"NOW()","expires"=>$expires,"membership"=>"pro"),array("id"=>$this->userid))){
    	// 	Main::redirect(Main::href("user/settings","",FALSE),array("success",e("Your payment was successfully made. Thank you.")));
    	// }else{
    	// 	Main::redirect(Main::href("user/settings","",FALSE),array("danger",e("An unexpected issue occurred. Please contact us for more info.")));
    	// }


    }
    // Return to settings page
   // return Main::redirect(Main::href("user/settings","",FALSE),array("danger",e("An unexpected issue occurred. Please contact us for more info.")));
   return false;
	}


  /**
* Create suggestion Email for Agent
**/
public static function createMailSuggestionUser($vorname, $name, $mails) {   
  $nml=null;
    $vorname = preg_replace('/\s+/', '', $vorname);
    $name = preg_replace('/\s+/', '', $name);

    if(strlen($vorname) <= 3 && strlen($name) >= 4){
        $nml = strtolower($vorname[0].".".$name)."@".DOMAIN; 
    }elseif(strlen($vorname) >= 4 && strlen($name) <= 3){
        $nml = strtolower($vorname.".".$name)."@".DOMAIN;
    }elseif(strlen($vorname) >= 4 && strlen($name) >= 4){
      $nml = strtolower($vorname.".".$name)."@".DOMAIN;  
    }else{
      $words  = explode(' ', strtolower($vorname.$name));
      $longestWordLength = 0;
      $longestWord = '';
    
      foreach ($words as $word) {
      if (strlen($word) > $longestWordLength) {
          $longestWordLength = strlen($word);
          if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $word)){
              $longestWord =preg_replace('/\s\s+/', '', $word);
          }else{ $longestWord = null; }
        }
      } 
      $nml = !empty($longestWord) ? preg_replace('/\s\s+/', '', $longestWord)."@".DOMAIN : null;  
    }
    usleep(150);

    if($nml && !empty($nml)){
      if (in_array($nml, $mails)) {
          return strtolower($vorname[0].".".$name).date("m")."@".DOMAIN; 
        }else{
          return $nml;
        }
    }else{
      return false;
    } 
  
}


/**
* Create suggestion Email for Agent
**/
public function createMailSuggestionFirm($id, $mails = FALSE) {   
  $nml=null;
  if($mails && $numeFrm = $this->db->get("firmen", array("select"=>"compname", "where"=>array("usid"=>$id), "limit"=>1, "return_type"=>"single"))){
    $mails = preg_replace('/[^a-zA-z0-9.@]/','~', $mails);
    $usernameparts = array_filter(explode(" ", strtolower($numeFrm['compname']))); 
    $username_parts = array_slice($usernameparts, 0, 2);
    
    if(strlen($username_parts[0]) <= 3 && strlen($username_parts[1]) >= 4){
        $nml = "agent.".$username_parts[1]."@".DOMAIN; 
    }elseif(strlen($username_parts[0]) >= 4 && strlen($username_parts[1]) <= 3){
        $nml = "agent.".$username_parts[0]."@".DOMAIN;
    }elseif(strlen($username_parts[0]) >= 4 && strlen($username_parts[1]) >= 4){
      $nml = "agent.".$username_parts[0]."-".$username_parts[1]."@".DOMAIN;  
    }else{
      $words  = explode(' ', strtolower($numeFrm['compname']));
      $longestWordLength = 0;
      $longestWord = '';
    
      foreach ($words as $word) {
      if (strlen($word) > $longestWordLength) {
          $longestWordLength = strlen($word);
          if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $word)){
              $longestWord = $word;
          }else{ $longestWord = null; }
        }
      } 
      $nml = !empty($longestWord) ? "agent.".$longestWord."@".DOMAIN : null;  
    }
    usleep(150);
    $listingMails =  explode("~", $mails);
    if($nml && !empty($nml)){
    if (in_array($nml, $listingMails)) {
        return "agent.".$username_parts[0].date("m")."@".DOMAIN; 
      }else{
        return $nml;
      }
    }else{
      return false;
    } 
  }
  return false;    
}



}
?>