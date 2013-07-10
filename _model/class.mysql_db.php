<?php

Class Mysql_DB {

private static $instance;

private $dbh;
private $tbl_prefix;

private $query_count;
private $query_time;

private function __construct() {}

public static function getInstance() {
	if( !self::$instance ) {
		self::$instance = new Mysql_DB();
	}
	return self::$instance;
}

public function initDB($conn_info) {
	if( isset($this->dbh) ) { return true; }
	if( ! $this->checkArray($conn_info, 4, 5) ) {
		Throw new Exception("Incorrect number of arguments for MySQL connection.");
	} else if( ! (isset($conn_info['hostname']) && isset($conn_info['dbname']) && isset($conn_info['username']) && isset($conn_info['password'])) ) {
		Throw new Exception("MySQL connection info does not contain all necessary parts.");
	}
	if( isset($conn_info['prefix']) ) {
		$this->tbl_prefix = $conn_info['prefix'];
	} else {
		$this->tbl_prefix = '';
	}

	$uri = sprintf("mysql:host=%s;dbname=%s", $conn_info['hostname'], $conn_info['dbname']);
	$this->dbh = new PDO($uri, $conn_info['username'], $conn_info['password']);

	$this->query_count = 0;
	$this->query_time = 0;
}

public function doQuery($query, $params=NULL) {
	$this->requireInit();
	$timer = microtime(true);
	$dbh = $this->dbh;
	if(!$query) {
		return NULL;
	} else {
		$sth = $dbh->prepare($query);
		if($sth->execute($params)) {
			$this->query_time += microtime(true) - $timer;
			$this->query_count++;
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		} else {
			$err_arr = $sth->errorInfo();
			$err_msg = sprintf("SQLSTATE ERR: %s<br />\nmySQL ERR: %s<br />\nMessage: %s<br />\n", $err_arr[0], $err_arr[1], $err_arr[2]);
			//$sth->debugDumpParams();
			Throw new Exception($err_msg);
		}
	}
}

public function getStats() {
	return array(
		'query_count'	=> $this->query_count,
		'query_time'	=> $this->query_time
	);
}

private function checkArray($arr, $min_ele, $max_ele, $allow_empty=FALSE) {
	$cnt = count($arr);
	if( ($cnt < $min_ele) || ($cnt > $max_ele) ) { return false; }
	else if( !$allow_empty ) {
		foreach( $arr as $element ) {
			if( empty($element) ) { return false; }
		}
	}
	return true;
}

public function getTablePrefix() {
	$this->requireInit();
	return $this->tbl_prefix;
}

private function requireInit() {
	if( !isset($this->dbh) ) {
		throw new Exception('Database connection has not been initialized.');
	}
}

public function isInitialized() {
	return isset($this->dbh);
}

} //-- End of model class
