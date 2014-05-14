<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<base href="http://<?php echo DOMAIN;?>/" />
		<title>Nhà đất, Nha dat, Nhà ở, Nha o, nhà phố, nha pho, nhà vườn, nha
		vuon, biệt thự, biet thu, chung cư, chung cu, căn hộ, can ho, cao ốc, cao oc, khu quy hoạch, khu qui hoach</title>
		<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0"/>
		<meta name="description" content="Nhà đất, Nha dat, Nhà ở, Nha o, nhà phố, nha pho, nhà vườn, nha
		vuon, biệt thự, biet thu, chung cư, chung cu, căn hộ, can ho, cao ốc, cao oc, khu quy hoạch, khu qui hoach">
		<meta name="author" content="Nhà đất, Nha dat, Nhà ở, Nha o, nhà phố, nha pho, nhà vườn, nha
		vuon, biệt thự, biet thu, chung cư, chung cu, căn hộ, can ho, cao ốc, cao oc, khu quy hoạch, khu qui hoach">
		<!-- Le styles -->
		<link href="css/bootstrap3.min.css" rel="stylesheet">
		<link href="css/font-awesome.css" rel="stylesheet">
		<!--[if IE 7]>
		<link rel="stylesheet" href="css/font-awesome-ie7.min.css">
		<![endif]-->
		<link href="css/index.css?<?php echo rand(); ?>" rel="stylesheet">
		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="/js/html5shiv.js"></script>
		<![endif]-->
		<!-- Fav and touch icons -->
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png">
		<!-- Finally load libraries -->
		<script src="/lib/jquery/jquery-1.10.2.js"></script>
		<script src="/lib/jquery/jquery.form.min.js"></script>
		<script src="/lib/angular/angular.min.js"></script>
		<script src="/lib/angular/angular-route.min.js"></script>
		<script src="/lib/angular/angular-sanitize.min.js"></script>
		<script src="/lib/angular/angular-touch.min.js"></script>
		<script src="/lib/angular/angular-animate.min.js"></script>
		<!-- App load libraries -->
		<script src="/js/underscore.js"></script>
		<script src="/js/app.js?<?php echo rand(); ?>"></script>
		<script src="/js/controllers.js?<?php echo rand(); ?>"></script>
		<script src="/js/services.js?<?php echo rand(); ?>"></script>
		<script src="/js/directives.js?<?php echo rand(); ?>"></script>
		<script src="/js/filters.js?<?php echo rand(); ?>"></script>
		<script src="/js/ui-bootstrap.js"></script>
		<script src="/js/google-maps.js"></script>
		<script src="/js/facebook.js"></script>
		<!-- Themes -->
		<script src="//maps.googleapis.com/maps/api/js?sensor=false&language=vi"></script>
		<script src="/js/init.js"></script>
		<script>angular.module("app").constant("CSRF_TOKEN", '<?php echo token(); ?>');</script>
	</head>
	<body ng-app="app">
		<header ng-hide="isIntro">
			<div class="nhadat">
				<a href="/" title="nhadat">nhadat.com</a>
				<div class="ver">beta</div>
			</div>
			<mainnav></mainnav>
		</header>
		<flash></flash>
		<mainpage></mainpage>
		<scrolltop></scrolltop>
	<footer app-version></footer>
</body>
</html>