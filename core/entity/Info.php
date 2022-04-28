<?php
class Entity_Info {
	
	private $class;
	
	public function __construct($class) {
		$this->$class = $class;
	}
	
	// the aim of this method seems to parse comments annotations
	// in order to generate typage of class members, DB mapping and others cool stuff
	public function addDBInfo() {
		
	}
}