<?php
namespace App\includes;
use App\includes\Forms;
use App\includes\Main;
use App\includes\library\Mobile_Detect;


/*
########################
If you are a coder you know what to do with this !!!

SGVsbG8gY29sbGVhZ3VlISAKSWYgeW91IHJlYWQgdGhlc2UgbGluZXMgaXQgbWVhbnMgdGhhdCB5b3Ugd2lsbCBjb250aW51ZSB0byB3b3JrIG9uIHRoaXMgc2NyaXB0IG9yIHRoYXQgeW91IHdhbnQgdG8gd29yay4KT2YgY291cnNlIHlvdSB3ZXJlIHRvbGQgdGhhdCB5b3Ugd2lsbCBoYXZlIGEgbG90IG9mIHByb2plY3RzIHRvIGRvIGFuZCB0aGF0IHRoZXkgYXJlIGV4Y2VsbGVudCBwZW9wbGUgYW5kIHRoYXQgdGhleSB3aWxsIHBheSBldmVyeSBwZW5ueSAuLi4uCkkgd29ya2VkIGFsb25lIGRheSBhbmQgbmlnaHQgYW5kIHdva2UgdXAgd2l0aCBhIGJpZyBraWNrIGluIHRoZSBhc3MuIFRha2UgY2FyZSEKSSBjYW4ndCB0ZWxsIHlvdSB0aGUgd2hvbGUgc3RvcnkgaGVyZSwgYnV0IEkgd2FybmVkIHlvdSEKU3VjY2VzcyE=

########################
*/

class Controller{

	protected $isPage = FALSE;
	protected $logged = FALSE;
	protected $admin = FALSE, $userid = "0";



  public function model($model, $config, $db){

	  $class = __NAMESPACE__ . '\\models\\' . $model;
  
    return new $class($config, $db);
  }


  public function view($view, $params = []){   
	  
		$viewLayout = Tools::parseLayout($view);
	 	if($viewLayout[0] == "web"){
			include(ROOT."/themes/docsite/docsite.php");
			die();
		}
		
		$ogPara = array_key_exists('anz', $params) ? $params['anz'] : "";

		Main::canonicalhref('/'.$viewLayout[1],  $ogPara);

		$this->header($viewLayout[0]);

		$content = $viewLayout[0] === 'admin' ? '' : $this->top($params);

	  	if($wig = $this->db->getWigs($view)){
				asort($wig);
				foreach($wig as $key => $val){
				if($val >= 1)	$content .=	Forms::FormsHtml($key, $params);
				}
			} 


		if(!$view = Tools::parseView($view)) Main::redirect("https://google.com", [], "", TRUE);
		if(!file_exists(VIEWS .$view)){
			Main::redirect("https://google.com", [], "", TRUE);
			die();
		}

		if(!isset($_SESSION['isMobile'])){
			$detectMobile = new Mobile_Detect;
			if($detectMobile->isMobile()){
				$_SESSION['isMobile'] = 'y';
			}else{
				$_SESSION['isMobile'] = 'n';
			}
	  }

		require_once(VIEWS .$view);
			
    $params = array_key_exists("datain", $params) ? $params['datain'] : null;
		if(array_key_exists("u", $this->model->text)){
			$params['unique_key'] = $this->model->text['u']['unique_key']; // means that is looged in  ['datain']
	  }  
		$this->footer($viewLayout[0], $params); 
  }

	/**
* Get Template from Templates
**/

  protected function t($template){
    if(!file_exists(TEMPLATE."/$template.php")) $template="404";
    return TEMPLATE."/$template.php";
 	} 


/**
* Get Top Header
**/
protected function top($menulinks = false){
	$top = Main::websettings("topbar");
	
	return Forms::FormsHtml(__FUNCTION__, array("top"=>$top, "menulinks"=>$menulinks));
}
/**
* Get and Prepare the Header
**/
protected function header($layout, $wellcomeMsg = "welcomeby"){
	$layouts = $layout === 'admin' ? '_admin' : '';
	if($cdna = Main::resources($this->config, $this->config["theme"].$layouts, "css")){
    if($cdna){
				$styles = explode(';', $this->config[$cdna]);
				foreach($styles as $css){ 
					Main::cdncss($css);
				}
		}
	}

 	if($this->isPage && (isset($this->config[$this->isPage]['css']) || isset($this->config[$this->isPage]['cdncss']))){
	  $css = $this->config[$this->isPage]["css"]?explode(';', $this->config[$this->isPage]["css"]):null;
		$csscdn = array_key_exists("cdncss", $this->config[$this->isPage]) && isset($this->config[$this->isPage]["cdncss"]) ? explode(';', $this->config[$this->isPage]["cdncss"]):null;
 	}


	// $analitics=''; 
  // if($ga = $this->db->get("thegoogle", array('select'=>'valls', 'where'=>array('config'=>'analitics'),  'limit'=>1, 'return_type'=>'single'))){
	// 	$analitics .= $ga['valls'];
  // } 

	global $wellcome;
	$wellcome.= $wellcomeMsg;
	if($layout === 'admin')	{
		include($this->t('headeradmin'));  
	}else{
		include($this->t(__FUNCTION__));
	}
}
/**
* Get and Prepare the Footer
**/
protected function footer($layout, $params = null){
	$layouts = $layout === 'admin' ? '_admin' : '';

		if($cdna = Main::resources($this->config, $this->config["theme"].$layouts, "js")){
			if($cdna){
					$javascripts = explode(';', $this->config[$cdna]);
					foreach($javascripts as $js){ 
						if(strpos($js, "_")){
							$jas = explode('_', $js);
							Main::cdn($jas[0], $jas[1]); /* ex.: jquery_3.5.1 */
						}else{
							Main::cdn($js, null);
						}
					}
			}
		} 

		if($this->isPage && (isset($this->config[$this->isPage]['js']) || isset($this->config[$this->isPage]['cdnjs']))){

			if($javacdn = isset($this->config[$this->isPage]['cdnjs']) && !empty($this->config[$this->isPage]['cdnjs']) ? explode(';', $this->config[$this->isPage]['cdnjs']) : false){
				foreach($javacdn as $js){  
					if($js) Main::add($js);
				}
			}

			if($javas = isset($this->config[$this->isPage]['js']) && !empty($this->config[$this->isPage]['js']) ? explode(';', $this->config[$this->isPage]['js']) : false){
				foreach($javas as $js){  
					if($js) Main::add($this->config['url']."/static/".$js);
				}
			}

			
			
		}
     
		
			
		if($params){ 
			$someScript = $params;
		}else{
			$someScript =null;
		}

		if($layout === 'admin')	{
				include($this->t('footeradmin'));  
		}else{
				include($this->t(__FUNCTION__));
		}
	}
		
		
		public function check(){ 
			if($info=Main::user()){
				if($user = $this->db->get("allusers", array('where'=>array('crt'=>$info[0], 'unique_key'=>$info[2]),'limit'=>1,'return_type'=>'object'))){
					$this->user = $user;								
					$this->userid = $this->user->crt;
					$this->user->membership = !empty($user->membership) ? $user->membership : null;
					$this->user->avatar = "https://www.gravatar.com/avatar/".md5(trim($this->user->usermail))."?s=150";		
	
					return $this->user;
				}
			}
			return FALSE;
		}


		public function content(){
			header("Location: https://google.com");
			die();
		}


}
?>