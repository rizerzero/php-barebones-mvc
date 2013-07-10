<?php

class IndexController extends BaseController {

public function index() {
	/*** set a template variable ***/
	$this->registry->template->page_title = 'Welcome to PHPRO MVC';

	/*** load the index template ***/
	$this->registry->template->show('index');
}

} //-- end of class IndexController
