<?php
include 'mapclass.php';
include 'dungeon.php';
include 'dblogin.php';

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

echo $dungeon->getminimap();

