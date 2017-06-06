<?php
include 'dblogin.php';
$mysqli=mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
$query="SELECT * FROM creatures";
$result=$mysqli->query($query);
$allplayer=false;
$weapon=array(
	"acc"=>0,
	"block"=>0,
	"blunt"=>2,
	"slash"=>5,
	"pierce"=>0
	);
$advweapon=array(
	"acc"=>0,
	"block"=>6,
	"blunt"=>2,
	"slash"=>5,
	"pierce"=>0
	);
if ($result) {
	while($stats=$result->fetch_assoc()){
		$creatures[]=$stats;
		$output=array();
		$output["creaturetype"]=$stats["creaturetype"];
		$output["level"]=$stats["level"];
		if (!$allplayer) {
			$allplayer=$stats;
		}
		for ($i=0;$i<14;$i++){
			$adversary=$allplayer;
			$adversary["maxhp"]+=2*$i;
			$adversary["strength"]+=floor($i/2);
			$adversary["dexterity"]+=floor($i/2);
			$attacker=$stats;
			$roundsalive=(rawdefense($attacker)*extradefense($attacker,$weapon,$adversary,$advweapon)/rawoffense($adversary,$weapon));
			$dmgoutput=$roundsalive*rawoffense($attacker,$weapon);
			$relativepower=$dmgoutput/(rawdefense($adversary)*extradefense($adversary,$advweapon,$attacker,$weapon));
			$output["vsplayer"][]=round($relativepower*100)."%";
		}
		var_dump($output);
	}
}
function rawoffense($stats,$weapon){
	$val=(weaponavg($weapon)+weaponavg($weapon)*critchance($stats["dexterity"])*(critmultiplier($stats["dexterity"])-1))*forcemultiplier($stats["strength"])*hitchance($stats["dexterity"],$weapon);
	return $val;
}
function rawdefense($stats){
	$val=$stats["maxhp"];
	return $val;
}
function extradefense($stats,$weapon,$advstats,$advweapon){
	$val=1/(dodgepenalty($stats["dexterity"],$advstats["dexterity"])*acpenalty($advweapon,$stats["armour"])*blockpenalty($stats,$weapon,$advstats));
	return $val;
}
function hitchance($dexterity,$weapon){
	$val=pow(0.4,(1/($dexterity+$weapon["acc"])));
	return $val;
}
function critchance($dexterity){
	$val=1-pow(0.98,$dexterity);
	return $val;
}
function critmultiplier($dexterity){
	$val=pow(1.1,$dexterity);
	return $val;
}
function forcemultiplier($strength){
	$val=pow(1.125,$strength-6);
	return $val;
}
function dodgepenalty($dex1,$dex2){
	$val=1-(1-pow(0.93,max($dex1-4,0)))*pow(0.98,max($dex2-$dex1,0));
	return $val;
}
function blockpenalty($stats,$weapon,$advstats){
	$val=1-(1-pow(0.95,$weapon["block"]))*pow(0.97,max($advstats["strength"]-$stats["strength"],0));
	return $val;
}
function weaponavg($weapon){
	$val=($weapon["blunt"]+$weapon["blunt"]*$weapon["slash"])/2+$weapon["pierce"];
	return $val;
}
function acpenalty($advweapon,$ac){
	$val=(max(($advweapon["blunt"]+$advweapon["blunt"]*$advweapon["slash"])/2-$ac/2,1)+$advweapon["pierce"])/weaponavg($advweapon);
	return $val;
}