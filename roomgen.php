<?php
include 'dblogin.php';
$mysqli=mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
$query="SELECT * FROM creatures";
if ($mysqli->query($query)) {
	$stats=$mysqli->query($query)->fetch_all();	
} else {
	echo "creature not found";
}
$allplayer=$stats[0];
$playerlevel=1;
$levelbonus=array(
	6=>0,
	7=>0,
	3=>4
	);
$eqbonus=array(
	5=>2,
	6=>3,
	7=>0,
	8=>0
	);
var_dump($allplayer);
foreach ($stats as $entry=>$monster) {
	$monsterstats=false;
	$monsterstats["name"]=$monster[0];
	$monsterstats["level"]=$monster[2];
	var_dump($monsterstats);
	$challengebylevel;
	for ($i=0;$i<10;$i++){
		$player=$allplayer;
		if ($i>0) for ($j=0;$j<$i;$j++){
			foreach ($levelbonus as $stat=>$value){
				$player[$stat]+=$value;
			}
		}
		$hp=$monster[3];
		$ac=$monster[8];
		$expecteddam=((3*$player[5]/4)-min($ac,$player[5])/2)*(1-pow(0.7,$player[6]));
		$evasionadjust=pow(0.9,$monster[7]);
		$monsterstats["roundsforplayertokill"]=$hp/$expecteddam;
		$hp=$player[3];
		$ac=$player[8];
		$expecteddam=((3*$monster[5]/4)-min($ac,$monster[5])/2)*(1-pow(0.7,$monster[6]));
		$evasionadjust=pow(0.9,$player[7]);
		$monsterstats["roundstokillplayer"]=$hp/$expecteddam;
		$monsterstats["damagetoplayerestimate"]=intval(100*$expecteddam*$monsterstats["roundsforplayertokill"]/$hp)."%";
		$challengebylevel[$i+1]["unarmed"]=$monsterstats["damagetoplayerestimate"]=intval(100*$expecteddam*$monsterstats["roundsforplayertokill"]/$hp)."%";
		foreach ($eqbonus as $stat=>$value){
			$player[$stat]+=$value;
		}
		$hp=$monster[3];
		$ac=$monster[8];
		$expecteddam=((3*$player[5]/4)-min($ac,$player[5])/2)*(1-pow(0.7,$player[6]));
		$evasionadjust=pow(0.9,$monster[7]);
		$monsterstats["roundsforplayertokill"]=$hp/$expecteddam;
		$hp=$player[3];
		$ac=$player[8];
		$expecteddam=((3*$monster[5]/4)-min($ac,$monster[5])/2)*(1-pow(0.7,$monster[6]));
		$evasionadjust=pow(0.9,$player[7]);
		$monsterstats["roundstokillplayer"]=$hp/$expecteddam;
		$monsterstats["damagetoplayerestimate"]=intval(100*$expecteddam*$monsterstats["roundsforplayertokill"]/$hp)."%";
		$challengebylevel[$i+1]["equipped"]=$monsterstats["damagetoplayerestimate"]=intval(100*$expecteddam*$monsterstats["roundsforplayertokill"]/$hp)."%";
	}
	var_dump($challengebylevel);
}