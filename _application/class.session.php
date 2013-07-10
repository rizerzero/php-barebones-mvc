<?php
class Session {

private $registry;
private $sess_id;

private $current_user;
private $user_info;
private $user_groups = array();

public function __construct($registry) {
	$this->registry = $registry;
}

public function isLoggedIn() {
	return isset($this->current_user);
}

public function processLogin($username, $password) {
	$pb = new PBKDF2();
	$query_file = __SITE_PATH . '/_queries/user_login.sql'; //TODO: un-kludge
	$query = sprintf(file_get_contents($query_file), $this->registry->db->getTablePrefix());
	$rs = $this->registry->db->doQuery($query, array($username));
	if( empty($rs) || !$pb->validate_password($password, $rs[0]['user_pw']) ) {
		return array(0, 'Login Unsuccessful');
	} else {
		$this->current_user = $rs[0]['user_id'];

		$query_file = __SITE_PATH . '/_queries/user_info.sql'; //TODO: un-kludge
		$query = sprintf(file_get_contents($query_file), $this->registry->db->getTablePrefix());
		$rs = $this->registry->db->doQuery($query, array($this->current_user));
		$this->user_info = $rs[0];
		unset($this->user_info['user_pw']);

		$query_file = __SITE_PATH . '/_queries/user_groups.sql'; //TODO: un-kludge
		$query = sprintf(file_get_contents($query_file), $this->registry->db->getTablePrefix());
		$rs = $this->registry->db->doQuery($query, array($this->current_user));
		if( !empty($rs) ) {
			foreach($rs as $row) {
				$this->user_groups[] = $row['group_map_gid'];
			}
		}		

		return array(1, 'Login Successful');
	}
}

} // -- end class Session --
