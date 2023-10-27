<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Stellenangebote extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];


  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open']; 

    $this->users = Main::user();

   $this->text = ["title" => e("Stellenangebote"), "color" => "#dark", "modal"=>["titel"=>e("Leider haben wir aktuell keine passende Stellenanzeigen für")."<br/><span>".e("Ihre Suchanfrage")."</span>", "overbutton"=>e("Verpassen Sie keine neu veröffentlichen Stellenangebote zu Ihrer Suche"),"button"=>e("Neue Stellenangebote per Email erhalten"), "img"=>"jobAlertResults.svg"],
   "jobsperemail"=>["titel"=>e("Jobs per Email erhalten"), "untertitel"=>e("Aktivieren Sie jetzt Ihren Job-Alert für:"), "whhr"=>"stellenangebote"],
   "joberr"=>["titel"=>e('Jobs per Email erhalten'), "untertitel"=>e('Wählen Sie Ihr Berufsfeld und Position!'), "whhr"=>"stellenangebote"]];

   Main::pagetitle($this->text['title']);
  }


 
    public function doGetClients($params = false){
      if($params){
          header("Content-Type: application/json; charset=UTF-8");

          // if(!$user = Main::user()){
          //   return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          // }

          // if(!$thisUser = $this->prufUser($frm['ai_token'])){
          //   return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          // } 'where'=>array('anzeigen.usid'=>$user[0]), 

  
         if( !$allUsers = $this->db->get("anzeigen",  
          array("select"=>"anzeigen.crt, anzeigen.titel, anzeigen.filiale, anzeigen.city, anzeigen.derbundes, anzeigen.latit, anzeigen.longit, anzeigen.berufsfeld, firmen.frmname",  "join"=>array(
          array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"anzeigen.usid"))
          ), 'where-not'=>array('anzeigen.latit'=>''),  'return_type'=>'all')) ){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>'No Results!!!')));
          }

          foreach($allUsers as $k => $val){
            if($val['filiale'] == '') { $allUsers[$k]['filiale'] =  $val['frmname']; }

            $allUsers[$k]['beruf'] = Main::returnValue('dropdowns', 'berufsfeld', $val['berufsfeld']);

            $allUsers[$k]['crt'] = Main::encrypt($val['crt']);
           }

           
          return array("user_error"=>1, "user_reason"=>1, "msg"=>$allUsers); 
      }else{
        //return array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr");
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }
    }


    public function getAjaxAnzeigen($params = ''){
      $info = Main::user();

      $filtr =[]; $theFiltersConditions=[];  $bundesCodes = []; $relev=1;
      if($params){
        $coords=FALSE;
        $sucheDocTops = isset($params->sucheDocTops) ? $params->sucheDocTops : FALSE; /// ""  Augenarzt etc meserii
        $sucheOrtTops = isset($params->sucheOrtTops) ? $params->sucheOrtTops : FALSE; // localitatea
        $fachcoords = isset($params->sucheCoordinates) ? json_decode($params->sucheCoordinates, true) : FALSE; // localitatea
        $berufkreis = isset($params->berufwahl) ? $params->berufwahl : FALSE; // kreis 5 10 50 100 200

        $stellberufwahl = isset($params->stellberufwahl) ? $params->stellberufwahl : FALSE;  /// ""  dokmedic helferin 
        $positiones = isset($params->positiones) ? $params->positiones : FALSE;
        $fachbereich = isset($params->subjectfilter) ? $params->subjectfilter : FALSE;

        $einrichtung = isset($params->stll_einrichtung) ? $params->stll_einrichtung : FALSE;  /// alle  klinik reha-klinic MVZ
        $vetrag = isset($params->stll_vetrag) ? $params->stll_vetrag : FALSE;  /// alleVertrag befristed
 
        $art = isset($params->stll_art) ? $params->stll_art : FALSE;   /// alleanstellung  Teilzeit vollzeit
        $region = isset($params->stll_reg) ? $params->stll_reg : FALSE; // Bundesweit  Berlin Rheinlan

        $relev = isset($params->stll_relev) ? $params->stll_relev : 1; 

        if($berufkreis && (int)$berufkreis >= 1){    // Array ( [country_code] => DE [city_name] => Bonn [latitude] => 50.73438 [longitude] => 7.09548 )
          if(trim($sucheOrtTops) && !empty($sucheOrtTops)){
            $coords = $this->geoFinder($sucheOrtTops);
          }elseif(!$sucheOrtTops){
            if(!$fachcoords){
              $coords = $this->geoFinder();  // get it by IP address
            }else{
              $coords = array('country_code'=>'', 'city_name'=>'', 'latitude'=>str_replace(",",".", $fachcoords['latitude']), 'longitude'=>str_replace(",",".", $fachcoords['longitude']));
            }
          }
        }

        if($stellberufwahl){  // dropdown
          $theFiltersConditions['berufsfeld'] = $stellberufwahl;
        }
        /// "sucheDocTops":"","berufwahl":"0","sucheOrtTops":"","sucheCoordinates":"","stellberufwahl":"dokmedic"
        if(trim($sucheOrtTops) && !empty($sucheOrtTops)){  // Orasul ales TOP  
          if(!$coords){
            $theFiltersConditionsCIty['city'] = str_replace("%20", " ", $sucheOrtTops);
          }
        }

        if(trim($sucheDocTops) && !empty($sucheDocTops)){  // Doctor Arzt etc
          $theFiltersConditionsNames['titel'] = $sucheDocTops;
        }

        if(Main::checkCheckuri($positiones, ['allposits', 'allemfaberufe', 'Alle Krankenpﬂege-Berufe', 'Alle Altenpﬂege-Berufe', 'Alle therapeutischen Berufe', 'Alle pharmazeutischen Berufe'])){  //  
          $filtr['positionen'] = $positiones;
        }

        if(Main::checkCheckuri($fachbereich, ['allefache'])){    
          $filtr['fachbereich'] = $fachbereich;
        }

        if(Main::checkCheckuri($einrichtung, "alle")){   
          $filtr['frmeinricht'] = $einrichtung;
        }

        if(Main::checkCheckuri($vetrag, "alleVertrag")){  // vertrag
          $filtr['vertrag'] = $vetrag;
        }
 
        
        if(Main::checkCheckuri($art, "alleanstellung")){  // anstellung   
          $filtr['arts'] = $art;
        }
    
        if(Main::checkCheckuri($region, "Bundesweit")){  // region
          $filtr['derbundes'] = $region;
        }  


        $conditions = array('return_type'=>'all');
        if($theFiltersConditions && !empty($theFiltersConditions)){ $conditions['where'] = $theFiltersConditions; }
        if($sucheDocTops && !empty($sucheDocTops)){ $conditions['where-like'] = $theFiltersConditionsNames; }
        if($sucheOrtTops && !empty($theFiltersConditionsCIty)){ $conditions['where-like'] = $theFiltersConditionsCIty; }
        if($relev == 2){$conditions['order_by'] = 'art DESC';}

        if($aallAnz = $this->db->get("anzeigen_view", $conditions)){
            if($params && $filtr){
              foreach($aallAnz as $key => $user){
                foreach($filtr as $k => $val) { 
                  if($k == 'derbundes'){ 
                    foreach($region as $val){
                      array_push($bundesCodes, Main::derbundes($val, true));
                    }
                    foreach($aallAnz as $key => $val){
                      if (!in_array($val['derbundes'], $bundesCodes)){ 
                        unset($aallAnz[$key]);
                      }
                    }
                  }else{
                    if (!in_array($user[$k], $filtr[$k])){ 
                      unset($aallAnz[$key]);
                    }
                  }
                }
              }
            }

            //  $sucheDocTops
            if($coords){
              foreach($aallAnz as $key => $val){
                if (!$az = $this->checkRadius($val['art'], str_replace(",",".", $coords['latitude']), str_replace(",",".", $coords['longitude']), $berufkreis)){   
                  unset($aallAnz[$key]); // pirmasens 5km
                }
              }
            }
            $aallAnz = array_values($aallAnz);

            foreach($aallAnz as $key => $val){
              if($lbs = $this->db->get('bewerbeliste', array('select'=>'crt', 'where'=>array('stellen'=>$val['art']), 'limit'=>1, 'return_type' =>'single'))){    
                $aallAnz[$key]['bwrt'] = $lbs['crt'];
              }
            }

            $aallAnz = $this->responseAjaxAnzeigen($aallAnz, $info, $sucheOrtTops);
            if($relev == 3){ 
              usort($aallAnz, function($a,$b){
                return $b['beliebt'] <=> $a['beliebt'];
              });
            }
          return $aallAnz;
        }
       return false;
      } 
      return false;
    }


    public function responseAjaxAnzeigen($aallAnz, $info, $sucheOrtTops){    
      foreach($aallAnz as $key => $val){
        $aallAnz[$key]['starts'] = Main::unidate($val['starts']);
        $aallAnz[$key]['unterText'] = e('Unternehmenprofil');

        $aallAnz[$key]['unterORRRTTTT'] = $sucheOrtTops;

        $aallAnz[$key]['fachbereich'] = '/static/images/svg/job.svg"/> '.Main::relreturnValue($val['berufsfeld'], "subjectjob", $val['fachbereich']);  /// berufsfeld   "dokmedic"

        $aallAnz[$key]['teest'] = isset($aallEinricht) ? $aallEinricht : "Este googl";
        $aallAnz[$key]['derbundes'] = $val['derbundes'] == "BS" ? e('Bundesweit') : $val['derbundes'];


        if($val['filiale'] == '') { $aallAnz[$key]['filiale'] =  $val['frmname']; } 
        $aallAnz[$key]['beruf'] = Main::returnValue('dropdowns', 'berufsfeld', $val['berufsfeld']);
        $aallAnz[$key]['aacrt'] = Main::encrypt($val['art']);  /// city


        if(!$val['anzlogo']) { $aallAnz[$key]['anzlogo'] = UPLOAD_PATH_ANZEIGE.'no-avatars/firmen-no-logo.svg';}else{ $aallAnz[$key]['anzlogo'] = UPLOAD_PATH_ANZEIGE.$val['art'].'/'.$val['anzlogo'];  }

        if(isset($val['bwrt']) && $val['bwrt'] >= 1){
          $aallAnz[$key]['bewerbt'] = ['bwerbtClassIcon'=>'fa-check', 'bwerbtStyle'=>'background-color: #fff;color: #e94b3a;',  // $aallAnz[$key]['bewerbt']['bwerbtClassIcon']
        'bwerbtClass'=>'daswerbstes', 'bwerbtText'=>e('Beworben')];
        }else{
          $aallAnz[$key]['bewerbt'] = ['bwerbtClassIcon'=>'fa-angle-right', 'bwerbtStyle'=>'background-color: #e94b3a;color: #fff;', 
        'bwerbtClass'=>'', 'bwerbtText'=>e('Sofort bewerben')];
        }

        if($info){ 
          $art = Main::encrypt($val['art']);
          $ffid = Main::encrypt($val['ffid']);
          $ffrt = Main::encrypt($val['ffrt']); 
          $aallAnz[$key]['city'] = $val['city'].', ';
          $aallAnz[$key]['frmname'] = '<a href="#" class="mr-4" style="color: #00527f;"><span><i class="fa fa-building-o text-muted mr-1"></i><span id="copyCompany">'.$val['frmname'].'</span></span></a>';

          $aallAnz[$key]['positionen'] = '<p class="mb-0 mr-2 leading-tight" style="color: #027aaf;font-size: 16px"><img style="height: 13px!important;" src="/static/images/svg/jobPosition.svg"/> '.Main::relreturnValue($val['berufsfeld'], "positionjob", $val['positionen']).'</p>';
          

          if($lbs = $this->db->get('kandidatliebste', array('select'=>'crt', 'where' => array('usid'=>$info[0], 'liebste'=>$val['art']), 'limit' => 1, 'return_type' => 'single'))){ 
            $lbsCounts = $this->db->get('kandidatliebste', array('select'=>'crt', 'where' => array('liebste'=>$val['art']), 'return_type' => 'count'));   
            $aallAnz[$key]['anzliebste'] = "fa-heart";
          }else{
            $aallAnz[$key]['anzliebste'] = "fa-heart-o"; 
            $lbsCounts = 0;
          }

          $aallAnz[$key]['beliebt'] = $lbsCounts;

          $aallAnz[$key]['logged'] = [ 'linkView'=>['/einzelheiten/'.$art, ''], 'unterProfile'=>['/unternehmenprofil/'.$ffid, ''],
        'llieb'=>'<a href="#" class="liebstesstellen" data-id="'.$art.'-'.$ffrt.'-'.$info[2].'" class="btn mt-3 mt-sm-0"> <i style="color: #00527f;top: 5px;position: relative;" class="fa fa-2x '.$aallAnz[$key]['anzliebste'].' liebstes"></i> </a>',
        'bewerb'=>'<a href="#" class="btn btn-pill mt-3 mr-2 mt-sm-0 '.$aallAnz[$key]['bewerbt']['bwerbtClass'].' dobewerbt" data-bewerben="'.$art.'-'.$ffid.'-'.$info[2].'" style="'.$aallAnz[$key]['bewerbt']['bwerbtStyle'].'border: 2px solid #e94b3a;"><span class="fa '.$aallAnz[$key]['bewerbt']['bwerbtClassIcon'].'"></span> '.$aallAnz[$key]['bewerbt']['bwerbtText'] .'</a>'];
          
        }else{
          $aallAnz[$key]['city'] = '';
          $aallAnz[$key]['frmname'] = '';
          $aallAnz[$key]['derbundes'] = Main::derbundes($val['derbundes']);
          $aallAnz[$key]['positionen'] = '';

          $aallAnz[$key]['logged'] = ['linkView'=>['#', 'liebstesstellen'], 'unterProfile'=>['', 'showUnternProfil'],
          'llieb'=>'<a href="#" class="liebstesstellen"  class="btn mt-3 mt-sm-0"> <i style="color: #00527f;top: 5px;position: relative;" class="fa fa-2x fa-heart-o"></i> </a>',
          'bewerb'=>'<a href="/sofortbewerben/'.Main::encrypt($val['art']).'" class="btn btn-pill mt-3 mt-sm-0" style="background-color: #fff;border: 2px solid #e94b3a;color: #e94b3a;"><span class="fa '.$aallAnz[$key]['bewerbt']['bwerbtClassIcon'].'"></span> '.e('Sofort bewerben').'</a>'];

          if(!$lbsCounts = $this->db->get('kandidatliebste', array('select'=>'crt', 'where' => array('liebste'=>$val['art']), 'return_type' => 'count'))){   
            $lbsCounts = 0;
          }

          $aallAnz[$key]['beliebt'] = $lbsCounts;
        }
      }
      return array_unique($aallAnz, SORT_REGULAR);
    }



    public function stellenAlerts($params = ''){
      if($params){
        header("Content-Type: application/json; charset=UTF-8");  
  
        if(!$this->users && !$this->users[0] >= 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        $kandidateFach = $this->db->get("kandidate", array("select"=>"subjectjob", 'where'=>array('usid'=>$this->users[0]), 'limit'=>1, 'return_type'=>'single'));
        
        $inserat=0;
        $alerte = $this->getFrimAlerts("frmsealerts", $this->users[0], $params);
        if($alerte && count($alerte)){
          foreach($alerte as $key => $val){
            $ins = $this->db->insert("frmsealerts", array("usid"=>$this->users[0], "beruf"=>$val['beruf'], "fach"=>$kandidateFach['subjectjob']));
            $inserat += $ins;
          }
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Job-Alert existiert bereits!'))));
        }
  
        if($inserat && $inserat >= 1){
          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg!")))); 
        }
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }
    }






/*
CREATE VIEW anzeigen_view AS
SELECT anzeigen.crt as art, anzeigen.ansprechs, anzeigen.titel, anzeigen.berufsfeld, anzeigen.positionen, anzeigen.fachbereich, anzeigen.arts, anzeigen.filiale, anzeigen.starts, anzeigen.citycode, anzeigen.city, anzeigen.derbundes, anzeigen.derland, anzeigen.anzlogo, anzeigen.created, firmen.crt as ffrt, firmen.usid as ffid, firmen.frmeinricht, firmen.frmawall, firmen.frmavatar, firmen.frmname, paystats.ison
FROM anzeigen
JOIN firmen ON firmen.usid = anzeigen.usid
JOIN paystats ON paystats.anzid = anzeigen.crt
WHERE paystats.ison >= 3;  */

        

 
       /* $aar = array("select"=>"anzeigen.crt as art, anzeigen.ansprechs, anzeigen.titel, anzeigen.berufsfeld, anzeigen.positionen, anzeigen.fachbereich, anzeigen.arts, anzeigen.filiale, anzeigen.starts, anzeigen.citycode, anzeigen.city, anzeigen.derbundes, anzeigen.derland, anzeigen.anzlogo, anzeigen.created, firmen.crt as ffrt, firmen.usid as ffid, firmen.frmeinricht, firmen.frmawall, firmen.frmavatar, firmen.frmname, paystats.ison", "join"=>array(
        array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"anzeigen.usid")),
        array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt"))
      ), 'big-equal'=>array('paystats.ison'=>3), 'return_type'=>'all'); */




       // if($aallAnz = $this->db->get("anzeigen", array("select"=>"anzeigen.crt as art, anzeigen.ansprechs, anzeigen.titel, anzeigen.berufsfeld, anzeigen.positionen, anzeigen.fachbereich, anzeigen.arts, anzeigen.filiale, anzeigen.starts, anzeigen.citycode, anzeigen.city, anzeigen.derbundes, anzeigen.derland, anzeigen.anzlogo, anzeigen.created, firmen.crt as ffrt, firmen.usid as ffid, firmen.frmawall, firmen.frmavatar, firmen.frmname, paystats.ison", "join"=>array(
      //   array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"anzeigen.usid")),
      //   array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt"))), 'big-equal'=>array('paystats.ison'=>3), 'return_type'=>'all'))){




}