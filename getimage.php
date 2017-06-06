<?php
header('Content-Type: image/png');
$resized=imagecreatetruecolor($_GET["tilesize"],$_GET["tilesize"]);
imagealphablending($resized,false);
imagesavealpha($resized,true);
if ($_GET["imageof"]!="none") {
	$original=imagecreatefrompng("tiles/".$_GET["imageof"].".png");
	imagecopyresampled($resized,$original,0,0,0,0,$_GET["tilesize"],$_GET["tilesize"],400,400);
}
imagepng($resized);
imagedestroy($resized);
