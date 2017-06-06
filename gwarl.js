var waiting=false;
var showanimations=true;
var interrupt=false;
$(document).ready(function(){
	var mapsize=612;
	var radius=8;
	var tilesize=Math.floor(mapsize/(2*radius+1));
	var playerpos;
	var input;
	var lastoutputs;
	var newtiles=false;
	var casting=false;
	var opentab="items";
	function refreshgame(outputs,lastoutput){
		//main update sequence called from animate
		var ctx = document.getElementById("map").getContext("2d");
		outputs=animationcycle(outputs,lastoutput,ctx,mapsize,radius,opentab);
		if (outputs.clear&&interrupt==false){
			ctx.font = "12px";
			ctx.fillStyle = "#C0C0C0";
			ctx.fillText("Waiting for server",0,10);
			$.post("playmove.php",{command:"moveto",modifier:JSON.stringify(input)},function(data,status){
				var output=parsedata(data);
				lastoutputs=refreshgame(output,outputs);
			});
		}
		return outputs;
	}
	$(this).keydown(function(e){
		e.preventDefault();
		if (!waiting){
			var keycode = e.which;        
			var move=keypress(keycode);
			if (move!=false) {
				waiting=true;
				var ctx = document.getElementById("map").getContext("2d");
				ctx.font = "12px";
				ctx.fillStyle = "#C0C0C0";
				ctx.fillText("Waiting for server",0,10);
				$.post("playmove.php",move,function(data,status){
					var output=parsedata(data);
					lastoutputs=refreshgame(output,lastoutputs);
				});
			}
		}
	});
	$("#map").click(function(event){
		event.preventDefault();
		var mousex=event.clientX-this.offsetLeft;
		var mousey=event.clientY-this.offsetTop;
		inputx=(mousex-(mousex%tilesize))/tilesize;
		inputy=(mousey-(mousey%tilesize))/tilesize;
		inputx+=lastoutputs.stats.position.x-radius;
		inputy+=lastoutputs.stats.position.y-radius;
		if (event.shiftKey){
			input={"spell":casting,"x":inputx,"y":inputy};
		} else {
			input={"x":inputx,"y":inputy};
		}
		if (!waiting) {
			waiting=true;
			var ctx = document.getElementById("map").getContext("2d");
			ctx.font = "12px";
			ctx.fillStyle = "#C0C0C0";
			ctx.fillText("Waiting for server",0,10);
			if (event.shiftKey){
				$.post("playmove.php",{command:"cast",modifier:JSON.stringify(input)},function(data,status){
					var outputs=parsedata(data);
					lastoutputs=refreshgame(outputs,lastoutputs);
				});
			} else {
				$.post("playmove.php",{command:"moveto",modifier:JSON.stringify(input)},function(data,status){
					var outputs=parsedata(data);
					lastoutputs=refreshgame(outputs,lastoutputs);
				});
			}
		}
	});
	$("#map").mousemove(function(event){
		var mousex=event.clientX-this.offsetLeft;
		var mousey=event.clientY-this.offsetTop;
		var focus=getsquarecontents(
				lastoutputs.terrain,
				lastoutputs.decals,
				lastoutputs.items,
				lastoutputs.creatures,
				{
					"x":(mousex-(mousex%tilesize))/tilesize,
					"y":(mousey-(mousey%tilesize))/tilesize
				},
				radius
				);
		var thingtype="none144";
		var infctx=document.getElementById("controls").getContext("2d");
		var infimg=document.getElementById(thingtype);
		infctx.drawImage(infimg,0,180);
		for (thing in focus){
			if (thing==2&&focus[thing]!=false) thingtype=focus[thing];
			if (thing==3&&focus[thing]!=false) thingtype="creature";
		}
		if (thingtype=="creature"){
			drawportrait(focus[3].type,focus[3].equipment);
		}
	});
	$("#newlevel").click(function(){
		$("#menu").css("display","none");
		$("#map").css("display","block");
		if (!waiting) {
			waiting=true;
			var ctx = document.getElementById("map").getContext("2d");
			ctx.font = "12px";
			ctx.fillStyle = "#C0C0C0";
			ctx.fillText("Waiting for server",0,10);
			$.post("levelgen.php",{dud:0},function(data,status){
				//$("#player").attr("src","getplayerimg.php?tilesize=108");
				var outputs=parsedata(data);
				lastoutputs=refreshgame(outputs,lastoutputs);
				$("#movelog").html("Player has entered the dungeon</br>");
			});
		}
	});
	$("#controls").mousemove(function(event){
		event.preventDefault();
		var mousex=event.clientX-this.offsetLeft;
		var mousey=event.clientY-this.offsetTop;
		var ctx=document.getElementById("controls").getContext("2d");
		var tilename="none";
		if (typeof(lastoutputs)!='undefined'){
			if (mousex>62&&mousey>324){
				if (opentab=="items"){
					if (mousex>136){
						if (mousey<372){
							itemchoice=Math.floor((mousex-136)/36);
							if (lastoutputs.stats.equipment[itemchoice]){
								tilename=lastoutputs.stats.equipment[itemchoice].type;
							}
						} else if (mousey<466){
							itemchoice=Math.floor((mousex-136)/36)
									+7*Math.floor((mousey-372)/50);
							if (typeof(lastoutputs.stats.inventory[itemchoice])!='undefined'){
								tilename=lastoutputs.stats.inventory[itemchoice].type;
							}
						} else {
							itemchoice=Math.floor((mousex-136)/36);
							if (typeof(lastoutputs.stats.onground[itemchoice])!='undefined'){
								if (lastoutputs.stats.onground[itemchoice].itemclass!="ring"&&lastoutputs.stats.onground[itemchoice].itemclass!="potion"){
									tilename=lastoutputs.stats.onground[itemchoice].name;
								} else if (lastoutputs.stats.onground[itemchoice].category=="corpse"){
									tilename=lastoutputs.stats.onground[itemchoice].name+"corpse";
								} else {
									tilename=lastoutputs.stats.onground[itemchoice].itemclass;
								}
							}
						}
					}
				}
			}
		}
		var infimg=document.getElementById("none144");
		ctx.drawImage(infimg,0,180);
		infimg=document.getElementById(tilename+"144");
		ctx.drawImage(infimg,0,180);
	});
	$("#controls").click(function(event){
		event.preventDefault();
		$("#debug").html("clicked ");
		var mousex=event.clientX-this.offsetLeft;
		var mousey=event.clientY-this.offsetTop;
		var fback;
		if (mousey<180) {
			if (mousex<180){
				inputx=(mousex-(mousex%3))/3;
				inputy=(mousey-(mousey%3))/3;
				input={"x":inputx,"y":inputy};
				if (!waiting) {
					waiting=true;
					var ctx = document.getElementById("map").getContext("2d");
					ctx.font = "12px";
					ctx.fillStyle = "#C0C0C0";
					ctx.fillText("Waiting for server",0,10);
					$.post("playmove.php",{command:"moveto",modifier:JSON.stringify(input)},function(data,status){
						
						var outputs=parsedata(data);
						lastoutputs=refreshgame(outputs,lastoutputs);
					});
				}
				fback="minimap";
			} else {
				if (mousey<64){
					fback="bars";
				} else if (mousey<148){
					fback="stats";
				} else {
					if (mousex<232){
						fback="explore";
						if (!waiting) {
							input="explore";
							waiting=true;
							var ctx = document.getElementById("map").getContext("2d");
							ctx.font = "12px";
							ctx.fillStyle = "#C0C0C0";
							ctx.fillText("Waiting for server",0,10);
							$.post("playmove.php",{command:"moveto",modifier:JSON.stringify(input)},function(data,status){
								
								var outputs=parsedata(data);
								lastoutputs=refreshgame(outputs,lastoutputs);
							});
						}
					} else if (mousex<284){
						fback="fight";
						var target=getnexttarget(lastoutputs.creatures,radius);
						if (!waiting&&target!=false) {
							input=target;
							waiting=true;
							var ctx = document.getElementById("map").getContext("2d");
							ctx.font = "12px";
							ctx.fillStyle = "#C0C0C0";
							ctx.fillText("Waiting for server",0,10);
							$.post("playmove.php",{command:"moveto",modifier:JSON.stringify(input)},function(data,status){
								
								var outputs=parsedata(data);
								lastoutputs=refreshgame(outputs,lastoutputs);
							});
						} else {
							lastoutputs.movelog=["No creature in view"];
							updategame(lastoutputs,mapsize,radius);
						}
					} else if (mousex<336){
						fback="rest";
						var input = "wait";
						if (!waiting) {
							waiting=true;
							var ctx = document.getElementById("map").getContext("2d");
							ctx.font = "12px";
							ctx.fillStyle = "#C0C0C0";
							ctx.fillText("Waiting for server",0,10);
							$.post("playmove.php",{command:input,modifier:100},function(data,status){
								
								var outputs=parsedata(data);
								lastoutputs=refreshgame(outputs,lastoutputs);
							});
						}
					} else {
						fback="cast";
						var target=getnexttarget(lastoutputs.creatures,radius);
						if (!waiting&&target!=false) {
							input={"spell":casting,"x":target.x,"y":target.y};
							waiting=true;
							var ctx = document.getElementById("map").getContext("2d");
							ctx.font = "12px";
							ctx.fillStyle = "#C0C0C0";
							ctx.fillText("Waiting for server",0,10);
							$.post("playmove.php",{command:"cast",modifier:JSON.stringify(input)},function(data,status){
								
								var outputs=parsedata(data);
								lastoutputs=refreshgame(outputs,lastoutputs);
							});
						} else {
							lastoutputs.movelog=[];
							updategame(lastoutputs,mapsize,radius);
						}
					}
				}
			}
		} else if (mousey<324) {
			fback="infobox";
		} else {
			if (mousex<62){
				var ctx = document.getElementById("controls").getContext("2d");
				if (mousey<362){
					fback="items";
					opentab="items";
				} else if (mousey<400){
					fback="magic";
					opentab="magic";
				} else if (mousey<438){
					fback="skills";
					opentab="skills";
				} else if (mousey<476){
					fback="options";
					opentab="options";
				} else {
					fback="blank";
				}
				var tabimg = document.getElementById("ui"+opentab);
				ctx.drawImage(tabimg,62,324,326,196);
			} else {
				fback="menuarea";
				if (opentab=="items"){
					if (mousex>136){
						var input;
						var itemchoice;
						if (mousey<370){
							input = "remove";
							var eqlist = ["weapon","ring","cloak","armour","helmet","shield"];
							itemchoice=Math.floor((mousex-136)/36);
							itemchoice = JSON.stringify(eqlist[itemchoice]);
							fback="equip "+itemchoice;
						} else if (mousey<464){
							if (event.shiftKey) {
								input = "drop";
							} else {
								input = "use";
							}
							itemchoice=Math.floor((mousex-136)/36)
									+7*Math.floor((mousey-370)/50);
							fback="inventory "+itemchoice;
						} else {
							input = "pickup";
							itemchoice=Math.floor((mousex-136)/36);
							fback="ground "+itemchoice;
						}
						if (!waiting) {
							waiting=true;
							var ctx = document.getElementById("map").getContext("2d");
							ctx.font = "12px";
							ctx.fillStyle = "#C0C0C0";
							ctx.fillText("Waiting for server",0,10);
							$.post("playmove.php",{command:input,modifier:itemchoice},function(data,status){
								var outputs=parsedata(data);
								lastoutputs=refreshgame(outputs,lastoutputs);
							});	
						}
					}
				} else if (opentab=="options"){
					if (mousex<190&&mousey<386){
						var input = "suicide";
						if (!waiting) {
							waiting=true;
							var ctx = document.getElementById("map").getContext("2d");
							ctx.font = "12px";
							ctx.fillStyle = "#C0C0C0";
							ctx.fillText("Waiting for server",0,10);
							$.post("playmove.php",{command:input,modifier:false},function(data,status){
								
								var outputs=parsedata(data);
								lastoutputs=refreshgame(outputs,lastoutputs);
							});
						}
					}
				};
			}
		}
		$("#debug").html(fback);
	});
	
	//actual code to be executed upon page loading
	$("#loadingscreen").css("display","none");
	$("#menu").css("display","block");
	drawUIskin(opentab);
});