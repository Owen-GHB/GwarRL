<?php
include "creature.php";
include "humanoid.php";
include "player.php";
include "item.php";
include "spell.php";
//Dungeon itself
class Dungeon extends Terrain{
	public $creatures;
	public $items;
	public $viewcentre;
	public $visible;
	public $explored;
	public $decals;
	
	public function __construct() {
		//constructor overloading hack from comments on docs
		$a = func_get_args();
		$i = func_num_args();
		if (method_exists($this,$f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else {
			//default constructor goes here
			$this->boardsize["x"]=60;
			$this->boardsize["y"]=60;
			$this->fillspace("wall");
			$this->creatures=NULL;
			$this->items=NULL;
			$this->explored=NULL;
			$this->decals=NULL;
		}
	}
	public function __construct1($thisspace){
		$this->terrain=$thisspace->terrain;
		$this->boardsize=$thisspace->boardsize;
		$this->creatures=NULL;
		$this->items=NULL;
		$this->explored=NULL;
		$this->decals=NULL;
	}
	public function __construct3($thisspace,$creatures,$items){
		$this->terrain=$thisspace->terrain;
		$this->boardsize=$thisspace->boardsize;
		$this->creatures=$creatures;
		$this->items=$items;
		$this->explored=NULL;
		$this->decals=NULL;
	}
	public function __construct4($thisspace,$creatures,$items,$explored){
		$this->terrain=$thisspace->terrain;
		$this->boardsize=$thisspace->boardsize;
		$this->creatures=$creatures;
		$this->items=$items;
		$this->explored=$explored;
	}
	public function __construct5($thisspace,$creatures,$items,$explored,$decals){
		$this->terrain=$thisspace->terrain;
		$this->boardsize=$thisspace->boardsize;
		$this->creatures=$creatures;
		$this->items=$items;
		$this->explored=$explored;
		$this->decals=$decals;
		$this->visible=$this->getlineofsightfrom($this->creatures[0]->position);
	}
	public function __construct6($thisspace,$creatures,$items,$explored,$decals,$visible){
		$this->terrain=$thisspace->terrain;
		$this->boardsize=$thisspace->boardsize;
		$this->creatures=$creatures;
		$this->items=$items;
		$this->explored=$explored;
		$this->decals=$decals;
		$this->visible=$visible;
		$this->builddistancemap($this->creatures[0]->position);
	}
	
	//functions for loading items/creatures into the this
	public function addplayer($playername,$gender,$hairstyle){
		$position=$this->pickempty($this->terrain);
		$this->creatures[]=new Player($position,$playername,$gender,$hairstyle);
	}
	public function addcreature($creatureset,$type,$position){
		if ($position=="random"){
			$position=$this->pickempty($this->terrain);
		}
		if ($creatureset=="goblinoid"
			&&$type!="troll"
			||$type=="skeleton"
			||$type=="demon"
			||$type=="baron"){
			$this->creatures[]=new Humanoid($type,$position);
		} else $this->creatures[]=new Creature($type,$position);
	}
	public function additem($itemclass,$name,$position){
		if ($position=="random"){
			$position=$this->pickempty($this->terrain);
		}
		if (is_array($position)) $position=$this->getindex($position);
		if (isset($this->items[$position])&&is_array($this->items[$position])){
			array_unshift($this->items[$position],new Item($itemclass,$name));
		} else $this->items[$position][]=new Item($itemclass,$name);
	}
	public function pickempty($subspace){
		$picked=false;
		while (!$picked){
			$pick=rand(0,$this->boardsize["x"]*$this->boardsize["y"]-1);
			if ($this->gettile($pick)!="wall"&&$this->checkoccupancy($pick)==false){
				$picked=true;				
			}
		}
		return $pick;
	}
	
	//utility functions for handling creature locations
	public function checkoccupancy($target){
		$occupied=false;
		if (is_array($this->creatures)){
			foreach ($this->creatures as $creature){
				if ($creature->position==$target){
					$occupied=true;
				}
			}
		}
		return $occupied;
	}
	public function getoccupyingcreatureid($target){
		if (is_array($this->creatures)){
			foreach ($this->creatures as $key => $creature){
				if ($creature->position==$target){
					$occupantkey=$key;
				}
			}
		}
		return $occupantkey;
	}
	public function checklofbetween($indexfrom,$indexto){
		$lof=true;
		$path=$this->getline($indexfrom,$indexto);
		$distance=$path["distance"];
		if($distance>8) $lof=false;
		foreach ($path as $step => $thistile){
			if (is_numeric($step)&&$step>0){
				if (is_array($thistile)){
					if (($this->checkoccupancy($thistile[0])||$this->checkoccupancy($thistile[1]))&&$step<$distance){
						$lof=false;
					}
				} else if ($this->checkoccupancy($thistile)&&$step<$distance){
					$lof=false;
				} 
			}
		}
		return $lof;
	}
	
	//handling the player creature
	public function moveplayer($command,$modifier){
		$modifier=stripslashes($modifier);
		$modifier=json_decode($modifier,true);
		global $animations;
		switch ($command) {
		case "move":
			if (is_numeric($modifier)){
				$from=$this->getcartesian($this->creatures[0]->position);
				$target=array("x"=>$from["x"]+(($modifier-1)%3)-1,"y"=>$from["y"]+ceil((1-$modifier)/3)+1);
				$target=$this->getindex($target);
				if ($this->checkoccupancy($target)&&$modifier!=5){
					$validmove=$this->creatures[0]->attack($this,$this->getoccupyingcreatureid($target));
				} else if ($modifier==5){
					$validmove=$this->creatures[0]->pickup($this);
					if (!$validmove) $validmove=$this->creatures[0]->movecreature($this,$modifier);
				} else {
					$validmove=$this->creatures[0]->movecreature($this,$modifier);
					if ($validmove) $animations[]=array("type"=>"playermove","direction"=>$modifier);
				}
				if ($validmove) {
					$this->addexploration();
					$this->movemonsters();
				}
			}
			break;
		case "climbstair":
			if ($modifier=="down"&&$_SESSION["currentfloor"]<9&&$this->gettile($this->creatures[0]->position)=="downstair"){
				$_SESSION["currentfloor"]++;
			} else if ($modifier=="down"&&$this->gettile($this->creatures[0]->position)=="downstair"){
				$this->creatures[0]->hp=0;
				$animations[]=array("type"=>"death");
				$eventlog[]="You slipped and fell";
			} else if ($modifier=="down"&&$_SESSION["currentfloor"]>1&&$this->gettile($this->creatures[0]->position)=="upstair"){
				$_SESSION["currentfloor"]--;
			}
			break;
		case "moveto":
			if (is_array($modifier)){
				$target=$this->getindex($modifier);
				$pathfrom=$this->buildpath($target);
				$pathto=$this->reversepath($pathfrom);
				if (count($pathto)>0) {
					$direction=$pathto[0];
				} else $direction=5;
				$validmove=false;
				global $automove;
				global $time;
				$automove=true;
				if ($this->checkoccupancy($target)&&$direction!=5){
					$automove=false;
					$validmove=$this->creatures[0]->attack($this,$this->getoccupyingcreatureid($target));
				} else if ($direction==5){
					$automove=false;
					$validmove=$this->creatures[0]->pickup($this);
					if (!$validmove) {
						if ($this->terrain[$this->creatures[0]->position]==3&&$_SESSION["currentfloor"]<9){
							$_SESSION["currentfloor"]++;
						} else if ($this->terrain[$this->creatures[0]->position]==2){
							$_SESSION["currentfloor"]--;
						} else $validmove=$this->creatures[0]->movecreature($this,$direction);
					}
				}
				if (!$validmove) {
					$validmove=$this->creatures[0]->movecreature($this,$direction);
					if ($validmove) $animations[]=array("type"=>"playermove","direction"=>$direction);
				}
				if ($validmove) {
					if ($this->creatures[0]->position==$target){
						$automove=false;
					}
					$this->addexploration();
					$this->movemonsters();
					$visiblearea=$this->visible;
					if ($automove) foreach ($this->creatures as $key => $creature) {
						if (in_array($creature->position,$visiblearea)&&$key){
							$automove=false;
						}
					}
				} else $automove=false;
			} else if ($modifier=="explore"){
				global $automove;
				$automove=true;
				$validmove=$this->creatures[0]->explore($this);
				if ($validmove){
					$this->addexploration();
					$this->movemonsters();
					$visiblearea=$this->visible;
				}
				$visiblearea=$this->visible;
				foreach ($this->creatures as $key => $creature) {
					if (in_array($creature->position,$visiblearea)&&$key){
						$automove=false;
					}
				}
			}
			break;
		case "use":
			$validmove=$this->creatures[0]->useitem($modifier);
			if ($validmove) $this->movemonsters();
			break;
		case "remove":
			$validmove=$this->creatures[0]->rem($modifier);
			if ($validmove) $this->movemonsters();
			break;
		case "pickup":
			$validmove=$this->creatures[0]->pickup($this);
			if ($validmove) $this->movemonsters();
			break;
		case "drop":
			$validmove=$this->creatures[0]->drop($this,$modifier);
			if ($validmove) $this->movemonsters();
			break;
		case "cast":
			global $eventlog;
			$target=$this->getindex(array("x"=>$modifier["x"],"y"=>$modifier["y"]));
			$spellname=$modifier["spell"];
			if ($this->checkoccupancy($target)&&$spellname){
				$validmove=$this->creatures[0]->cast($this,$spellname,$this->getoccupyingcreatureid($target));
				if ($validmove) $this->movemonsters();
			} else {
				global $eventlog;
				if (!$spellname) $eventlog[]="No spell selected";
			}
			break;
		case "wait":
			$turnspassed=0;
			$visiblearea=$this->visible;
			$vismonster=false;
			foreach ($this->creatures as $key => $creature) {
				if (in_array($creature->position,$visiblearea)&&$key){
					$vismonster=true;
				}
			}
			while ($turnspassed<$modifier&&($this->creatures[0]->hp<$this->creatures[0]->maxhp||$this->creatures[0]->mana<$this->creatures[0]->maxmana)&&!$vismonster) {
				$this->creatures[0]->movecreature($this,5);
				$this->movemonsters();
				$turnspassed++;
				foreach ($this->creatures as $key => $creature) {
					if (in_array($creature->position,$visiblearea)&&$key){
						$vismonster=true;
						break;
					}
				}
			}
			global $eventlog;
			$eventlog[]="waited ".$turnspassed." turns";
			break;
		case "suicide":
			$this->creatures[0]->hp=0;
			global $animations;
			global $eventlog;
			$animations[]=array("type"=>"death");
			$eventlog[]="You lose the will to continue";
			break;
		default:
			break;
		}
	}
	
	//global regeneration
	public function regenall(){
		foreach ($this->creatures as $key => $creature){
			if ($this->creatures[$key]->hp<$this->creatures[$key]->maxhp) {
				$regenperiod=ceil(200/$this->creatures[$key]->maxhp);
				if (($this->creatures[0]->turncount*$this->creatures[$key]->regen)%$regenperiod<$this->creatures[$key]->regen) $this->creatures[$key]->hp++;
			}
			if ($this->creatures[$key]->mana<$this->creatures[$key]->maxmana) {
				$regenperiod=ceil(40/$this->creatures[$key]->maxmana);
				if (($this->creatures[0]->turncount)%$regenperiod==0) $this->creatures[$key]->mana++;
			}
		}
	}
	
	//update exploration visibility and movement tables
	public function addexploration(){
		$this->visible=$this->getlineofsightfrom($this->creatures[0]->position);
		$visible=$this->visible;
		foreach ($visible as $index=>$location){
			if (!in_array($location,$this->explored)){
				$this->explored[]=$location;
			} 
		}
		$this->builddistancemap($this->creatures[0]->position);
	}
	
	//computer controlled AI methods, includes call to global regen
	public function movemonsters(){
		$this->creatures[0]->turncount++;
		$this->regenall();
		foreach ($this->creatures as $key => $creature){
			$this->creatures[$key]->waiting=true;
			if ($key!=0&&$this->creatures[$key]->alive&&$this->creatures[0]->hp>0) $this->creatures[$key]->moveAI($this);
		}
	}
	
	//constructing outputs for the client to interpret
	public function getoutputs(){
		global $time;
		$output["stats"]=$this->getplayerstats();
		$output["terrain"]=$this->getvisterrain(false);
		$output["explored"]=$this->getvisterrain(true);
		$output["creatures"]=$this->getviscreatures();
		$nearexploration=array_intersect($this->explored,$this->getnearindices($this->creatures[0]->position,6));
		$output["items"]=$this->getvisitems($nearexploration);
		$output["decals"]=$this->getvisdecals($nearexploration);
		global $automove;
		if (!isset($automove)) $automove=false;
		$output["clear"]=$automove;
		global $eventlog;
		$output["movelog"]=$eventlog;
		global $animations;
		$output["animations"]=$animations;
		global $maprefresh;
		if (isset($maprefresh)&&$maprefresh){
			//$output["minimap"]=$this->getminimap($this->explored);
			$output["maprefresh"]=true;
		}
		$time["end"]=microtime(true);
		foreach ($time as $thistime){
			$output["time"][]=$thistime-$time["start"];
		}
		return json_encode($output);
	}
	public function getplayerstats(){
		if (isset($this->creatures[0])){
			$stats["hp"]=$this->creatures[0]->hp;
			$stats["maxhp"]=$this->creatures[0]->maxhp;
			$stats["mana"]=$this->creatures[0]->mana;
			$stats["maxmana"]=$this->creatures[0]->maxmana;
			$stats["experience"]=$this->creatures[0]->experience;
			$stats["level"]=$this->creatures[0]->level;
			$stats["strength"]=intval($this->creatures[0]->strength);
			$stats["dexterity"]=intval($this->creatures[0]->dexterity);
			$stats["equipment"]=array();
			foreach ($this->creatures[0]->equipment as $slot => $posession){
				if ($posession!=false) {
					if ($posession->category=="equipment"&&$posession->itemclass!="ring"){
						$stats["equipment"][]=array(
							"type"=>$posession->name,
							"strrep"=>$posession->strrep
							);
					} else {
						$stats["equipment"][]=array(
							"type"=>$posession->itemclass,
							"strrep"=>$posession->strrep
							);
					}
				} else {
					$stats["equipment"][]=false;
				}
			}
			$stats["inventory"]=array();
			foreach ($this->creatures[0]->inventory as $slot => $posession){
				if ($posession->category=="equipment"&&$posession->itemclass!="ring"){
					$stats["inventory"][]=array(
						"type"=>$posession->name,
						"strrep"=>$posession->strrep
						);
				} else {
					$stats["inventory"][]=array(
						"type"=>$posession->itemclass,
						"strrep"=>$posession->strrep
						);
				}
			}
			$stats["repetoire"]=$this->creatures[0]->repetoire;
			$stats["position"]=$this->getcartesian($this->creatures[0]->position);
			if (isset($this->items[$this->creatures[0]->position])) {
				$stats["onground"]=$this->items[$this->creatures[0]->position];
			} else $stats["onground"]=false;
		} else {
			$stats["hp"]=":(";
			$stats["inventory"]=NULL;
			$stats["position"]=$this->getcartesian($this->creatures[0]->position);
		}
		return $stats;
	}
	public function getvisterrain($incexp){
		if ($incexp) {
			$visible=array_intersect($this->explored,$this->getnearindices($this->creatures[0]->position,8));
		} else {
			$visible=$this->visible;
		}
		$centre=$this->getcartesian($this->creatures[0]->position);
		$radius=8;
		$xlow=$centre["x"]-$radius;
		$xhigh=$centre["x"]+$radius;
		$ylow=$centre["y"]-$radius;
		$yhigh=$centre["y"]+$radius;
		$map="";
		for ($i=$xlow;$i<=$xhigh;$i++){
			if ($i-$xlow){
				$map.="L";
			}
			for ($j=$ylow;$j<=$yhigh;$j++){
				if (in_array($this->getindex(array("x"=>$i,"y"=>$j)),$visible)){
					$map.=$this->terrain[$this->getindex(array("x"=>$i,"y"=>$j))];
				} else {
					$map.="u";
				}
			}
		}
		return $map;
	}
	public function getvisitems(){
		$visible=array_intersect($this->explored,$this->getnearindices($this->creatures[0]->position,8));
		if (is_array($this->items)) foreach ($this->items as $position => $itemstack){
			if (in_array($position,$visible)){
				if ($itemstack[0]->itemclass!="corpse"){
					if ($itemstack[0]->category=="equipment"&&$itemstack[0]->itemclass!="ring"){
						$output["type"]=$itemstack[0]->name;
					} else {
						$output["type"]=$itemstack[0]->itemclass;
					}
				} else {
					$output["type"]=$itemstack[0]->name.$itemstack[0]->itemclass;
				}
				$output["position"]=$this->getcartesian($position);
				$outputs[]=$output;
			}
		}
		if (!isset($outputs)) $outputs=false;
		return $outputs;
	}
	public function getviscreatures(){
		$visible=$this->visible;
		if (is_array($this->creatures)) foreach ($this->creatures as $creature){
			if (in_array($creature->position,$visible)){
				$output["type"]=$creature->creaturetype;
				$output["condition"]=$creature->hp/$creature->maxhp;
				$output["position"]=$this->getcartesian($creature->position);
				$output["equipment"]=array();
				if ($creature->equipment!=false) {
					foreach ($creature->equipment as $slot => $posession){
						if ($posession!=false){
							if ($posession->itemclass!="ring"){
								$output["equipment"][]=$posession->name;
							} 
						} 
					}
				}
				$outputs[]=$output;
			}
		}
		return $outputs;
	}
	public function getvisdecals(){
		$visible=array_intersect($this->explored,$this->getnearindices($this->creatures[0]->position,8));
		$centre=$this->getcartesian($this->creatures[0]->position);
		$radius=8;
		$xlow=$centre["x"]-$radius;
		$xhigh=$centre["x"]+$radius;
		$ylow=$centre["y"]-$radius;
		$yhigh=$centre["y"]+$radius;
		$map="";
		for ($i=$xlow;$i<=$xhigh;$i++){
			if ($i-$xlow){
				$map.="L";
			}
			for ($j=$ylow;$j<=$yhigh;$j++){
				if (in_array($this->getindex(array("x"=>$i,"y"=>$j)),$visible)&&array_key_exists(intval($this->getindex(array("x"=>$i,"y"=>$j))),$this->decals)){
					$map.=$this->decals[$this->getindex(array("x"=>$i,"y"=>$j))];
				} else {
					$map.="0";
				}
			}
		}
		return $map;
	}
	public function getminimap(){
		$strrep="";
		for ($i=0;$i<$this->boardsize["x"]*$this->boardsize["y"];$i++){
			if (!($i%60)&&$i) $strrep=$strrep."L";
			$tilerep=$this->terrain[$i];
			if (in_array($i,$this->explored)){
				$strrep.=$tilerep;
			} else $strrep.="u";
		}
		return $strrep;
	}
	public function drawpicture(){
		$picture=imagecreatetruecolor($this->boardsize["x"]*3,$this->boardsize["y"]*3);
		$floor=imagecolorallocate($picture,160,160,160);
		$player=imagecolorallocate($picture,0,255,0);
		$wall=imagecolorallocate($picture,80,80,80);
		for ($i=0;$i<$this->boardsize["x"]*$this->boardsize["y"];$i++){
			$tilexy = $this->getcartesian($i);
			$imagex = 3*$tilexy["x"];
			$imagey = 3*$tilexy["y"];
			if ($this->gettile($i)=="wall"){
				$colour=$wall;
			} else {
				$colour=$floor;
			}
			if (in_array($i,$this->explored)) imagefilledrectangle($picture,$imagex,$imagey,$imagex+2,$imagey+2,$colour);
		}
		return $picture;
	}
}
