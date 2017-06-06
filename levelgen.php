<?php
include 'mapclass.php';
include 'dungeon.php';
include 'dblogin.php';
session_start();
//terrain randomisation/level generation functions. Procedural because I want to
function buildrandompath($tofill,$sectorspace){
	$step=0;
	$path[$step]=rand(0,$sectorspace->boardsize["x"]*$sectorspace->boardsize["y"]-1);
	$sectorsfilled=1;
	while ($sectorsfilled<$tofill){
		$adjacent=$sectorspace->getnearindices($path[$step],1);
		$nextstep=$adjacent[array_rand($adjacent)];
		if (!in_array($nextstep,$path)){
			$sectorsfilled++;
		}
		$step++;
		$path[$step]=$nextstep;		
	}
	return $path;
}
function carvecorridor($start,$end,$dungeon){
	//move along long axis to midpoint, then along short axis, then long axis to endpoint
	if (abs($start["x"]-$end["x"])<abs($start["y"]-$end["y"])){
		$axis=array("long"=>"y","short"=>"x");
	} else {
		$axis=array("long"=>"x","short"=>"y");
	}
	$direction=array("x"=>min(1, max(-1, $end["x"]-$start["x"])),"y"=>min(1, max(-1, $end["y"]-$start["y"])));
	$midpoint=ceil(($start[$axis["long"]]+$end[$axis["long"]])/2)-$start[$axis["long"]];
	for ($i=0;$i<abs($midpoint);$i++){
		$thistile[$axis["long"]]=$start[$axis["long"]]+$i*$direction[$axis["long"]];
		$thistile[$axis["short"]]=$start[$axis["short"]];
		$dungeon->settile($dungeon->getindex($thistile),"floor");
	}
	for ($i=0;$i<abs($start[$axis["short"]]-$end[$axis["short"]]);$i++){
		$thistile[$axis["long"]]=$start[$axis["long"]]+$midpoint;
		$thistile[$axis["short"]]=$start[$axis["short"]]+$i*$direction[$axis["short"]];
		$dungeon->settile($dungeon->getindex($thistile),"floor");
	};
	for ($i=0;$i<abs($start[$axis["long"]]-$end[$axis["long"]])-abs($midpoint);$i++){
		$thistile[$axis["long"]]=$start[$axis["long"]]+$midpoint+$i*$direction[$axis["long"]];
		$thistile[$axis["short"]]=$end[$axis["short"]];
		$dungeon->settile($dungeon->getindex($thistile),"floor");
	}
	return $dungeon;
}
function connectsectors($sectorindexpair,$sectorspace,$dungeon){
	$sectorsize = array("x"=>$dungeon->boardsize["x"]/$sectorspace->boardsize["x"],"y"=>$dungeon->boardsize["y"]/$sectorspace->boardsize["y"]);
	$sectorposition["from"]=$sectorspace->getcartesian($sectorindexpair["from"]);
	$sectoroffset["from"]=array("x"=>$sectorsize["x"]*$sectorposition["from"]["x"],"y"=>$sectorsize["y"]*$sectorposition["from"]["y"]);
	$sectormiddle["from"]=array("x"=>$sectoroffset["from"]["x"]+floor($sectorsize["x"]/2),"y"=>$sectoroffset["from"]["y"]+floor($sectorsize["y"]/2));
	$sectorposition["to"]=$sectorspace->getcartesian($sectorindexpair["to"]);
	$sectoroffset["to"]=array("x"=>$sectorsize["x"]*$sectorposition["to"]["x"],"y"=>$sectorsize["y"]*$sectorposition["to"]["y"]);
	$sectormiddle["to"]=array("x"=>$sectoroffset["to"]["x"]+floor($sectorsize["x"]/2),"y"=>$sectoroffset["to"]["y"]+floor($sectorsize["y"]/2));
	$from=array("x"=>$sectormiddle["from"]["x"]+rand(1,5)-3,"y"=>$sectormiddle["from"]["y"]+rand(1,5)-3);
	$to=array("x"=>$sectormiddle["to"]["x"]+rand(1,5)-3,"y"=>$sectormiddle["to"]["y"]+rand(1,5)-3);
	$dungeon=carvecorridor($from,$to,$dungeon);
	return $dungeon;
}
function populateroom($size,$centre,$dungeon,$floor){
	$creatureset=choosecreatureset($floor);
	$monsterlist=makemonsterlist($creatureset,$floor);
	$lowcorner=array("x"=>$centre["x"]-ceil($size["x"]/2),"y"=>$centre["y"]-ceil($size["y"]/2));
	foreach ($monsterlist as $type => $amount){
		if ($amount>0) for ($i=0;$i<$amount;$i++){
			$picked=false;
			while (!$picked){
				$creaturepos["x"]=rand($lowcorner["x"]+1,$lowcorner["x"]+$size["x"]-1);
				$creaturepos["y"]=rand($lowcorner["y"]+1,$lowcorner["y"]+$size["y"]-1);
				$position=$dungeon->getindex($creaturepos);
				if ($dungeon->gettile($position)!="wall"&&$dungeon->checkoccupancy($position)==false){
					$picked=true;				
				}
			}
			$dungeon->addcreature($creatureset,$type,$position);
		}
	}
	for ($i=0;$i<3;$i++){
		$rn=rand(1,5);
		if ($rn==5){
			$picked=false;
			while (!$picked){
				$itempos["x"]=rand($lowcorner["x"]+1,$lowcorner["x"]+$size["x"]-1);
				$itempos["y"]=rand($lowcorner["y"]+1,$lowcorner["y"]+$size["y"]-1);
				$position=$dungeon->getindex($itempos);
				if ($dungeon->gettile($position)!="wall"&&$dungeon->checkoccupancy($position)==false){
					$picked=true;				
				}
			}
			$iteminfo=chooseitem($floor);
			$dungeon->additem($iteminfo["itemclass"],$iteminfo["name"],$position);
		}
	}
	return $dungeon;
}
function choosecreatureset($floor){
	$weights=array(
		"animal"=>(60+$floor*5),
		"goblinoid"=>(50+$floor*10),
		"undead"=>(($floor+abs($floor-2)-2)*10),
		"demonic"=>(($floor+abs($floor-3)-3)*10)
		);
	if ($floor>7) $weights["animal"]=0;
	$cweight=0;
	$chosen=false;
	foreach ($weights as $set=>$weight){
		$cweight+=$weight;
	}
	$rn=rand(1,$cweight);
	$cweight=0;
	foreach ($weights as $set=>$weight){
		$cweight+=$weight;
		if (!$chosen&&$rn<=$cweight) {
			$creatureset=$set;
			$chosen=true;
		}
	}
	return $creatureset;
}
function makemonsterlist($creatureset,$floor){
	global $mysqli;
	$query="SELECT creaturetype, level FROM creatures WHERE creatureset='".$creatureset."'";
	if ($mysqli->query($query)) {
		$result=$mysqli->query($query);
		while ($row=$result->fetch_assoc()) {
			$list[]=$row;
		}
		$result->free();
	} else {
		echo "creatureset not found";
	}
	$cweight=0;
	foreach ($list as $entryno => $entry){
		if (abs($floor-$entry["level"])<3) {
			$weights[$entryno]=intval(1000/pow((abs($floor-$entry["level"])+1),2));
		} else $weights[$entryno]=1;
		$list[$entryno][1]=0;
		$cweight+=$weights[$entryno];
	}
	$roompopulation=rand($floor*4,$floor*6);
	$choice=-1;
	$placed=0;
	while ($roompopulation>$placed){
		$rn=rand(1,$cweight);
		$cweight=0;
		if ($creatureset!="animal") $choice=-1;
		foreach ($list as $entryno => $entry){
			$cweight+=$weights[$entryno];
			if ($choice<0&&$rn<=$cweight) $choice=$entryno;
		}
		$list[$choice][1]++;
		$placed+=$list[$choice]["level"];
	}
	foreach ($list as $entryno => $entry){
		if ($entry[1]>0) $list[$entry["creaturetype"]]=$entry[1];
		unset ($list[$entryno]);
	}
	return $list;
}
function chooseitem($floor){
	$weights=array(
		"potion"=>4,
		"ring"=>2,
		"weapon"=>3,
		"armour"=>3,
		"shield"=>2,
		"helmet"=>1
		);
	$cweight=0;
	$chosen=false;
	foreach ($weights as $set=>$weight){
		$cweight+=$weight;
	}
	$rn=rand(1,$cweight);
	$cweight=0;
	foreach ($weights as $set=>$weight){
		$cweight+=$weight;
		if (!$chosen&&$rn<=$cweight) {
			$final["itemclass"]=$set;
			$chosen=true;
		}
	}
	global $mysqli;
	$query="SELECT name, rarity FROM items WHERE class='".$final["itemclass"]."'";
	if ($mysqli->query($query)) {
		$result=$mysqli->query($query);
		while ($row=$result->fetch_assoc()) {
			$list[]=$row;
		}
		$result->free();
	} else {
		echo "itemclass not found";
	}
	$cweight=0;
	foreach ($list as $entryno => $entry){
		$weights[$entryno]=intval(1000/pow(max($entry["rarity"]-$floor,1),2));
		$cweight+=$weights[$entryno];
	}
	$choice=-1;
	$rn=rand(1,$cweight);
	$cweight=0;
	foreach ($list as $entryno => $entry){
		$cweight+=$weights[$entryno];
		if ($choice<0&&$rn<=$cweight) {
			$final["name"]=$entry["name"];
			$choice++;
		}
	}
	return $final;
}
function carverectangle($size,$centre,$dungeon){
	$lowcorner=array("x"=>$centre["x"]-ceil($size["x"]/2),"y"=>$centre["y"]-ceil($size["y"]/2));
	for ($i=0;$i<$size["x"];$i++) {
		for ($j=0;$j<$size["y"];$j++) {
			$tilepos=array("x"=>$lowcorner["x"]+$i,"y"=>$lowcorner["y"]+$j);
			$tileindex=$dungeon->getindex($tilepos);
			$dungeon->settile($tileindex,"floor");
		}
	}
	return $dungeon;
}
function carvesector($sectorindex,$sectorspace,$dungeon,$floor){
	$sectorsize=array("x"=>$dungeon->boardsize["x"]/$sectorspace->boardsize["x"],"y"=>$dungeon->boardsize["y"]/$sectorspace->boardsize["y"]);
	$sectorposition=$sectorspace->getcartesian($sectorindex);
	$sectoroffset=array("x"=>$sectorsize["x"]*$sectorposition["x"],"y"=>$sectorsize["y"]*$sectorposition["y"]);
	$sectormiddle=array("x"=>$sectoroffset["x"]+floor($sectorsize["x"]/2),"y"=>$sectoroffset["y"]+floor($sectorsize["y"]/2));
	$roomsize=array("x"=>rand(6,10),"y"=>rand(6,10));
	$roomcentre=array("x"=>$sectormiddle["x"],"y"=>$sectormiddle["y"]);
	$dungeon=carverectangle($roomsize,$roomcentre,$dungeon);
	if (isset($dungeon->creatures[0])){
		$dungeon=populateroom($roomsize,$roomcentre,$dungeon,$floor);
	}
	if (isset($args)&&$args=="final"){
		$lowcorner=array("x"=>$roomcentre["x"]-ceil($roomsize["x"]/2),"y"=>$roomcentre["y"]-ceil($roomsize["y"]/2));
		$stairpos["x"]=rand($lowcorner["x"]+1,$lowcorner["x"]+$roomsize["x"]-1);
		$stairpos["y"]=rand($lowcorner["y"]+1,$lowcorner["y"]+$roomsize["y"]-1);
	}
	return $dungeon;
}
function makedecals($dungeon){
	$candidates=array_rand($dungeon->terrain,210);
	foreach ($candidates as $candidate){
		if ($dungeon->terrain[$candidate]==1){ 
			$decal=rand(1,9);
			$decals[$candidate]=$decal;
		}
	}
	return $decals;
}
function makelevel($dungeon,$floor){
	$sectorsize=15;
	$tofill=4+2*(min($floor,3));
	$sectorspace=array("x"=>$dungeon->boardsize["x"]/$sectorsize,"y"=>$dungeon->boardsize["y"]/$sectorsize);
	$sectorspace=new DungeonSpace($sectorspace);
	$path = buildrandompath($tofill,$sectorspace);
	$dungeon=carvesector($path[0],$sectorspace,$dungeon,$floor);
	$dungeon->addplayer("Gwilim","male",0);
	$carved[]=$path[0];
	for ($i=1;$i<count($path);$i++){
		$args="none";
		if ($i==count($path)-1) $args="final";
		$sectorindexpair=array("from"=>$path[$i-1],"to"=>$path[$i]);
		$dungeon=connectsectors($sectorindexpair,$sectorspace,$dungeon);
		if (!in_array($path[$i],$carved)){
			$dungeon=carvesector($path[$i],$sectorspace,$dungeon,$floor);
			$carved[]=$path[$i];
		}
	}
	if ($floor>1) $dungeon->settile($dungeon->creatures[0]->position,"upstair");
	//treat items
	foreach ($dungeon->items as $key=>$item){
		if (is_array($item)) foreach($item as $thisitem){
			$quality=$thisitem->setquality();
			$enchant=false;
			if (!$quality) $enchant=$thisitem->setenchant($floor);
			if ($enchant) $thisitem->setbrand();
		} 
	}
	
	$dungeon->settile($dungeon->pickempty($dungeon->terrain),"downstair");
	$dungeon->decals=makedecals($dungeon);
	$dungeon->explored=array();
	$dungeon->addexploration();
	return $dungeon;
}

$time["start"]=microtime(true);
$mysqli=mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

if (isset($_SESSION["currentfloor"])){
	$floor=$_SESSION["currentfloor"];
	$terrain=$_SESSION["terrain"][$floor];
	$decals=$_SESSION["decals"][$floor];
	$creatures=$_SESSION["creatures"][$floor];
	$items=$_SESSION["items"][$floor];
	$explored=$_SESSION["explored"][$floor];
	$boardsize = array("x"=>60,"y"=>60);
	$dungeonspace = new Terrain($boardsize,$terrain);
	$dungeon = new Dungeon($dungeonspace,$creatures,$items,$explored,$decals);
	if ($dungeon->creatures[0]->hp>0){
		
	} else {
		for ($floor=9;$floor>0;$floor--){
			$dungeon = new Dungeon();
			$dungeon = makelevel($dungeon,$floor);
			$_SESSION["terrain"][$floor]=$dungeon->terrain;
			$_SESSION["decals"][$floor]=$dungeon->decals;
			$_SESSION["creatures"][$floor]=$dungeon->creatures;
			$_SESSION["items"][$floor]=$dungeon->items;
			$_SESSION["explored"][$floor]=$dungeon->explored;
			$_SESSION["visible"][$floor]=$dungeon->visible;
		}
		$_SESSION["currentfloor"]=1;
	}
} else {
	for ($floor=9;$floor>0;$floor--){
		$dungeon = new Dungeon();
		$dungeon = makelevel($dungeon,$floor);
		$_SESSION["terrain"][$floor]=$dungeon->terrain;
		$_SESSION["decals"][$floor]=$dungeon->decals;
		$_SESSION["creatures"][$floor]=$dungeon->creatures;
		$_SESSION["items"][$floor]=$dungeon->items;
		$_SESSION["explored"][$floor]=$dungeon->explored;
		$_SESSION["visible"][$floor]=$dungeon->visible;
	}
	$_SESSION["currentfloor"]=1;
}
global $maprefresh;
$maprefresh=true;
echo $dungeon->getoutputs();