<?php
	$d=dirname(__FILE__);
	require("$d/incCommon.php");
	include("$d/incHeader.php");
?>

<?php
	if(!sqlValue("select count(1) from membership_groups where allowSignup=1")){
		$noSignup=TRUE;
		?>
		<div class="status">
			<i>Attention!</i>
			<br /><a href="../membership_signup.php" target="_blank">Visitor sign up</a> 
			is disabled because there are no groups where visitors can sign up currently.
			To enable visitor sign-up, set at least one group to allow visitor sign-up.
			</div>
		<?php
	}
?>

<?php
	// get the count of records having no owners in each table
	$arrTables=getTableList();

	foreach($arrTables as $tn=>$tc){
		$countOwned=sqlValue("select count(1) from membership_userrecords where tableName='$tn' and not isnull(groupID)");
		$countAll=sqlValue("select count(1) from `$tn`");

		if($countAll>$countOwned){
			?>
			<div class="status">
				You have data in one or more tables that doesn't have an owner.
				To assign an owner group for this data, <a href="pageAssignOwners.php">click here</a>.
				</div>
			<?php
			break;
		}
	}
?>

<table align="center" width="750" cellspacing="8" cellpadding="0" border="0">
	<tr><td colspan="2" align="center">
		<h1>Membership Management Homepage</h1>
		</td></tr>
	<tr><td valign="top" align="left">

<!-- ################# Newest Updates ######################## -->
	<table cellspacing="0" width="100%">
	<tr><td colspan="2" class="tdHeader">Newest Updates <a href="pageViewRecords.php?sort=dateUpdated&sortDir=desc"><img border="0" src="images/link_icon.gif" title="View all new updates"></a></td></tr>
	<?php
		$res=sql("select tableName, pkValue, dateUpdated, recID from membership_userrecords order by dateUpdated desc limit 5");
		while($row=mysql_fetch_row($res)){
			?>
			<tr>
				<td class="tdCaptionCell" align="right"><?php echo @date($adminConfig['PHPDateTimeFormat'], $row[2]); ?></td>
				<td class="tdCell" align="left"><a href="pageEditOwnership.php?recID=<?php echo $row[3]; ?>"><img src="images/data_icon.gif" border="0" alt="View record details" title="View record details"></a> <?php echo substr(getCSVData($row[0], $row[1]), 0, 15); ?> ...</td>
				</tr>
			<?php
		}
	?>
	</table>
<!-- ####################################################### -->

	</td><td valign="top" align="right">

<!-- ################# Newest Entries ######################## -->
	<table cellspacing="0" width="100%">
	<tr><td colspan="2" class="tdHeader">Newest Entries <a href="pageViewRecords.php?sort=dateAdded&sortDir=desc"><img border="0" src="images/link_icon.gif" title="View all new entries"></a></td></tr>
	<?php
		$res=sql("select tableName, pkValue, dateAdded, recID from membership_userrecords order by dateAdded desc limit 5");
		while($row=mysql_fetch_row($res)){
			?>
			<tr>
				<td class="tdCaptionCell" align="right"><?php echo @date($adminConfig['PHPDateTimeFormat'], $row[2]); ?></td>
				<td class="tdCell" align="left"><a href="pageEditOwnership.php?recID=<?php echo $row[3]; ?>"><img src="images/data_icon.gif" border="0" alt="View record details" title="View record details"></a> <?php echo substr(getCSVData($row[0], $row[1]), 0, 15); ?> ...</td>
				</tr>
			<?php
		}
	?>
	</table>
<!-- ####################################################### -->

	</td></tr>
	<tr><td valign="top" colspan="2"><table cellspacing="8" width="100%"><tr><td align="left" valign="top">

<!-- ################# Top Members ######################## -->
	<table cellspacing="0" width="100%">
	<tr><td colspan="2" class="tdHeader">Top Members</td></tr>
	<?php
		$res=sql("select lcase(memberID), count(1) from membership_userrecords group by memberID order by 2 desc limit 5");
		while($row=mysql_fetch_row($res)){
			?>
			<tr>
				<td class="tdCaptionCell" align="left"><a href="pageEditMember.php?memberID=<?php echo urlencode($row[0]); ?>"><img src="images/edit_icon.gif" border="0" alt="Edit member details" title="Edit member details"></a> <?php echo $row[0]; ?></td>
				<td class="tdCell" align="right"><?php echo $row[1]; ?> records <a href="pageViewRecords.php?memberID=<?php echo urlencode($row[0]); ?>"><img src="images/data_icon.gif" border="0" alt="View member's data records" title="View member's data records"></a></td>
				</tr>
			<?php
		}
	?>
	</table>
<!-- ####################################################### -->

	</td><td valign="top" align="center">

<!-- ################# Members Stats ######################## -->
	<table cellspacing="0" width="100%">
	<tr><td colspan="2" class="tdHeader">Members Stats</td></tr>
		<tr>
			<td class="tdCaptionCell" align="right">Total groups</td>
			<td class="tdCell" align="right"><?php echo sqlValue("select count(1) from membership_groups"); ?> <a href="pageViewGroups.php"><img src="images/view_icon.gif" border="0" alt="View groups" title="View groups"></a></td>
			</tr>
		<tr>
			<td class="tdCaptionCell" align="right">Active members</td>
			<td class="tdCell" align="right"><?php echo sqlValue("select count(1) from membership_users where isApproved=1 and isBanned=0"); ?> <a href="pageViewMembers.php?status=2"><img src="images/view_icon.gif" border="0" alt="View active members" title="View active members"></a></td>
			</tr>
		<tr>
			<?php
				$awaiting=sqlValue("select count(1) from membership_users where isApproved=0");
			?>
			<td class="tdCaptionCell" <?php echo ($awaiting ? "style=\"color: red;\"" : ""); ?> align="right">Members awaiting approval</td>
			<td class="tdCell" align="right"><?php echo $awaiting; ?> <a href="pageViewMembers.php?status=1"><img src="images/view_icon.gif" border="0" alt="View members awaiting approval" title="View members awaiting approval"></a></td>
			</tr>
		<tr>
			<td class="tdCaptionCell" align="right">Banned members</td>
			<td class="tdCell" align="right"><?php echo sqlValue("select count(1) from membership_users where isApproved=1 and isBanned=1"); ?> <a href="pageViewMembers.php?status=3"><img src="images/view_icon.gif" border="0" alt="View banned members" title="View banned members"></a></td>
			</tr>
		<tr>
			<td class="tdCaptionCell" align="right">Total members</td>
			<td class="tdCell" align="right"><?php echo sqlValue("select count(1) from membership_users"); ?> <a href="pageViewMembers.php"><img src="images/view_icon.gif" border="0" alt="View all members" title="View all members"></a></td>
			</tr>
		</table>
<!-- ####################################################### -->

	</td><td valign="top" align="right">

<!-- ################# Quick links ######################## -->
	<table cellspacing="0" width="100%">
		<tr><td colspan="2" class="tdHeader">Quick Links</td></tr>
		<tr>
			<td class="tdCaptionCell" align="right">Batch transfer wizard</td>
			<td class="tdCell" align="right"><a href="pageTransferOwnership.php"><img src="images/link_icon.gif" border="0" alt="Click here to open" title="Click here to open"></a></td>
			</tr>
		<tr>
			<td class="tdCaptionCell" align="right">Admin settings</td>
			<td class="tdCell" align="right"><a href="pageSettings.php"><img src="images/link_icon.gif" border="0" alt="Click here to open" title="Click here to open"></a></td>
			</tr>
		<tr>
			<td class="tdCaptionCell" align="right">Send a message to all groups</td>
			<td class="tdCell" align="right"><a href="pageMail.php?sendToAll=1"><img src="images/link_icon.gif" border="0" alt="Click here to open" title="Click here to open"></a></td>
			</tr>
		<tr>
			<td class="tdCaptionCell" align="right">Edit anonymous permissions</td>
			<td class="tdCell" align="right"><a href="pageEditGroup.php?groupID=<?php echo sqlValue("select groupID from membership_groups where name='".$adminConfig['anonymousGroup']."'"); ?>"><img src="images/link_icon.gif" border="0" alt="Click here to open" title="Click here to open"></a></td>
			</tr>
		<tr>
			<td class="tdCaptionCell" align="right">Import CSV data</td>
			<td class="tdCell" align="right"><a href="pageUploadCSV.php"><img src="images/link_icon.gif" border="0" alt="Click here to open" title="Click here to open"></a></td>
			</tr>
		</table>
	</td></tr></table>
<!-- ####################################################### -->

	</td></tr></table>

<?php
	include("$d/incFooter.php");
?>