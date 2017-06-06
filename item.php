<?php
class Item{
	public $category;
	public $itemclass;
	public $name;
	public $strrep;
	public $affects;
	public $carryable;
	public function __construct($itemclass,$name){
		$this->name=$name;
		$this->itemclass=$itemclass;
		if ($itemclass=="corpse"){
			$this->category="corpse";
			$this->affects=NULL;
			$this->carryable=false;
			$this->strrep=$name." ".$itemclass;
			if ($name=="orcslave"||$name=="orcsoldier"||$name=="orcshaman"||$name=="orccaptain") {
				$this->name="orc";
			}
			if ($name=="goblinsoldier"||$name=="goblinshaman") {
				$this->name="goblin";
			}
		} else if ($itemclass=="none"){
			$info=array("category"=>"none","affects"=>false,"carryable"=>false);
			foreach ($info as $key => $val){
				$this->$key=$val;
			}
			$this->strrep="nothing";
		} else {
			$info=$this->findinfo($itemclass,$name);
			foreach ($info as $key => $val){
				if ($key=="affects") {
					$this->$key=json_decode($val,true);
				} else $this->$key=$val;
			}
			$this->carryable=true;
			if ($this->category=="consumable"||$this->itemclass=="ring"){
				$this->strrep=$this->itemclass." of ".$this->name;
			} else if ($this->itemclass=="weapon"||$this->name=="robes"){
				$this->strrep=$this->name;
			} else {
				$this->strrep=$this->name." ".$this->itemclass;
			}
		}
	}
	public function findinfo($itemclass,$name){
		//query the creatures database
		global $mysqli;
		$query="SELECT * FROM items WHERE name='".$name."'";
		if ($mysqli->query($query)) {
			$info=$mysqli->query($query)->fetch_assoc();
		}
		return $info;
	}
	public function setquality(){
		$quality=false;
		if ($this->itemclass=="weapon"){
			$rn=rand(1,6);
			if ($rn==6) {
				$quality=true;
				$this->affects["acc"]+=1;
				$this->strrep="well balanced ".$this->strrep;
			} 
		} else if ($this->itemclass=="armour"){
			$rn=rand(1,5);
			if ($rn==5) {
				$quality=true;
				$this->affects["armour"]+=1;
				$this->strrep="thick ".$this->strrep;
			}
		}
		return $quality;
	}
	public function setenchant($floor){
		$enchant=false;
		if ($this->itemclass=="weapon"){
			$rn=rand(1,5);
			if ($rn==5) {
				$enchant=rand(1,ceil($floor/3));
				for ($i=0;$i<$enchant;$i++){
					$rn2=rand(0,2);
					if ($rn2==2) $rn2=rand(0,2);
					$weaponattr=array("bash","slash","pierce");
					$this->affects[$weaponattr[$rn2]]++;
				}
				$this->strrep="+".$enchant." enchanted ".$this->strrep;
			}
		} else if ($this->itemclass=="armour"){
			$rn=rand(1,5);
			if ($rn==5) {
				$enchant=rand(1,min(floor($floor/3)+1,4));
				$this->affects["block"]+=$enchant;
				$this->strrep="+".$enchant." enchanted ".$this->strrep;
			}
		} else if ($this->name=="dexterity"){
			$enchant=rand(0,$floor);
			$this->affects["dexterity"]+=$enchant;
			$this->strrep="+".($enchant+1)." ".$this->strrep;
		} else if ($this->name=="strength"){
			$enchant=rand(0,$floor);
			$this->affects["strength"]+=$enchant;
			$this->strrep="+".($enchant+1)." ".$this->strrep;
		} else if ($this->name=="magic"){
			$enchant=rand(0,$floor);
			$this->affects["maxmana"]+=$enchant;
			$this->strrep="+".($enchant+1)." ".$this->strrep;
		}
		return $enchant;
	}
	public function setbrand(){
		$rn=rand(1,5);
		if ($rn==5&&$this->itemclass=="weapon") {
			$this->strrep=$this->strrep." of possibility";
		}
	}
	public function setego(){
		
	}
}
