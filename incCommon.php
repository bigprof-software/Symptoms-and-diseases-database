<?php
	error_reporting(E_ALL ^ E_NOTICE);
	if(function_exists('set_magic_quotes_runtime')) @set_magic_quotes_runtime(0);
	ob_start();

	// check if setup was performed or not yet
	$d=dirname(__FILE__);
	$setupStyle="border: solid 1px red; background-color: #FFFFE0; color: red; font-size: 16px; font-family: arial; font-weight: bold; padding: 10px; width:400px; text-align: left;";
	if(!is_file("$d/config.php")){
		?>
		<META HTTP-EQUIV="Refresh" CONTENT="2;url=setup.php">
		<center>
		<div style="<?php echo $setupStyle ?>">
			<?php echo $Translation['db setup needed']; ?>
			</div>
			</center>
		<?php
		exit;
	}
	if(!is_file("$d/admin/incConfig.php")){
		?>
		<META HTTP-EQUIV="Refresh" CONTENT="2;url=admin/">
		<center>
		<div style="<?php echo $setupStyle ?>">
			<?php echo $Translation['admin setup needed']; ?>
			</div>
			</center>
		<?php
		exit;
	}
	// -----------------------------------------

	include("$d/admin/incFunctions.php");
	include("$d/admin/incConfig.php");
	// include global hook functions
	@include("$d/hooks/__global.php");

	// check sessions config
	$noPathCheck=True;
	$arrPath=explode(';', ini_get('session.save_path'));
	$save_path=$arrPath[count($arrPath)-1];
	if(!$noPathCheck && !is_dir($save_path)){
		?>
		<center>
		<div style="<?php echo $setupStyle ?>">
			Your site is not configured to support sessions correctly. Please edit your php.ini file and change the value of <i>session.save_path</i> to a valid path.
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
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-Type: text/html; charset=iso-8859-1');

	// check if membership system exists
	setupMembership();

	// silently apply db changes, if any
	@include_once("$d/updateDB.php");

	// do we have a login request?
	logInMember();

	#########################################################
	/*
	~~~~~~ LIST OF FUNCTIONS ~~~~~~
		getTableList() -- returns an associative array (tableName=>tableData, tableData is array(tableCaption, tableDescription, tableIcon)) of tables accessible by current user
		getLoggedMemberID() -- returns memberID of logged member. If no login, returns anonymous memberID
		getLoggedGroupID() -- returns groupID of logged member, or anonymous groupID
		logOutMember() -- destroys session and logs member out.
		logInMember() -- checks POST login. If not valid, redirects to index.php, else returns TRUE
		getTablePermissions($tn) -- returns an array of permissions allowed for logged member to given table (allowAccess, allowInsert, allowView, allowEdit, allowDelete) -- allowAccess is set to true if any access level is allowed
		htmlUserBar() -- returns html code for displaying user login status to be used on top of pages.
		showNotifications($msg, $class) -- returns html code for displaying a notification. If no parameters provided, processes the GET request for possible notifications.
		parseMySQLDate(a, b) -- returns a if valid mysql date, or b if valid mysql date, or today if b is true, or empty if b is false.
		parseCode(code) -- calculates and returns special values to be inserted in automatic fields.
		addFilter(i, filterAnd, filterField, filterOperator, filterValue) -- enforce a filter over data
		clearFilters() -- clear all filters
		getMemberInfo() -- returns an array containing the currently signed-in member's info
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	*/
	#########################################################
	function getTableList(){
		$arrTables=array(
			'diseases'=>array('Diseases', '', 'table.gif'),
			'patients'=>array('Patients', '', 'table.gif'),
			'symptoms'=>array('Symptoms', '', 'table.gif'),
			'disease_symptoms'=>array('Disease symptoms', '', 'table.gif'),
			'patient_symptoms'=>array('Patient symptoms', '', 'table.gif')
			);
		if(is_array($arrTables)){
			foreach($arrTables as $tn=>$tc){
				$arrPerm=getTablePermissions($tn);
				if($arrPerm[0]){
					$arrAccessTables[$tn]=$tc;
				}
			}
		}

		return $arrAccessTables;
	}
	#########################################################
	function getTablePermissions($tn){
		$groupID=getLoggedGroupID();
		$res=sql("select allowInsert, allowView, allowEdit, allowDelete from membership_grouppermissions where groupID='$groupID' and tableName='$tn'");
		if($row=mysql_fetch_row($res)){
			if($row[0] || $row[1] || $row[2] || $row[3]){
				$arrRet[0]=TRUE;
			}else{
				$arrRet[0]=FALSE;
			}
			$arrRet[1]=$row[0];
			$arrRet[2]=$row[1];
			$arrRet[3]=$row[2];
			$arrRet[4]=$row[3];

			return $arrRet;
		}

		return array(FALSE, 0, 0, 0, 0);
	}
	#########################################################
	function getLoggedGroupID(){
		if($_SESSION['memberGroupID']!=''){
			return $_SESSION['memberGroupID'];
		}else{
			setAnonymousAccess();
			return getLoggedGroupID();
		}
	}
	#########################################################
	function getLoggedMemberID(){
		if($_SESSION['memberID']!=''){
			return strtolower($_SESSION['memberID']);
		}else{
			setAnonymousAccess();
			return getLoggedMemberID();
		}
	}
	#########################################################
	function setAnonymousAccess(){
		global $adminConfig;

		$anonGroupID=sqlValue("select groupID from membership_groups where name='".$adminConfig['anonymousGroup']."'");
		$_SESSION['memberGroupID']=($anonGroupID ? $anonGroupID : 0);

		$anonMemberID=sqlValue("select lcase(memberID) from membership_users where lcase(memberID)='".strtolower($adminConfig['anonymousMember'])."' and groupID='$anonGroupID'");
		$_SESSION['memberID']=($anonMemberID ? $anonMemberID : 0);
	}
	#########################################################
	function logInMember(){
		$redir='index.php';
		if($_POST['signIn']!=''){
			if($_POST['username']!='' && $_POST['password']!=''){
				$username=makeSafe(strtolower($_POST['username']));
				$password=md5($_POST['password']);

				if(sqlValue("select count(1) from membership_users where lcase(memberID)='$username' and passMD5='$password' and isApproved=1 and isBanned=0")==1){
					$_SESSION['memberID']=$username;
					$_SESSION['memberGroupID']=sqlValue("select groupID from membership_users where lcase(memberID)='$username'");
					if($_POST['rememberMe']==1){
						@setcookie('symptoms_and_diseases_rememberMe', md5($username.$password), time()+86400*30);
					}else{
						@setcookie('symptoms_and_diseases_rememberMe', '', time()-86400*30);
					}

					// hook: login_ok
					if(function_exists('login_ok')){
						$args=array();
						if(!$redir=login_ok(getMemberInfo(), $args)){
							$redir='index.php';
						}
					}

					redirect($redir);
					exit;
				}
			}

			// hook: login_failed
			if(function_exists('login_failed')){
				$args=array();
				login_failed(array(
					'username' => $_POST['username'],
					'password' => $_POST['password'],
					'IP' => $_SERVER['REMOTE_ADDR']
					), $args);
			}

			redirect("index.php?loginFailed=1");
			exit;
		}elseif((!$_SESSION['memberID'] || $_SESSION['memberID']==$adminConfig['anonymousMember']) && $_COOKIE['symptoms_and_diseases_rememberMe']!=''){
			$chk=makeSafe($_COOKIE['symptoms_and_diseases_rememberMe']);
			if($username=sqlValue("select memberID from membership_users where convert(md5(concat(memberID, passMD5)), char)='$chk'")){
				$_SESSION['memberID']=$username;
				$_SESSION['memberGroupID']=sqlValue("select groupID from membership_users where lcase(memberID)='$username'");
			}
		}
	}
	#########################################################
	function logOutMember(){
		logOutUser();
		redirect("index.php?signIn=1");
	}
	#########################################################
	function htmlUserBar(){
		global $adminConfig, $Translation;

		if($_POST['Print_x']!='' || $_GET['Print_x']!='' || $_POST['dvprint_x']!='' || $_GET['dvprint_x']!=''){
			return '';
		}

		ob_start();

		?>
		<div class="TableFooter" style="text-align: right;">
			<?php
				if(!$_GET['signIn'] && !$_GET['loginFailed']){
					if(getLoggedMemberID()==$adminConfig['anonymousMember']){
						?><?php echo $Translation['not signed in']; ?>. <a href="index.php?signOut=1"><?php echo $Translation['sign in']; ?></a><?php
					}else{
						?><?php echo $Translation['signed as']; ?> '<?php echo getLoggedMemberID(); ?>'. <a href="index.php?signOut=1"><?php echo $Translation['sign out']; ?></a><?php
					}
				}
			?>
			 &nbsp; &nbsp; &nbsp; 
			</div><br /><br />
		<?php

		$html=ob_get_contents();
		ob_end_clean();

		return $html;
	}
	#########################################################
	function showNotifications($msg='', $class=''){
		global $Translation;

		$notifyTemplate='<div id="%%ID%%" class="%%CLASS%%" style="display: none;">%%MSG%%</div>'.
					'<script>new Effect.Appear("%%ID%%", {duration:2, from:0.0, to:1.0}); '.
					'new PeriodicalExecuter(function(pe){ '.
					'new Effect.Fade("%%ID%%", {duration:2}); '.
					'pe.stop();'.
					'}, 5); </script>'."\n";

		if(!$msg){ // if no msg, use url to detect message to display
			if($_GET['record-added-ok']!=''){
				$msg=$Translation['new record saved'];
				$class='SuccessNotify';
			}elseif($_GET['record-updated-ok']!=''){
				$msg=$Translation['record updated'];
				$class='SuccessNotify';
			}else{
				return '';
			}
		}
		$id='notification-'.rand();

		$out=$notifyTemplate;
		$out=str_replace('%%ID%%', $id, $out);
		$out=str_replace('%%MSG%%', $msg, $out);
		$out=str_replace('%%CLASS%%', $class, $out);

		return $out;
	}
	#########################################################
	function parseMySQLDate($date, $altDate){
		// is $date valid?
		if(preg_match("/^\d{4}-\d{1,2}-\d{1,2}$/", trim($date))){
			return trim($date);
		}

		if(preg_match("/^\d{4}-\d{1,2}-\d{1,2}$/", trim($altDate))){
			return trim($altDate);
		}

		if($altDate){
			return @date('Y-m-d');
		}

		return '';
	}
	#########################################################
	function parseCode($code, $isInsert=true, $rawData=false){
		if($isInsert){
			$arrCodes=array(
				'<%%creatorusername%%>' => $_SESSION['memberID'],
				'<%%creatorgroupid%%>' => $_SESSION['memberGroupID'],
				'<%%creatorip%%>' => $_SERVER['REMOTE_ADDR'],
				'<%%creatorgroup%%>' => sqlValue("select name from membership_groups where groupID='{$_SESSION['memberGroupID']}'"),

				'<%%creationdate%%>' => ($rawData ? @date('Y-m-d') : @date('n/j/Y')),
				'<%%creationtime%%>' => ($rawData ? @date('H:i:s') : @date('h:i:s a')),
				'<%%creationdatetime%%>' => ($rawData ? @date('Y-m-d H:i:s') : @date('n/j/Y h:i:s a')),
				'<%%creationtimestamp%%>' => ($rawData ? @date('Y-m-d H:i:s') : @time())
			);
		}else{
			$arrCodes=array(
				'<%%editorusername%%>' => $_SESSION['memberID'],
				'<%%editorgroupid%%>' => $_SESSION['memberGroupID'],
				'<%%editorip%%>' => $_SERVER['REMOTE_ADDR'],
				'<%%editorgroup%%>' => sqlValue("select name from membership_groups where groupID='{$_SESSION['memberGroupID']}'"),

				'<%%editingdate%%>' => ($rawData ? @date('Y-m-d') : @date('n/j/Y')),
				'<%%editingtime%%>' => ($rawData ? @date('H:i:s') : @date('h:i:s a')),
				'<%%editingdatetime%%>' => ($rawData ? @date('Y-m-d H:i:s') : @date('n/j/Y h:i:s a')),
				'<%%editingtimestamp%%>' => ($rawData ? @date('Y-m-d H:i:s') : @time())
			);
		}

		$pc=str_ireplace(array_keys($arrCodes), array_values($arrCodes), $code);

		return $pc;
	}
	#########################################################
	function addFilter($index, $filterAnd, $filterField, $filterOperator, $filterValue){
		// validate input
		if($index<1 || $index>80 || !is_int($index))   return false;
		if($filterAnd!='or')   $filterAnd='and';
		$filterField=intval($filterField);
		$filterOperator=strtolower($filterOperator);
		if(!in_array($filterOperator, array('<=>', '!=', '>', '>=', '<', '<=', 'like', 'not like', 'isEmpty', 'isNotEmpty')))
			$filterOperator='like';

		if(!$filterField){
			$filterOperator='';
			$filterValue='';
		}

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$_POST['FilterAnd'][$index]=$filterAnd;
			$_POST['FilterField'][$index]=$filterField;
			$_POST['FilterOperator'][$index]=$filterOperator;
			$_POST['FilterValue'][$index]=$filterValue;
		}else{
			$_GET['FilterAnd'][$index]=$filterAnd;
			$_GET['FilterField'][$index]=$filterField;
			$_GET['FilterOperator'][$index]=$filterOperator;
			$_GET['FilterValue'][$index]=$filterValue;
		}

		return true;
	}
	#########################################################
	function clearFilters(){
		for($i=1; $i<=80; $i++){
			addFilter($i, '', 0, '', '');
		}
	}
	#########################################################
	function getMemberInfo($memberID=''){
		global $adminConfig;
		$mi=array();

		if(!$memberID){
			$memberID=getLoggedMemberID();
		}

		if($memberID){
			$res=sql("select * from membership_users where memberID='".addslashes($memberID)."'");
			if($row=mysql_fetch_assoc($res)){
				$mi['username']=$memberID;
				$mi['groupID']=$row['groupID'];
				$mi['group']=sqlValue("select name from membership_groups where groupID='".$row['groupID']."'");
				$mi['admin']=($adminConfig['adminUsername']==$memberID ? TRUE : FALSE);
				$mi['email']=$row['email'];
				$mi['custom'][0]=$row['custom1'];
				$mi['custom'][1]=$row['custom2'];
				$mi['custom'][2]=$row['custom3'];
				$mi['custom'][3]=$row['custom4'];
				$mi['banned']=($row['isBanned'] ? TRUE : FALSE);
				$mi['approved']=($row['isApproved'] ? TRUE : FALSE);
				$mi['signupDate']=date('n/j/Y', @strtotime($row['signupDate']));
				$mi['comments']=$row['comments'];
				$mi['IP']=$_SERVER['REMOTE_ADDR'];
			}
		}

		return $mi;
	}
