<?php
	$d=dirname(__FILE__);
	require("$d/incCommon.php");
	include("$d/incHeader.php");

	if($_POST['saveChanges']!=''){
		// validate inputs
		$adminUsername=makeSafe(strtolower($_POST['adminUsername']));

		// check if this member already exists

		if($adminConfig['adminUsername']!=$adminUsername && sqlValue("select count(1) from membership_users where lcase(memberID)='$adminUsername'")){
			// display status
			echo "<div class=\"status\">Couldn't save admin settings. The new admin username is already held be a member. Please make sure the new admin username is unique.<br />Back to <a href=\"pageSettings.php\">Admin settings</a>.</div>";
			// exit
			include("$d/incFooter.php");
		}

		$adminPassword=$_POST['adminPassword'];
		if($adminPassword!=''){
			$adminPassword=md5($adminPassword);
		}else{
			$adminPassword=$adminConfig['adminPassword'];
		}

		$notifyAdminNewMembers=intval($_POST['notifyAdminNewMembers']);
		$custom1=makeSafe($_POST['custom1']);
		$custom2=makeSafe($_POST['custom2']);
		$custom3=makeSafe($_POST['custom3']);
		$custom4=makeSafe($_POST['custom4']);

		$MySQLDateFormat=makeSafe($_POST['MySQLDateFormat']);
		$PHPDateFormat=makeSafe($_POST['PHPDateFormat']);
		$PHPDateTimeFormat=makeSafe($_POST['PHPDateTimeFormat']);

		$groupsPerPage=(intval($_POST['groupsPerPage']) ? intval($_POST['groupsPerPage']) : $adminConfig['groupsPerPage']);
		$membersPerPage=(intval($_POST['membersPerPage']) ? intval($_POST['membersPerPage']) : $adminConfig['membersPerPage']);
		$recordsPerPage=(intval($_POST['recordsPerPage']) ? intval($_POST['recordsPerPage']) : $adminConfig['recordsPerPage']);

		$defaultSignUp=intval($_POST['visitorSignup']);

		$anonymousGroup=makeSafe($_POST['anonymousGroup']);
		$anonymousMember=makeSafe($_POST['anonymousMember']);

		$senderEmail=isEmail($_POST['senderEmail']);
		$senderName=makeSafe($_POST['senderName']);

		$approvalMessage=makeSafe($_POST['approvalMessage']);
		//$approvalMessage=str_replace(array("\r", "\n"), '\n', $approvalMessage);
		$approvalSubject=makeSafe($_POST['approvalSubject']);

		// save changes
		if(!$fp=@fopen($conFile, "w")){
			errorMsg("Couldn't create the file '$conFile'. Please make sure the directory is writeable (Try chmoding it to 755 or 777).");
			include("$d/incFooter.php");
		}else{
			fwrite($fp, "<?php\n\t");

			fwrite($fp, "\$adminConfig['adminUsername']='$adminUsername';\n\t");
			fwrite($fp, "\$adminConfig['adminPassword']='$adminPassword';\n\t");
			fwrite($fp, "\$adminConfig['notifyAdminNewMembers']=$notifyAdminNewMembers;\n\t");
			fwrite($fp, "\$adminConfig['defaultSignUp']=$defaultSignUp;\n\t");
			fwrite($fp, "\$adminConfig['anonymousGroup']='$anonymousGroup';\n\t");
			fwrite($fp, "\$adminConfig['anonymousMember']='$anonymousMember';\n\t");
			fwrite($fp, "\$adminConfig['groupsPerPage']=$groupsPerPage;\n\t");
			fwrite($fp, "\$adminConfig['membersPerPage']=$membersPerPage;\n\t");
			fwrite($fp, "\$adminConfig['recordsPerPage']=$recordsPerPage;\n\t");
			fwrite($fp, "\$adminConfig['custom1']='$custom1';\n\t");
			fwrite($fp, "\$adminConfig['custom2']='$custom2';\n\t");
			fwrite($fp, "\$adminConfig['custom3']='$custom3';\n\t");
			fwrite($fp, "\$adminConfig['custom4']='$custom4';\n\t");
			fwrite($fp, "\$adminConfig['MySQLDateFormat']='$MySQLDateFormat';\n\t");
			fwrite($fp, "\$adminConfig['PHPDateFormat']='$PHPDateFormat';\n\t");
			fwrite($fp, "\$adminConfig['PHPDateTimeFormat']='$PHPDateTimeFormat';\n\t");
			fwrite($fp, "\$adminConfig['senderName']='$senderName';\n\t");
			fwrite($fp, "\$adminConfig['senderEmail']='$senderEmail';\n\t");
			fwrite($fp, "\$adminConfig['approvalMessage']=\"$approvalMessage\";\n\t");
			fwrite($fp, "\$adminConfig['approvalSubject']='$approvalSubject';\n\t");

			fwrite($fp, "?>");
			fclose($fp);

			// update admin member
			sql("update membership_users set memberID='$adminUsername', passMD5='$adminPassword', email='$senderEmail', comments=concat_ws('', comments, '\\n', 'Record updated automatically on ".@date('Y-m-d')."') where lcase(memberID)='".strtolower($adminConfig['adminUsername'])."'");
		}

		// display status
		echo "<div class=\"status\">Admin settings saved successfully.<br />Back to <a href=\"pageSettings.php\">Admin settings</a>.</div>";

		// exit
		include("$d/incFooter.php");
	}    

?>

<h1>Admin Settings</h1>

<form method="post" action="pageSettings.php">
	<table border="0" cellspacing="0" cellpadding="0">
		<tr><td align="center" colspan="2" class="tdFormCaption"><input type="checkbox" id="showToolTips" value="1" checked><label for="showToolTips">Show tool tips as mouse moves over options</label></td></tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Admin username</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="adminUsername" value="<?php echo $adminConfig['adminUsername']; ?>" size="20" class="formTextBox">
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Admin password</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="password" name="adminPassword" id="adminPassword" value="" size="20" class="formTextBox">
				<br />Type a password only if you want to change the admin password.
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Confirm password</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="password" name="confirmPassword" id="confirmPassword" value="" size="20" class="formTextBox">
				</td>
			</tr>

		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Sender email</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="senderEmail" id="senderEmail" value="<?php echo $adminConfig['senderEmail']; ?>" size="40" class="formTextBox">
				<br />Sender name and email are used in the 'To' field when sending 
				<br />email messages to groups or members.
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Admin notifications</div>
				</td>
			<td align="left" class="tdFormInput">
				<?php
					echo htmlRadioGroup(
						"notifyAdminNewMembers",
						array(0, 1, 2),
						array(
							"No email notifications to admin.",
							"Notify admin only when a new member is waiting for approval.",
							"Notify admin for all new sign-ups."
						),
						$adminConfig['notifyAdminNewMembers']
					);
				?>
				</td>
			</tr>

		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Sender name</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="senderName" value="<?php echo $adminConfig['senderName']; ?>" size="40" class="formTextBox">
				</td>
			</tr>

		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Members custom field 1</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="custom1" value="<?php echo $adminConfig['custom1']; ?>" size="20" class="formTextBox">
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Members custom field 2</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="custom2" value="<?php echo $adminConfig['custom2']; ?>" size="20" class="formTextBox">
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Members custom field 3</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="custom3" value="<?php echo $adminConfig['custom3']; ?>" size="20" class="formTextBox">
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Members custom field 4</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="custom4" value="<?php echo $adminConfig['custom4']; ?>" size="20" class="formTextBox">
				</td>
			</tr>

		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Member approval<br />email subject</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="approvalSubject" value="<?php echo $adminConfig['approvalSubject']; ?>" size="40" class="formTextBox">
				<br />When the admin approves a member, the member is notified by 
				<br />email that he is approved. You can control the subject of the
				<br />approval email in this box,  and the content in the box below.
				</td>
			</tr>

		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Member approval<br />email message</div>
				</td>
			<td align="left" class="tdFormInput">
				<textarea wrap="virtual" name="approvalMessage" cols="60" rows="6" class="formTextBox"><?php echo htmlspecialchars(str_replace(array('\r', '\n'), array("", "\n"), $adminConfig['approvalMessage'])); ?></textarea>
				</td>
			</tr>

		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">MySQL date<br />formatting string</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="MySQLDateFormat" value="<?php echo $adminConfig['MySQLDateFormat']; ?>" size="30" class="formTextBox">
				<br />Please refer to <a href="http://dev.mysql.com/doc/refman/5.0/en/date-and-time-functions.html#function_date-format" target="_blank">the MySQL reference</a> for possible formats.
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">PHP short date<br />formatting string</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="PHPDateFormat" value="<?php echo $adminConfig['PHPDateFormat']; ?>" size="30" class="formTextBox">
				<br />Please refer to <a href="http://www.php.net/date" target="_blank">the PHP manual</a> for possible formats. 
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">PHP long date<br />formatting string</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="PHPDateTimeFormat" value="<?php echo $adminConfig['PHPDateTimeFormat']; ?>" size="30" class="formTextBox">
				<br />Please refer to <a href="http://www.php.net/date" target="_blank">the PHP manual</a> for possible formats. 
				</td>
			</tr>

		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Groups per page</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="groupsPerPage" value="<?php echo $adminConfig['groupsPerPage']; ?>" size="5" class="formTextBox">
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Members per page</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="membersPerPage" value="<?php echo $adminConfig['membersPerPage']; ?>" size="5" class="formTextBox">
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Records per page</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="recordsPerPage" value="<?php echo $adminConfig['recordsPerPage']; ?>" size="5" class="formTextBox">
				</td>
			</tr>



		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Default sign-up mode<br />for new groups</div>
				</td>
			<td align="left" class="tdFormInput">
				<?php
					echo htmlRadioGroup(
						"visitorSignup",
						array(0, 1, 2),
						array(
							"No sign-up allowed. Only the admin can add members.",
							"Sign-up allowed, but the admin must approve members.",
							"Sign-up allowed, and automatically approve members."
						),
						$adminConfig['defaultSignUp']
					);
				?>
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Name of the anonymous<br />group</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="anonymousGroup" value="<?php echo $adminConfig['anonymousGroup']; ?>" size="30" class="formTextBox">
				</td>
			</tr>

		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Name of the anonymous<br />user</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="anonymousMember" value="<?php echo $adminConfig['anonymousMember']; ?>" size="30" class="formTextBox">
				</td>
			</tr>

		<tr>
			<td colspan="2" align="right" class="tdFormFooter">
				<input type="submit" name="saveChanges" value="Save changes" onClick="return jsValidateAdminSettings();">
				</td>
			</tr>
		</table>
</form>



<?php
	include("$d/incFooter.php");
?>