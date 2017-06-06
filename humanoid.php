<?php
class Humanoid extends Creature{
	public $inventory;
	public $equipment;
	public function __construct($type,$position){
		$this->creaturetype=$type;
		$this->position=$position;
		$this->alive=true;
		$this->waiting=true;
		//notarget
		$this->destination=false;
		$stats=$this->findstats($type);
		foreach ($stats as $stat => $val){
			if ($stat!="creaturetype"&&$stat!="creatureset"){
				$this->$stat=intval($val);
			} else $this->$stat=$val;
		}
		//adjust database and remove this line
		$this->maxmana=12;
		$this->hp=intval($this->maxhp);
		
		//load spell list
		if ($this->repetoire) {
			$this->repetoire=array();
		}
		$this->giverepetoire();
		
		//load inventory
		if ($this->inventory) {
			$this->inventory=array();
		}
		
		//load equipment array
		$this->equipment=array(
			"weapon"=>false,
			"ring"=>false,
			"cloak"=>false,
			"armour"=>false,
			"helmet"=>false,
			"gloves"=>false,
			"shield"=>false
			);
		$this->giveequipment();		
	}
	public function pickup($dungeon){
		$success=false;
		$exhausted=false;
		$itemlocation=$this->position;
		if (isset($dungeon->items[$itemlocation])&&is_array($dungeon->items[$itemlocation])){
			$stackindex=0;
			while (!$success){
				if (!isset($dungeon->items[$itemlocation][$stackindex])) {
					break;
				}
				if ($dungeon->items[$itemlocation][$stackindex]->carryable){
					$this->inventory[]=$dungeon->items[$itemlocation][$stackindex];
					$this->waiting=false;
					unset($dungeon->items[$itemlocation][$stackindex]);
					$dungeon->items[$itemlocation]=array_values($dungeon->items[$itemlocation]);
					if (!isset($dungeon->items[$itemlocation][0])) unset($dungeon->items[$itemlocation]);
					$success=true;
				}
				$stackindex++;
			}
		}
		return $success;
	}
	public function drop($dungeon,$invpos){
		$success=false;
		$itemlocation=$this->position;
		if (isset($this->inventory[$invpos])){
			$item=$this->inventory[$invpos];
			$dungeon->items[$itemlocation][]=clone $item;
			$this->waiting=false;
			unset($this->inventory[$invpos]);
			$this->inventory=array_values($this->inventory);
			$success=true;
		}
		return $success;
	}
	public function useitem($inventoryslot){
		$success=false;
		if (isset($this->inventory)&&isset($this->inventory[$inventoryslot])){
			$item=$this->inventory[$inventoryslot];
			if ($item->category=="consumable"){
				if (isset($item->affects)) {
					$modifying=$item->affects;
					foreach ($modifying as $stat=>$mod){
						$this->$stat+=$mod;
						if ($stat="hp"&&$this->hp>$this->maxhp){
							$this->hp=$this->maxhp;
						}
					}
				}
				unset($this->inventory[$inventoryslot]);
				$this->inventory=array_values($this->inventory);
			} else if ($item->category=="equipment"){
				$this->wear($inventoryslot);
			}
			$success=true;
		}
		return $success;
	}
	public function wear($invpos){
		$success=false;
		if (isset($this->inventory[$invpos])&&$this->inventory[$invpos]->category=="equipment"){
			$eqslot=$this->inventory[$invpos]->itemclass;
			if ($eqslot){
				if ($this->equipment[$eqslot]) $this->rem($eqslot);
				$modifying=$this->inventory[$invpos]->affects;
				if ($this->inventory[$invpos]->itemclass=="ring") foreach ($modifying as $stat=>$mod){
					$this->$stat+=$mod;
					if ($stat="hp"&&$this->hp>$this->maxhp){
						$this->hp=$this->maxhp;
					}
				}
				$this->equipment[$eqslot]=clone $this->inventory[$invpos];
				unset($this->inventory[$invpos]);
				$this->inventory=array_values($this->inventory);
				$success=true;
			}
		}
		return $success;
	}
	public function rem($eqslot){
		$success=false;
		if ($this->equipment[$eqslot]){
			$this->inventory[]=clone $this->equipment[$eqslot];
			$modifying=$this->equipment[$eqslot]->affects;
			if ($this->equipment[$eqslot]->itemclass=="ring") foreach ($modifying as $stat=>$mod){
				$this->$stat-=$mod;
				if ($stat="hp"&&$this->hp<1){
					$this->hp=1;
				}
			}
			$this->equipment[$eqslot]=false;
			$success=true;
		}
		return $success;
	}
	public function dropall($dungeon){
		foreach ($this->equipment as $slot=>$piece){
			if ($piece!=false) {
				$this->rem($slot);
			}
		}
		while (count($this->inventory)>0) {
			$this->drop($dungeon,0);
		}
	}
	public function giveequipment(){
		if ($this->creatureset=="goblinoid"){
			$weapontypes=array("spear","knife","handaxe","spetum","waraxe","mace","billhook","broadsword");
			$weapontype=rand(0+floor($this->level/3),min($this->level,7));
			$weapontype=$weapontypes[$weapontype];
			if ($this->creaturetype=="goblinshaman"||$this->creaturetype=="orcshaman"){
				$weapontype="staff";
			}
		} else if ($this->creatureset=="demonic"){
			$weapontypes=array("demonsword","demonwhip","demontrident");
			if ($this->creaturetype=="demon"){
				$weapontype="demontrident";
			} else {
				$weapontype=$weapontypes[rand(0,1)];
			}
		} else if ($this->creatureset=="undead"){
			$weapontypes=array("spetum","mace","billhook");
			$weapontype=$weapontypes[rand(0,2)];
		}
		$this->inventory[0]=new Item("weapon",$weapontype);
		if (!$this->inventory[0]->setquality()) $this->inventory[0]->setenchant($this->level);
		$this->wear(0);
	}
}