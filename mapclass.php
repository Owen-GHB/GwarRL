<?php
//DungeonSpace!
class DungeonSpace{
	public $boardsize;	
	//construction and initialisation functions
	public function __construct() {
		//constructor overloading hack pray it works
		$a = func_get_args();
		$i = func_num_args();
		if (method_exists($this,$f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else {
			//default constructor goes here
			$this->boardsize["x"]=75;
			$this->boardsize["y"]=75;
			$this->boardsize["z"]=5;
		}
	}
	//first argument is assumed to be the size of the space as an array
	public function __construct1($boardsize) {
		$this->boardsize=$boardsize;
	}

	//utility functions
	public function getx($tileindex){
		$cartesianx=$tileindex%$this->boardsize["x"];
		return $cartesianx;	
	}
	public function gety($tileindex){
		$cartesiany=floor($tileindex/$this->boardsize["y"]);
		return $cartesiany;
	}
	public function getcartesian($tileindex){
		$cartesian=array("x"=>$this->getx($tileindex),"y"=>$this->gety($tileindex));
		return $cartesian;
	}
	public function getindex($cartesian){
		$tileindex=$cartesian["x"]+$cartesian["y"]*$this->boardsize["x"];
		return $tileindex;
	}
	
	// roll this into getadjacent, don't think it's needed elsewhere
	public function checkborder($tileindex){
		$tilepos=$this->getcartesian($tileindex);
		switch ($tilepos["x"]){
		case 0:
			$borders["x"]="low";
			break;
		case $this->boardsize["x"]-1:
			$borders["x"]="high";
			break;
		default:
			$borders["x"]="none";
		}
		switch ($tilepos["y"]){
		case 0:
			$borders["y"]="low";
			break;
		case $this->boardsize["y"]-1:
			$borders["y"]="high";
			break;
		default:
			$borders["y"]="none";
		}
		return $borders;
	}
	public function getnearindices($tileindex,$radius){
		$centre=$this->getcartesian($tileindex);
		$xlow=max($centre["x"]-$radius,0);
		$xhigh=min($centre["x"]+$radius,$this->boardsize["x"]-1);
		$ylow=max($centre["y"]-$radius,0);
		$yhigh=min($centre["y"]+$radius,$this->boardsize["y"]-1);
		for ($i=$xlow;$i<=$xhigh;$i++){
			for ($j=$ylow;$j<=$yhigh;$j++){
				$nearindices[]=$i+$this->boardsize["x"]*$j;
			}
		}
		return $nearindices;
	}
	
	public function getdirection($indexfrom,$indexto){
		$start=$this->getcartesian($indexfrom);
		$end=$this->getcartesian($indexto);
		$offset["x"]=$this->getx($indexto)-$this->getx($indexfrom);
		$offset["y"]=$this->gety($indexto)-$this->gety($indexfrom);
		if ($offset["y"]>2*abs($offset["x"])){
			$direction=2;
		} else if ($offset["y"]==2*abs($offset["x"])&&$offset["x"]>0) {
			$direction[]=2;
			$direction[]=3;
		} else if ($offset["y"]>1/2*abs($offset["x"])&&$offset["x"]>0) {
			$direction=3;
		} else if ($offset["y"]==1/2*abs($offset["x"])&&$offset["x"]>0) {
			$direction[]=3;
			$direction[]=6;
		} else if ($offset["y"]==2*abs($offset["x"])&&$offset["x"]<0) {
			$direction[]=2;
			$direction[]=1;
		} else if ($offset["y"]>1/2*abs($offset["x"])&&$offset["x"]<0) {
			$direction=1;
		} else if ($offset["y"]==1/2*abs($offset["x"])&&$offset["x"]<0) {
			$direction[]=1;
			$direction[]=4;
		} else if (abs($offset["y"])<1/2*abs($offset["x"])&&$offset["x"]>0) {
			$direction=6;
		} else if ($offset["y"]==-1/2*abs($offset["x"])&&$offset["x"]>0) {
			$direction[]=6;
			$direction[]=3;
		} else if (abs($offset["y"])<1/2*abs($offset["x"])&&$offset["x"]<0) {
			$direction=4;
		} else if ($offset["y"]==-1/2*abs($offset["x"])&&$offset["x"]<0) {
			$direction[]=4;
			$direction[]=7;
		} else if ($offset["y"]<-1/2*abs($offset["x"])&&$offset["x"]>0) {
			$direction=9;
		} else if ($offset["y"]==-1/2*abs($offset["x"])&&$offset["x"]>0) {
			$direction[]=9;
			$direction[]=8;
		} else if ($offset["y"]<-1/2*abs($offset["x"])&&$offset["x"]<0) {
			$direction=7;
		} else if ($offset["y"]==-1/2*abs($offset["x"])&&$offset["x"]<0) {
			$direction[]=7;
			$direction[]=8;
		} else if ($offset["y"]<-2*abs($offset["x"])){
			$direction=8;
		} else if ($offset["y"]==0&&$offset["x"]==0){
			$direction=5;
		} else echo "This maths is bad";
		return $direction;
	}
	//get canonical line (or two) from pair of points
	public function getline($indexfrom,$indexto){
		//calculate distance and directions
		$start=$this->getcartesian($indexfrom);
		$end=$this->getcartesian($indexto);
		$offset["x"]=$this->getx($indexto)-$this->getx($indexfrom);
		$offset["y"]=$this->gety($indexto)-$this->gety($indexfrom);
		if (abs($offset["x"])>abs($offset["y"])){
			$longaxis="x";
			$shortaxis="y";
		} else {
			$longaxis="y";
			$shortaxis="x";
		}
		$direction["x"]=min(1,max(-1,$end["x"]-$start["x"]));
		$direction["y"]=min(1,max(-1,$end["y"]-$start["y"]));
		$distance=max(abs($offset["x"]),abs($offset["y"]));
		$diagonals=min(abs($offset["x"]),abs($offset["y"]));
		$laterals=$distance-$diagonals;
		$lateralshift=$this->getindex(array($longaxis=>$start[$longaxis]+$direction[$longaxis],$shortaxis=>$start[$shortaxis]))-$indexfrom;
		$diagonalshift=$this->getindex(array($longaxis=>$start[$longaxis]+$direction[$longaxis],$shortaxis=>$start[$shortaxis]+$direction[$shortaxis]))-$indexfrom;
		
		//check if we have two lines or one
		if ($laterals!=0&&$diagonals!=0&&$distance%2==0) {
			$npaths=2;
		} else $npaths=1;
		
		//get single canonical line
		if ($npaths==1){
			$path[0]=$indexfrom;
			$path[$distance]=$indexto;
			for($i=1;$i<=$distance/2;$i++){
				if ($diagonals>$laterals){
					$path[$i]=$path[$i-1]+$diagonalshift;
					$diagonals--;
					$path[$distance-$i]=$path[$distance-$i+1]-$diagonalshift;
					$diagonals--;
				} else if ($laterals>$diagonals) {
					$path[$i]=$path[$i-1]+$lateralshift;
					$laterals--;
					$path[$distance-$i]=$path[$distance-$i+1]-$lateralshift;
					$laterals--;
				}
			}
		}
		//get double canonical lines
		if ($npaths==2){
			$paths[0][0]=$indexfrom;
			$paths[1][0]=$indexfrom;
			$paths[0][$distance]=$indexto;
			$paths[1][$distance]=$indexto;
			for($i=1;$i<$distance/2;$i++){
				if ($diagonals>$laterals){
					$paths[0][$i]=$paths[0][$i-1]+$diagonalshift;
					$paths[1][$i]=$paths[1][$i-1]+$diagonalshift;
					$diagonals--;
					$paths[0][$distance-$i]=$paths[0][$distance-$i+1]-$diagonalshift;
					$paths[1][$distance-$i]=$paths[1][$distance-$i+1]-$diagonalshift;
					$diagonals--;
				} else if ($laterals>$diagonals) {
					$paths[0][$i]=$paths[0][$i-1]+$lateralshift;
					$paths[1][$i]=$paths[1][$i-1]+$lateralshift;
					$laterals--;
					$paths[0][$distance-$i]=$paths[0][$distance-$i+1]-$lateralshift;
					$paths[1][$distance-$i]=$paths[1][$distance-$i+1]-$lateralshift;
					$laterals--;
				} else if ($laterals==$diagonals) {
					$paths[0][$i]=$paths[0][$i-1]+($i%2)*$diagonalshift+(($i+1)%2)*$lateralshift;
					$paths[1][$i]=$paths[1][$i-1]+(($i+1)%2)*$diagonalshift+($i%2)*$lateralshift;
					$paths[0][$distance-$i]=$paths[0][$distance-$i+1]-(($i+1)%2)*$diagonalshift-($i%2)*$lateralshift;
					$paths[1][$distance-$i]=$paths[1][$distance-$i+1]-($i%2)*$diagonalshift-(($i+1)%2)*$lateralshift;
					$diagonals--;
					$laterals--;
				}
			}
			$paths[0][$distance/2]=$paths[0][$distance/2-1]+(($distance/2)%2)*$diagonalshift+(($distance/2+1)%2)*$lateralshift;
			$paths[1][$distance/2]=$paths[1][$distance/2-1]+(($distance/2+1)%2)*$diagonalshift+(($distance/2)%2)*$lateralshift;
		}
		$returnvalue["npaths"]=$npaths;
		if ($npaths==1){
			foreach ($path as $step => $position){
				$returnvalue[$step]=$position;
			}
		}
		if ($npaths==2){
			foreach ($paths as $pathindex => $path){
				foreach ($path as $step => $position){
					$returnvalue[$step][$pathindex]=$position;
				}
			}
		}
		$returnvalue["distance"]=$distance;
		return $returnvalue;
	}
}
class Terrain extends DungeonSpace {
	public $terrain;
	public $distancemap;
	//construction and initialisation functions
	public function __construct() {
		//constructor overloading hack pray it works
		$a = func_get_args();
		$i = func_num_args();
		if (method_exists($this,$f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else {
			//default constructor goes here
			$this->boardsize["x"]=75;
			$this->boardsize["y"]=75;
			$this->fillspace("wall");
			foreach ($this->terrain as $index=>$type){
				$this->distancemap[$index]=-1;
			}
		}
	}
	public function __construct1($boardsize) {
		$this->boardsize=$boardsize;
		$this->fillspace("wall");
	}
	public function __construct2($boardsize,$terrain) {
		$this->boardsize=$boardsize;
		$this->terrain=$terrain;
	}
	public function fillspace($thing){
		switch ($thing){
		case "floor":
			$thing="0";
			break;
		case "wall":
			$thing="1";
			break;
		default:
			$thing="1";
			break;
		}
		for ($i=0;$i<$this->boardsize["x"]*$this->boardsize["y"];$i++){
			$this->terrain[]=$thing;
		}
	}
	public function gettile($position){
		$tile=$this->terrain[$position];
		switch ($tile){
		case "0":
			$tile="floor";
			break;
		case "1":
			$tile="wall";
			break;
		case "2":
			$tile="upstair";
			break;
		case "3":
			$tile="downstair";
			break;
		default:
			$tile="wall";
			break;
		}
		return $tile;
	}
	public function settile($position,$tile){
		switch ($tile){
		case "floor":
			$this->terrain[$position]="0";
			break;
		case "wall":
			$this->terrain[$position]="1";
			break;
		case "upstair":
			$this->terrain[$position]="2";
			break;
		case "downstair":
			$this->terrain[$position]="3";
			break;
		default:
			$this->terrain[$position]="1";
			break;
		}
	}
	public function getarea($positions){
		foreach ($positions as $position){
			$tiles[]=gettile($position);
		}
		return $tiles;
	}
	public function setarea($positions,$tiles){
		foreach ($positions as $key => $position){
			$this->settile($position,$tiles[$key]);
		}
	}
	//return array with index of every square in line of sight
	public function getlineofsightfrom($position){
		$neartiles=$this->getnearindices($position,7);
		foreach ($neartiles as $tile){
			if ($this->checklosbetween($position,$tile)){
				$visible[]=$tile;
			}
		}
		return $visible;
	}
	public function checklosbetween($indexfrom,$indexto){
		$los=true;
		$path=$this->getline($indexfrom,$indexto);
		$distance=$path["distance"];
		if($distance>7) $los=false;
		foreach ($path as $step => $thistile){
			if (is_numeric($step)){
				if (is_array($thistile)){
					if ($this->gettile($thistile[0])=="wall"&&$this->gettile($thistile[1])=="wall"&&$step<$distance){
						$los=false;
					}
				} else if ($this->gettile($thistile)=="wall"&&$step<$distance){
					$los=false;
				} 
			}
		}
		return $los;
	}
	//djikstra map
	public function builddistancemap($goal){
		foreach ($this->terrain as $index=>$type){
			$this->distancemap[$index]=-1;
		}
		$this->distancemap[$goal]=0;
		$edge=$this->getnearindices($goal,1);
		$step=1;
		while (count($edge)>0){
			foreach ($edge as $edgeindex=>$tileindex){
				if ($this->terrain[$tileindex]==1||$this->distancemap[$tileindex]>=0){
					unset($edge[$edgeindex]);
				} else {
					$this->distancemap[$tileindex]=$step;
				}
			}
			foreach ($edge as $edgeindex=>$tileindex){
				$neartiles=$this->getnearindices($tileindex,1);
				foreach ($neartiles as $tileindex){
					if ($this->terrain[$tileindex]!=1&&
						$this->distancemap[$tileindex]<0&&
						!in_array($tileindex,$edge)){
						$edge[]=$tileindex;
					}
				}
			}
			$step++;
		}
	}
	public function buildpath($origin){
		$goal=array_keys($this->distancemap,0)[0];
		$path=array();
		$nopath=false;
		$stepsleft=$this->distancemap[$origin];
		$currenttile=$origin;
		if ($stepsleft>0) while ($stepsleft>0&&$nopath==false){
			$adjacenttiles=$this->getnearindices($currenttile,1);
			unset ($adjacenttiles[4]);
			$choices=array();
			foreach ($adjacenttiles as $dir=>$tile){
				if ($this->distancemap[$tile]>=0&&$this->distancemap[$tile]<$stepsleft) {
					$choices["tiles"][]=$tile;
					$choices["directions"][]=$this->getdirection($currenttile,$tile);
					$choices["mod"][]=abs($this->getx($tile)-$this->getx($goal))+abs($this->gety($tile)-$this->gety($goal));
				}
			}
			if (count($choices)>0) {
				$minima=array_keys($choices["mod"],min($choices["mod"]));
				if (count($minima)>1) {
					$choice=$minima[array_rand($minima)];
				} else $choice=$minima[0];
				$currenttile=$choices["tiles"][$choice];
				$direction=$choices["directions"][$choice];
				$path[]=$direction;
				$stepsleft--;
			} else $nopath=true;
		}
		return $path;
	}
	public function reversepath($path){
		$reversepath=array();
		if (count($path)) foreach ($path as $step=>$direction){
			$reversepath[count($path)-$step-1]=10-$direction;
		}
		return $reversepath;
	}
}
