//for things outside of the solar system (DSOs, stars)
logTime=false;
var offSet=new Object();
isPrintFr = false;
offSet.x=0;
offSet.y=0;
zoom=1;
unixTime = Date.now();
var canv = document.getElementById("starmap");
console.log(canv.height + " " + canv.width);
//setSize();
var ctx = canv.getContext("2d");
//ctx.imageSmoothingEnabled = false;
var lat = deg2rad(51.48);
var longitude = deg2rad(0);
setSize();
var i =1;

window.onload = function() {
	startUp();
	setSize();
	time = Date.now();
	timeInt = setInterval(writeDate, 1000);
	writeDate();
	drawStuff();
  setInterval(drawStuff, 250);
	setInterval(keepTime, 10);
	setInterval(dateInput, 5);
}

window.onresize=function() {
	setSize();
	drawStuff();
}

function setSize(){
    var testWidth = getBrowserWidth() * 0.978;
    var testHeight = getBrowserHeight() * 0.978;
    var size = Math.min(testWidth, testHeight);
    if(size > 895){
			var realSize = size * window.devicePixelRatio;
	  	canv.width = realSize;
	    canv.height = realSize;
    }
    else{
    	canv.width = 900 * window.devicePixelRatio;
      canv.height = 900 * window.devicePixelRatio;

			size = 900;
    }

    height = canv.height;
    width = canv.width;


    //document.style.width = canv.width;
    //canv.style.height = canv.height;
		var starmapDOM = document.getElementById("starmap")
    starmapDOM.style.marginLeft = -size/2+"px";
		starmapDOM.style.width = starmapDOM.style.height = size + "px";
//  ctx = canv.getContext("2d");
//  drawStuff();
}


var dataline="";

function drawStuff(){
    var t = Date.now();
	JD = getJD();
	theta = SideralTime(JD)*Math.PI/180+longitude; //theta is the local sideral time
	if(isPrintFr) ctx.fillStyle = "#ADC9DB";
	else ctx.fillStyle = "#002C4A"; //Prussian blue
	ctx.fillRect(0, 0, width, height);
	ctx.fillStyle = "#00233B";
	ctx.beginPath();
	ctx.arc(height/2, height/2, height/2-8.8, 0, 2*Math.PI);
	ctx.closePath();
	ctx.fill();
	ctx.lineWidth=1;
	if(isPrintFr) ctx.strokeStyle = "black";
	else ctx.strokeStyle = 'white';
	ctx.strokeRect(7, 7, 190, 70);
	if(isPrintFr) ctx.fillStyle = "white";
	ctx.beginPath();
	ctx.arc(height/2, height/2, height/2-10, 0, 2*Math.PI);
	ctx.closePath();
	ctx.fill();
	ctx.lineWidth = 2;
	ctx.beginPath();
	ctx.arc(height/2, height/2, height/2-8.8, 0, 2*Math.PI);
	ctx.closePath();
	ctx.stroke();
	ctx.save();
	ctx.beginPath();
	ctx.arc(height/2, height/2, height/2-10, 0, 2*Math.PI);
	ctx.closePath();
	ctx.clip();


	if(document.getElementById("milkyWay").checked && zoom == 1) drawMilkyWay();
	if (document.getElementById("lines").checked) {
		drawLines(); //draws the constellation lines
	}
	//drawBounds();
	ctx.font = "10px Arial";
	drawStars(); //draws the stars
	if (document.getElementById("messier").checked)
		drawDSO(); //draws deep space objects
	ctx.font = "12px Arial";
	if(isPrintFr) ctx.fillStyle = "#1C1C1C";
	else ctx.fillStyle="#FCB3EA";
	if (document.getElementById("names").checked)
		drawConNames(); //draws constellation names
	if(document.getElementById("planets").checked){
	 	drawPlanets();
		moonPos();
		logPos=false;
	}
	ctx.restore();
	ctx.font = "17px Arial";
	if(isPrintFr) ctx.fillStyle = "black";
	else ctx.fillStyle='white';
	ctx.fillText(date(), 14, 27);
	ctx.fillText("lat: "+Math.round(lat*180000/Math.PI)/1000+"\u00B0", 14, 47);
	ctx.fillText("long: "+Math.round(longitude*180000/Math.PI)/1000+"\u00B0", 14, 67);
	var size= 180;
//    ctx.drawImage(document.getElementById("logo"), height-size*0.86+3, height-size-3, size*0.86, size);
	if(document.getElementById("points").checked) drawCardPoints();
	ctx.lineWidth=1;

    if(logTime) console.log(Date.now()-t);
}

function drawLines(){
	var T = (JD-2451545)/36525;

	var m = 15*deg2rad(3.07496 + 0.00186*T)/3600;
	var n = 15*deg2rad(1.33621-0.00057*T)/3600;
	var n1 = deg2rad(20.0431 - 0.0085*T)/3600;

	if(zoom == 1) ctx.lineWidth=1;
	else if(zoom == 2) ctx.lineWidth=1.25;
	else ctx.lineWidth=1.5;
	if(isPrintFr) ctx.strokeStyle='#A0A0A0';
	else ctx.strokeStyle='#2D4A5E';
	ctx.beginPath()
	for (var line=0; line<conRA.length; line++) {
		var isAboveHor = new Object()
		var beginEquatorial = calcPrecession(conDE[line][0], conRA[line][0]);
		var endEquatorial = calcPrecession(conDE[line][1], conRA[line][1]);
		var begin = projectStereo(beginEquatorial.dec, beginEquatorial.ra, true);
		var end = projectStereo(endEquatorial.dec, endEquatorial.ra, true);
		isAboveHor.begin = Math.pow(begin.x-height/2, 2)+Math.pow(begin.y-height/2, 2) < Math.pow(height/2-8, 2);
		isAboveHor.end  = Math.pow(end.x-height/2, 2)+Math.pow(end.y-height/2, 2) < Math.pow(height/2-8, 2);
		if(isAboveHor.end || isAboveHor.begin){
				ctx.moveTo(begin.x, begin.y);
				ctx.lineTo(end.x, end.y);
		}
	}
	ctx.closePath();
	ctx.stroke();

}

function drawMilkyWay(){
	ctx.lineWidth = 0.25;
	if(isPrintFr) ctx.strokeStyle="#101010";
	else ctx.strokeStyle = "#67A2B5";
	ctx.beginPath();
	var isAboveHor;
	for(var i = 0; i < milkyway.length; i++){
		if(lat-milkyway[i].de<Math.PI/2){
			var posEquatorial = calcPrecession(milkyway[i].de, milkyway[i].ra)

			var pos = projectStereo(posEquatorial.dec, posEquatorial.ra, true);
			isAboveHor = Math.pow(pos.x-height/2, 2)+Math.pow(pos.y-height/2, 2) < Math.pow(height/2+8, 2);
		}
		else isAboveHor = false;
		if(isAboveHor){
			if(milkyway[i].mode === "M") ctx.moveTo(pos.x, pos.y);
			else ctx.lineTo(pos.x, pos.y);
		} else {
			ctx.stroke();
			ctx.closePath();
			ctx.beginPath();
		}
	}
}

function drawBounds(){
	ctx.lineWidth = 1.2;
	ctx.strokeStyle = "red";
	var cname=bounds[0].con;
	ctx.beginPath();
	var isAboveHor;
	for(var i = 0; i<bounds.length; i++){
	 	if(lat-bounds[i].de<Math.PI/2){
			var pos = projectStereo(bounds[i].de, bounds[i].ra, true);
			isAboveHor = Math.pow(pos.x-height/2, 2)+Math.pow(pos.y-height/2, 2) < Math.pow(height/2+8, 2);
		}
		else isAboveHor = false;

		if (isAboveHor) {
			if(bounds[i].con != cname){
				ctx.beginPath();
				ctx.moveTo(pos.x, pos.y);
			}
			else ctx.lineTo(pos.x, pos.y);
			if (typeof bounds[i+1] === 'undefined') ctx.stroke();
			else if(bounds[i].con != bounds[i+1].con) ctx.stroke();
		}
		else{
			ctx.stroke();
			ctx.beginPath();
		}
		cname = bounds[i].con;
	}
}

function drawConNames(){
	for(var con = 0; con < 87; con++){
		var nameEquatorial = calcPrecession(constellation[con].dec, constellation[con].ra);
		var name = projectStereo(nameEquatorial.dec, nameEquatorial.ra);
		if (name) {
			ctx.fillText(constellation[con].name, name.x, name.y);
		}
	}
}

function drawStars(){
	var T = (JD-2451545)/36525;
	var m = 15*deg2rad(3.07496 + 0.00186*T)/3600;
	var n = 15*deg2rad(1.33621-0.00057*T)/3600;
	var n1 = deg2rad(20.0431 - 0.0085*T)/3600;
	ctx.font = "12px Arial";
	if(isPrintFr) ctx.fillStyle = "black";
		else ctx.fillStyle='#FC5858';
	ctx.beginPath();
	for (var HR = 0; HR < stars.length; HR++){
		if (stars[HR].mag < 5.5)  {
			var starEquatorial = calcPrecession(stars[HR].dec, stars[HR].ra);
			coord=projectStereo(starEquatorial.dec, starEquatorial.ra, false);
			if (coord) {
				size = 5*Math.pow(1.45, -stars[HR].mag);
				ctx.moveTo(coord.x, coord.y);
				ctx.arc(coord.x, coord.y, size, 0, 2*Math.PI);
				//   ctx.fillRect(coord.x, coord.y, size*2, size*2);
				if (stars[HR].name.trim() !== "") {
				//	console.log(stars[HR].name+"   "+decDeg(starEquatorial.dec)+"    "+decHours(starEquatorial.ra));
					ctx.fillText(stars[HR].name, (coord.x+size+2), (coord.y+size+2));
				}
			}
		}
	}
	if(isPrintFr) ctx.fillStyle="black";
		else ctx.fillStyle ='white';
	ctx.closePath();
	ctx.fill();
}

function drawDSO(){
	ctx.strokeStyle='green';
	ctx.fillStyle='#FCC868';
	for (var mes = 0; mes < 110; mes++){
		var coordEquatorial = calcPrecession(mesDE[mes], mesRA[mes]);
		var coord=projectStereo(coordEquatorial.dec, coordEquatorial.ra, false);

		if (coord) {
			if (mesType[mes] == 'GC') gcSymbol(coord['x'], coord['y'], 4);
			if (mesType[mes] == 'GX') gxSymbol(coord['x'], coord['y'], 4, 2);
			if (mesType[mes] == 'DN') dnSymbol(coord['x'], coord['y'], 6, 6);
			if (mesType[mes] == 'PN') pnSymbol(coord['x'], coord['y'], 3);
			if (mesType[mes] == 'OC') ocSymbol(coord['x'], coord['y'], 4);
			if (mesMag[mes] < 7 || zoom>1) {
				if(isPrintFr) ctx.fillStyle="black" ;
				else ctx.fillStyle='#FCC868';
				ctx.fillText("M"+(mes+1), coord.x+7, coord.y+5);
			}
		}
	}

	//ctx.beginPath();
}

function date(){
	var dateObj = new Date(unixTime);
	var month = dateObj.getMonth() + 1;
	var day = dateObj.getDate();
	var year = dateObj.getFullYear();
	var hour = dateObj.getHours();
	var minute = dateObj.getMinutes();
	var second = dateObj.getSeconds();
	if (day<10) day="0"+day;
	if (month<10) month="0"+month;
	if (hour<10) hour="0"+hour;
	if (minute<10) minute="0"+minute;
	if (second<10) second="0"+second;
	var newdate = newdate = year + "/" + month + "/" + day+" "+hour+":"+minute+":"+second;
	return newdate;
}

function SideralTime(JD){
	//Returns sideral time in degrees!!!!!
	var T = (JD-2451545.0)/36525;
	var theta0 = 280.46061837+360.98564736629*(JD-2451545.0)+0.000387933*Math.pow(T, 2)-Math.pow(T, 3)/38710000;
	while(theta0 > 360) theta0-= 360;
	return theta0;
}


function getJD(){
	//returns Julian Days
    var dateObj = new Date(unixTime);
	var day = dateObj.getUTCDate()+dateObj.getUTCHours()/24.00+dateObj.getUTCMinutes()/1440.00+dateObj.getUTCSeconds()/86400;
	var month = dateObj.getUTCMonth() + 1;
    var year = dateObj.getUTCFullYear();
    if(month == 1 && month == 2){
		year--;
		month+= 12;
	}
   var a = Math.floor(year/100);
   var b = 2-a+Math.floor(a/4);
   var JD = Math.floor(365.25*(year+4716))+Math.floor(30.6001*(month+1))+day+b-1524.5;
   return  JD;
}

function projectStereo(dec, ra, returnifUnderHorizon) {
	//Transformation to horizontal coordonates
	if (lat-dec<Math.PI/2 || returnifUnderHorizon) {
		var H  = theta-ra; //H = hour angle
		var alt = (Math.asin(Math.sin(lat)*Math.sin(dec)+Math.cos(lat)*Math.cos(dec)*Math.cos(H)));
			if (alt>0 || returnifUnderHorizon) {
				var azTan = Math.cos(H)*Math.sin(lat) - Math.tan(dec)*Math.cos(lat);
			    var az = Math.atan2(Math.sin(H), azTan);
				var stereo=new Object();
				//Projects coordonates on cartesian plane
				var r = 1/Math.tan((Math.PI/2+alt)/2);
				stereo.x = offSet.x+height/2+zoom*(height/2-10)*r*Math.cos(Math.PI/2-az);
				stereo.y = offSet.y+height/2+zoom*(height/2-10)*r*Math.sin(Math.PI/2-az);
				if(Math.pow(height/2-stereo.x, 2)+Math.pow(height/2-stereo.y, 2)< Math.pow(height/2-8, 2) || returnifUnderHorizon)
				return stereo;
					else return false;
			}
		else return false;
	}
	else return false;
}

function drawCardPoints(){
	if(zoom == 1){
		ctx.fillStyle='red';
		ctx.fillText("N", height/2, 27);
		ctx.fillText("S", height/2, height-15);
		ctx.fillText("E", 15, height/2);
		ctx.fillText("W", height-30, height/2);
	}
}

function calcPrecession(dec, ra){
	var T = (JD - 2451545)/36525;
	//T = 0.288670500;
	var eqC = new Object();
	var xi = deg2rad(2306.2181*T + 0.30188*Math.pow(T, 2) + 0.017998*Math.pow(T, 3))/3600;
	var zeta = deg2rad(2306.2181*T + 1.09468*Math.pow(T, 2) + 0.018203*Math.pow(T, 3))/3600;
	var theta = deg2rad(2004.3109*T - 0.42665*Math.pow(T, 2) - 0.041833*Math.pow(T, 3))/3600;
	var A = Math.cos(dec)*Math.sin(ra+xi);
	var B = Math.cos(theta)*Math.cos(dec)*Math.cos(ra + xi) - Math.sin(theta)*Math.sin(dec);
	var C = Math.sin(theta)*Math.cos(dec)*Math.cos(ra + xi) + Math.cos(theta)*Math.sin(dec);
	eqC.ra = Math.atan2(A, B) + zeta;
	eqC.dec = Math.asin(C);
	//console.log(decHours(eqC.ra)+"   "+decDeg(eqC.dec));
	return eqC;
}


function downloadImage(){
	var img = document.createElement("img");
	img.src = canv.toDataURL("image/png");
	var url = img.src.replace(/^data:image\/[^;]/, 'data:application/octet-stream');
	download(url, "starmap"+date()+".png", "image/png");
}

function keepTime(){
	unixTime+=(Date.now()-time);
	time = Date.now();
}
