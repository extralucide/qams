<?php
class Problem{
	private $id
	private $synopsis
	
	function getId(){
		return($this->id);
	}	
	function getSynopsis(){
		return($this->synopsis);
	}
	function __construct($context=null) {
		$this->id = "";
		$this->synopsis = "";
	}
}