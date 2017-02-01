<?php
$cas_host = 'cas.nwpu.edu.cn';

// CAS Server 路径
$cas_context = '/cas';

// CAS server.端口
$cas_port = 80;

// // phpCAS simple client //

// import phpCAS lib 
include_once('CAS.php');

phpCAS::setDebug();

// initialize phpCAS 
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context, false);

// no SSL validation for the CAS server 
phpCAS::setNoCasServerValidation();

// force CAS authentication 
phpCAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server 
// and the user's login name can be read with phpCAS::getUser().

// logout if desired 
if (isset($_REQUEST['logout'])) {

 

 $param=array("service"=>"http://localhost/Phpcasclient1/example_simple.php");//退出登录后返回

 phpCAS::logout($param);

}

// for this test, simply print that the authentication was successfull 
?> 
<html>   <head>     <title>phpCAS simple client</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8">    </head>   <body>     
<h1>Successfull Authentication!这是客户端1</h1>     
<p>the user's login is <b><?php echo phpCAS::getUser(); ?></b>.</p>     
<p>phpCAS version is <b><?php echo phpCAS::getVersion(); ?></b>.</p>      
<p><a href="http://192.168.18.8:8989/Casclient1/index.jsp">去java客户端1</a></p>     
 <p><a href="?logout=">退出</a></p>   </body> 
 
 </html>