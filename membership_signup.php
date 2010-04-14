<?php
	$d=dirname(__FILE__);
	include("$d/defaultLang.php");
	include("$d/language.php");
	include("$d/lib.php");
	include("$d/header.php");

	if($_POST['signUp']!=''){
		// receive data
		$memberID=makeSafe(strtolower($_POST['memberID']));
		$email=isEmail($_POST['email']);
		$password=$_POST['password'];
		$confirmPassword=$_POST['confirmPassword'];
		$groupID=intval($_POST['groupID']);
		$custom1=makeSafe($_POST['custom1']);
		$custom2=makeSafe($_POST['custom2']);
		$custom3=makeSafe($_POST['custom3']);
		$custom4=makeSafe($_POST['custom4']);

		// validate data
		if($memberID==''){
			?><div class="Error"><?php echo $Translation['username empty']; ?></div><?php
			exit;
		}
		if(strlen($password)<4 || trim($password)!=$password){
			?><div class="Error"><?php echo $Translation['password invalid']; ?></div><?php
			exit;
		}
		if($password!=$confirmPassword){
			?><div class="Error"><?php echo $Translation['password no match']; ?></div><?php
			exit;
		}
		if(sqlValue("select count(1) from membership_users where lcase(memberID)='$memberID'")){
			?><div class="Error"><?php echo $Translation['username exists']; ?></div><?php
			exit;
		}
		if(!$email){
			?><div class="Error"><?php echo $Translation['email invalid']; ?></div><?php
			exit;
		}
		if(!sqlValue("select count(1) from membership_groups where groupID='$groupID' and allowSignup=1")){
			?><div class="Error"><?php echo $Translation['group invalid']; ?></div><?php
			exit;
		}

		// save member data
		$needsApproval=sqlValue("select needsApproval from membership_groups where groupID='$groupID'");
		sql("INSERT INTO `membership_users` set memberID='$memberID', passMD5='".md5($password)."', email='$email', signupDate='".@date('Y-m-d')."', groupID='$groupID', isBanned='0', isApproved='".($needsApproval==1 ? '0' : '1')."', custom1='$custom1', custom2='$custom2', custom3='$custom3', custom4='$custom4', comments='member signed up through the registration form.'");

		// admin mail notification
		if($adminConfig['notifyAdminNewMembers']==2 && !$needsApproval){
			@mail($adminConfig['senderEmail'], '[symptoms_and_diseases] New member signup', "A new member has signed up for symptoms_and_diseases.\n\nMember name: $memberID\nMember group: ".sqlValue("select name from membership_groups where groupID='$groupID'"));
		}elseif($adminConfig['notifyAdminNewMembers']>=1 && $needsApproval){
			@mail($adminConfig['senderEmail'], '[symptoms_and_diseases] New member waiting approval', "A new member has signed up for symptoms_and_diseases.\n\nMember name: $memberID\nMember group: ".sqlValue("select name from membership_groups where groupID='$groupID'"));
		}

		// hook: member_activity
		if(function_exists('member_activity')){
			$args=array();
			member_activity(getMemberInfo($memberID), ($needsApproval ? 'pending' : 'automatic'), $args);
		}

		// redirect to thanks page
		$redirect=($needsApproval ? '' : '?redir=1');
		redirect("membership_thankyou.php$redirect");

		// exit
		exit;
	}
?>
<script>
	function jsValidateSignup(){
		var p1=document.getElementById('password').value;
		var p2=document.getElementById('confirmPassword').value;
		if(p1=='' || p1==p2){
			return true;
		}else{
			window.alert("<?php echo $Translation['password no match']; ?>");
			document.getElementById('password').focus();
			return false;
		}
	}
	</script>

<center>
<h1 class="TableTitle" style="text-align: center;"><?php echo $Translation['sign up here']; ?></h1>

<div class="TableBody" style="width: 400px;"><?php echo $Translation['registered? sign in']; ?></div><br />

<?php
	if(!$cg=sqlValue("select count(1) from membership_groups where allowSignup=1")){
		$noSignup=TRUE;
		?>
		<div class="Error"><?php echo $Translation['sign up disabled']; ?></div>
		<?php
	}
?>

<?php if(!$noSignup){ ?>

<form method="post" action="membership_signup.php" onSubmit="return jsValidateSignup();">
	<table>
		<tr>
			<td align="right" class="TableHeader" valign="top">
				<div class="TableHeader"><?php echo $Translation['username']; ?></div>
				</td>
			<td align="left" class="TableBody">
				<input type="text" name="memberID" id="memberID" value="" size="20" class="TextBox">
				<input type="button" value="<?php echo $Translation['check availability']; ?>" onClick="window.open('checkMemberID.php?memberID='+document.getElementById('memberID').value, 'checkMember', 'innerHeight=100,innerWidth=600,dependent=yes,screenX=200,screenY=200,status=no');">
				</td>
			</tr>
		<tr>
			<td align="right" class="TableHeader" valign="top">
				<div class="TableHeader"><?php echo $Translation['password']; ?></div>
				</td>
			<td align="left" class="TableBody">
				<input type="password" name="password" id="password" value="" size="20" class="TextBox">
				</td>
			</tr>
		<tr>
			<td align="right" class="TableHeader" valign="top">
				<div class="TableHeader"><?php echo $Translation['confirm password']; ?></div>
				</td>
			<td align="left" class="TableBody">
				<input type="password" name="confirmPassword" id="confirmPassword" value="" size="20" class="TextBox">
				</td>
			</tr>
		<tr>
			<td align="right" class="TableHeader" valign="top">
				<div class="TableHeader"><?php echo $Translation['email']; ?></div>
				</td>
			<td align="left" class="TableBody">
				<input type="text" name="email" value="" size="40" class="TextBox">
				</td>
			</tr>
		<tr>
			<td align="right" class="TableHeader" valign="top">
				<div class="TableHeader"><?php echo $Translation['group']; ?></div>
				</td>
			<td align="left" class="TableBody" width="400">
				<?php
					echo htmlSQLSelect('groupID', "select groupID, concat(name, if(needsApproval=1, ' *', ' ')) from membership_groups where allowSignup=1 order by name", ($cg==1 ? sqlValue("select groupID from membership_groups where allowSignup=1 order by name limit 1") : 0 ));
				?>
				<br /><?php echo $Translation['groups *']; ?>
				</td>
			</tr>
	<?php if($adminConfig['custom1']!=''){ ?>
		<tr>
			<td align="right" class="TableHeader" valign="top">
				<div class="TableHeader"><?php echo $adminConfig['custom1']; ?></div>
				</td>
			<td align="left" class="TableBody">
				<input type="text" name="custom1" value="" size="40" class="TextBox">
				</td>
			</tr>
	<?php } ?>
	<?php if($adminConfig['custom2']!=''){ ?>
		<tr>
			<td align="right" class="TableHeader" valign="top">
				<div class="TableHeader"><?php echo $adminConfig['custom2']; ?></div>
				</td>
			<td align="left" class="TableBody">
				<input type="text" name="custom2" value="" size="40" class="TextBox">
				</td>
			</tr>
	<?php } ?>
	<?php if($adminConfig['custom3']!=''){ ?>
		<tr>
			<td align="right" class="TableHeader" valign="top">
				<div class="TableHeader"><?php echo $adminConfig['custom3']; ?></div>
				</td>
			<td align="left" class="TableBody">
				<input type="text" name="custom3" value="" size="40" class="TextBox">
				</td>
			</tr>
	<?php } ?>
	<?php if($adminConfig['custom4']!=''){ ?>
		<tr>
			<td align="right" class="TableHeader" valign="top">
				<div class="TableHeader"><?php echo $adminConfig['custom4']; ?></div>
				</td>
			<td align="left" class="TableBody">
				<input type="text" name="custom4" value="" size="40" class="TextBox">
				</td>
			</tr>
	<?php } ?>
		<tr>
			<td colspan="2" align="right" class="tdFormFooter">
				<input type="submit" name="signUp" value="<?php echo $Translation['sign up']; ?>">
				</td>
			</tr>
		</table>
</form>
<script>document.getElementById('memberID').focus();</script>

<?php } ?>

</center>

<?php include("$d/footer.php"); ?>