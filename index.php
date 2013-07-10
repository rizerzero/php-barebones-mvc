<?php
/* - - - - - - -
 * Based on tutorial from:
 * http://www.phpro.org/tutorials/Model-View-Controller-MVC.html
 * - - - - - - */
session_start();
error_reporting(E_ALL);

/* define the site path constant */
$site_path = realpath(dirname(__FILE__));
define ('__SITE_PATH', $site_path);

require '_includes/inc.init.php';

$registry->session = new Session($registry);

$registry->router = new Router($registry);
$registry->router->setPath(__SITE_PATH . '/_controller/');

$registry->template = new Template($registry);

$registry->router->loader();

/*echo '<pre>';
var_dump($registry);
var_dump($_GET);
echo '</pre>';*/
