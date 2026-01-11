var MJD_AT_J2000 = 51544;
var UNIX_TIME_J2000 = 946684800;
var SECONDS_IN_YEAR = 365.25 * 24 * 60 * 60;
var SECONDS_IN_DAY = 24 * 3600;

var GRAVITATIONAL_CONSTANT = 6.67408E-11 // m^3 * kg^-1 * s ^ -2
var EARTH_MASS = 5.972E24;  //kg
var EARTH_RADIUS = 6371000; //m



function deg2rad(angle){
  return angle * Math.PI / 180.00;
}

function rad2deg(angle){
  return angle * 180 / Math.PI;
}

function getDistance(p1, p2){
  return Math.sqrt((p1.x - p2.x) * (p1.x - p2.x) + (p1.y - p2.y) * (p1.y - p2.y) + (p1.z - p2.z) * (p1.z - p2.z));
}

function unixToEpoch(unixTime){
  return (unixTime - UNIX_TIME_J2000) / (100 * SECONDS_IN_YEAR);
}

function epochToUnixTime(epoch){
  return UNIX_TIME_J2000 + epoch * 100 * SECONDS_IN_YEAR;
}

function unixToString(unixTime){
  return new Date(unixTime * 1000).toString();
}

function getBrowserWidth(){
  return Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
}

function getBrowserHeight(){
  return Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
}
