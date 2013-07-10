<?php
	$this->addMeta( array(
		'http-equiv'	=> 'Content-Type',
		'content'		=> 'text/html; charset=utf-8'
	));
	$this->addStylesheet('css/reset.css');
	$this->addStylesheet('css/style.css');
?><!DOCTYPE HTML>
<html>
<head>
	<title><?php echo $page_title; ?></title>
<?php echo $this->getHTMLHeaders("\t"); ?>
</head>
<body>
<div id="container">
	<div id="content">
		<div id="header">
			<h1>header</h1>
			<!--<ul id="topmenu">
				<li><a href="#">Inactive Tab</a></li>
				<li><a href="#">Inactive Tab</a></li>
				<li><a href="#" class="active">Active Tab</a></li>
			</ul>-->
		</div>
		<div id="body">
			<div id="sidebar">
				<ul>
					<li><a href="#">Home</a></li>
					<li><a href="#">Blog</a></li>
					<li><a href="#">Application</a></li>
				</ul>
			</div>
			<div id="breadcrumbs">
				<ul>
					<li><a href="#">Main</a></li>
					<li>&raquo;</li>
					<li><a href="#">Category</a></li>
					<li>&raquo;</li>
					<li><a href="#" class="active">Current</a></li>
				</ul>
				<div id="user-info">
					<p>You are currently logged in as <a href="#">user</a> | <a href="#">Control Panel</a> | <a href="#">Logout</a></p>
				</div>
			</div>
			<div id="body-main">
				<h2>body</h2>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse quis diam erat. In pretium, dui pharetra imperdiet auctor, odio lorem commodo diam, in euismod massa neque id eros. Ut id diam sed metus fermentum aliquet et sed enim. Quisque varius, tortor sed porttitor porta, lorem elit fringilla erat, sit amet consectetur magna massa consectetur est. In elit nunc, venenatis at cursus at, dignissim id nisi. Cras orci nisi, bibendum ac pharetra id, ultrices eget urna. Quisque ligula ante, scelerisque venenatis sodales eu, imperdiet tincidunt est. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Duis ornare rhoncus varius. Duis in tortor nisl. Mauris pellentesque nibh justo, sit amet viverra lacus. Mauris bibendum mi a libero dictum ut ullamcorper leo feugiat. </p>
			</div>
		</div>
		<div id="footer">
			<h3>footer</h3>
		</div>
	</div>
</div>
</body>
</html>
