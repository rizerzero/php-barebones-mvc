<?php

Class Form {

private $fieldtypes = array('text', 'password', 'submit', 'checkbox', 'textarea');

private $name, $target, $method;
private $fields = array();
private $labels = array();

private $fields_validated = array(
	'success' => array(),
	'failure' => array()
);

private $complexity_regexps = array(
	'/[a-z]/',      //lowercase
	'/[A-Z]/',      //uppercase
	'/[^A-Za-z]/'   //non-alpha
);

public function __construct($name, $target, $method='POST') {
	$this->name		= $name;
	$this->target	= $target;
	$this->method	= $method;
}

public function getComplexities() {
	return $this->complexity_regexps;
}

// allows the user to override the default complexity regexps without modifying the class file
public function setComplexities(array $complexities) {
	$this->complexity_regexps = $complexities;
}

public function addField($name, $type, $options=NULL, $constraint=NULL) {
	if( $this->fieldExists($name) ) {
		throw new Exception(sprintf('Field of name "%s" already created.', $name));
	} else if( ! in_array($type, $this->fieldtypes) ) {
		throw new Exception(sprintf('Field type "%s" not currently supported by addField().', $type));
	}
	$this->fields[] = array(
		'name'		=> $name,
		'type'		=> $type,
		'options'	=> $options,
		'constraint' => $constraint
	);
}

public function addLabel($fieldname, $label) {
	if( !$this->fieldExists($fieldname) ) {
		throw new Exception('Cannot attach label to nonexistant field ' . $fieldname);
	}
	$this->labels[$fieldname] = $label;
}

// -- dropdown selection box -- //
public function addSelect($name, $opts_arr, $default=NULL, $options=NULL) {
	if( ! is_array($opts_arr) ) { throw new Exception('Argument 2 must be an array.'); }
	$this->fields[] = array(
		'name'		=> $name,
		'type'		=> 'select',
		'opts_arr'  => $opts_arr,
		'default'	=> $default,
		'options'   => $options
	);
}

private function fieldExists($fieldname) {
	foreach($this->fields as $field) {
		if( $field['name'] === $fieldname ) { return true; }
	}
	return false;
}

private function getFieldByType($type) {
	for($i=0; $i<count($this->fields); $i++) {
		if( $this->fields[$i]['type'] == $type) {
			return $i;
		}
	}
	return NULL;
}

private function getFieldHTML($field, $fieldname, $pre=NULL) {
	if( is_null($pre) ) { $pre = ''; }
	$out = '';

	if( in_array($field['type'], array('text', 'password', 'submit', 'checkbox')) ) {
		$out .= sprintf('<input name="%s" id="%s" type="%s"', $fieldname, $fieldname, $field['type']);
		if( !empty($field['options']) && is_array($field['options']) ) {
			foreach($field['options'] as $key => $value) {
				$out .= sprintf(' %s="%s"', $key, $value);
			}
		}
		$out .= '>';
	} else if( $field['type'] == 'textarea' ) {
		$out .= sprintf('<textarea name="%s" id="%s"', $fieldname, $fieldname);
		if( !empty($field['options']) && is_array($field['options']) ) {
			foreach($field['options'] as $key => $value) {
				$out .= sprintf(' %s="%s"', $key, $value);
			}
		}
		$out .= '></textarea>';
	} else if( $field['type'] == 'select' ) {
		$out .= sprintf('<select name="%s" id="%s"', $fieldname, $fieldname);
		if( !empty($field['options']) && is_array($field['options']) ) {
			foreach($field['options'] as $key => $value) {
				$out .= sprintf(' %s="%s"', $key, $value);
			}
		}
		$out .= ">\n";
		foreach($field['opts_arr'] as $opt_name => $opt_value) {
			$out .= $pre . "\t" . sprintf('<option value="%s"%s>%s</option>'."\n",
				$opt_name,
				($field['default'] == $opt_name) ? ' selected' : '',
				$opt_value
			);
		}
		$out .= $pre . '</select>';
	} else { throw new Exception('I don\'t know how to output field type ' . $field['type']); }
	return $out;
}

public function outHTML($pre=NULL) {
	if( is_null($pre) ) { $pre = ''; }
	$out =  $pre . sprintf('<form name="%s" id="%s" action="%s" method="%s">', $this->name, $this->name, $this->target, $this->method) . "\n";
	$out .= $pre . sprintf('<table id="%s-table">'."\n", $this->name);
	foreach($this->fields as $field) {
		$fieldname = $this->name . '-' . $field['name'];
		$out .= $pre . "\t<tr>\n";
		$out .= $pre . "\t\t<td>";
//		if( isset($this->labels[$field['name']]) ) {
//			$out .= sprintf('<label for="%s">%s</label>', $fieldname, $this->labels[$field['name']]);
		if( isset($field['options']['label']) ) {
			$out .= sprintf('<label for="%s">%s</label>', $fieldname, $field['options']['label']);
		} else {
			$out .= '&nbsp;';
		}
		$out .= "</td>\n";
		$out .= $pre . "\t\t<td>" . $this->getFieldHTML($field, $fieldname, $pre . "\t\t");
		$out .= "</td>\n";
		$out .= $pre . "\t</tr>\n";
	}
	$out .= $pre . "</table>\n";
	$out .= $pre . "</form>\n";
	return $out;
}

public function isSubmitted() {
	$target_field_index = $this->getFieldByType('submit');
	if( is_null($target_field_index) ) { $target_field_index = 0; }

	$target_field_name = $this->name . '-' . $this->fields[$target_field_index]['name'];
	$meth_spec = '_' . $this->method;
	global ${$meth_spec};
	//die(sprintf('%s %s %s', $meth_spec, $target_field_name, ${$meth_spec}[$target_field_name]));
	if( isset(${$meth_spec}[$target_field_name]) ) {
		return true;
	} else {
		return false;
	}
}

public function validate() {
	$method = '_' . $this->method;
	global ${$method};
	foreach($this->fields as $field) {
		//if the field name ends with [] then it is a multi-select array.
		if( preg_match('/\[\]$/', $field['name']) ) {
			$parts = preg_split('/\[\]$/', $field['name']);
			$fieldname = $parts[0];
			$request_var = $this->name . '-' . $fieldname;
			if( !isset(${$method}[$request_var]) || empty(${$method}[$request_var]) ) {
				$this->fields_validated['failure'][$fieldname] = 'validate_multiselect_blank';
				continue;
			}
			foreach( ${$method}[$request_var] as $option ) {
				//ensure all supplied indexes are valid.
				if( !isset($field['opts_arr'][$option]) ) {
					$this->fields_validated['failure'][$fieldname] = 'validate_multiselect_indexes';
					continue;
				}
			}
			if( !isset($this->fields_validated['failure'][$fieldname]) ) {
				$this->fields_validated['success'][$fieldname] = ${$method}[$request_var];
			}
			continue;
		} else {
			$request_var = $this->name . '-' . $field['name'];
		}
		if( isset($field['constraint']) ) {
			// -- special case if field is permitted to be blank -- //
			if( isset($field['constraint']['allowblank']) ) {
				if( empty(${$method}[$request_var]) ) {
					$this->fields_validated['success'][$field['name']] = NULL;
					continue;
				} else if (count($field['constraint']) == 1) {
					//if no other validation methods defined, consider raising a warning here.
					$this->fields_validated['success'][$field['name']] = ${$method}[$request_var];
					continue;
				}
				unset($field['constraint']['allowblank']);
			} else if( empty(${$method}[$request_var]) ) {
				$this->fields_validated['failure'][$field['name']] = 'validate_noblank';
				continue;
			} // -- end allowblank special case -- //

			foreach($field['constraint'] as $constraint => $action) {
//				$request_var = $this->name . '-' . $field['name'];
				$validation_method = 'validate_'.$constraint;
				if( ! method_exists($this, $validation_method) ) {
					throw new Exception(sprintf('Validation method %s not found.', $validation_method));
				} else {
					$res = $this->$validation_method(${$method}[$request_var], $action);
					if( is_null($res) ) {
						$this->fields_validated['failure'][$field['name']] = $validation_method;	
					} else {
						$this->fields_validated['success'][$field['name']] = $res;
					}
				}
			}
		} else {
			// -- action if no constraint is declared, consider raising a warning here -- //
			$this->fields_validated['success'][$field['name']] = ${$method}[$request_var];
		}
	}
	return $this->fields_validated;
}

// -- Validation Functions Below -- //

private function validate_regex($var, $action) {
	return $result = filter_var($var,
		FILTER_VALIDATE_REGEXP,
		array(
			'options' => array(
				'regexp' => $action,
				'default' => NULL
			)
		)
	);
}

private function validate_email($var, $action) {
	return $result = filter_var($var,
		FILTER_VALIDATE_EMAIL,
		array(
			'options' => array(
				'default' => NULL
			)
		)
	);
}

private function validate_maxlength($var, $action) {
	return (strlen($var) <= (int)$action) ? $var : NULL;
}

private function validate_minlength($var, $action) {
	return (strlen($var) >= (int)$action) ? $var : NULL;
}

// must match a previously validated field //
private function validate_matchfield($var, $action) {
	if( isset($this->fields_validated['success'][$action]) && ($var == $this->fields_validated['success'][$action]) ) {
		return $var;
	} else {
		return NULL;
	}
}

private function validate_complexity($var, $action) {
/*	$req_regex = array(
		'/[a-z]/',		//lowercase
		'/[A-Z]/',		//uppercase
		'/[^A-Za-z]/'   //non-alpha
	);*/

	foreach($this->complexity_regexps as $regex) {
		if( !preg_match($regex, $var) ) {
			return NULL;
		}
	}
	return $var;
}

} // -- end class Form --
