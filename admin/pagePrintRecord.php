<?php
	$d=dirname(__FILE__);
	require("$d/incCommon.php");
?>
<!doctype html public "-//W3C//DTD html 4.0 //en">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link rel="stylesheet" type="text/css" href="adminStyles.css">
		<title>Membership Management -- Record details</title>
		</head>
	<body>
		<div align="center">

<?php
	$recID=makeSafe($_GET['recID']);
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
			die("<div class=\"status\">Error: Record not found!</div>");
		}
	}


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
		<h2>Table: <?php echo $tableName; ?></h2>
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



	include("$d/incFooter.php");
?>