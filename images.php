<?php
include 'dblogin.php';
$mysqli=mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
$query="SELECT creaturetype FROM creatures";
$result=$mysqli->query($query);
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
		echo '<img class="graphic" id="'.$output["creaturetype"].'144" src="getimage.php?imageof='.$output["creaturetype"].'&tilesize=144"></img>';
		echo '<img class="graphic" id="'.$output["creaturetype"].'" src="getimage.php?imageof='.$output["creaturetype"].'&tilesize=36"></img>';
		if (!in_array($output["creaturetype"],$nocorpse)){
			echo '<img class="graphic" id="'.$output["creaturetype"].'corpse144" src="getimage.php?imageof='.$output["creaturetype"].'corpse&tilesize=144"></img>';
			echo '<img class="graphic" id="'.$output["creaturetype"].'corpse" src="getimage.php?imageof='.$output["creaturetype"].'corpse&tilesize=36"></img>';
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
			echo '<img class="graphic" id="'.$output["name"].'144" src="getimage.php?imageof='.$output["name"].'&tilesize=144"></img>';
			echo '<img class="graphic" id="'.$output["name"].'" src="getimage.php?imageof='.$output["name"].'&tilesize=36"></img>';
		}
	}
}