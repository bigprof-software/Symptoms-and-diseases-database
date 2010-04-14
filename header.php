<?php $htmlUserBar=htmlUserBar(); ?>
<!doctype html public "-//W3C//DTD html 4.0 //en">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>symptoms and diseases | <?php echo $x->TableTitle; ?></title>
		<script src="resources/lightbox/js/prototype.js"></script>
		<script src="resources/lightbox/js/scriptaculous.js?load=effects,builder"></script>
		<script src="resources/lightbox/js/lightbox.js"></script>
		<link rel="stylesheet" type="text/css" href="resources/lightbox/css/lightbox.css" media="screen">
		<link rel="stylesheet" type="text/css" href="style.css">
		</head>
	<body>
		<!-- Add header template below here .. -->

		<?php echo $htmlUserBar; ?>
		<!-- process notifications -->
		<?php echo showNotifications(); ?>
