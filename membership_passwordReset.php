<?php
	$d=dirname(__FILE__);
	include("$d/defaultLang.php");
	include("$d/language.php");
	include("$d/lib.php");
	include("$d/header.php");

#_______________________________________________________________________________
# Step 4: Final step; change the password
#_______________________________________________________________________________
	if($_POST['changePassword'] && $_SESSION['resetUsername']!=$adminConfig['adminUsername']){
		echo StyleSheet();
		if($_POST['key']!=$_SESSION['resetKey'] || !$_POST['key']){
			?>
			<div class="Error">
				<?php echo $Translation['password reset invalid']; ?>
				</div>
			<?php
			exit;
		}

		if($_POST['newPassword']!=$_POST['confirmPassword'] || !$_POST['newPassword']){
			?>
			<div class="Error">
				<?php echo $Translation['password no match']; ?>
				</div>
			<?php
			exit;
		}

		sql("update membership_users set passMD5='".md5($_POST['newPassword'])."' where lcase(memberID)='".strtolower($_SESSION['resetUsername'])."'");

		?>
		<div style="width:500px; margin:0px auto; text-align:left;">
			<div class="TableTitle">
				<?php echo $Translation['password reset done']; ?>
				</div>
			</div>
		<?php

		exit;
	}
#_______________________________________________________________________________
# Step 3: This is the special link that came to the member by email. This is
#         where the member enters his new password.
#_______________________________________________________________________________
	if($_GET['key']){
		echo StyleSheet();
		if($_GET['key']==$_SESSION['resetKey'] &&  $_SESSION['resetUsername']!=$adminConfig['adminUsername']){
			$res=sql("select * from membership_users where lcase(memberID)='".strtolower($_SESSION['resetUsername'])."'");
			if(!$row=mysql_fetch_assoc($res)){
				?>
				<div class="Error">
					<?php echo $Translation['password reset invalid']; ?>
					</div>
				<?php
				exit;
			}
			?>
			<div align="center">
				<form method="post" action="membership_passwordReset.php">
					<table border="0" cellspacing="1" cellpadding="4" align="center" width="500">
						<tr>
							<td colspan="2" class="TableHeader">
								<div class="TableTitle"><?php echo $Translation['password change']; ?></div>
								</td>
							</tr>
						<tr>
							<td align="right" class="TableHeader" width="160" <?php echo $highlight; ?>>
								<?php echo $Translation['username']; ?>
								</td>
							<td align="left" class="TableBody" width="340">
								<?php echo $row['memberID']; ?>
								</td>
							</tr>
						<tr>
							<td align="right" class="TableHeader">
								<?php echo $Translation['new password']; ?>
								</td>
							<td align="left" class="TableBody">
								<input type="password" name="newPassword" value="" size="20" class="TextBox">
								</td>
							</tr>
						<tr>
							<td align="right" class="TableHeader">
								<?php echo $Translation['confirm password']; ?>
								</td>
							<td align="left" class="TableBody">
								<input type="password" name="confirmPassword" value="" size="20" class="TextBox">
								</td>
							</tr>
						<tr>
							<td colspan="2" align="right" class="TableHeader">
								<input type="submit" name="changePassword" value="<?php echo $Translation['ok']; ?>">
								</td>
							</tr>
						</table>
						<input type="hidden" name="key" value="<?php echo $_GET['key']; ?>">
					</form>
				</div>
			<?php
			exit;
		}else{
			?>
			<div class="Error">
				<?php echo $Translation['password reset invalid']; ?>
				</div>
			<?php
			exit;
		}
	}
#_______________________________________________________________________________
# Step 2: Send email to member containing the reset link
#_______________________________________________________________________________
	if($_POST['reset']){
		$username=makeSafe(strtolower($_POST['username']));
		$email=isEmail($_POST['email']);

		if((!$username && !$email) || ($username==$adminConfig['adminUsername'])){
			redirect("membership_passwordReset.php?emptyData=1");
		}

		echo StyleSheet();

		$res=sql("select * from membership_users where lcase(memberID)='$username' or email='$email' limit 1");
		if(!$row=mysql_fetch_assoc($res)){
			?>
			<div class="Error">
				<?php echo $Translation['password reset invalid']; ?>
				</div>
			<?php
			exit;
		}else{
			// avoid admin password change
			if($row['memberID']==$adminConfig['adminUsername']){
				?>
				<div class="Error">
					<?php echo $Translation['password reset invalid']; ?>
					</div>
				<?php
				exit;
			}

			// generate and store unique key
			$key=md5(microtime());
			$_SESSION['resetKey']=$key;
			$_SESSION['resetUsername']=$row['memberID'];

			// determine password reset URL
			$host=$_SERVER['HTTP_HOST'];
			$uri=rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$http=(strtolower($_SERVER['HTTPS']) == 'on' ? 'https:' : 'http:');
			$ResetLink="$http//$host$uri/"."membership_passwordReset.php?key=$key";

			// send reset instructions
			@mail($row['email'], $Translation['password reset subject'], str_replace('<ResetLink>', $ResetLink, $Translation['password reset message']), "From: ".$adminConfig['senderName']." <".$adminConfig['senderEmail'].">");
		}

		// display confirmation
		?>
		<div style="width:500px; margin:0px auto; text-align:left;">
			<div class="TableTitle">
				<?php echo $Translation['password reset ready']; ?>
				</div>
			</div>
		<?php
		exit;
	}

#_______________________________________________________________________________
# Step 1: get the username or email of the member who wants to reset his password
#_______________________________________________________________________________
	echo StyleSheet();

	if($_GET['emptyData']){
		$highlight="style=\"color: red;\"";
	}

	?>


	<div align="center">
		<form method="post" action="membership_passwordReset.php">
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="500">
				<tr>
					<td colspan="2" class="TableHeader">
						<div class="TableTitle"><?php echo $Translation['password reset']; ?></div>
						</td>
					</tr>
				<tr>
					<td colspan="2" class="TableBody" align="left">
						<div class="TableBody"><?php echo $Translation['password reset details']; ?></div>
						</td>
					</tr>
				<tr>
					<td align="right" class="TableHeader" width="160" <?php echo $highlight; ?>>
						<?php echo $Translation['username']; ?>
						</td>
					<td align="left" class="TableBody" width="340">
						<input type="text" name="username" value="" size="20" class="TextBox">
						</td>
					</tr>
				<tr>
					<td align="right" class="TableHeader" <?php echo $highlight; ?>>
						<?php echo '<i>'.$Translation['or'].':</i> '.$Translation['email']; ?>
						</td>
					<td align="left" class="TableBody">
						<input type="text" name="email" value="" size="45" class="TextBox">
						</td>
					</tr>
				<tr>
					<td colspan="2" align="right" class="TableHeader">
						<input type="submit" name="reset" value="<?php echo $Translation['ok']; ?>">
						</td>
					</tr>
				<tr>
					<td colspan="2" align="center" class="TableHeader">
						<?php echo $Translation['browse as guest']; ?>
						</td>
					</tr>
				</table>
			</form>
		<br /><br />
		<div class="TableFooter">
			<b><a href=http://www.bigprof.com/appgini/>BigProf Software</a> - <?php echo $Translation['powered by']; ?> AppGini 4.52</b>
			</div>
		</div>
	<?php
?>
<?php include("$d/footer.php"); ?>