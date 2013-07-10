<?php
/*
 * Password hashing with PBKDF2.
 * Author: havoc AT defuse.ca
 * www: https://defuse.ca/php-pbkdf2.htm
 *
 * Converted to a class by Sammitch.
 *  http://sammitch.ca
 */

class PBKDF2 {
	public $hash_algorithm, $iterations, $salt_bytes, $hash_bytes;
	
	private $hash_info = array(
		'sections'		=> 4,
		'algo_index'	=> 0,
		'iter_index'	=> 1,
		'salt_index'	=> 2,
		'pbkdf2_index'	=> 3
	);
	
	//public function __constuct($algo = 'sha256', $iter = 2048, $salt_b = 24, $hash_b = 24) {
	public function PBKDF2($algo = 'sha256', $iter = 2048, $salt_b = 24, $hash_b = 24) {
		$this->hash_algorithm	= $algo;
		$this->iterations		= $iter;
		$this->salt_bytes		= $salt_b;
		$this->hash_bytes		= $hash_b;
	}

	public function create_hash($password) {
		// format: algorithm:iterations:salt:hash
		$salt = base64_encode(mcrypt_create_iv($this->salt_bytes, MCRYPT_DEV_URANDOM));
		return $this->hash_algorithm . ":" . $this->iterations . ":" .  $salt . ":" . 
			base64_encode($this->pbkdf2_hash(
				$this->hash_algorithm,
				$password,
				$salt,
				$this->iterations,
				$this->hash_bytes,
				true
			));
	}

	public function validate_password($password, $good_hash) {
		$params = explode(":", $good_hash);
		if(count($params) < $this->hash_info['sections'])
		   return false; 
		$pbkdf2 = base64_decode($params[$this->hash_info['pbkdf2_index']]);
		return $this->slow_equals(
			$pbkdf2,
			$this->pbkdf2_hash(
				$params[$this->hash_info['algo_index']],
				$password,
				$params[$this->hash_info['salt_index']],
				(int)$params[$this->hash_info['iter_index']],
				strlen($pbkdf2),
				true
			)
		);
	}

	// Compares two strings $a and $b in length-constant time.
	private function slow_equals($a, $b) {
		$diff = strlen($a) ^ strlen($b);
		for($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}
		return $diff === 0; 
	}

	/*
	 * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
	 * $algorithm - The hash algorithm to use. Recommended: SHA256
	 * $password - The password.
	 * $salt - A salt that is unique to the password.
	 * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
	 * $key_length - The length of the derived key in bytes.
	 * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
	 * Returns: A $key_length-byte key derived from the password and salt.
	 *
	 * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
	 *
	 * This implementation of PBKDF2 was originally created by https://defuse.ca
	 * With improvements by http://www.variations-of-shadow.com
	 */
	private function pbkdf2_hash($algorithm, $password, $salt, $count, $key_length, $raw_output = false) {
		$algorithm = strtolower($algorithm);
		if(!in_array($algorithm, hash_algos(), true)) {
			throw new Exception('PBKDF2 ERROR: Invalid hash algorithm.');
		}
		if($count <= 0 || $key_length <= 0) {
			throw new Exception('PBKDF2 ERROR: Invalid parameters.');
		}

		$hash_length = strlen(hash($algorithm, "", true));
		$block_count = ceil($key_length / $hash_length);

		$output = "";
		for($i = 1; $i <= $block_count; $i++) {
			// $i encoded as 4 bytes, big endian.
			$last = $salt . pack("N", $i);
			// first iteration
			$last = $xorsum = hash_hmac($algorithm, $last, $password, true);
			// perform the other $count - 1 iterations
			for ($j = 1; $j < $count; $j++) {
				$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
			}
			$output .= $xorsum;
		}

		if($raw_output) {
			return substr($output, 0, $key_length);
		} else {
			return bin2hex(substr($output, 0, $key_length));
		}
	}

	

}
