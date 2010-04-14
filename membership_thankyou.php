<?php
	$d=dirname(__FILE__);
	include("$d/defaultLang.php");
	include("$d/language.php");
	include("$d/lib.php");
	include("$d/header.php");

	if($_GET['redir']==1){
		echo '<META HTTP-EQUIV="Refresh" CONTENT="5;url=index.php?signIn=1">';
	}
?>

<center>
	<div style="width: 500px; text-align: left;">
		<h1 class="TableTitle"><?php echo $Translation['thanks']; ?></h1>

		<img src="handshake.jpg"><br /><br />
		<div class="TableBody">
			<?php echo $Translation['sign in no approval']; ?>
			</div><br />
		<div class="TableBody">
			<?php echo $Translation['sign in wait approval']; ?>
			</div>
		</div>
	</center>
<?php include("$d/footer.php"); ?>
