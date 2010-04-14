<?php
	error_reporting(E_ALL ^ E_NOTICE);
	if(function_exists('set_magic_quotes_runtime')) @set_magic_quotes_runtime(0);
	ob_start();
	$d=dirname(__FILE__);
	include("$d/incFunctions.php");

	// check sessions config
	$noPathCheck=True;
	$arrPath=explode(';', ini_get('session.save_path'));
	$save_path=$arrPath[count($arrPath)-1];
	if(!$noPathCheck && !is_dir($save_path)){
		?>
		<link rel="stylesheet" type="text/css" href="adminStyles.css">
		<center>
		<div class="status">
			Your site is not configured to support sessions correctly. Please edit your php.ini file and change the value of <i>session.save_path</i> to a valid path.
			<br /><br />
			Current session.save_path value is '<?php echo $save_path; ?>'.
			</div>
			</center>
		<?php
		exit;
	}
	if(session_id()){ session_write_close(); }
	@ini_set('session.save_handler', 'files');
	@ini_set('session.serialize_handler', 'php');
	@ini_set('session.use_cookies', '1');
	@ini_set('session.use_only_cookies', '1');
	@ini_set('session.cache_limiter', 'nocache');
	@session_name('symptoms_and_diseases');
	session_start();
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");


	// check if initial setup was performed or not
	if(!is_file("$d/../config.php")){
		errorMsg("ERROR! You didn't configure your connection yet. Please <a href=\"../setup.php\">run the setup procedure</a> first.");
		exit;
	}


	// check if the file 'incConfig.php' exists or not. If not, generate one.
	$conFile="$d/incConfig.php";

	if(!is_file($conFile)){
		if(!$fp=@fopen($conFile, "w")){
			errorMsg("Couldn't create the file '$conFile'. Please make sure the directory is writeable (Try chmoding it to 755 or 777).");
			exit;
		}else{
			fwrite($fp, "<?php\n\t");

			fwrite($fp, "\$adminConfig['adminUsername']='admin';\n\t");
			fwrite($fp, "\$adminConfig['adminPassword']='21232f297a57a5a743894a0e4a801fc3';\n\t");
			fwrite($fp, "\$adminConfig['notifyAdminNewMembers']=0;\n\t");
			fwrite($fp, "\$adminConfig['defaultSignUp']=1;\n\t");
			fwrite($fp, "\$adminConfig['anonymousGroup']='anonymous';\n\t");
			fwrite($fp, "\$adminConfig['anonymousMember']='guest';\n\t");
			fwrite($fp, "\$adminConfig['groupsPerPage']=10;\n\t");
			fwrite($fp, "\$adminConfig['membersPerPage']=10;\n\t");
			fwrite($fp, "\$adminConfig['recordsPerPage']=10;\n\t");
			fwrite($fp, "\$adminConfig['custom1']='Full Name';\n\t");
			fwrite($fp, "\$adminConfig['custom2']='Address';\n\t");
			fwrite($fp, "\$adminConfig['custom3']='City';\n\t");
			fwrite($fp, "\$adminConfig['custom4']='State';\n\t");
			fwrite($fp, "\$adminConfig['MySQLDateFormat']='%c/%e/%Y';\n\t");
			fwrite($fp, "\$adminConfig['PHPDateFormat']='n/j/Y';\n\t");
			fwrite($fp, "\$adminConfig['PHPDateTimeFormat']='m/d/Y, h:i a';\n\t");
			fwrite($fp, "\$adminConfig['senderName']='Membership management';\n\t");
			fwrite($fp, "\$adminConfig['senderEmail']='admin@".$_SERVER['SERVER_NAME']."';\n\t");
			fwrite($fp, "\$adminConfig['approvalSubject']='Your membership is now approved';\n\t");
			fwrite($fp, "\$adminConfig['approvalMessage']=\"Dear member,\\n\\nYour membership is now approved by the admin. You can log in to your account here:\\nhttp://".$_SERVER['HTTP_HOST'].str_replace("/admin", "", rtrim(dirname($_SERVER['PHP_SELF']), '/\\'))."\\n\\nRegards,\\nAdmin\";\n\t");

			fwrite($fp, "?>");
			fclose($fp);         
		}
	}

	include($conFile);

	// check if membership system exists
	setupMembership();


	########################################################################

	// do we have an admin log out request?
	if($_GET['signOut']==1){
		logOutUser();
		?><META HTTP-EQUIV="Refresh" CONTENT="0;url=index.php"><?php
		exit;
	}

	// is there a logged user?
	if(!$uname=getLoggedAdmin()){
		// is there a user trying to log in?
		if(!checkUser($_POST['username'], $_POST['password'])){
			// display login form
			include("$d/pageLogin.php");
			exit;
		}else{
			redirect('pageHome.php');
		}
	}

?>