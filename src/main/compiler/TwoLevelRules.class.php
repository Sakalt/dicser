<?php
// Donut: open source dictionary toolkit
// version    0.11-dev
// author     Thomas de Roo
// license    MIT
// file:      TwoLevelRules.class.php

// A holder for two-level rules

class pTwolcRules{

	public $rules = array();

	public function __construct($table){
		$dataModel = new pDataModel($table);
		//$dataModel->setOrder(" sorter ASC ");
		$dataModel->getObjects();
		foreach($dataModel->getObjects()->fetchAll() as $rule)
			$this->rules[] = $rule['rule'];
	} 

	public function toArray(){
		return $this->rules;
	}

}