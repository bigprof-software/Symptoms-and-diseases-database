<?php
	$d=dirname(__FILE__);
	require("$d/incCommon.php");

	// get groupID of anonymous group
	$anonGroupID=sqlValue("select groupID from membership_groups where name='".$adminConfig['anonymousGroup']."'");

	// request to save changes?
	if($_POST['saveChanges']!=''){
		// validate data
		$name=makeSafe($_POST['name']);
		$description=makeSafe($_POST['description']);
		switch($_POST['visitorSignup']){
			case 0:
				$allowSignup=0;
				$needsApproval=1;
				break;
			case 2:
				$allowSignup=1;
				$needsApproval=0;
				break;
			default:
				$allowSignup=1;
				$needsApproval=1;
		}
		###############################
		$diseases_insert=checkPermissionVal('diseases_insert');
		$diseases_view=checkPermissionVal('diseases_view');
		$diseases_edit=checkPermissionVal('diseases_edit');
		$diseases_delete=checkPermissionVal('diseases_delete');
		###############################
		$patients_insert=checkPermissionVal('patients_insert');
		$patients_view=checkPermissionVal('patients_view');
		$patients_edit=checkPermissionVal('patients_edit');
		$patients_delete=checkPermissionVal('patients_delete');
		###############################
		$symptoms_insert=checkPermissionVal('symptoms_insert');
		$symptoms_view=checkPermissionVal('symptoms_view');
		$symptoms_edit=checkPermissionVal('symptoms_edit');
		$symptoms_delete=checkPermissionVal('symptoms_delete');
		###############################
		$disease_symptoms_insert=checkPermissionVal('disease_symptoms_insert');
		$disease_symptoms_view=checkPermissionVal('disease_symptoms_view');
		$disease_symptoms_edit=checkPermissionVal('disease_symptoms_edit');
		$disease_symptoms_delete=checkPermissionVal('disease_symptoms_delete');
		###############################
		$patient_symptoms_insert=checkPermissionVal('patient_symptoms_insert');
		$patient_symptoms_view=checkPermissionVal('patient_symptoms_view');
		$patient_symptoms_edit=checkPermissionVal('patient_symptoms_edit');
		$patient_symptoms_delete=checkPermissionVal('patient_symptoms_delete');
		###############################

		// new group or old?
		if($_POST['groupID']==''){ // new group
			// make sure group name is unique
			if(sqlValue("select count(1) from membership_groups where name='$name'")){
				echo "<div class=\"error\">Error: Group name already exists. You must choose a unique group name.</div>";
				include("$d/incFooter.php");
			}

			// add group
			sql("insert into membership_groups set name='$name', description='$description', allowSignup='$allowSignup', needsApproval='$needsApproval'");

			// get new groupID
			$groupID=mysql_insert_id();

		}else{ // old group
			// validate groupID
			$groupID=intval($_POST['groupID']);

			if($groupID==$anonGroupID){
				$name=$adminConfig['anonymousGroup'];
				$allowSignup=0;
				$needsApproval=0;
			}

			// make sure group name is unique
			if(sqlValue("select count(1) from membership_groups where name='$name' and groupID!='$groupID'")){
				echo "<div class=\"error\">Error: Group name already exists. You must choose a unique group name.</div>";
				include("$d/incFooter.php");
			}

			// update group
			sql("update membership_groups set name='$name', description='$description', allowSignup='$allowSignup', needsApproval='$needsApproval' where groupID='$groupID'");

			// reset then add group permissions
			sql("delete from membership_grouppermissions where groupID='$groupID' and tableName='diseases'");
			sql("delete from membership_grouppermissions where groupID='$groupID' and tableName='patients'");
			sql("delete from membership_grouppermissions where groupID='$groupID' and tableName='symptoms'");
			sql("delete from membership_grouppermissions where groupID='$groupID' and tableName='disease_symptoms'");
			sql("delete from membership_grouppermissions where groupID='$groupID' and tableName='patient_symptoms'");
		}

		// add group permissions
		if($groupID){
			// table 'diseases'
			sql("insert into membership_grouppermissions set groupID='$groupID', tableName='diseases', allowInsert='$diseases_insert', allowView='$diseases_view', allowEdit='$diseases_edit', allowDelete='$diseases_delete'");
			// table 'patients'
			sql("insert into membership_grouppermissions set groupID='$groupID', tableName='patients', allowInsert='$patients_insert', allowView='$patients_view', allowEdit='$patients_edit', allowDelete='$patients_delete'");
			// table 'symptoms'
			sql("insert into membership_grouppermissions set groupID='$groupID', tableName='symptoms', allowInsert='$symptoms_insert', allowView='$symptoms_view', allowEdit='$symptoms_edit', allowDelete='$symptoms_delete'");
			// table 'disease_symptoms'
			sql("insert into membership_grouppermissions set groupID='$groupID', tableName='disease_symptoms', allowInsert='$disease_symptoms_insert', allowView='$disease_symptoms_view', allowEdit='$disease_symptoms_edit', allowDelete='$disease_symptoms_delete'");
			// table 'patient_symptoms'
			sql("insert into membership_grouppermissions set groupID='$groupID', tableName='patient_symptoms', allowInsert='$patient_symptoms_insert', allowView='$patient_symptoms_view', allowEdit='$patient_symptoms_edit', allowDelete='$patient_symptoms_delete'");
		}

		// redirect to group editing page
		redirect("pageEditGroup.php?groupID=$groupID");

	}elseif($_GET['groupID']!=''){
		// we have an edit request for a group
		$groupID=intval($_GET['groupID']);
	}

	include("$d/incHeader.php");

	if($groupID!=''){
		// fetch group data to fill in the form below
		$res=sql("select * from membership_groups where groupID='$groupID'");
		if($row=mysql_fetch_assoc($res)){
			// get group data
			$name=$row['name'];
			$description=$row['description'];
			$visitorSignup=($row['allowSignup']==1 && $row['needsApproval']==1 ? 1 : ($row['allowSignup']==1 ? 2 : 0));

			// get group permissions for each table
			$res=sql("select * from membership_grouppermissions where groupID='$groupID'");
			while($row=mysql_fetch_assoc($res)){
				$tableName=$row['tableName'];
				$vIns=$tableName."_insert";
				$vUpd=$tableName."_edit";
				$vDel=$tableName."_delete";
				$vVue=$tableName."_view";
				$$vIns=$row['allowInsert'];
				$$vUpd=$row['allowEdit'];
				$$vDel=$row['allowDelete'];
				$$vVue=$row['allowView'];
			}
		}else{
			// no such group exists
			echo "<div class=\"error\">Error: Group not found!</div>";
			$groupID=0;
		}
	}
?>
<h1><?php echo ($groupID ? "Edit Group '$name'" : "Add New Group"); ?></h1>
<?php if($anonGroupID==$groupID){ ?>
	<div class="status">Attention! This is the anonymous group.</div>
<?php } ?>
<input type="checkbox" id="showToolTips" value="1" checked><label for="showToolTips">Show tool tips as mouse moves over options</label>
<form method="post" action="pageEditGroup.php">
	<input type="hidden" name="groupID" value="<?php echo $groupID; ?>">
	<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td align="right" class="tdFormCaption" valign="top">
				<div class="formFieldCaption">Group name</div>
				</td>
			<td align="left" class="tdFormInput">
				<input type="text" name="name" <?php echo ($anonGroupID==$groupID ? "readonly" : ""); ?> value="<?php echo $name; ?>" size="20" class="formTextBox">
				<br />
				<?php if($anonGroupID==$groupID){ ?>
					The name of the anonymous group is read-only here.
				<?php }else{ ?>
					If you name the group '<?php echo $adminConfig['anonymousGroup']; ?>', it will be considered the anonymous group<br />
					that defines the permissions of guest visitors that do not log into the system.
				<?php } ?>
				</td>
			</tr>
		<tr>
			<td align="right" valign="top" class="tdFormCaption">
				<div class="formFieldCaption">Description</div>
				</td>
			<td align="left" class="tdFormInput">
				<textarea name="description" cols="50" rows="5" class="formTextBox"><?php echo $description; ?></textarea>
				</td>
			</tr>
		<?php if($anonGroupID!=$groupID){ ?>
		<tr>
			<td align="right" valign="top" class="tdFormCaption">
				<div class="formFieldCaption">Allow visitors to sign up?</div>
				</td>
			<td align="left" class="tdFormInput">
				<?php
					echo htmlRadioGroup(
						"visitorSignup",
						array(0, 1, 2),
						array(
							"No. Only the admin can add users.",
							"Yes, and the admin must approve them.",
							"Yes, and automatically approve them."
						),
						($groupID ? $visitorSignup : $adminConfig['defaultSignUp'])
					);
				?>
				</td>
			</tr>
		<?php } ?>
		<tr>
			<td colspan="2" align="right" class="tdFormFooter">
				<input type="submit" name="saveChanges" value="Save changes">
				</td>
			</tr>
		<tr>
			<td colspan="2" class="tdFormHeader">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td class="tdFormHeader" colspan="5"><h2>Table permissions for this group</h2></td>
						</tr>
					<?php
						// permissions arrays common to the radio groups below
						$arrPermVal=array(0, 1, 2, 3);
						$arrPermText=array("No", "Owner", "Group", "All");
					?>
					<tr>
						<td class="tdHeader"><div class="ColCaption">Table</div></td>
						<td class="tdHeader"><div class="ColCaption">Insert</div></td>
						<td class="tdHeader"><div class="ColCaption">View</div></td>
						<td class="tdHeader"><div class="ColCaption">Edit</div></td>
						<td class="tdHeader"><div class="ColCaption">Delete</div></td>
						</tr>
				<!-- Diseases table -->
					<tr>
						<td class="tdCaptionCell" valign="top">Diseases</td>
						<td class="tdCell" valign="top">
							<input onMouseOver="stm(diseases_addTip, toolTipStyle);" onMouseOut="htm();" type="checkbox" name="diseases_insert" value="1" <?php echo ($diseases_insert ? "checked class=\"highlight\"" : ""); ?>>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("diseases_view", $arrPermVal, $arrPermText, $diseases_view, "highlight");
							?>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("diseases_edit", $arrPermVal, $arrPermText, $diseases_edit, "highlight");
							?>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("diseases_delete", $arrPermVal, $arrPermText, $diseases_delete, "highlight");
							?>
							</td>
						</tr>
				<!-- Patients table -->
					<tr>
						<td class="tdCaptionCell" valign="top">Patients</td>
						<td class="tdCell" valign="top">
							<input onMouseOver="stm(patients_addTip, toolTipStyle);" onMouseOut="htm();" type="checkbox" name="patients_insert" value="1" <?php echo ($patients_insert ? "checked class=\"highlight\"" : ""); ?>>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("patients_view", $arrPermVal, $arrPermText, $patients_view, "highlight");
							?>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("patients_edit", $arrPermVal, $arrPermText, $patients_edit, "highlight");
							?>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("patients_delete", $arrPermVal, $arrPermText, $patients_delete, "highlight");
							?>
							</td>
						</tr>
				<!-- Symptoms table -->
					<tr>
						<td class="tdCaptionCell" valign="top">Symptoms</td>
						<td class="tdCell" valign="top">
							<input onMouseOver="stm(symptoms_addTip, toolTipStyle);" onMouseOut="htm();" type="checkbox" name="symptoms_insert" value="1" <?php echo ($symptoms_insert ? "checked class=\"highlight\"" : ""); ?>>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("symptoms_view", $arrPermVal, $arrPermText, $symptoms_view, "highlight");
							?>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("symptoms_edit", $arrPermVal, $arrPermText, $symptoms_edit, "highlight");
							?>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("symptoms_delete", $arrPermVal, $arrPermText, $symptoms_delete, "highlight");
							?>
							</td>
						</tr>
				<!-- Disease symptoms table -->
					<tr>
						<td class="tdCaptionCell" valign="top">Disease symptoms</td>
						<td class="tdCell" valign="top">
							<input onMouseOver="stm(disease_symptoms_addTip, toolTipStyle);" onMouseOut="htm();" type="checkbox" name="disease_symptoms_insert" value="1" <?php echo ($disease_symptoms_insert ? "checked class=\"highlight\"" : ""); ?>>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("disease_symptoms_view", $arrPermVal, $arrPermText, $disease_symptoms_view, "highlight");
							?>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("disease_symptoms_edit", $arrPermVal, $arrPermText, $disease_symptoms_edit, "highlight");
							?>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("disease_symptoms_delete", $arrPermVal, $arrPermText, $disease_symptoms_delete, "highlight");
							?>
							</td>
						</tr>
				<!-- Patient symptoms table -->
					<tr>
						<td class="tdCaptionCell" valign="top">Patient symptoms</td>
						<td class="tdCell" valign="top">
							<input onMouseOver="stm(patient_symptoms_addTip, toolTipStyle);" onMouseOut="htm();" type="checkbox" name="patient_symptoms_insert" value="1" <?php echo ($patient_symptoms_insert ? "checked class=\"highlight\"" : ""); ?>>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("patient_symptoms_view", $arrPermVal, $arrPermText, $patient_symptoms_view, "highlight");
							?>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("patient_symptoms_edit", $arrPermVal, $arrPermText, $patient_symptoms_edit, "highlight");
							?>
							</td>
						<td class="tdCell">
							<?php
								echo htmlRadioGroup("patient_symptoms_delete", $arrPermVal, $arrPermText, $patient_symptoms_delete, "highlight");
							?>
							</td>
						</tr>
					</table>
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