<?php
//Creature class
class Creature{
	public $creaturetype;
	public $creatureset;
	public $level;
	public $maxhp;
	public $regen;
	public $hp;
	public $maxmana;
	public $mana;
	public $strength;
	public $dexterity;
	public $armour;
	public $inventory;
	public $defaultweapon;
	public $repetoire;
	public $position;
	public $waiting;
	public $route;
	public $alive;
	public function __construct($type,$position){
		$this->position=$position;
		$this->alive=true;
		$this->waiting=true;
		//notarget
		$this->destination=false;
		$this->route=array();
		$stats=$this->findstats($type);
		foreach ($stats as $stat => $val){
			if ($stat!="creaturetype"&&$stat!="creatureset"){
				$this->$stat=intval($val);
			} else $this->$stat=$val;
		}
		$this->creaturetype=$type;
		$this->hp=intval($this->maxhp);
		$this->equipment=false;
		//adjust database and remove these lines
		$this->maxmana=10;
		$this->defaultweapon=array(
			"bash"=>2,
			"slash"=>4,
			"pierce"=>0,
			"block"=>0,
			"acc"=>0
			);
		
		//load spell list
		if ($this->repetoire) {
			$this->repetoire=array();
		}
		$this->giverepetoire();		
	}
	
	//validation for actions
	public function getvalidactions($dungeon){
		$adjacenttiles=$dungeon->getnearindices($this->position,1);
		//reorder array to suit directional actions
		for ($i=0;$i<4;$i++){
			$direction=$dungeon->getdirection($this->position,$adjacenttiles[$i]);
			$potentialtargets[$direction]=$adjacenttiles[$i];
		}
		$potentialtargets[5]=$this->position;
		for ($i=5;$i<9;$i++){
			$direction=$dungeon->getdirection($this->position,$adjacenttiles[$i]);
			$potentialtargets[$direction]=$adjacenttiles[$i];
		}
		unset($adjacenttiles);
		foreach ($potentialtargets as $direction => $target){
			if ($dungeon->gettile($target)=="wall"){
				unset($potentialtargets[$direction]);
			} else if (!$dungeon->checkoccupancy($target)||$direction==5){
				$actions["move"][]=$direction;
			} else {
				$actions["attack"][]=$dungeon->getoccupyingcreatureid($target);
			}
		}
		return $actions;
	}
	public function movecreature($dungeon,$direction){
		global $animations;
		$animation=array(
				"type"=>"creaturemove",
				"from"=>$dungeon->getcartesian($this->position-$dungeon->creatures[0]->position+488),
				"direction"=>$direction
				);
		$from=$dungeon->getcartesian($this->position);
		$target=array("x"=>$from["x"]+(($direction-1)%3)-1,"y"=>$from["y"]+ceil((1-$direction)/3)+1);
		$target=$dungeon->getindex($target);
		if ($dungeon->gettile($target)!="wall"&&!$dungeon->checkoccupancy($target)||$direction==5){
			$this->position=$target;
			$success=true;
			$this->waiting=false;
		} else $success=false;
		if ($success&&$this->creaturetype!="player") {
			$visiblearea=$dungeon->visible;
			if (in_array($this->position,$visiblearea)) {
				$animations[]=$animation;
			}
		}
		return $success;
	}
	public function getarmour(){
		$armour=$this->armour;
		if ($this->inventory){
			foreach ($this->equipment as $slot=>$piece){
				if (isset($piece->affects["armour"])){
					$armour+=$piece->affects["armour"];
				}
			}
		}
		return $armour;
	}
	public function getblock(){
		$block=0;
		if ($this->inventory){
			foreach ($this->equipment as $slot=>$piece){
				if (isset($piece->affects["block"])){
					$block+=$piece->affects["block"];
				}
			}
		}
		return $block;
	}
	public function getdex(){
		$dex=$this->dexterity;
		if ($this->inventory){
			foreach ($this->equipment as $slot=>$piece){
				if (isset($piece->affects["pen"])){
					$dex-=$piece->affects["pen"];
				}
			}
		}
		$dex=round(($this->dexterity*$this->strength+$dex*(24-$this->strength))/24);
		$dex=min($dex,$this->dexterity);
		$dex=max($dex,1);
		return $dex;
	}
	public function attack($dungeon,$victimid){
		$validactions=$this->getvalidactions($dungeon);
		$victim=$dungeon->creatures[$victimid];
		if (is_array($this->inventory)&&$this->equipment["weapon"]!=false){
			$weapon=$this->equipment["weapon"]->affects;
		} else $weapon=$this->defaultweapon;
		if (is_array($victim->inventory)&&$victim->equipment["weapon"]!=false){
			$advweapon=$victim->equipment["weapon"]->affects;
		} else $advweapon=$victim->defaultweapon;
		if (isset($validactions["attack"])&&in_array($victim->position,$dungeon->getnearindices($this->position,1))){
			$hitroll=rand(1,100);
			$tohit=round(100*pow(0.4,1/max($this->dexterity+$weapon["acc"],1)));
			$nomiss=$hitroll<$tohit;
			$hitroll=rand(1,100);
			$tohit=round(100*pow(0.9,$victim->getdex()));
			$hit=$hitroll<$tohit;
			if (!$hit){
				$hitroll=rand(1,100);
				$tohit=round(100*pow(0.98,$this->dexterity+$weapon["acc"]-$victim->getdex()));
				$hit=$hitroll>$tohit;
			}
			$hitroll=rand(1,100);
			$tohit=round(100*pow(0.95,$victim->getblock()));
			$noblock=$hitroll<$tohit;
			if (!$noblock){
				$hitroll=rand(1,100);
				$tohit=round(100*pow(0.97,$this->strength-$victim->strength));
				$noblock=$hitroll>$tohit;
			}
			$hitroll=rand(1,100);
			$tohit=round(100*pow(0.98,$this->getdex()));
			$crit=$hitroll>$tohit;
			$damage=0;
			for ($i=0;$i<$weapon["bash"];$i++){
				$damage+=rand(1,$weapon["slash"]);
			}
			$damage=round($damage*pow(1.125,$this->strength-6));
			$reduction=rand(1,$victim->getarmour());
			if (!$crit) {
				$damage=max($damage-$reduction,1);
				$damage+=$weapon["pierce"];
			} else {
				$damage+=$weapon["pierce"];
				$damage=round($damage*pow(1.1,$this->getdex()));
			}
			$damage*=$hit*$nomiss*$noblock;
			$dungeon->creatures[$victimid]->hp-=$damage;
			global $animations;
			//find on-screen poisitions for animation
			$direction=$dungeon->getdirection($this->position,$victim->position);
			$animations[]=array(
				"type"=>"melee",
				"location"=>$dungeon->getcartesian($this->position-$dungeon->creatures[0]->position+488),
				"direction"=>$direction
				);
			if ($damage>0) $animations[]=array(
				"type"=>"bleed",
				"location"=>$dungeon->getcartesian($victim->position-$dungeon->creatures[0]->position+488)
				);
			global $eventlog;
			if (!$nomiss) {
				$eventlog[]=$this->creaturetype." missed ".$victim->creaturetype;
			} else if (!$hit) {
				$eventlog[]=$victim->creaturetype." dodged ".$this->creaturetype."'s attack";
			} else if (!$noblock) {
				$eventlog[]=$victim->creaturetype." blocked ".$this->creaturetype."'s attack";
			} else if ($crit) {
				$eventlog[]=$this->creaturetype." hit ".$victim->creaturetype." for ".$damage." damage!";
			} else {
				$eventlog[]=$this->creaturetype." hit ".$victim->creaturetype." for ".$damage." damage";
			}
			$this->waiting=false;
			if ($victim->hp<1){
				$location=$victim->position;
				if ($victimid) {
					if (is_array($victim->inventory)) $dungeon->creatures[$victimid]->dropall($dungeon);
					$dungeon->additem("corpse",$victim->creaturetype,$location);
					$dungeon->creatures[$victimid]->alive=false;
					$dungeon->creatures[0]->gainexp($victim->level);
					unset($dungeon->creatures[$victimid]);
				} else {
					$animations[]=array("type"=>"death");
					$eventlog[]="Oh no! You died :(";
				}
			}
			$success=true;
		} else $success=false;
		
		return $success;
	}
	public function movetoward($dungeon,$destination){
		$direction=$dungeon->getdirection($this->position,$destination);
		if (is_array($direction)){
			$success=$this->movecreature($dungeon,$direction[0])*$direction[0];
			if ($success&&$this->creaturetype!="player") {
				$animation["direction"]=$direction[0];
			} else if ($this->creaturetype!="player") {
				$success=$this->movecreature($dungeon,$direction[1])*$direction[1];
				if ($success&&$this->creaturetype!="player") {
					$animation["direction"]=$direction[1];
				}
			}
		} else {
			$success=$this->movecreature($dungeon,$direction)*$direction;
		}
		return $success;
	}
	public function movewrtgoal($dungeon,$objective){
		$success=false;
		$adjacenttiles=$dungeon->getnearindices($this->position,1);
		//reorder array to suit directional actions
		for ($i=0;$i<4;$i++){
			$direction=$dungeon->getdirection($this->position,$adjacenttiles[$i]);
			if ($dungeon->distancemap[$adjacenttiles[$i]]>0&&!$dungeon->checkoccupancy($adjacenttiles[$i])) $slope[$direction]=$dungeon->distancemap[$adjacenttiles[$i]]-$dungeon->distancemap[$this->position];
		}
		$slope[5]=0;
		for ($i=5;$i<9;$i++){
			$direction=$dungeon->getdirection($this->position,$adjacenttiles[$i]);
			if ($dungeon->distancemap[$adjacenttiles[$i]]>0&&!$dungeon->checkoccupancy($adjacenttiles[$i])) $slope[$direction]=$dungeon->distancemap[$adjacenttiles[$i]]-$dungeon->distancemap[$this->position];
		}
		unset($adjacenttiles);
		switch ($objective){
		case "toward":
			$bestcase=min($slope);
			break;
		case "away":
			$bestcase=max($slope);
			break;
		case "along":
			$bestcase=0;
		default:
			break;
		}
		foreach ($slope as $direction=>$thiscase){
			if ($thiscase!=$bestcase) unset($slope[$direction]);
		}
		$direction=array_rand($slope);
		$success=$this->movecreature($dungeon,$direction);
		return $success;
	}
	public function cast($dungeon,$spellname,$victim){
		$knowspell=false;
		global $eventlog;
		foreach ($this->repetoire as $known){
			if ($known->spellname==$spellname){
				$spell=$known;
				$knowspell=true;
			}
		}
		$cancast=true;
		if ($this->creaturetype=="player"&&($this->equipment["weapon"]==false||$this->equipment["weapon"]->name!="staff")){
			$cancast=false;
			$eventlog[]="Must wield a staff to cast spells";
		}
		$lof=$dungeon->checklofbetween($this->position,$dungeon->creatures[$victim]->position);
		if ($this->creaturetype=="player"&&$lof==false) $eventlog[]="No line of fire";
		$cancast*=$lof*$knowspell;
		if ($cancast&&$this->mana>=$spell->cost){
			$this->mana-=$spell->cost;
			if ($spell->nature=="damage"){
				$damage=rand($spell->power,$this->level+$spell->power);
				$dungeon->creatures[$victim]->hp-=$damage;
				global $animations;
				//find on-screen poisitions for animation
				$animations[]=array(
					"type"=>$spell->spellname,
					"origin"=>$dungeon->getcartesian($this->position-$dungeon->creatures[0]->position+488),
					"location"=>$dungeon->getcartesian($dungeon->creatures[$victim]->position-$dungeon->creatures[0]->position+488)
					);
				$eventlog[]=$this->creaturetype." cast ".$spell->spellname." against ".$dungeon->creatures[$victim]->creaturetype." for ".$damage." damage";
				$this->waiting=false;
				if ($dungeon->creatures[$victim]->hp<1){
					$location=$dungeon->creatures[$victim]->position;
					if ($victim) {
						if (is_array($dungeon->creatures[$victim]->inventory)) $dungeon->creatures[$victim]->dropall($dungeon);
						$dungeon->additem("corpse",$dungeon->creatures[$victim]->creaturetype,$location);
						$dungeon->creatures[$victim]->alive=false;
						$dungeon->creatures[0]->gainexp($dungeon->creatures[$victim]->level);
						unset($dungeon->creatures[$victim]);
					} else $eventlog[]="Oh no! You died :(";
				}
				$success=true;
			}
		} else {
			$success=false;
			if ($this->mana<$spell->cost) $eventlog[]="Not enough mana";
		}
		return $success;
	}
	
	public function moveAI($dungeon){
		global $eventlog;
		$validactions=$this->getvalidactions($dungeon);
		$neartoplayer=in_array($this->position,$dungeon->visible);
		if ($neartoplayer){
			$this->route=$dungeon->buildpath($this->position);
		} 
		if (isset($validactions["attack"])&&$this->waiting){
			foreach ($validactions["attack"] as $victim){
				if ($this->waiting&&$victim==0) {
					$this->attack($dungeon,0);
				}
			}
		}
		if ($this->waiting&&$neartoplayer) {
			if (isset($this->repetoire[0])) {
				$rn=rand(1,3);
				if ($rn==1) {
					$cast=$this->cast($dungeon,$this->repetoire[0]->spellname,0);
					if (!$cast&&$this->waiting){
						$this->movewrtgoal($dungeon,"along");
						$this->route=$dungeon->buildpath($this->position);
					}
				}
				
			}
		}
		if (count($this->route)>0&&$this->waiting) {
			$direction=array_shift($this->route);
			$this->movecreature($dungeon,$direction);
		}
		if ($this->waiting&&$neartoplayer) {
			$this->movewrtgoal($dungeon,"toward");
			$this->route=$dungeon->buildpath($this->position);
		}
		$moved=false;
		if ($this->waiting) while (!$moved) {
			$direction=rand(1,9);
			$moved=$this->movecreature($dungeon,$direction);
		}
	}
	public function giverepetoire(){
		if ($this->creaturetype=="imp"||$this->creaturetype=="baron"){
			$this->repetoire[]=new Spell("burn");
		} else if ($this->creaturetype=="goblinshaman"||$this->creaturetype=="orcshaman"){
			$this->repetoire[]=new Spell("shock");
		} else if ($this->creaturetype=="banshee"){
			$this->repetoire[]=new Spell("chill");
		}
	}
	public function findstats($type){
		//query the creatures database
		global $mysqli;
		$query="SELECT * FROM creatures WHERE creaturetype='".$type."'";
		if ($result=$mysqli->query($query)) {
			$stats=$result->fetch_assoc();	
			$result->free();
		} else {
			echo "creature not found";
		}
		return $stats;
	}
}
