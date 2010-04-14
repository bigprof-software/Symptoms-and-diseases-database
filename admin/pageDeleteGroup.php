<?php
	$d=dirname(__FILE__);
	require("$d/incCommon.php");

	// validate input
	$groupID=intval($_GET['groupID']);

	// make sure group has no members
	if(sqlValue("select count(1) from membership_users where groupID='$groupID'")){
		errorMsg("Can't delete this group. Please remove members first.");
		include("$d/incFooter.php");
	}

	// make sure group has no records
	if(sqlValue("select count(1) from membership_userrecords where groupID='$groupID'")){
		errorMsg("Can't delete this group. Please transfer its data records to another group first..");
		include("$d/incFooter.php");
	}


	sql("delete from membership_groups where groupID='$groupID'");
	sql("delete from membership_grouppermissions where groupID='$groupID'");

	if($_SERVER['HTTP_REFERER']){
		redirect($_SERVER['HTTP_REFERER'], TRUE);
	}else{
		redirect("pageViewGroups.php");
	}

?>