<?php
// Donut 0.13-dev - Emma de Roo - Licensed under MIT
// file: TwoLevelString.class.php

// A string with two levels

class pTwolcString{

	protected $_string, $_orginalString;

	public function __construct($string){
		$this->_string = $string;
		$this->_orginalString = $string;
	}

	public function __toString(){
		return $this->_string;
	}

	public function change($search, $replace, $middle){
		//$this->_string = preg_replace($search, $replace, $this->_string);
		$string = $this->_string;
		$this->_string = preg_replace_callback($search, function($matches) use ($search, $replace, $middle, $string){
			$match = $matches[1];
			$output = $string;

			$middleExploded = explode(',', substr($middle, 1, -1));
			$replaceExploded = explode(',', substr($replace, 1, -1));

			if(isset($replaceExploded[array_search($match, $middleExploded)]) AND p::StartsWith($replace, '['))
				return $replaceExploded[array_search($match, $middleExploded)];
			else
				return str_replace("%", $match, $replace);
				
		}, $this->_string);
	}

	public function toDebug(){
		//return $this->_string;
		return "Surface level: ".$this->toSurface()."<br /> Corrected:&emsp; ".$this->_string."<br /> Generated by inflection:&emsp; ".$this->_orginalString.'';
	}

	public function toRulesheet(){
		//return $this->_string;
		return "<table class='describe'><tr class='title'><td>Surface level</td><td>".$this->toSurface()."</td></tr><tr><td>Corrected</td><td>".$this->_string."</td></tr><tr><td>Generated by inflection</td><td>".$this->_orginalString.'</td></tr></table>';
	}

	public function toSurface(){
		//return $this->_string;
		if(is_array($this->_string))
			var_dump($this->_string);
		return strtolower(str_replace(array('0', '+', '{', '::', '&', '#'), '', $this->_string));
	}

}
