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

header('Content-Type: image/png');
$resized=imagecreatetruecolor($_GET["tilesize"],$_GET["tilesize"]);
imagealphablending($resized,false);
imagesavealpha($resized,true);
if ($dungeon->creatures[0]->gender!="none") {
	$original=imagecreatefrompng("tiles/".$dungeon->creatures[0]->gender.".png");
	imagecopyresampled($resized,$original,0,0,0,0,$_GET["tilesize"],$_GET["tilesize"],400,400);
}
if ($dungeon->creatures[0]->hairstyle) {
	$original=imagecreatefrompng("tiles/".$dungeon->creatures[0]->gender."hair".$dungeon->creatures[0]->hairstyle.".png");
	imagecopyresampled($resized,$original,0,0,0,0,$_GET["tilesize"],$_GET["tilesize"],400,400);
}
imagepng($resized);
imagedestroy($resized);
