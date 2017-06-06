<?php
include 'mapclass.php';
include 'dungeon.php';
include 'dblogin.php';
class Pathmap{
	public $distances;
	public function __construct($dungeonspace,$goal){
		foreach ($dungeonspace->terrain as $index=>$type){
			$this->distances[$index]=-1;
		}
		$this->distances[$goal]=0;
		$edge=$dungeonspace->getnearindices($goal,1);
		$step=1;
		while (count($edge)>0){
			foreach ($edge as $edgeindex=>$tileindex){
				if ($dungeonspace->terrain[$tileindex]==1||$this->distances[$tileindex]>=0){
					unset($edge[$edgeindex]);
				} else {
					$this->distances[$tileindex]=$step;
				}
			}
			foreach ($edge as $edgeindex=>$tileindex){
				$neartiles=$dungeonspace->getnearindices($tileindex,1);
				foreach ($neartiles as $tileindex){
					if ($dungeonspace->terrain[$tileindex]!=1&&
						$this->distances[$tileindex]<0&&
						!in_array($tileindex,$edge)){
						$edge[]=$tileindex;
					}
				}
			}
			$step++;
		}
	}
	public function drawpicture(){
		$picture=imagecreatetruecolor(300,300);
		$maxdistance=max($this->distances);
		for ($i=0;$i<3600;$i++){
			$tilexy = array(
				"x"=>$i%60,
				"y"=>floor($i/60),
				);
			$imagex = 5*$tilexy["x"];
			$imagey = 5*$tilexy["y"];
			if ($this->distances[$i]<0){
				$colour=imagecolorallocate($picture,0,0,0);
			} else {
				$colour=imagecolorallocate($picture,intval(255*(1-$this->distances[$i]/$maxdistance)),0,intval(255*$this->distances[$i]/$maxdistance));
			}
			imagefilledrectangle($picture,$imagex,$imagey,$imagex+4,$imagey+4,$colour);
		}
		header('Content-Type: image/png');
		imagepng($picture);
		imagedestroy($picture);
	}
}
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
$path=$dungeon->buildpath(260);
$reversepath=$dungeon->reversepath($path);
var_dump($path);
var_dump($reversepath);

