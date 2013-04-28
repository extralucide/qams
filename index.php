<?php
define('ATOMIK_AUTORUN', false);
include "atomik/index.php";
include "atomik/app/config.php";
/* include "includes/config.php"; */
define('NO_ATOMIK',"yes");
include "atomik/app/includes/Db.class.php";
include "atomik/app/includes/User.class.php";

if (isset($_GET['guest']) ){
	session_start();
	if (isset($_SESSION['current_project_id'])){
		unset($_SESSION['current_project_id']);
		unset($_SESSION['current_project_name']);
		//var_dump($_SESSION);
	}	
	Db::login();
}
$loggedOut = isset($_GET['loggedOut'])?$_GET['loggedOut']:False;
if ($loggedOut == "TRUE") {
	$cookie = isset($_REQUEST['bug_cookie'])?$_REQUEST['bug_cookie']:"";
	//ob_start("manage_log");
	$array=unserialize(stripslashes($cookie));	
	Db::logout(&$array);
	setcookie("bug_cookie", "");
	header('Location: index.php');
}
else if(isset($_POST['login'])) {
/*
 * Just logged in ?
 */
  /* username exists? */
  $username = $_POST['username'];
  $password = $_POST['password'];
  // test username
  if(!empty($username)) {
	Db::login($username, 
			  $password);
  }
  else {	
    // pose probleme avec le header ...
    setcookie("bug_cookie", "");
  }
}
else if (isset($_POST['create_db'])) {
	$db = new Db(false);
	$db->db_create_qams_db();
}
else if (isset($_REQUEST['bug_cookie'])){
	/* If user is logged in, get all user information */
	$array=unserialize(stripslashes($_REQUEST['bug_cookie']));
	$userLogUsername=$array[0];
	$userLogFname=$array[1];
	$userLogLname=$array[2];
	$userLogEmail=$array[3];
	$userIsAdmin=$array[4];
	$userLogLastLogged=$array[5];
	$postName=$array[1]." ".$array[2];
	$userLogID = $array[6];
	$IP = $array[7];
	/* Go to home page */
	header('Location:atomik/index.php?action=home');
}
Db::setOS();
if (Db::getOS() == "iphone"){
	// echo "Vous utilisez un Iphone.";
	$body = "iphone";
}	
else if (Db::getOS() == "unix"){
	// echo "Vous utilisez un système Unix.";
	$body = "body_classic";	
}	
else{	
	$body = "body_classic";	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Login Page</title>
		<link rel="shortcut icon" href="/qams/qams.ico" />
	<link rel="stylesheet" type="text/css" href="atomik/assets/css/style_login.css" />
</head>
<body>
	<div class="no_patch_company"><br /></div>
	<form action="" method="post">
		<fieldset>
<!--[if IE 6]>
<div style="display:none">
<![endif]-->			
			<legend>Log in</legend>
			
			<label for="login">Username</label>
			<input type="text" id="login" name="username"/>
			<div class="clear"></div>
			
			<label for="password">Password</label>
			<input type="password" id="password" name="password"/>
			<div class="clear"></div>
			
			<label for="remember_me" style="padding: 0;display:none" >Remember me?</label>
			<input type="checkbox" id="remember_me" style="position: relative; top: 3px; margin: 0;display:none " name="remember_me"/>
			<div class="clear"></div>
			
			<br />
			<span class="art-button-wrapper">
			<span class="l"> </span>
			<span class="r"> </span>
			<input class="art-button" type="submit" name="login" value="Login" />
			</span>
<!--[if IE 6]>
</div>
<![endif]-->	
<!--[if IE 6]>
<div style="margin-left:10px;margin-top:20px">
<p>D&eacute;sol&eacute; mais cette application web n'est pas compatible avec <b>Internet Explorer 6</b> ou le navigateur web </p><p>int&eacute;gr&eacute; de <b>Lotus Notes</b> pour le moment.</p>
<p>Veuillez utiliser <b>Firefox</b>, <b>Safari</b> ou <b>Chrome</b>.</p>
<br/>
<p><font size="1.5" color="white">Veuillez changer les pr&eacute;f&eacute;rences de Lotus dans le menu <b>Fichier -> Pr&eacute;f&eacute;rences...</b></font></p>
</div>
<![endif]-->	
		</fieldset>
	</form>	
</body>
</html>
