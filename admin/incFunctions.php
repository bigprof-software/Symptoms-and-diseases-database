<?php
	########################################################################
	/*
	~~~~~~ LIST OF FUNCTIONS ~~~~~~
		getTableList() -- returns an associative array of all tables in this application in the format tableName=>tableCaption
		getThumbnailSpecs($tableName, $fieldName, $view) -- returns an associative array specifying the width, height and identifier of the thumbnail file.
		createThumbnail($img, $specs) -- $specs is an array as returned by getThumbnailSpecs(). Returns true on success, false on failure.
		makeSafe($string)
		checkPermissionVal($pvn)
		sql($statment)
		sqlValue($statment)
		getLoggedAdmin()
		checkUser($username, $password)
		logOutUser()
		getPKFieldName($tn)
		getCSVData($tn, $pkValue, $stripTag=true)
		errorMsg($msg)
		redirect($URL, $absolute=FALSE)
		htmlRadioGroup($name, $arrValue, $arrCaption, $selectedValue, $selClass="", $class="", $separator="<br />")
		htmlSelect($name, $arrValue, $arrCaption, $selectedValue, $class="", $selectedClass="")
		htmlSQLSelect($name, $sql, $selectedValue, $class="", $selectedClass="")
		isEmail($email) -- returns $email if valid or false otherwise.
		notifyMemberApproval($memberID) -- send an email to member acknowledging his approval by admin, returns false if no mail is sent
		setupMembership() -- check if membership tables exist or not. If not, create them.
		thisOr($this, $or) -- return $this if it has a value, or $or if not.
		getUploadedFile($FieldName, $MaxSize=0, $FileTypes='csv|txt', $NoRename=false, $dir='')
		toBytes($val)
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	*/
	########################################################################

	#########################################################
	if(!function_exists('getTableList')){
		function getTableList(){
			$arrTables=array(
				"diseases"=>"Diseases",
				"patients"=>"Patients",
				"symptoms"=>"Symptoms",
				"disease_symptoms"=>"Disease symptoms",
				"patient_symptoms"=>"Patient symptoms"
				);

			return $arrTables;
		}
	}
	########################################################################
	function getThumbnailSpecs($tableName, $fieldName, $view){
		return FALSE;
	}
	########################################################################
	function createThumbnail($img, $specs){
		$w=$specs['width'];
		$h=$specs['height'];
		$id=$specs['identifier'];
		$path=dirname($img);

		// image doesn't exist or inaccessible?
		if(!$size=@getimagesize($img))   return FALSE;

		// calculate thumbnail size to maintain aspect ratio
		$ow=$size[0]; // original image width
		$oh=$size[1]; // original image height
		$twbh=$h/$oh*$ow; // calculated thumbnail width based on given height
		$thbw=$w/$ow*$oh; // calculated thumbnail height based on given width
		if($w && $h){
			if($twbh>$w) $h=$thbw;
			if($thbw>$h) $w=$twbh;
		}elseif($w){
			$h=$thbw;
		}elseif($h){
			$w=$twbh;
		}else{
			return FALSE;
		}

		// dir not writeable?
		if(!is_writable($path))  return FALSE;

		// GD lib not loaded?
		if(!function_exists('gd_info'))  return FALSE;
		$gd=gd_info();

		// GD lib older than 2.0?
		preg_match('/\d/', $gd['GD Version'], $gdm);
		if($gdm[0]<2)    return FALSE;

		// get file extension
		preg_match('/\.[a-zA-Z]{3,4}$/U', $img, $matches);
		$ext=strtolower($matches[0]);

		// check if supplied image is supported and specify actions based on file type
		if($ext=='.gif'){
			if(!$gd['GIF Create Support'])   return FALSE;
			$thumbFunc='imagegif';
		}elseif($ext=='.png'){
			if(!$gd['PNG Support'])  return FALSE;
			$thumbFunc='imagepng';
		}elseif($ext=='.jpg' || $ext=='.jpe' || $ext=='.jpeg'){
			if(!$gd['JPG Support'] && !$gd['JPEG Support'])  return FALSE;
			$thumbFunc='imagejpeg';
		}else{
			return FALSE;
		}

		// determine thumbnail file name
		$ext=$matches[0];
		$thumb=substr($img, 0, -5).str_replace($ext, $id.$ext, substr($img, -5));

		// if the original image smaller than thumb, then just copy it to thumb
		if($h>$oh && $w>$ow){
			return (@copy($img, $thumb) ? TRUE : FALSE);
		}

		// get image data
		if(!$imgData=imagecreatefromstring(implode('', file($img)))) return FALSE;

		// finally, create thumbnail
		$thumbData=imagecreatetruecolor($w, $h);

		//preserve transparency of png and gif images
		if($thumbFunc=='imagepng'){
			if(($clr=@imagecolorallocate($thumbData, 0, 0, 0))!=-1){
				@imagecolortransparent($thumbData, $clr);
				@imagealphablending($thumbData, false);
				@imagesavealpha($thumbData, true);
			}
		}elseif($thumbFunc=='imagegif'){
			@imagealphablending($thumbData, false);
			$transIndex=imagecolortransparent($imgData);
			if($transIndex>=0){
				$transClr=imagecolorsforindex($imgData, $transIndex);
				$transIndex=imagecolorallocatealpha($thumbData, $transClr['red'], $transClr['green'], $transClr['blue'], 127);
				imagefill($thumbData, 0, 0, $transIndex);
			}
		}

		// resize original image into thumbnail
		if(!imagecopyresampled($thumbData, $imgData, 0, 0 , 0, 0, $w, $h, $ow, $oh)) return FALSE;
		unset($imgData);

		// gif transparency
		if($thumbFunc=='imagegif' && $transIndex>=0){
			imagecolortransparent($thumbData, $transIndex);
			for($y=0; $y<$h; ++$y)
				for($x=0; $x<$w; ++$x)
					if(((imagecolorat($thumbData, $x, $y)>>24) & 0x7F) >= 100)   imagesetpixel($thumbData, $x, $y, $transIndex);
			imagetruecolortopalette($thumbData, true, 255);
			imagesavealpha($thumbData, false);
		}

		if(!$thumbFunc($thumbData, $thumb))  return FALSE;
		unset($thumbData);

		return TRUE;
	}
	########################################################################
	function makeSafe($string){
		$string=(get_magic_quotes_gpc() ? stripslashes($string) : $string);
		if(function_exists('mysql_real_escape_string')){
			// send a trivial query to initiate mysql connection
			sql("select (1+1) from membership_groups limit 1");
			return mysql_real_escape_string($string);
		}else{
			return mysql_escape_string($string);
		}
	}
	########################################################################
	function checkPermissionVal($pvn){
		// fn to make sure the value in the given POST variable is 0, 1, 2 or 3
		// if the value is invalid, it default to 0
		$pvn=intval($_POST[$pvn]);
		if($pvn!=1 && $pvn!=2 && $pvn!=3){
			return 0;
		}else{
			return $pvn;
		}
	}
	########################################################################
	if(!function_exists('sql')){
		function sql($statment){
			static $connected=FALSE; // would be set to TRUE on successful connection

			if(!$connected){
				// get db connection data from config file
				@require(dirname(__FILE__)."/../config.php");

				/****** Connect to MySQL ******/
				if(!mysql_connect($dbServer, $dbUsername, $dbPassword)){
					echo "<div class=\"error\">Couldn't connect to MySQL at '$dbServer'.</div>";
					exit;
				}

				/****** Connection Charset ********/
				@mysql_query("SET NAMES 'latin1'");

				/****** Select DB ********/
				if(!mysql_select_db($dbDatabase)){
					echo "<div class=\"error\">Couldn't connect to the database '$dbDatabase'.</div>";
					exit;
				}

				$connected=TRUE;
			}

			if(!$result = @mysql_query($statment)){
				echo "An error occured while attempting to execute:<br /><pre>".htmlspecialchars($statment)."</pre><br />MySQL said:<br /><pre>".mysql_error()."</pre>";
				exit;
			}

			return $result;
		}
	}
	########################################################################
	function sqlValue($statment){
		// executes a statment that retreives a single data value and returns the value retrieved
		if(!$res=sql($statment)){
			return FALSE;
		}
		if(!$row=mysql_fetch_row($res)){
			return FALSE;
		}
		return $row[0];
	}
	########################################################################
	function getLoggedAdmin(){
		// checks session variables to see whether the admin is logged or not
		// if not, it returns FALSE
		// if logged, it returns the user id

		global $adminConfig;

		if($_SESSION['adminUsername']!=''){
			return $_SESSION['adminUsername'];
		}elseif($_SESSION['memberID']==$adminConfig['adminUsername']){
			$_SESSION['adminUsername']=$_SESSION['memberID'];
			return $_SESSION['adminUsername'];
		}else{
			return FALSE;
		}
	}
	########################################################################
	function checkUser($username, $password){
		// checks given username and password for validity
		// if valid, registers the username in a session and returns true
		// else, return FALSE and destroys session

		require(dirname(__FILE__)."/incConfig.php");
		if($username!=$adminConfig['adminUsername'] || md5($password)!=$adminConfig['adminPassword']){
			return FALSE;
		}

		$_SESSION['adminUsername']=$username;
		return TRUE;
	}
	########################################################################
	function logOutUser(){
		// destroys current session
		$_SESSION = array();
		if(isset($_COOKIE[session_name()])){
			setcookie(session_name(), '', time()-42000, '/');
		}
		if(isset($_COOKIE['symptoms_and_diseases_rememberMe'])){
			setcookie('symptoms_and_diseases_rememberMe', '', time()-42000);
		}
		session_destroy();
	}
	########################################################################
	function getPKFieldName($tn){
		// get pk field name of given table

		if(!$res=sql("show fields from `$tn`")){
			return FALSE;
		}

		while($row=mysql_fetch_assoc($res)){
			if($row['Key']=='PRI'){
				return $row['Field'];
			}
		}

		return FALSE;
	}
	########################################################################
	function getCSVData($tn, $pkValue, $stripTags=true){
		// get pk field name for given table
		if(!$pkField=getPKFieldName($tn)){
			return "";
		}

		// get a concat string to produce a csv list of field values for given table record
		if(!$res=sql("show fields from `$tn`")){
			return "";
		}
		while($row=mysql_fetch_assoc($res)){
			$csvFieldList.="`{$row['Field']}`,";
		}
		$csvFieldList=substr($csvFieldList, 0, -1);

		$csvData=sqlValue("select CONCAT_WS(', ', $csvFieldList) from `$tn` where `$pkField`='$pkValue'");

		return ($stripTags ? strip_tags($csvData) : $csvData);
	}
	########################################################################
	function errorMsg($msg){
		echo "<div class=\"status\" style=\"font-weight: bold; color: red;\">$msg</div>";
	}
	########################################################################
	function redirect($URL, $absolute=FALSE){
		$host   = $_SERVER['HTTP_HOST'];
		$uri    = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$http   = (strtolower($_SERVER['HTTPS']) == 'on' ? 'https:' : 'http:');
		$fullURL=($absolute ? "" : "$http//$host$uri/")."$URL";
		if(!headers_sent()){
			header("Location: $fullURL");
		}else{
			echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0;url=$fullURL\">";
			echo "<br /><br /><a href=\"$fullURL\">Click here</a> if you aren't automatically redirected.";
		}
		exit;
	}
	########################################################################
	function htmlRadioGroup($name, $arrValue, $arrCaption, $selectedValue, $selClass="", $class="", $separator="<br />"){
		if(is_array($arrValue)){
			for($i=0; $i<count($arrValue); $i++){
				$out.="<span onMouseOver=\"stm(".$name.$arrValue[$i]."Tip, toolTipStyle);\"  onMouseOut=\"htm();\" class=\"".($arrValue[$i]==$selectedValue ? $selClass :$class)."\"><input type=\"radio\" id=\"$name$i\" name=\"$name\" value=\"".$arrValue[$i]."\"".($arrValue[$i]==$selectedValue ? " checked" : "")."> <label for=\"$name$i\">".$arrCaption[$i]."</label></span>".$separator;
			}
		}
		return $out;
	}
	########################################################################
	function htmlSelect($name, $arrValue, $arrCaption, $selectedValue, $class="", $selectedClass=""){
		if($selectedClass==""){
			$selectedClass=$class;
		}
		if(is_array($arrValue)){
			$out="<select name=\"$name\" id=\"$name\">";
			for($i=0; $i<count($arrValue); $i++){
				$out.="<option value=\"".$arrValue[$i]."\"".($arrValue[$i]==$selectedValue ? " selected class=\"$class\"" : " class=\"$selectedClass\"").">".$arrCaption[$i]."</option>";
			}
			$out.="</select>";
		}
		return $out;
	}
	########################################################################
	function htmlSQLSelect($name, $sql, $selectedValue, $class="", $selectedClass=""){
		$arrVal[]='';
		$arrCap[]='';
		if($res=sql($sql)){
			while($row=mysql_fetch_row($res)){
				$arrVal[]=$row[0];
				$arrCap[]=$row[1];
			}
			return htmlSelect($name, $arrVal, $arrCap, $selectedValue, $class, $selectedClass);
		}else{
			return "";
		}
	}
	########################################################################
	function isEmail($email){
		if(preg_match('/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i', $email)){
			return $email;
		}else{
			return FALSE;
		}
	}
	########################################################################
	function notifyMemberApproval($memberID){
		require(dirname(__FILE__)."/incConfig.php");
		$memberID=strtolower($memberID);

		$email=sqlValue("select email from membership_users where lcase(memberID)='$memberID'");
		if(!isEmail($email)){
			return FALSE;
		}
		if(!@mail($email, $adminConfig['approvalSubject'], $adminConfig['approvalMessage'], "From: ".$adminConfig['senderName']." <".$adminConfig['senderEmail'].">")){
			return FALSE;
		}

		return TRUE;
	}
	########################################################################
	function setupMembership(){
		require(dirname(__FILE__)."/incConfig.php");

		// check if membership tables exist or not
		sql("CREATE TABLE IF NOT EXISTS membership_groups (groupID int unsigned NOT NULL auto_increment, name varchar(20), description text, allowSignup tinyint, needsApproval tinyint, PRIMARY KEY (groupID))");
		sql("CREATE TABLE IF NOT EXISTS membership_users (memberID varchar(20) NOT NULL, passMD5 varchar(40), email varchar(100), signupDate date, groupID int unsigned, isBanned tinyint, isApproved tinyint, custom1 text, custom2 text, custom3 text, custom4 text, comments text, PRIMARY KEY (memberID))");
		sql("CREATE TABLE IF NOT EXISTS membership_grouppermissions (permissionID int unsigned NOT NULL auto_increment,  groupID int, tableName varchar(100), allowInsert tinyint, allowView tinyint NOT NULL DEFAULT '0', allowEdit tinyint NOT NULL DEFAULT '0', allowDelete tinyint NOT NULL DEFAULT '0', PRIMARY KEY (permissionID))");
		sql("CREATE TABLE IF NOT EXISTS membership_userrecords (recID bigint unsigned NOT NULL auto_increment, tableName varchar(100), pkValue varchar(255), memberID varchar(20), dateAdded bigint unsigned, dateUpdated bigint unsigned, groupID int, PRIMARY KEY (recID))");

		// create membership indices if not existing
		@mysql_query("ALTER TABLE membership_userrecords ADD INDEX pkValue (pkValue)");
		@mysql_query("ALTER TABLE membership_userrecords ADD INDEX tableName (tableName)");


		// check if anonymous group and user exist. If not, create them
		$anonGroupID=sqlValue("select groupID from membership_groups where name='".$adminConfig['anonymousGroup']."'");
		if(!$anonGroupID){
			sql("insert into membership_groups set name='".$adminConfig['anonymousGroup']."', allowSignup=0, needsApproval=0, description='Anonymous group created automatically on ".@date("Y-m-d")."'");
			$anonGroupID=mysql_insert_id();

			// set anonymous group permissions
			sql("insert into membership_grouppermissions set groupID='$anonGroupID', tableName='diseases', allowInsert=0, allowView=0, allowEdit=0, allowDelete=0");
			sql("insert into membership_grouppermissions set groupID='$anonGroupID', tableName='patients', allowInsert=0, allowView=0, allowEdit=0, allowDelete=0");
			sql("insert into membership_grouppermissions set groupID='$anonGroupID', tableName='symptoms', allowInsert=0, allowView=0, allowEdit=0, allowDelete=0");
			sql("insert into membership_grouppermissions set groupID='$anonGroupID', tableName='disease_symptoms', allowInsert=0, allowView=0, allowEdit=0, allowDelete=0");
			sql("insert into membership_grouppermissions set groupID='$anonGroupID', tableName='patient_symptoms', allowInsert=0, allowView=0, allowEdit=0, allowDelete=0");
		}
		$anonMemberID=sqlValue("select lcase(memberID) from membership_users where lcase(memberID)='".strtolower($adminConfig['anonymousMember'])."' and groupID='$anonGroupID'");
		if(!$anonMemberID){
			sql("insert into membership_users set memberID='".strtolower($adminConfig['anonymousMember'])."', signUpDate='".@date('Y-m-d')."', groupID='$anonGroupID', isBanned=0, isApproved=1, comments='Anonymous member created automatically on ".@date('Y-m-d')."'");
		}

		// check if admin group and user exist. If not, create them
		$adminGroupID=sqlValue("select groupID from membership_groups where name='Admins'");
		if(!$adminGroupID){
			sql("insert into membership_groups set name='Admins', allowSignup=0, needsApproval=1, description='Admin group created automatically on ".@date('Y-m-d')."'");
			$adminGroupID=mysql_insert_id();
		}

		if(sqlValue("select count(1) from membership_grouppermissions where groupID='$adminGroupID'")<5){
			sql("delete from membership_grouppermissions where groupID='$adminGroupID'");
			// set admin group permissions
			sql("insert into membership_grouppermissions set groupID='$adminGroupID', tableName='diseases', allowInsert=1, allowView=3, allowEdit=3, allowDelete=3");
			sql("insert into membership_grouppermissions set groupID='$adminGroupID', tableName='patients', allowInsert=1, allowView=3, allowEdit=3, allowDelete=3");
			sql("insert into membership_grouppermissions set groupID='$adminGroupID', tableName='symptoms', allowInsert=1, allowView=3, allowEdit=3, allowDelete=3");
			sql("insert into membership_grouppermissions set groupID='$adminGroupID', tableName='disease_symptoms', allowInsert=1, allowView=3, allowEdit=3, allowDelete=3");
			sql("insert into membership_grouppermissions set groupID='$adminGroupID', tableName='patient_symptoms', allowInsert=1, allowView=3, allowEdit=3, allowDelete=3");
		}
		$adminMemberID=sqlValue("select lcase(memberID) from membership_users where lcase(memberID)='".strtolower($adminConfig['adminUsername'])."' and groupID='$adminGroupID'");
		if(!$adminMemberID){
			sql("insert into membership_users set memberID='".strtolower($adminConfig['adminUsername'])."', passMD5='".$adminConfig['adminPassword']."', email='".$adminConfig['senderEmail']."', signUpDate='".@date('Y-m-d')."', groupID='$adminGroupID', isBanned=0, isApproved=1, comments='Admin member created automatically on ".@date('Y-m-d')."'");
		}

	}
	########################################################################
	function thisOr($this, $or='&nbsp;'){
		return ($this!='' ? $this : $or);
	}
	########################################################################
	function getUploadedFile($FieldName, $MaxSize=0, $FileTypes='csv|txt', $NoRename=false, $dir=''){
		$d=dirname(__FILE__);
		if(is_array($_FILES)){
			$f = $_FILES[$FieldName];
		}else{
			return 'Your php settings don\'t allow file uploads.';
		}

		if(!$MaxSize){
			$MaxSize=toBytes(ini_get('upload_max_filesize'));
		}

		if(!is_dir("$d/csv")){
			@mkdir("$d/csv");
		}

		$dir=(is_dir($dir) && is_writable($dir) ? $dir : "$d/csv/");

		if($f['error']!=4 && $f['name']!=''){
			if($f['size']>$MaxSize || $f['error']){
				return 'File size exceeds maximum allowed of '.intval($MaxSize / 1024).'KB';
			}
			if(!preg_match('/\.('.$FileTypes.')$/i', $f['name'], $ft)){
				return 'File type not allowed. Only these file types are allowed: '.str_replace('|', ', ', $FileTypes);
			}

			if($NoRename){
				$n  = str_replace(' ', '_', $f['name']);
			}else{
				$n  = microtime();
				$n  = str_replace(' ', '_', $n);
				$n  = str_replace('0.', '', $n);
				$n .= $ft[0];
			}

			if(!@move_uploaded_file($f['tmp_name'], $dir . $n)){
				return 'Couldn\'t save the uploaded file. Try chmoding the upload folder "'.$dir.'" to 777.';
			}else{
				@chmod($dir.$n, 0666);
				return $dir.$n;
			}
		}
		return 'An error occured while uploading the file. Please try again.';
	}
	########################################################################
	function toBytes($val){
		$val = trim($val);
		$last = strtolower($val{strlen($val)-1});
		switch($last){
			 // The 'G' modifier is available since PHP 5.1.0
			 case 'g':
					$val *= 1024;
			 case 'm':
					$val *= 1024;
			 case 'k':
					$val *= 1024;
		}

		return $val;
	}
	########################################################################
?>