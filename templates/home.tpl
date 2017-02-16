<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
		<title><?= UTF8::encode($this->get_value("pgtitle")) ?></title>
		<meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        
        <!-- Stylesheets -->
		<link rel="stylesheet" href="/assets/css/normalize.min.css">
		<link rel="stylesheet" href="/assets/css/font-awesome.min.css">
		<link rel="stylesheet" href="/assets/css/main.css">
		
		<!-- Javascript -->
		<script type="text/javascript" src="/assets/js/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="/assets/js/dropzone.js"></script>
        <script type="text/javascript" src="/assets/editor/ckeditor.js"></script>
		<script type="text/javascript" src="/assets/editor/adapters/jquery.js"></script>
	</head>
	<body>
		<div class="wrapper">
		<?php if (Session::get_instance()->get_property("user") != null) { ?>
			<div class="user">
				<p>Sie sind eingeloggt als <strong><?= Session::get_instance()->get_property("user") ?> ( <a href="/app/logout">Abmelden</a></strong> )</p>
			</div>
			
			
		<?php } ?>
		
		
			