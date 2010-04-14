<?php

	$d=dirname(__FILE__);
	$x->TableTitle=$Translation['homepage'];
	include("$d/defaultLang.php");
	include("$d/language.php");
	include("$d/incCommon.php");
	include("$d/header.php");

	if($_GET['signOut']==1){
		logOutMember();
	}

	$tablesPerRow=2;
	$arrTables=getTableList();

	?>
	<div align="center"><table cellpadding="8">
		<?php if($_GET['loginFailed']==1 || $_GET['signIn']==1){ ?>
		<tr><td colspan="2" align="center">
			<?php if($_GET['loginFailed']){ ?>
			<div class="Error"><?php echo $Translation['login failed']; ?></div>
			<?php } ?>
			<form method="post" action="index.php">
				<table border="0" cellspacing="1" cellpadding="4" align="center">
					<tr>
						<td colspan="2" class="TableHeader">
							<div class="TableTitle"><?php echo $Translation['sign in here']; ?></div>
							</td>
						</tr>
					<tr>
						<td align="right" class="TableHeader">
							<?php echo $Translation['username']; ?>
							</td>
						<td align="left" class="TableBody">
							<input type="text" name="username" value="" size="20" class="TextBox">
							</td>
						</tr>
					<tr>
						<td align="right" class="TableHeader">
							<?php echo $Translation['password']; ?>
							</td>
						<td align="left" class="TableBody">
							<input type="password" name="password" value="" size="20"class="TextBox">
							</td>
						</tr>
					<tr>
						<td colspan="2" align="right" class="TableHeader">
							<span style="margin: 0 20px;"><input type="checkbox" name="rememberMe" id="rememberMe" value="1"> <label for="rememberMe"><?php echo $Translation['remember me']; ?></label></span>
							<input type="submit" name="signIn" value="<?php echo $Translation['sign in']; ?>">
							</td>
						</tr>
					<tr>
						<td colspan="2" align="left" class="TableHeader">
							<?php echo $Translation['go to signup']; ?>
							<br /><br />
							</td>
						</tr>
					<tr>
						<td colspan="2" align="left" class="TableHeader">
							<?php echo $Translation['forgot password']; ?>
							<br /><br />
							</td>
						</tr>
					<tr>
						<td colspan="2" align="left" class="TableHeader">
							<?php echo $Translation['browse as guest']; ?>
							<br /><br />
							</td>
						</tr>
					</table>
				</form>
				<script>document.getElementsByName('username')[0].focus();</script>
			</td></tr>
		<?php } ?>
	<?php
		if(!$_GET['signIn'] && !$_GET['loginFailed']){
			if(is_array($arrTables)){
				if(getLoggedAdmin()){
					?><tr><td colspan="<?php echo ($tablesPerRow*3-1); ?>" class="TableTitle" style="text-align: center;"><a href="admin/"><img src=table.gif border=0 align="top"></a> <a href="admin/" class="TableTitle" style="color: red;"><?php echo $Translation['admin area']; ?></a><br /><br /></td></tr><?php
				}
				$i=0;
				foreach($arrTables as $tn=>$tc){
					$tChk=array_search($tn, array());
					if($tChk!==false && $tChk!==null){
						$searchFirst='?Filter_x=1';
					}else{
						$searchFirst='';
					}
					if(!$i % $tablesPerRow){ echo '<tr>'; }
					?><td valign="top"><a href=<?php echo $tn; ?>_view.php<?php echo $searchFirst; ?>><img src=<?php echo $tc[2];?> border=0></a></td><td valign="top" align="left"><a href=<?php echo $tn; ?>_view.php<?php echo $searchFirst; ?> class="TableTitle"><?php echo $tc[0]; ?></a><br /><?php echo $tc[1]; ?></td><?php
					if($i % $tablesPerRow == ($tablesPerRow - 1)){ echo '</tr>'; }else{ echo '<td width="50">&nbsp;</td>'; }
					$i++;
				}
			}else{
				?><tr><td><div class="Error"><?php echo $Translation['no table access']; ?><script language="javaScript">setInterval("window.location='index.php?signOut=1'", 2000);</script></div></td></tr><?php
			}
		}
?>

</table><br /><br /><div class="TableFooter"><b><a href=http://www.bigprof.com/appgini/>BigProf Software</a> - <?php echo $Translation['powered by']; ?> AppGini 4.52</b></div>

</div>
</html>
