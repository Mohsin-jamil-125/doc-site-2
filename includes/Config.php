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


  // header("Content-Security-Policy-Report-Only: default-src 'self' ws: gap: data: 'unsafe-inline' *.googleapis.com *.ggpht.com *.gstatic.com  https://sockjs-eu.pusher.com https://sock66-eu.pusher.com https://js.pusher.com https://www.google.com/ https://maps.google.com/ https://lh3.googleusercontent.com https://cdnjs.cloudflare.com https://docsite.b-cdn.net; object-src 'none';report-uri https://csp.doc-site.de");

  header("Access-Control-Allow-Origin: https://doc-site.de");
  header("Access-Control-Allow-Methods: *");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


   // Define domain name and ROOT   
   defined("ROOT") or define("ROOT", dirname(dirname(__FILE__)));
   defined("_VERSION") or define("_VERSION", "1.0");
   defined("APP") or define("APP", 123);
   defined("DOMAIN") or define("DOMAIN", "doc-site.de");
   
  // Define session name & jwt sess name
   defined("_SESSION_NAME") or define("_SESSION_NAME", "__doc_site");
   defined("_SESSION_JWT") or define("_SESSION_JWT", "_doc_site");
   defined("_SESSION_ENCRYPT_KEY") or define("_SESSION_ENCRYPT_KEY", "z%Kq!OasDCmaQws2@9oL!oOdkhollyhb3#ar#");
   defined("JWTKEY") or define('JWTKEY', "B#&Eaw(!H+MdcM!ThQaZxC87YtlkUWmZq4t7w!z%C*F-J@Mh");

   defined("MODELS") or define("MODELS", ROOT."/includes/models/");
   defined("CONTROLLERS") or define("CONTROLLERS", ROOT."/includes/controllers/");
   defined("VIEWS") or define("VIEWS", ROOT."/includes/views/");
   defined("MIDDLEWARES") or define("MIDDLEWARES", ROOT."/includes/middlewares/");

   defined("HEIDEL_PASS") or define('HEIDEL_PASS', 's-priv-2a10cuvMLUeY1iKpLR0V3OPWe16kbCqI');

   defined("SOCIALAUTH") or define("SOCIALAUTH", ROOT."/includes/library/hybridauth/");
   defined("YOUTUBEAUTH") or define("YOUTUBEAUTH", ROOT."/includes/library/client_secret_416484406276-5t9po3mc7nd5fet3pbkpdk11sqcrlfo7.apps.googleusercontent.com.json");

   

   defined("UPLOAD_PATH_ANSPRECHPARTNERS") or define("UPLOAD_PATH_ANSPRECHPARTNERS", "/static/images/ansprechpartners/");
   defined("UPLOAD_PATH_AVATARS") or define("UPLOAD_PATH_AVATARS", "/static/images/users/");
   defined("UPLOAD_PATH_USRVIDEO") or define("UPLOAD_PATH_USRVIDEO", "/static/images/videos/");
   defined("UPLOAD_PATH_LEBENS") or define("UPLOAD_PATH_LEBENS", "/static/uploads/lebens/");
   defined("UPLOAD_PATH_OPTS") or define("UPLOAD_PATH_OPTS", "/static/uploads/opts/");
   defined("UPLOAD_PATH_VIDEO") or define("UPLOAD_PATH_VIDEO", "/static/video/");
   defined("UPLOAD_PATH_VIDEOFIRM") or define("UPLOAD_PATH_VIDEOFIRM", "/static/videofirms/");

   defined("UPLOAD_PATH_LOGO") or define("UPLOAD_PATH_LOGO", "/static/uploads/firmen/");
   defined("UPLOAD_PATH_WALL") or define("UPLOAD_PATH_WALL", "/static/uploads/walls/");

   defined("UPLOAD_PATH_ANZEIGE") or define("UPLOAD_PATH_ANZEIGE", "/static/uploads/anzeige/");

   


   // OAuth & site configuration 
  defined("OAUTH_CLIENT_ID") or define("OAUTH_CLIENT_ID", "416484406276-5t9po3mc7nd5fet3pbkpdk11sqcrlfo7.apps.googleusercontent.com");
  defined("OAUTH_CLIENT_SECRET") or define("OAUTH_CLIENT_SECRET", "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");

  defined("DOC_APIUS_SECRET") or define("DOC_APIUS_SECRET", "vwegdwvs");
  defined("DOC_API_SECRET") or define("DOC_API_SECRET", "(D23XhWst;6Ty5");

  defined("DOC_FB") or define("DOC_FB", "1043607999910618");
  defined("PAGE_DOC_FB") or define("PAGE_DOC_FB", "114529817914502");

  defined("ANALTIC") or define("ANALTIC", "static/analitics.txt");

  defined("GOOGLEKY") or define("GOOGLEKY", "AIzaSyDcY59sqo5tvLGntVxFtRZM8zy5lyHInoY");


 define('CLIENT_ID', '78d8f003tog4p3');
 define('CLIENT_SECRET', 'VUBlrom0sATjCFDQ');
 define('REDIRECT_URL', 'https://doc-site.de/linkedin');
 define('SCOPES', 'r_emailaddress,r_liteprofile,w_member_social');

 defined("LINKEDIN_SHARE_ACCESS_TOKEN") or define("LINKEDIN_SHARE_ACCESS_TOKEN", "AQVcr2ZtyKY9X0Tti0gUrXiAK38fY4_wi7_pEwMgHfAlQhG-k5MJIMZbqz_YKy340On3_9qPBCMIOtXcEseI19D5jbY3PYkMbBKpZmu07f9nvtaTmoB86smzA6OwdrPYCEUulezq_k83UTsFVCuf6Eudg0GlISDXzyjVPMSbzksolA8rydrEh1IyjzViuF_SGP4QQPer1SalGZIKtPB2825VomyxVzmMo2xYJsRROXhyysxDrOG7Pn8rwVp1ZeYRyHQ0sM_V7KJjuMCrrqTQim2YHTqj0kBrE2zCZ-W8aZv2WKz7CqWT2SEe_PztcQhQg53jxzkvThIXeK9W8CCRDBfHFJbL3g");
 defined("LINKEDIN_SHARE_ID") or define("LINKEDIN_SHARE_ID", "YWkEdaKC2b");


 
 


// Database Configuration
  $dbinfo = array (
    "dbHost" => 'localhost',     
    "dbName" => 'u571197530_doc',            
    "dbUsername" => 'root',        
    "dbPassword" => '',  
    "dbPort" => '',
    "dbcharset" => 'utf8mb4', 
    "dbPrefix" => ''  
  );

  $config = array(
    "home/index"=>array(
      "js"=>"",
      "css"=>""
    ),
    "home/indexmap"=>array(
      "js"=>"plugins/map/jquery.axgmap.js",
      "css"=>"",
      "cdncss"=>false,
      "cdnjs"=>'https://maps.google.com/maps/api/js?key='.GOOGLEKY
    ),

    "home/stellenangebote"=>array(
      "js"=>"plugins/maps-google/markerclusterer.js;js/stellenangebote.js",
      "css"=>"",
      "cdncss"=>false,
      "cdnjs"=>'https://maps.google.com/maps/api/js?key='.GOOGLEKY
    ),

    "home/anmeldung"=>array(
      "js"=>"js/appjs.js",
      "css"=>""
    ),
    "home/registrieren"=>array(
      "js"=>"js/appjs.js",
      "css"=>""
    ),
    "home/vergessen"=>array(
      "js"=>"js/vergessen.js",
      "css"=>""
    ),
    "home/recover"=>array(
      "js"=>"js/recover.js",
      "css"=>""
    ),
    "home/neupasswort"=>array(   
      "js"=>"js/neupasswort.js",
      "css"=>""
    ),
    "home/sofortbewerben"=>array(   
      "js"=>"js/sofortbewerben.js",
      "css"=>""
    ),
    "home/kontaktkoo"=>array(   
      "js"=>"",
      "css"=>"",
      "cdncss"=>false,
      "cdnjs"=>'https://www.google.com/recaptcha/api.js'
    ),
    "home/einzelheiten"=>array(   
      "js"=>"",
      "css"=>""
    ),
    "home/covid"=>array(   
      "js"=>"plugins/cropper/cropper.min.js;js/covid.js",
      "css"=>"plugins/cropper/cropper.min.css"
    ),
    "kandidat/willkommen"=>array(
      "js"=>"js/willkommen.js",
      "css"=>""
    ),
    "kandidat/profilanlegen"=>array(
      "js"=>"plugins/jquery-validation/dist/jquery.validate.min.js;plugins/bootstrap-wizard/jquery.bootstrap.wizard.js;js/profilanlegen.js",
      "css"=>"plugins/forn-wizard/css/material-bootstrap-wizard.css;plugins/forn-wizard/css/demo.css"
    ),
    "kandidat/testprofilanlegen"=>array(
      "js"=>"plugins/jquery-validation/dist/jquery.validate.min.js;plugins/bootstrap-wizard/jquery.bootstrap.wizard.js;js/testprofilanlegen.js",
      "css"=>"plugins/forn-wizard/css/material-bootstrap-wizard.css;plugins/forn-wizard/css/demo.css"
    ),
    "kandidat/dashprofil"=>array(
      "js"=>"plugins/cropper/cropper.min.js;js/dashprofil.js",
      "css"=>"plugins/cropper/cropper.min.css"
    ),
    "kandidat/einstellungen"=>array(
      "js"=>"js/einstellungen.js",
      "css"=>""
    ),
    "kandidat/loeschenkonto"=>array(
      "js"=>"js/kontoloeschen.js",
      "css"=>""
    ),
    "kandidat/liebste"=>array(
      "js"=>"plugins/maps-google/markerclusterer.js;js/liebste.js",
      "css"=>"",
      "cdncss"=>false,
      "cdnjs"=>'https://maps.google.com/maps/api/js?key='.GOOGLEKY
    ),
    "kandidat/warnungen"=>array(
      "js"=>"plugins/datatable/jquery.dataTables.min.js;plugins/datatable/dataTables.bootstrap4.min.js;js/frmsalerts.js",
      "css"=>"plugins/datatable/dataTables.bootstrap4.min.css;plugins/datatable/jquery.dataTables.min.css",
    ),
    "firma/meineanzeigen"=>array(
      "js"=>"js/anzeige.js",
      "css"=>"",
    ),
    "firma/rechnungen"=>array(
      "js"=>"plugins/datatable/jquery.dataTables.min.js;plugins/datatable/dataTables.bootstrap4.min.js;js/rechnungen.js",
      "css"=>"plugins/datatable/dataTables.bootstrap4.min.css;plugins/datatable/jquery.dataTables.min.css",
    ),
    "firma/kandidatenalerts"=>array(
      "js"=>"plugins/datatable/jquery.dataTables.min.js;plugins/datatable/dataTables.bootstrap4.min.js;js/kandidatenalerts.js",
      "css"=>"plugins/datatable/dataTables.bootstrap4.min.css;plugins/datatable/jquery.dataTables.min.css",
    ),
    "firma/nachrichten"=>array(
      "js"=>"js/nachrichten.js",
      "css"=>"",
    ),
    "firma/neuenachricht"=>array(
      "js"=>"js/neuenachricht.js",
      "css"=>"",
    ),
    "firma/kandidaten"=>array(
      "js"=>"plugins/maps-google/markerclusterer.js;js/kandidaten.js",
      "css"=>"",
      "cdncss"=>false,
      "cdnjs"=>'https://maps.google.com/maps/api/js?key='.GOOGLEKY
    ),
    "firma/anzeige"=>array(
      "js"=>"plugins/input-mask/jquery.maskedinput.js;plugins/jquery-inputmask/jquery.inputmask.bundle.min.js;js/anzeige.js",
      "css"=>""
    ),
    "firma/firmanlegen"=>array(
      "js"=>"plugins/jquery-validation/dist/jquery.validate.min.js;plugins/bootstrap-wizard/jquery.bootstrap.wizard.js;js/firmanlegen.js",
      "css"=>"plugins/forn-wizard/css/material-bootstrap-wizard.css;plugins/forn-wizard/css/demo.css"
    ),
    "firma/companyprofil"=>array(
      "js"=>"plugins/cropper/cropper.min.js;js/companyprofil.js",
      "css"=>"plugins/cropper/cropper.min.css"
    ), 
    "firma/companyeinstellungen"=>array(
      "js"=>"js/frmsettsings.js",
      "css"=>""
    ), 
    "firma/zahlen"=>array(
      "js"=>"js/zahlen.js",
      "css"=>"",
      "cdncss"=>"https://static.unzer.com/v1/heidelpay.css",
      "cdnjs"=>"https://static.unzer.com/v1/heidelpay.js"
    ), 
    "firma/bezahlung"=>array(
      "js"=>"js/bezahlung.js",
      "css"=>""
    ),
    "firma/companyanzeigen"=>array(   
      "js"=>"plugins/wysiwyag/jquery.richtext.js;js/formeditor.js;plugins/date-picker/spectrum.js;plugins/date-picker/jquery-ui.js;plugins/input-mask/jquery.maskedinput.js;plugins/select2/select2.full.min.js;plugins/bootstrap-datepicker/bootstrap-datepicker.js;plugins/cropper/cropper.min.js;js/companyanzeigen.js",
      "css"=>"plugins/wysiwyag/richtext.css;plugins/time-picker/jquery.timepicker.css;plugins/select2/select2.min.css;plugins/date-picker/spectrum.css;plugins/cropper/cropper.min.css;plugins/bootstrap-datepicker/bootstrap-datepicker.css"
    ),
    "firma/editeinzelheiten"=>array(   
      "js"=>"plugins/wysiwyag/jquery.richtext.js;js/formeditor.js;plugins/date-picker/spectrum.js;plugins/date-picker/jquery-ui.js;plugins/input-mask/jquery.maskedinput.js;plugins/select2/select2.full.min.js;plugins/bootstrap-datepicker/bootstrap-datepicker.js;plugins/cropper/cropper.min.js;js/editanzeigen.js",
      "css"=>"plugins/wysiwyag/richtext.css;plugins/time-picker/jquery.timepicker.css;plugins/select2/select2.min.css;plugins/date-picker/spectrum.css;plugins/cropper/cropper.min.css;plugins/bootstrap-datepicker/bootstrap-datepicker.css"
    ),
    "firma/editeinzell"=>array(   
      "js"=>"plugins/wysiwyag/jquery.richtext.js;js/formeditor.js;plugins/date-picker/spectrum.js;plugins/date-picker/jquery-ui.js;plugins/input-mask/jquery.maskedinput.js;plugins/select2/select2.full.min.js;plugins/bootstrap-datepicker/bootstrap-datepicker.js;plugins/cropper/cropper.min.js;js/editanzeigen.js",
      "css"=>"plugins/wysiwyag/richtext.css;plugins/time-picker/jquery.timepicker.css;plugins/select2/select2.min.css;plugins/date-picker/spectrum.css;plugins/cropper/cropper.min.css;plugins/bootstrap-datepicker/bootstrap-datepicker.css"
    ),
    "firma/neueransprechpartner"=>array(
      "js"=>"plugins/jquery-validation/dist/jquery.validate.min.js;plugins/bootstrap-wizard/jquery.bootstrap.wizard.js;plugins/cropper/cropper.min.js;js/neueransprechpartner.js",
      "css"=>"plugins/forn-wizard/css/material-bootstrap-wizard.css;plugins/cropper/cropper.min.css;plugins/forn-wizard/css/demo.css"
    ),
    "firma/editansprechpartner"=>array(
      "js"=>"plugins/jquery-validation/dist/jquery.validate.min.js;plugins/bootstrap-wizard/jquery.bootstrap.wizard.js;plugins/cropper/cropper.min.js;js/editansprechpartner.js",
      "css"=>"plugins/forn-wizard/css/material-bootstrap-wizard.css;plugins/cropper/cropper.min.css;plugins/forn-wizard/css/demo.css"
    ),
    "firma/ansprechpartner"=>array(
      "js"=>"plugins/cropper/cropper.min.js;js/ansprechpartner.js",
      "css"=>"plugins/cropper/cropper.min.css"
    ),
    "firma/loeschencompany"=>array(
      "js"=>"js/loesaccount.js",
      "css"=>""
    ),


    "admin/dashboard"=>array(  
      "js"=>"plugins/chart/Chart.bundle.js;plugins/morris/raphael-min.js;plugins/morris/morris.js;plugins/chart/utils.js;js/admin-custom.js",
      "css"=>""
    ),
    "admin/api"=>array(
      "js"=>"js/admin-custom.js;js/webadmin.js",
      "css"=>""
    ),
    "admin/websettings"=>array(
      "js"=>"js/admin-custom.js;js/webadmin.js",
      "css"=>""
    ),
    "admin/payment"=>array(
      "js"=>"js/admin-custom.js;js/webadmin.js",  
      "css"=>""
    ),
    "admin/angeboten"=>array(
      "js"=>"js/admin-custom.js;js/webadmin.js",  
      "css"=>""
    ),
    "admin/adminprofile"=>array(
      "js"=>"js/admin-custom.js",
      "css"=>""
    ),
    "admin/webtranslation"=>array(
      "js"=>"plugins/datatable/jquery.dataTables.min.js;plugins/datatable/dataTables.bootstrap4.min.js;js/admin-custom.js;js/webtranslation.js",
      "css"=>"plugins/datatable/dataTables.bootstrap4.min.css;plugins/datatable/jquery.dataTables.min.css",
      "cdncss"=>false,
      "cdnjs"=>false
    ),
    "admin/pricelabels"=>array(
      "js"=>"js/admin-custom.js",
      "css"=>""
    ),
    "admin/invoice"=>array(
      "js"=>"js/admin-custom.js",
      "css"=>""
    ),
    "admin/adminkandidaten"=>array(
      "js"=>"plugins/datatable/jquery.dataTables.min.js;plugins/datatable/dataTables.bootstrap4.min.js;js/admin-custom.js",
      "css"=>"plugins/datatable/dataTables.bootstrap4.min.css;plugins/datatable/jquery.dataTables.min.css",
    ),
    "admin/adminunternehmen"=>array(
      "js"=>"plugins/datatable/jquery.dataTables.min.js;plugins/datatable/dataTables.bootstrap4.min.js;js/admin-custom.js",
      "css"=>"plugins/datatable/dataTables.bootstrap4.min.css;plugins/datatable/jquery.dataTables.min.css",
    ),
    "admin/adminstellen"=>array(
      "js"=>"plugins/datatable/jquery.dataTables.min.js;plugins/datatable/dataTables.bootstrap4.min.js;js/admin-custom.js",
      "css"=>"plugins/datatable/dataTables.bootstrap4.min.css;plugins/datatable/jquery.dataTables.min.css",
    ),
    "admin/adminbewerbt"=>array(
      "js"=>"plugins/datatable/jquery.dataTables.min.js;plugins/datatable/dataTables.bootstrap4.min.js;js/admin-custom.js",
      "css"=>"plugins/datatable/dataTables.bootstrap4.min.css;plugins/datatable/jquery.dataTables.min.css",
    ),

    "admin/invoiceslist"=>array(
      "js"=>"plugins/datatable/jquery.dataTables.min.js;plugins/datatable/dataTables.bootstrap4.min.js;js/admin-custom.js",
      "css"=>"plugins/datatable/dataTables.bootstrap4.min.css;plugins/datatable/jquery.dataTables.min.css",
    ),

    "admin/addslist"=>array(
      "js"=>"js/admin-custom.js",
      "css"=>""
    ),

    "admin/newadds"=>array(
      "js"=>"js/admin-custom.js",
      "css"=>"plugins/select2/select2.min.css;plugins/wysiwyag/richtext.css"
    ),

    "admin/firmenprofil"=>array(
      "js"=>"js/admin-custom.js",
      "css"=>""
    ),

    "admin/editfirmenprofil"=>array(
      "js"=>"plugins/select2/select2.full.min.js;js/admin-custom.js",
      "css"=>"plugins/select2/select2.min.css"
    ),   
    "admin/webseitenansicht"=>array(                        
      "js"=>"js/admin-custom.js",
      "css"=>"",
    ),

    "admin/email"=>array(
      "js"=>"js/admin-custom.js",
      "css"=>""
    ),
    "admin/emailvorlagen"=>array(   
      "js"=>"plugins/select2/select2.full.min.js;js/admin-custom.js",
      "css"=>"plugins/select2/select2.min.css"
    ),
    "admin/achat"=>array(
      "js"=>"js/admin-custom.js",
      "css"=>""
    ),
    "admin/newemail"=>array(
      "js"=>"js/admin-custom.js",
      "css"=>""
    ),
    "_404"=>array(
      "js"=>"",
      "css"=>""
    ),
    "accessLevel"=>array(
      "open"=>10,
      "admins"=>1,
      "firma"=>2,
      "kandidat"=>3,
      "register"=>20    
    ),
    'socialsAuth' => array(   
      'callback' => 'https://doc-site.de/socials',
      'providers' => array(
            'Google' => array(
              'enabled' => true,
              'keys' => array('key' => '416484406276-54jfu4ou8q3g02n8h75ifb8pob3s1pct.apps.googleusercontent.com', 'secret' => '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')
            ),
            'LinkedIn' => array(
              'enabled' => true,
              'keys' => array('key' => '86hocga6ikl0ko','secret' => '9rbSTGTSs4mk9ory'),
              "scope" => "r_liteprofile r_emailaddress"
            ),
            'Facebook' => array(
              'enabled' => true,
              'keys' => array('id'=>'1043607999910618', 'secret'=>'dd8bfd9025c5bbbaeba45ffc44326643')
            ),
            "Instagram" => array (  
              "enabled" => true,
              "keys" => array ( "key" => "1876376442551816", "secret" => "ace37750cc01a4cc7eecee5a9fd30623"),
              "scope" => "user_profile, user_media"
            ),
            "Xing" => array (  
              "enabled" => true,
              "keys" => array ( "key" => "fvaefra", "secret" => "fwefwef"),
              "scope" => ""
            ),

      )
    ),
    "timezone" => "Europe/Berlin",
    "locale"=>"DE",
    "mod_rewrite" => TRUE,
    "gzip" => TRUE,
    "security" => 'khgjDCkjYI^vu7tu75o@3##fRyo!.ByDocSite',	  
    "public_token" => 'a28d820693e38f97dc6bdocsitebdbcac0f54e9',   
    "origin_check" => TRUE,  
    "resultLimit"=>10,
    "debug" => 2,   // Enable debug mode (outputs errors) - 0 = OFF, 1 = Error message, 2 = Error + Queries
    "demo" => 0 // Demo mode
  );


// Include core.php
require_once ('Core.php');
?>