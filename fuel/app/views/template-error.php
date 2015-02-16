<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<?php echo Asset::css('bootstrap.css'); ?>
</head>
<body>
	<div class="container">
		<h1><?php echo $title; ?></h1>
		<hr>
		<?php echo $content; ?>
	</div>
</body>
</html>
