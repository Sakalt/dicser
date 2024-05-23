<?php

// 	Donut: dictionary toolkit 
// 	version 0.1
// 	Thomas de Roo - MIT License
//	++	File: structure.class.php


interface pIntStructure{
	public function __construct($name, $type, $app, $default_section, $page_title);
	public function load();
	public function compile();
	public function render();
} 

abstract class pStructure{

	public $_name, $_meta, $_type, $_structure, $_menu, $_menu_content, $_default_section, $_page_title, $_app, $_permission, $_dispatchStructure;

	public static $permission;

	public function __construct($name, $type, $app, $default_section, $page_title, $dispatchStructure){
		$this->_name = $name;
		$this->_type = $type;
		$this->_app = $app;
		$this->_default_section = $default_section;
		$this->_page_title = $page_title;
		$this->_dispatchStructure = $dispatchStructure;
	}


	// This function returns a custom permission profile if it exists, other wise the default one
	public function itemPermission($item){
		if(isset($this->_structure[$item]['permission']))
			return $this->_structure[$item]['permission'];
		else
			return self::$permission;
	}

	public function compile(){

		// If the user requests a section and if it extist
		if(isset(pRegister::arg()['section']) AND array_key_exists(pRegister::arg()['section'], $this->_structure))
			$this->_section = pRegister::arg()['section'];
		else{

			$this->_error = pMainTemplate::NoticeBox('fa-info-circle fa-12', DA_SECTION_ERROR, 'notice');

			$this->_section = $this->_default_section;
		}


		$this->_parser = new pParser($this->_structure, $this->_structure[$this->_section], $this->_app, $this->_permission);
		;

		$this->_parser->compile();

		pMainTemplate::setTitle($this->_page_title);
	}


	public function load(){
		try {
			// Loading the sturcture

			if($this->_type != '')
				if(file_exists(p::FromRoot("code/structures/".$this->_name.".".$this->_type.".struct.php")))
					$this->_structure = require_once p::FromRoot("code/structures/".$this->_name.".".$this->_type.".struct.php");
				else
					$this->_structure = $this->_dispatchStructure;
			else
				if(file_exists(p::FromRoot("code/structures/".$this->_name.".struct.php")))
					$this->_structure = require_once p::FromRoot("code/structures/".$this->_name.".struct.php");
				else
					$this->_structure = $this->_dispatchStructure;

			// Putting the			
			if(isset($this->_structure['MAGIC_META']))
				$this->_meta = $this->_structure['MAGIC_META'];
			else
				$this->_meta = $this->_structure['metadata'];
			
			if(isset($this->_structure['MAGIC_MENU']))
				$this->_menu = $this->_structure['MAGIC_MENU'];
			
			unset($this->_structure['MAGIC_META']);
			unset($this->_structure['MAGIC_MENU']);


			if(isset($this->_meta['default_permission']))
				$this->_permission = $this->_meta['default_permission'];
			else
				$this->_permission = 0;

		} catch (Exception $e) {
			die();
		}
		
	}

}