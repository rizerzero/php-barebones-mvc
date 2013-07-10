<?php
require __SITE_PATH . '/_application/' . 'class.basecontroller.php';
require __SITE_PATH . '/_application/' . 'class.registry.php';
require __SITE_PATH . '/_application/' . 'class.router.php';
require __SITE_PATH . '/_application/' . 'class.template.php';
require __SITE_PATH . '/_application/' . 'class.session.php';

/* auto load model classes */
function __autoload($class_name) {
	$filename = 'class.' . strtolower($class_name) . '.php';
	$file = __SITE_PATH . '/_model/' . $filename;
	if( file_exists($file) == false ) {
		return false;
	}
	require($file);
}

$registry = new Registry();

/* initialize the database connection */
require('_includes/inc.db_cfg.php');
$registry->db = Mysql_DB::getInstance();
$registry->db->initDB($database_credentials);
unset($database_credentials);

$registry->http_root = 'http://fubes2000.savagenoodle.com/sandbox/mvc/';
