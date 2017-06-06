<?php
class Spell{
	public $school;
	public $nature;
	public $spellname;
	public $cost;
	public $level;
	public $power;
	public function __construct($spellname){
		$this->spellname=$spellname;
		$info=$this->findinfo($spellname);
		foreach ($info as $property => $val){
			if ($property!="name") $this->$property=$val;
		}
		$this->cost=intval($this->cost);
		$this->power=intval($this->power);
		$this->level=intval($this->level);
	}
	public function findinfo($spellname){
		global $mysqli;
		$query="SELECT * FROM spells WHERE name='".$spellname."'";
		if ($mysqli->query($query)) {
			$stats=$mysqli->query($query)->fetch_assoc();	
		} else {
			echo "spell not found";
		}
		return $stats;
	}
}