<?php
$cas_host = 'cas.nwpu.edu.cn';

// CAS Server 路径
$cas_context = '/cas';

// CAS server.端口
$cas_port = 80;

// // phpCAS simple client //

// import phpCAS lib
include_once('cas/CAS.php');

//phpCAS::setDebug();

// initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// no SSL validation for the CAS server
phpCAS::setNoCasServerValidation();

phpCAS::traceBegin();
phpCAS::handleLogoutRequests(false, false);
// force CAS authentication
phpCAS::forceAuthentication();

$cas_auth = phpCAS::checkAuthentication();
//phpCAS::traceEnd('auth'.$cas_auth);
$_SESSION['cas_auth']=$cas_auth;
// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().

// logout if desired
function casout() {
  //$param=array("service"=>"http://10.22.36.146:801/qd/user.php");
  //phpCAS::logout($param);
    $param=array("service"=>"http://localhost/public/");
  phpCAS::logout($param);
  session_destroy();
  echo '<script>url="user.php";window.location.href=url;</script> ';
  exit;
}
  $_SESSION['user_id'] = phpCAS::getUser();
//echo phpCAS::getUser();
/*
if ($cas_auth) {
  $result = getSql("SELECT * FROM u where _id='".phpCAS::getUser()."'");

  if(count($result)>0)
  {
    $_SESSION['user_name'] = $result[0]['name'];
  } else {
    $_SESSION['user_name'] = "Unknown";
  }
}
// for this test, simply print that the authentication was successfull
*/