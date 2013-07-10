<?php

class Template {

private $registry;
private $vars = array();
private $stylesheets = array();
private $metas = array();
private $http_headers = array();

function __construct($registry) {
	$this->registry = $registry;
	$this->http_root = $registry->http_root;
}

public function __set($index, $value) {
	$this->vars[$index] = $value;
}

public function show($name) {
	$this->sendHTTPHeaders();

	$path = __SITE_PATH . '/_views' . '/' . $name . '.php';

	if( !file_exists($path) ) {
		throw new Exception("Template '$name' not found in " . $path);
	}

	// Load variables
	foreach($this->vars as $key => $value) {
		$$key = $value;
	}

	include($path);
}

public function show_headers_only() {
	$this->sendHTTPHeaders();
}

public function HTTPRedirect($target, $absolute=FALSE, $code=NULL) {
	if( isset($code) ) {
		switch($code) {
			case 301:
				$this->setHTTPHeader("HTTP/1.1 301 Moved Permanently");
				break;;
			case 302:
				$this->setHTTPHeader("HTTP/1.1 302 Moved Temporarily");
				break;;
		}
	}
	if( $absolute ) {
		$this->setHTTPHeader( sprintf('Location: %s', $target) );
	} else {
		$this->setHTTPHeader( sprintf('Location: %s%s', $this->registry->http_root, $target) );
	}
}

public function setHTTPHeader($header_text) {
	$this->http_headers[] = $header_text;
}

public function sendHTTPHeaders() {
	if( !empty($this->http_headers) ) {
		foreach($this->http_headers as $header) {
			header($header);
		}
	}
}

public function addStylesheet($path, $absolute=FALSE) {
	if( $absolute ) {
		$this->stylesheets[] = $path;
	} else {
		$this->stylesheets[] = sprintf('%s%s', $this->registry->http_root, $path);
	}
}

public function addMeta($meta_arr) {
	$this->metas[] = $meta_arr;
}

public function addMetaRefresh($time, $url=NULL, $absolute=FALSE) {
	$tag = array( 'http-equiv' => 'refresh' );
	if( !isset($url) ) {
		$tag['content'] = $time;
	} else {
		if( $absolute ) {
			$tag['content'] = sprintf("%s;%s", $time, $url);
		} else {
			$tag['content'] = sprintf("%s;%s%s", $time, $this->registry->http_root, $url);
		}
	}
	$this->addMeta($tag);
}

public function getMetas($prefix=NULL) {
	if( !isset($prefix) ) {
		$prefix = '';
	}
	$out = '';
	foreach( $this->metas as $meta ) {
		$out .= $prefix . '<meta';
		foreach( $meta as $field => $value ) {
			$out .= sprintf(' %s="%s"', $field, $value);
		}
		$out .= ">\n";
	}
	return $out;
}

public function getStylesheets($prefix=NULL) {
	if( !isset($prefix) ) {
		$prefix = '';
	}
	$frame = '%s<link rel="stylesheet" type="text/css" href="%s">' . "\n";
	$out = '';
	foreach($this->stylesheets as $href) {
		$out .= sprintf($frame, $prefix, $href);
	}
	return $out;
}

public function getHTMLHeaders($prefix=NULL) {
	$out = '';
	$out .= $this->getMetas($prefix);
	$out .= $this->getStyleSheets($prefix);
	
	return $out;
}

}// -- end of Template class --
