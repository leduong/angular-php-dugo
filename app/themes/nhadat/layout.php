<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<base href="<?php echo HTTP_SERVER;?>/" />
		<title></title>
		<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0"/>
		<meta name="description" content="">
		<meta name="author" content="">
		<!-- Le styles -->
		<link href="/css/bootstrap.min.css" rel="stylesheet">
		<link href="/css/bootstrap-responsive.css" rel="stylesheet">
		<link href="/css/font-awesome.css" rel="stylesheet">
		<!--[if IE 7]>
		<link rel="stylesheet" href="/css/font-awesome-ie7.min.css">
		<![endif]-->
		<link href="/css/style.css" rel="stylesheet">
		<link href="/css/rentia.css" rel="stylesheet">
		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="js/html5shiv.js"></script>
		<![endif]-->
		<!-- Fav and touch icons -->
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="/ico/apple-touch-icon-144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/ico/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="/ico/apple-touch-icon-57-precomposed.png">
	</head>
	<body ng-app="app">
		<header>
			<div class="nhadat"><a href="/" title="nhadat">nhadat.com</a></div>
			<mainnav ng-controller="HeaderCtrl"></mainnav>
		</header>
		<!-- /Header -->
		<flash></flash>
		<div id="mainpage" ng-view>
		<?php if(isset($content)) echo $content; ?>
		</div>
		<a href="#" class="scroll-top"><i class="icon-chevron-up"></i></a>
		<footer>
			&copy; 2013 nhadat.com
		</footer>
		<!-- Finally load libraries -->
		<script src="js/lib/jquery/jquery.min.js"></script>
		<script src="js/lib/angular/angular.min.js"></script>
		<script src="js/lib/angular/angular-sanitize.min.js"></script>
		<script src="js/lib/bootstrap/bootstrap.min.js"></script>
		<!-- App load libraries -->
		<script src="js/underscore.js"></script>
		<script src="js/app.js"></script>
		<script src="js/services.js"></script>
		<script src="js/controllers.js"></script>
		<script src="js/filters.js"></script>
		<script src="js/directives.js"></script>
		<script src="js/ui-bootstrap.js"></script>
		<script src="js/google-maps.js"></script>
		<!-- Themes -->
		<script src="js/init.js"></script>
		<script src="http://maps.googleapis.com/maps/api/js?sensor=false&language=en"></script>
	</body>
</html>