<?php
class Player extends Humanoid{
	public $experience;
	public $turncount;
	public $playername;
	public $gender;
	public $hairstyle;
	public function __construct($position,$playername,$gender,$hairstyle){
		$this->creaturetype="player";
		$this->position=$position;
		$this->alive=true;
		$this->waiting=true;
		
		//add cosmetics
		$this->playername=$playername;
		$this->gender=$gender;
		$this->hairstyle=$hairstyle;
		
		//add base stats
		$stats=$this->findstats("player");
		foreach ($stats as $stat => $val){
			$this->$stat=$val;
		}
		$this->maxhp=intval($this->maxhp);
		$this->hp=intval($this->maxhp);
		$this->level=intval($this->level);
		$this->experience=0;
		$this->turncount=0;
		$this->maxmana=4;
		$this->mana=$this->maxmana;
		
		//load inventory
		$this->inventory=array();
		for ($i=0;$i<2;$i++){
			$this->inventory[$i]=new Item("potion","mend wounds");
		}
		
		//load spell list
		$this->repetoire=array();
		
		//load equipment
		$this->equipment=array(
			"weapon"=>false,
			"ring"=>false,
			"cloak"=>false,
			"armour"=>false,
			"helmet"=>false,
			"shield"=>false
			);
		
		//add and equip starting items
		$this->inventory[2]=new Item("weapon","shortsword");
		$this->wear(2);
		$this->inventory[2]=new Item("armour","robes");
		$this->wear(2);
	}
	public function gainlevel(){
		$this->level++;
		$this->maxhp+=4;
		$this->hp+=4;
		$this->maxmana+=1;
		$this->mana+=1;
		if (rand(0,1)==1){
			$this->strength++;
		} else {
			$this->dexterity++;
		}
	}
	public function gainexp($amount){
		$this->experience+=$amount;
		if ($this->experience>=($this->level+1)*($this->level+1)*($this->level+1)){
			$this->gainlevel();
		}
	}
	public function explore($dungeon){
		$success=false;
		$upperlimit=max($dungeon->distancemap);
		$destination=($this->position);
		foreach ($dungeon->terrain as $tileindex=>$tile){
			if ($dungeon->distancemap[$tileindex]>0
			&&$dungeon->terrain[$tileindex]!=1
			&&$dungeon->distancemap[$tileindex]<$upperlimit
			&&in_array($tileindex,$dungeon->explored)==false){
				$destination=$tileindex;
				$upperlimit=$dungeon->distancemap[$tileindex];
			}
		}
		$pathfrom=$dungeon->buildpath($destination);
		$explorepath=$dungeon->reversepath($pathfrom);
		if (count($explorepath)>0) {
			$success=$this->movecreature($dungeon,$explorepath[0]);
		} else {
			$destination=array_keys($dungeon->terrain,3)[0];
			$pathfrom=$dungeon->buildpath($destination);
			$explorepath=$dungeon->reversepath($pathfrom);
			if (count($explorepath)>0) {
				$success=$this->movecreature($dungeon,$explorepath[0]);
			} else {
				global $automove;
				$automove=false;
			}
		}
		global $animations;
		if ($success) $animations[]=array("type"=>"playermove","direction"=>$explorepath[0]);
		return $success;
	}
}