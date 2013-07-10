<?php
abstract class BaseController {

protected $registry;

function __construct($registry) {
	$this->registry = $registry;
}

/**
 * @all controllers must contain an index method
 */
abstract function index();

} //-- end class BaseController --
