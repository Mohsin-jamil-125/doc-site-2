<?php
namespace App\includes\library;

class cPanelApi {
    
    public function __construct($cpanelUrl, $cpaneluser, $cpanelPwd, $cpanelPort = '2083') {
        $this->cPanelUser = $cpaneluser;
        $this->cPanelPwd = $cpanelPwd;
        $this->cPanelUrl = $cpanelUrl;
        $this->cPanelPort = $cpanelPort;
    }



    /////////// EMAIL ///////////////

   
    public function createEmail($usermail, $password, $quota = '0') {

      $func = "https://$this->cPanelUrl:$this->cPanelPort/execute/Email/add_pop?email=$usermail&password=$password&quota=$quota&domain=$this->cPanelUrl&send_welcome_email=1";
      return $this->exe_cpanel($func);
      
      }
      
      public function deleteEmail($usermail) {
  
      $func = "https://$this->cPanelUrl:$this->cPanelPort/execute/Email/delete_pop?email=$usermail&domain=$this->cPanelUrl";
      return $this->exe_cpanel($func);
      
      }
      
      public function listEmail($usermail = '') {
  
      $func = "https://$this->cPanelUrl:$this->cPanelPort/execute/Email/list_pops?regex=$usermail";
      return $this->exe_cpanel($func);
      
      }
      
      
      public function setPasswordEmail($usermail, $password) {
  
      $func = "https://$this->cPanelUrl:$this->cPanelPort/execute/Email/passwd_pop?email=$usermail&password=$password&domain=$this->cPanelUrl";
      return $this->exe_cpanel($func);
      
      }
      
      
      public function addSpamFilter($email, $score = '8.0') {
  
      $func = "https://$this->cPanelUrl:$this->cPanelPort/execute/Email/add_spam_filter?required_score=$score&account=$email";
      return $this->exe_cpanel($func);
      
      }
      
      public function addForwarder($usermail, $emailfwd) {
  
      $func = "https://$this->cPanelUrl:$this->cPanelPort/execute/Email/add_forwarder?domain=$this->cPanelUrl&email=$usermail%40$this->cPanelUrl&fwdopt=fwd&fwdemail=$emailfwd";
      return $this->exe_cpanel($func);
      
      }
      
      public function suspendEmail($email) {
  
      $func = "https://$this->cPanelUrl:$this->cPanelPort/execute/Email/suspend_login?email=$email";
      return $this->exe_cpanel($func);
      
      }
      
      public function unsuspendEmail($email) {
  
      $func = "https://$this->cPanelUrl:$this->cPanelPort/execute/Email/unsuspend_login?email=$email";
      return $this->exe_cpanel($func);
      
      }
     
      
      /////////// END EMAIL /////////////// 


      private function exe_cpanel($func = '') {
        $query = $func;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($curl, CURLOPT_HEADER,0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        $header[0] = "Authorization: Basic " . base64_encode($this->cPanelUser.":".$this->cPanelPwd) . "\n\r";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $query);
        $result = curl_exec($curl);
        if ($result == false) {
            error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");   
        }
        curl_close($curl);
        return $result;
    }
    
    

}

?>