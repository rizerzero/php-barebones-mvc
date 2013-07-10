<?php
class Router {

private $registry;

private $path; // the controller directory path

private $args = array();
public $file;
public $controller;
public $action;

function __construct($registry) {
	$this->registry = $registry;
}

/* set controller directory path */
function setPath($path) {
	if (is_dir($path) == false) {
		throw new Exception ('Invalid controller path: `' . $path . '`');
	}
	/*** set the path ***/
	$this->path = $path;
}

/* load the controller */
public function loader() {
	/*** check the route ***/
	$this->getController();

	/*** if the file is not there diaf ***/
	if( !is_readable($this->file) ) {
		throw new Exception('Cannot load file: ' . $this->file);
	}

	/*** include the controller ***/
	include $this->file;

	/*** a new controller class instance ***/
	$class = ucfirst($this->controller) . 'Controller';
	$controller = new $class($this->registry);

	/*** check if the action is callable ***/
	if( !is_callable(array($controller, $this->action)) ) {
		$action = 'index';
	} else {
		$action = $this->action;
	}
	/*** run the action ***/
	$controller->$action();
}

 /* @get the controller */
private function getController() {
	/* get the route from the url */
	$route = (empty($_GET['rt'])) ? '' : $_GET['rt'];

	$this->registry->route_raw = $route;

	if( empty($route) ) {
		$route = 'index';
	} else {
		/*** get the parts of the route ***/
		$parts = explode('/', $route);
		$this->controller = array_shift($parts);
		if( !empty($parts) ) {
			$this->action = array_shift($parts);
		}
		$this->registry->route_remainder = $parts;
	}

	if( empty($this->controller) ) {
		$this->controller = 'index';
	}

	/*** Get action ***/
	if( empty($this->action) ) {
		$this->action = 'index';
	}

	/*** set the file path ***/
	$this->file = $this->path . 'class.' . $this->controller . 'controller.php';
}

} //-- end class Router --
