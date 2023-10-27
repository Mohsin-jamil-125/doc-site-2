<?php
/**
 * ====================================================================================
 *                           PRESTBIT UG (c) Alen O. Raul
 * ----------------------------------------------------------------------------------
 * @copyright Created by PRESTBIT UG. If you have downloaded this
 *  but not from author website or received it from third party, then you are engaged
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
 
 
/** 
* Start the session 
*/
use App\includes\session\Session;
use App\includes\Database;
use App\includes\Tools;
use App\includes\Main;

use App\includes\Controller;

$session = new Session([
	'name'   => _SESSION_NAME,
	'domain' => DOMAIN,
	'secure' => true,
	'decoy'  => false,
	'min'    => 60,
	'max'    => 600,
	'debug'  => false,
]);
$session->start();   

	if($config["locale"] == "DE"){
		setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
	}
	
	if($config["gzip"]){
	  ob_start("ob_gzhandler");
	}

	if(!isset($config["secret_key"]) || $config["secret_key"]=="RKEY"){
	  $config["secret_key"]="";
	}

	if(!empty($config["timezone"])){
		date_default_timezone_set($config["timezone"]);
	}

		
	$db = new Database($config, $dbinfo);
	if(!$config = $db->get_config()) die("Error! Contact your web administrator!");	

	$config["smtp"] = json_decode($config["smtp"], TRUE);  
  $config["timezone"] = $config["webgmt"] ? $config["webgmt"] : $config["timezone"];

	if($websettings = json_decode($config['websettings'], true)){
    foreach($websettings['socialsAuth'] as $key => $soc){
      if(!$soc || $soc == "") $config['socialsAuth']['providers'][$key]['enabled'] = false;
    }
  } 

	// Application Main Helper class
	Main::set("config", $config);
	// Template Path
	defined("TEMPLATE") or define("TEMPLATE", ROOT."/themes/{$config["theme"]}");

	if(!isset($config["debug"]) || $config["debug"] == 0) {
		error_reporting(0);
	}else{
		if($config["debug"] == 1) {
			ini_set('display_errors', FALSE);
			ini_set('log_errors', TRUE);
			ini_set('ignore_repeated_errors', TRUE);
			ini_set('error_log', '/home/vwegdwvs/public_html/errlog_log');
			ini_set('display_startup_errors', FALSE);
			error_reporting(E_ALL);
		}elseif($config["debug"] == 2) {
			ini_set('display_errors', TRUE);
			ini_set('log_errors', TRUE);
			ini_set('ignore_repeated_errors', TRUE);
			ini_set('display_startup_errors', TRUE);
			ini_set('error_log', '/home/vwegdwvs/public_html/errlog_log');
			error_reporting(E_ALL);
		}
	}

	
	// Default Language = DE
	$_language = $config["lang"];
	if(isset($_COOKIE["lang"]) && $lng = filter_var($_COOKIE["lang"], FILTER_SANITIZE_STRING)){
		if( is_string($lng) && strlen($lng) == "2"){
		   $_language = $lng;
		}
	} 

	if(isset($_GET["lang"]) && (is_string($_GET["lang"]) && $lng = filter_var($_GET["lang"], FILTER_SANITIZE_STRING))){
		if(strlen($lng) == "2"){
			setcookie("lang", strip_tags($lng), [
				'expires' => strtotime('+30 days'),
				'path' => '/',
				'domain' => DOMAIN,
				'secure' => true,
				'httponly' => true,
				'samesite' => 'None',
			]);
			$_language = strip_tags($lng);
		}	
	}	

	// Get the Language File
	if(isset($_language) && $_language!="en" && file_exists(ROOT."/includes/languages/".Main::clean($_language,3,TRUE).".php")) {
		include(ROOT."/includes/languages/".Main::clean($_language).".php");
		if(isset($lang) && is_array($lang)) {
			Main::set("lang", $lang);
		}
	}

	
	// Read string function
	function e($text){
		return Main::e($text);
	}

	/**
	*  Pusher server func
	*/
	function chat($myEvent, $messg, $batch = FALSE){
		$options = array(
			'cluster' => 'eu',
			'encrypted' => true
			);
			$pusher = new Pusher\Pusher(
			'6d5be3906906ad8b8ca3', //key
			'0e94db7dffc1d0f4b939', //secret
			'1348089', //appId
			$options
			);

			if($batch) {
				if($amsg = $pusher->triggerBatch($batch)){
					return $amsg;
				} 
			}

      if($amsg = $pusher->trigger('doc-site', $myEvent, $messg)){
			 return $amsg;
		  } 
		return false;	
	}
?>
