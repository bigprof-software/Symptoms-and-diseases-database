<?php
	$d=dirname(__FILE__);
	require("$d/incCommon.php");

	// request to save changes?
	if($_POST['saveChanges']!=''){
		// validate data
		$recID=intval($_POST['recID']);
		$memberID=makeSafe(strtolower($_POST['memberID']));
		$groupID=intval($_POST['groupID']);
		###############################

		// update ownership
		$upQry="UPDATE `membership_userrecords` set memberID='$memberID', groupID='$groupID' WHERE recID='$recID'";
		sql($upQry);

		// redirect to member editing page
		redirect("pageEditOwnership.php?recID=$recID");

	}elseif($_GET['recID']!=''){
		// we have an edit request for a member
		$recID=makeSafe($_GET['recID']);
	}

	include("$d/incHeader.php");

	if($recID!=''){
		// fetch record data to fill in the form below
		$res=sql("select * from membership_userrecords where recID='$recID'");
		if($row=mysql_fetch_assoc($res)){
			// get record data
			$tableName=$row['tableName'];
			$pkValue=$row['pkValue'];
			$memberID=strtolower($row['memberID']);
			$dateAdded=date($adminConfig['PHPDateTimeFormat'], $row['dateAdded']);
			$dateUpdated=date($adminConfig['PHPDateTimeFormat'], $row['dateUpdated']);
			$groupID=$row['groupID'];
		}else{
			// no such record exists
			die("<div class=\"error\">Error: Record not found!</div>");
		}
	}else{
		redirect("pageViewRecords.php");
	}
?>
<h1>Edit Record Ownership</h1>
<form method="post" action="pageEditOwnership.php">
	<input type="hidden" name="recID" value="<?php echo $recID; ?>">
	<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Owner group</div>
				</td>
			<td align="left" class="tdFormInput">
				<?php
					echo htmlSQLSelect('groupID', "select g.groupID, g.name from membership_groups g order by name", $groupID);
				?>
				<a href="#" onClick="window.location='pageViewRecords.php?groupID='+escape(document.getElementById('groupID').value);"><img src="images/data_icon.gif" alt="View all records by this group" title="View all records by this group" border="0"></a>
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Owner member</div>
				</td>
			<td align="left" class="tdFormInput" width="460">
				<?php
					echo htmlSQLSelect('memberID', "select lcase(memberID), lcase(memberID) from membership_users where groupID='$groupID' order by memberID", $memberID);
				?>
				<a href="#" onClick="window.location='pageViewRecords.php?memberID='+escape(document.getElementById('memberID').value);"><img src="images/data_icon.gif" alt="View all records by this member" title="View all records by this member" border="0"></a>
				<br />If you want to switch ownership of this record to a member of another group,
				you must change the owner group and save changes first.
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Record created on</div>
				</td>
			<td align="left" class="tdFormInput">
				<?php echo $dateAdded; ?>
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Record modified on</div>
				</td>
			<td align="left" class="tdFormInput">
				<?php echo $dateUpdated; ?>
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Table</div>
				</td>
			<td align="left" class="tdFormInput">
				<?php echo $tableName; ?>
				<a href="pageViewRecords.php?tableName=<?php echo $tableName; ?>"><img src="images/data_icon.gif" alt="View all records of this table" title="View all records of this table" border="0"></a>
				</td>
			</tr>
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Record data</div>
				<input type="button" value="Print" onClick="window.location='pagePrintRecord.php?recID=<?php echo $recID; ?>';"> &nbsp; &nbsp;
				</td>
			<td align="left" class="tdFormInput">
				<?php 
					// get pk field name
					$pkField=getPKFieldName($tableName);

					// get field list
					if(!$res=sql("show fields from `$tableName`")){
						errorMsg("Couldn't retrieve field list from '$tableName'");
					}
					while($row=mysql_fetch_assoc($res)){
						$field[]=$row['Field'];
					}

					$res=sql("select * from `$tableName` where `$pkField`='$pkValue'");
					if($row=mysql_fetch_assoc($res)){
						?>
						<table border="0" cellspacing="0" cellpadding="0" align="center">
							<tr>
								<td class="tdHeader"><div class="ColCaption">Field name</div></td>
								<td class="tdHeader"><div class="ColCaption">Value</div></td>
								</tr>
						<?php
						include("$d/../language.php");
						foreach($field as $fn){
							if(@is_file("$d/../".$Translation['ImageFolder'].$row[$fn])){
								$op="<a href=\""."../".$Translation['ImageFolder'].$row[$fn]."\" target=\"_blank\">".htmlspecialchars($row[$fn])."</a>";
							}else{
								$op=htmlspecialchars($row[$fn]);
							}
							?>
							<tr>
								<td class="tdCaptionCell" valign="top"><?php echo $fn; ?></td>
								<td class="tdCell" valign="top">
									<?php echo $op; ?>
									</td>
								</tr>
							<?php
						}
						?>
							</table>
						<?php
					}

				?>
				</td>
			</tr>
		<tr>
			<td colspan="2" align="right" class="tdFormFooter">
				<input type="submit" name="saveChanges" value="Save changes">
				</td>
			</tr>
		</table>
</form>


<?php
	include("$d/incFooter.php");
?>