<?php
$time["start"]=microtime(true);
include 'mapclass.php';
include 'dungeon.php';
include 'dblogin.php';
$givencommand=$_POST["command"];
$commandmodifier=$_POST["modifier"];
$commandmodifier=stripslashes($commandmodifier);

session_start();
$floor=$_SESSION["currentfloor"];
$terrain=$_SESSION["terrain"][$floor];
$decals=$_SESSION["decals"][$floor];
$creatures=$_SESSION["creatures"][$floor];
$items=$_SESSION["items"][$floor];
$explored=$_SESSION["explored"][$floor];
$visible=$_SESSION["visible"][$floor];

$boardsize = array("x"=>60,"y"=>60);
$dungeonspace = new Terrain($boardsize,$terrain);
$dungeon = new Dungeon($dungeonspace,$creatures,$items,$explored,$decals,$visible);

if ($dungeon->creatures[0]->hp>0){
	$dungeon->moveplayer($givencommand,$commandmodifier);
} else {
	global $animations;
	$animations[]=array("type"=>"death");
}

$_SESSION["creatures"][$floor]=$dungeon->creatures;
$_SESSION["items"][$floor]=$dungeon->items;
$_SESSION["explored"][$floor]=$dungeon->explored;
$_SESSION["visible"][$floor]=$dungeon->visible;

if ($_SESSION["currentfloor"]==$floor){
	global $maprefresh;
	$maprefresh=false;
	echo $dungeon->getoutputs();
} else {
	$player=clone $dungeon->creatures[0];
	$oldfloor=$floor;
	$floor=$_SESSION["currentfloor"];
	$terrain=$_SESSION["terrain"][$floor];
	$decals=$_SESSION["decals"][$floor];
	$creatures=$_SESSION["creatures"][$floor];
	$items=$_SESSION["items"][$floor];
	$explored=$_SESSION["explored"][$floor];
	$visible=$_SESSION["visible"][$floor];
	$dungeonspace = new Terrain($boardsize,$terrain);
	$dungeon = new Dungeon($dungeonspace,$creatures,$items,$explored,$decals,$visible);
	$position=$dungeon->creatures[0]->position;
	$dungeon->creatures[0]=$player;
	$dungeon->creatures[0]->position=$position;
	
	$_SESSION["creatures"][$floor]=$dungeon->creatures;
	
	global $maprefresh;
	$maprefresh=true;
	echo $dungeon->getoutputs();
}

