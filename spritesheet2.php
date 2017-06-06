<img id="wall" class="graphic" src="getimage.php?imageof=wall&tilesize=32">
<img id="floor" class="graphic" src="getimage.php?imageof=floor&tilesize=32">
<img id="stairup" class="graphic" src="getimage.php?imageof=stairup&tilesize=32">
<img id="stairdown" class="graphic" src="getimage.php?imageof=stairdown&tilesize=32">
<img id="decal1" class="graphic" src="getimage.php?imageof=decal1&tilesize=32">
<img id="decal2" class="graphic" src="getimage.php?imageof=decal2&tilesize=32">
<img id="decal3" class="graphic" src="getimage.php?imageof=decal3&tilesize=32">
<img id="decal4" class="graphic" src="getimage.php?imageof=decal4&tilesize=32">
<img id="decal5" class="graphic" src="getimage.php?imageof=decal5&tilesize=32">
<img id="decal6" class="graphic" src="getimage.php?imageof=decal6&tilesize=32">
<img id="decal7" class="graphic" src="getimage.php?imageof=decal7&tilesize=32">
<img id="decal8" class="graphic" src="getimage.php?imageof=decal8&tilesize=32">
<img id="decal9" class="graphic" src="getimage.php?imageof=decal9&tilesize=32">
<?php
include 'dblogin.php';
$mysqli=mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
$query="SELECT creaturetype FROM creatures";
$result=$mysqli->query($query);
$tilesize=32;
$nocorpse=array(
	"player",
	"goblinsoldier",
	"goblinshaman",
	"orcslave",
	"orcsoldier",
	"orccaptain",
	"orcshaman"
	);
if ($result) {
	while($output=$result->fetch_assoc()){
		echo '<img class="graphic" id="'.$output["creaturetype"].'" src="getimage.php?imageof='.$output["creaturetype"].'&tilesize=32"></img>';
		if (!in_array($output["creaturetype"],$nocorpse)){
			echo '<img class="graphic" id="'.$output["creaturetype"].'corpse" src="getimage.php?imageof='.$output["creaturetype"].'corpse&tilesize=32"></img>';
		}
	}
}
$query="SELECT * FROM items";
$result=$mysqli->query($query);
$miscclasses=array(
	"potion",
	"ring"
	);
if ($result) {
	while($output=$result->fetch_assoc()){
		if (!in_array($output["class"],$miscclasses)){
			echo '<img class="graphic" id="'.$output["name"].'" src="getimage.php?imageof='.$output["name"].'&tilesize=32"></img>';
		}
	}
}
