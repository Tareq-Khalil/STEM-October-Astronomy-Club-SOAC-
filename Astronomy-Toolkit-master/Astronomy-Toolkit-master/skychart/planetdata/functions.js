function Rect2Spheric(eq){
    eqSpheric = new Object();
    eqSpheric.ra = Math.atan2(eq.y, eq.x);
    eqSpheric.de = Math.atan2(eq.z, Math.sqrt(eq.x*eq.x+eq.y*eq.y));
    return eqSpheric;
}

function calcCoord(c, tau){
    var coord = 0;
    for(var i =0; i< c.length; i++){
        coord+=c[i]*Math.pow(tau, i);
    }
    return coord;
}

function eqCoord(sun, coord, epsilon){
	//sun - the sun's ecliptical rectangular coordinates, coord - the planets coordinates, epsilon - the obliquity of the ecliptic
    coord.x+=sun.x;
    coord.y+=sun.y;
    coord.z+=sun.z;
    var planetEqRect = rotateCoordinateSystem(coord, epsilon);
    var eqCoord = Rect2Spheric(planetEqRect);
    return eqCoord;
}

