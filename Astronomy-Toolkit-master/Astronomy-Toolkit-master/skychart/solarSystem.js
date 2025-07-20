//for all the things that have to do with the solar system (planets, asteroids, comets, sun, moons)
var logPos = false;
/*function moonInfo(){
    month = 29.530588853;
    moonAge = (JD - 2457043.052083);
    while (moonAge<month) moonAge +=month;
    while (moonAge>month) moonAge -=month;
   // moonAge = 28.5;
    var phaseName;
    phase = moonAge/month;
    ctx.lineWidth=1;
    if(isPrintFr) ctx.strokeStyle='black';
    else ctx.strokeStyle='white';
    var shadow;
    ctx.strokeRect(height-201, 7, 190, 70);
    if(phase<0.25) shadow = (1-phase -0.75)*160;
    else if (phase<0.5) shadow = -(phase-0.25)*160
    else if (phase<0.75) shadow = (1-phase-0.25)*160;
    else if(phase<1) shadow = -(phase-0.75)*160
    ctx.lineWidth=0;
    ctx.fillStyle='black';
    ctx.beginPath();
    ctx.arc(height-167, 42, 30, 0, 2*Math.PI);
    ctx.fill();
    ctx.fillStyle='gray';
    ctx.beginPath();
    if(phase<0.5) ctx.arc(height-167, 42, 30, -Math.PI/2, -3*Math.PI/2);
    else ctx.arc(height-167, 42, 30, Math.PI/2, 3*Math.PI/2);
    ctx.fill();
    if(phase<0.25) ctx.fillStyle='black';
    else if(phase<0.75) ctx.fillStyle='gray';
    else if(phase<1) ctx.fillStyle='black';
    ctx.beginPath();
    ctx.moveTo(height-167, 42+30);
    ctx.bezierCurveTo(height-167+shadow, 42+30, height-167+shadow, 42-30, height-167, 42-30);
    ctx.fill();
    if(moonAge<1) phaseName = "New Moon";
    else if (moonAge<6.375) phaseName = "Waxing Crescent";
    else if (moonAge<8.375) phaseName = "First Quarter";
    else if (moonAge<13.75) phaseName = "Waxing Gibbous";
    else if (moonAge<15.75) phaseName = "Full Moon";
    else if(moonAge<21.125) phaseName = "Waning Gibbous";
    else if (moonAge<23.125) phaseName = "Second Quarter";
    else if(moonAge<28.5) phaseName = "Waning Crescent"
    else if(moonAge<30) phaseName="New Moon";
    if(isPrintFr) ctx.fillStyle = "black";
    else ctx.fillStyle='white';
    ctx.font = "17px Arial";
    ctx.fillText(phaseName, height-141, 25);
    ctx.fillText("Age: "+Math.floor(moonAge*100)/100+" days", height-134, 69);

}*/

function normalise(angle){
    angle = angle - 2*Math.PI*Math.floor(angle/(2*Math.PI));
    return angle;
}

function moonPos(){
//   JD = 2448724.5;

    var T = (JD-2451545)/36525;
    e = deg2rad(23.4392916666666667-0.0130041666666667*T-0.0000001666666667*T*T+0.0000005027777778*T*T*T);
    var L1 = normalisedeg(218.3164477 + 481267.88123421*T - 0.0015786*T*T + Math.pow(T, 3)/538841 - Math.pow(T, 4)/65194000);
    var D = normalisedeg(297.8501921 + 445267.1114034*T - 0.0018819*T*T + Math.pow(T, 3)/545868 - Math.pow(T, 4)/113065000);
    var M = normalisedeg(357.5291092 + 35999.0502909*T - 0.0001536*T*T + Math.pow(T, 3)/24490000);
    var M1 = normalisedeg(134.9633964 + 477198.8675055*T + 0.0087414*T*T + Math.pow(T, 3)/69699-Math.pow(T, 4)/14712000);
    var F = normalisedeg(93.272095 + 483202.0175233*T - 0.0036539*T*T - Math.pow(T, 3)/3526000 + Math.pow(T, 4)/863310000);
    var A1 = normalisedeg((119.75+131.849*T));
    var A2 = normalisedeg((53.09 + 479264.290*T));
    var A3 = normalisedeg((313.45 + 481266.484*T));
    var E = normalisedeg(1-0.002516*T - 0.0000074*T*T);
    var sigma = calcMoon(D, M, M1, F, E);
    sigma.l+=3958*Math.sin(deg2rad(A1))+1962*Math.sin(deg2rad(L1-F))+318*Math.sin(deg2rad(A2));
    sigma.b+=-2235*Math.sin(deg2rad(L1))+382*Math.sin(deg2rad(A3))+175*Math.sin(deg2rad(A1-F))+175*Math.sin(deg2rad(A1+F))+
        127*Math.sin(deg2rad(L1-M1))-115*Math.sin(deg2rad(L1+M1));
    eclipticCoord = new Object();
    eclipticCoord.lambda = deg2rad(L1 + sigma.l/1000000);
    eclipticCoord.beta  = deg2rad(sigma.b/1000000);
    eclipticCoord.delta = 385000.56+sigma.r/1000;
    parAngle = 6378.14/eclipticCoord.delta;
    eq = ecliptic2equatorial(eclipticCoord, e);
    var HA = theta-eq.ra;
    var glat = lat - deg2rad(0.1924*Math.sin(2*lat));
    var rho = 0.99833 + 0.00167*Math.cos(2*lat);
    var g = Math.atan2(Math.tan(glat), Math.cos(HA));
    eq.ra -= parAngle*Math.cos(glat)*Math.sin(HA)/Math.cos(eq.de);
    eq.de -= parAngle*Math.sin(glat)*Math.sin(g-eq.de)/Math.sin(g);
    drawMoon();
    if(logPos) console.log("Moon   "+decHours(eq.ra)+"   "+decDeg(eq.de));
    //logPos = false;
}

function ecliptic2equatorial(ecl, eps) {
    eq = new Object();
    eq.ra = Math.atan2(Math.sin(ecl.lambda)*Math.cos(eps)-Math.tan(ecl.beta)*Math.sin(eps), Math.cos(ecl.lambda));
    eq.de = Math.asin(Math.sin(ecl.beta)*Math.cos(eps)+Math.cos(ecl.beta)*Math.sin(eps)*Math.sin(ecl.lambda));
    return eq;
}

function drawMoon() {
    ctx.fillStyle="gray";
    var moon = projectStereo(eq.de, eq.ra, false);
    ctx.beginPath();
    ctx.arc(moon.x, moon.y, 15, 0, 2*Math.PI);
    ctx.closePath();
    ctx.fill();

    ctx.font = "700 12px Arial";
    ctx.fillText("Moon", moon.x+15, moon.y+15);
}

function normalisedeg(angle){
    angle = angle - 360*Math.floor(angle/360);
    return angle;
}

//rotation of the coordinate system around the x axis
function rotateCoordinateSystem(oldCoord, angle){
    newCoord = new Object();
    newCoord.x = oldCoord.x;
    newCoord.y = oldCoord.y*Math.cos(angle) - oldCoord.z*Math.sin(angle);
    newCoord.z = oldCoord.y*Math.sin(angle) + oldCoord.z*Math.cos(angle);
    return newCoord;
}

function deg2rad(angle){
    return angle*Math.PI/180;
}

function rad2deg(angle){
    return angle*180/Math.PI;
}

/*function normalise(deg){
    while(deg<0 || deg>2*Math.PI){
        if (deg<0) deg+=2*Math.PI;
        else deg-=2*Math.PI;
    }
    return deg;
}*/

function Rect2Spheric(eq){
    eqSpheric = new Object();
    eqSpheric.ra = Math.atan2(eq.y, eq.x);
    eqSpheric.de = Math.atan2(eq.z, Math.sqrt(eq.x*eq.x+eq.y*eq.y));
    return eqSpheric;
}

function drawPlanets(){
//    JD = 2451545;
    ctx.beginPath();
    T = (JD-2451545)/365250;
    e = deg2rad(23.4392916666666667-0.0130041666666667*T-0.0000001666666667*Math.pow(T, 2)+0.0000005027777778*Math.pow(T, 3));
    earthPos();
    mercuryPos();
    venusPos();
    marsPos();
    jupiterPos();
    saturnPos();
    uranusPos();
    neptunePos();
    ctx.fillStyle='#FFFFFF';
    ctx.closePath();
    ctx.fill();
}

function mercuryPos(){
    var name = "Mercury";
    var mag = -1.7;
    time = Date.now();
    var mercury = calcmercury(T);
    var helio = new Object();
    helio.x = calcCoord(mercury.X, T);
    helio.y = calcCoord(mercury.Y, T);
    helio.z = calcCoord(mercury.Z, T);
    eqCoord(helio, e, name, mag);
//    console.log(Date.now()-time);
}

function venusPos(){
    var name = "Venus";
    var mag = -3.38;
    var venus = calcvenus(T);
    var helio = new Object();
    helio.x = calcCoord(venus.X, T);
    helio.y = calcCoord(venus.Y, T);
    helio.z = calcCoord(venus.Z, T);
    eqCoord(helio, e, name, mag);
}

function marsPos(){
    var name = "Mars";
    var mag = -1.26;
    var mars = calcmars(T);
    var helio = new Object();
    helio.x = calcCoord(mars.X, T);
    helio.y = calcCoord(mars.Y, T);
    helio.z = calcCoord(mars.Z, T);
    eqCoord(helio, e, name, mag);
}

function jupiterPos(){
    var name = "Jupiter";
    var mag = -2.21;
    var jupiter = calcjupiter(T);
    var helio = new Object();
    helio.x = calcCoord(jupiter.X, T);
    helio.y = calcCoord(jupiter.Y, T);
    helio.z = calcCoord(jupiter.Z, T);
    eqCoord(helio, e, name, mag);

}

function uranusPos(){
    var name = "Uranus";
    var mag = 6.15;
    var uranus = calcuranus(T);
    var helio = new Object();
    helio.x = calcCoord(uranus.X, T);
    helio.y = calcCoord(uranus.Y, T);
    helio.z = calcCoord(uranus.Z, T);
    eqCoord(helio, e, name, mag);

}

function earthPos(){
    time = Date.now();
    var earth = calcearth(T);
    var helio = new Object();
    helio.x = calcCoord(earth.X, T);
    helio.y = calcCoord(earth.Y, T);
    helio.z = calcCoord(earth.Z, T);
    sunEclipticRect = new Object();
    sunEclipticRect.x = -helio.x;
    sunEclipticRect.y = -helio.y;
    sunEclipticRect.z = -helio.z;
    var EqRect = rotateCoordinateSystem(sunEclipticRect, e);
    var eqCoord = Rect2Spheric(EqRect);
    var sun = projectStereo(eqCoord.de, eqCoord.ra, false);
    ctx.drawImage(document.getElementById("rose"), sun.x-20, sun.y-20, 40 ,40);
    if(logPos) console.log("Sun   "+decHours(eqCoord.ra)+"   "+decDeg(eqCoord.de));
}

function neptunePos(){
    var name = "Neptune";
    var mag = 7.75;
    var neptune  = calcneptune(T);
    var helio = new Object();
    helio.x = calcCoord(neptune.X, T);
    helio.y = calcCoord(neptune.Y, T);
    helio.z = calcCoord(neptune.Z, T);
    eqCoord(helio, e, name, mag);
}

function saturnPos(){
    var name = "Saturn";
    var mag = 0.4;
    var saturn = calcsaturn(T);
    var helio = new Object();
    helio.x = calcCoord(saturn.X, T);
    helio.y = calcCoord(saturn.Y, T);
    helio.z = calcCoord(saturn.Z, T);
    eqCoord(helio, e, name, mag);
}

function eqCoord(coord, epsilon, n, mag){
    coord.x+=sunEclipticRect.x;
    coord.y+=sunEclipticRect.y;
    coord.z+=sunEclipticRect.z;
    var planetEqRect = rotateCoordinateSystem(coord, epsilon);
    var eqCoord = Rect2Spheric(planetEqRect);
    var planet = projectStereo(eqCoord.de, eqCoord.ra, false);
    size = 4*Math.pow(1.2, -mag);
    ctx.moveTo(planet.x, planet.y);
    ctx.arc(planet.x, planet.y, size, 0, 2*Math.PI);
    ctx.font = "200 11px Arial";
    if(isPrintFr) ctx.fillStyle = "black";
    else ctx.fillStyle='#FF8400';
    ctx.fillText(n, planet.x+size+1.5, planet.y+size+1.5);
    if(logPos) console.log(n+"   "+decHours(eqCoord.ra)+"   "+decDeg(eqCoord.de));
}

function calcCoord(c, tau){
    var coord = 0;
    for(var i =0; i< c.length; i++){
        coord+=c[i]*Math.pow(tau, i);
    }
    return coord;
}

function Spheric2Rect(spheric, d){
    rect = new Object();
    rect.x = d*Math.cos(spheric.lon)*Math.cos(spheric.lat);
    rect.y = d*Math.sin(spheric.lon)*Math.cos(spheric.lat);
    rect.z = d*Math.sin(spheric.lat);
    return rect;
}
/*
function setSize(){
    var oldSize = Math.min(canv.width, canv.height);
	var testWidth = getBrowserWidth() * 0.978;
    var testHeight = getBrowserHeight() * 0.978;
    var size = Math.min(testWidth, testHeight);
    if(size > 895 && size < 1030){
        canv.width = size;
        canv.height = size;
   }
    else{
        canv.width=900 ;
        canv.height=900 ;
    }
	
    height = canv.height;
    width = canv.width;
	ctx.scale(width / oldSize, height / oldHeight);	
	//document.style.width = canv.width;
	//canv.style.height = canv.height;
    document.getElementById("starmap").style.marginLeft = -height/2+"px";
//	ctx = canv.getContext("2d");
//	drawStuff();	
}
*/
function getBrowserWidth(){
  return Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
}

function getBrowserHeight(){
  return Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
}

function decHours(angle) {
    var hd = rad2deg(normalise(angle))/15;
    var h = hd | 0;
    var m = (Math.abs(hd-h)*60) | 0;
    var s = (((Math.abs(hd-h)*3600-m*60)*1000) | 0) /1000;
    var str = h+"h "+m+"m "+s+"s";
    return str;
}

function decDeg(angle) {
    var hd = rad2deg(angle);
    var h = parseInt(hd);
    var m = parseInt(Math.abs(hd-h)*60);
    var s = parseInt((Math.abs(hd-h)*3600-m*60)*1000) /1000;
    if(angle<0 && h>=0) var str = "-"+h+"d "+m+"m "+s+"s";
        else var str = h+"d "+m+"m "+s+"s";
    return str;

}
