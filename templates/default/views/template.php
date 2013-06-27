<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo $title ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php
		foreach ($stylesheets as $style => $media)
		{
			echo HTML::style($style, array('media' => $media), NULL, TRUE), "\n";
		}
		?>

		<!--[if lt IE 9]>
			<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="brand" href="<?php echo URL::base(TRUE) ?>" title="<?php echo $description ?>"><?php echo $sitename ?></a>
					<div class="nav-collapse collapse"></div>
				</div>
			</div>
		</div>

		<div class="container"><?php echo $content ?></div>

		<?php
		foreach ($javascripts as $script)
		{
			echo HTML::script($script, NULL, NULL, TRUE), "\n";
		}
		?>
	</body>
</html>