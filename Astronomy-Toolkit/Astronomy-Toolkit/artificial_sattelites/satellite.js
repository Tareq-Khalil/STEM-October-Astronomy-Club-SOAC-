var ke= Math.sqrt(GRAVITATIONAL_CONSTANT * EARTH_MASS);
var J1 = 0.0010826269, J2 = -0.0000025323, J3 = -0.0000016204;

function Satellite(name, line1, line2){
  //console.log(name);
  this.name = name;
  this.satNumber = line1.substring(2, 7);
  var epochYear = parseInt(line1.substring(18, 20));
  var epochDays = parseFloat(line1.substring(20, 32));
  if(epochYear > 52){
    epochYear += 1900;
  }
  else{
    epochYear += 2000;
  }

  this.epochUnix = new Date(epochYear + "-01-01" + "T00:00:00Z").getTime() / 1000.0 + (epochDays - 1.0) * SECONDS_IN_DAY;

  this.meanMotionPrime =  2 * Math.PI * parseFloat(line1.substring(33, 43)) / Math.pow(SECONDS_IN_DAY, 2);
  this.meanMotionSecond = 0;
  //this.meanMotionSecond = parseFloat(line1.substring(44, 52));
  //this.dragTerm = parseFloat(line1.substring(53, 61));
  this.inclination = deg2rad(parseFloat(line2.substring(8, 16)));
  this.longitudeOfNode = deg2rad(parseFloat(line2.substring(17, 25)));
  this.eccentricity = parseFloat(line2.substring(26, 33)) / 10000000;
  this.argOfPericenter = deg2rad(parseFloat(line2.substring(34, 42)));
  this.meanAnomaly = deg2rad(parseFloat(line2.substring(43, 51)));
  this.meanMotion = 2 * Math.PI * parseFloat(line2.substring(52, 63)) / SECONDS_IN_DAY; //rad/s
  this.semiMajorAxis = Math.pow(ke / this.meanMotion, 2.0 / 3.0);
/*
  //console.log(this.eccentricity)
  var cosIncl = Math.cos(this.inclination);

  this.delta1 = 3/4 * J2 * EARTH_RADIUS * EARTH_RADIUS / (this.semiMajorAxis * this.semiMajorAxis) *
    (3 * cosIncl * cosIncl - 1)/Math.pow(1 - this.eccentricity * this.eccentricity, 3.0/2.0);

  this.a0 = this.semiMajorAxis * (1 + this.delta1 * (-1 / 3 + this.delta1 * (-1 - 134/81 * this.delta1)));
  this.p0 = this.a0 * (1 - this.eccentricity * this.eccentricity);
  this.q0 = this.a0 * (1 - this.eccentricity);
  this.L0 = this.meanAnomaly + this.argOfPericenter + this.longitudeOfNode;
  var t = J2 * Math.pow(EARTH_RADIUS / this.p0, 2) * this.meanMotion;
  this.longitudeOfNodePrime = - 3 / 2 * t * cosIncl;
  this.argOfPericenterPrime = 3 / 4  * t * ( 5 * cosIncl * cosIncl - 1);*/
}

Satellite.prototype.getPositionAtEpoch = function(epochNow){
  var deltaT = epochNow - this.epochUnix;
  //console.log(deltaT / SECONDS_IN_DAY);
  var semiMajorAxisNow = this.semiMajorAxis * Math.pow(this.meanMotion / (this.meanMotion + 2 *
     this.meanMotionPrime * deltaT+ 3 * this.meanMotionSecond * deltaT * deltaT), 2.0 / 3.0);
  var eccentricityNow = 1e-6;
  if(semiMajorAxisNow > this.q0){
    eccentricityNow = 1 - this.q0 / semiMajorAxisNow;
  }

  var p = semiMajorAxisNow * (1 - eccentricityNow * eccentricityNow);
  var longitudeOfNodeNow = this.longitudeOfNode + this.longitudeOfNodePrime * deltaT;
  var argOfPericenterNow = this.argOfPericenter + this.argOfPericenterPrime * deltaT;
  var LS = this.L0 + (this.meanMotion + this.longitudeOfNodePrime + this.argOfPericenterPrime) * deltaT +
    this.meanMotionPrime * deltaT * deltaT + this.meanMotionSecond * deltaT * deltaT * deltaT;

  while(LS > 2 * Math.PI) LS -= 2 * Math.PI;


  var ayNSL = eccentricityNow * Math.sin(argOfPericenterNow) - 1 / 2 * J3 / J2 * EARTH_RADIUS / p * Math.sin(this.inclination);
  var axNSL = eccentricityNow * Math.cos(argOfPericenterNow);
  var L = LS - 1 / 4 * J3 / J2 * EARTH_RADIUS / p * axNSL * Math.sin(this.inclination) * (3 + 5 * Math.cos(this.inclination) / (1 + Math.cos(this.inclination)));
  //console.log(rad2deg(L - longitudeOfNodeNow));

  var keplerRes = solveKeplerEquation(L - longitudeOfNodeNow, axNSL, ayNSL);
  var keplerRes = L - longitudeOfNodeNow;
  var eCosE = axNSL * Math.cos(keplerRes) + ayNSL * Math.sin(keplerRes);
  var eSinE = axNSL * Math.sin(keplerRes) - ayNSL * Math.cos(keplerRes);
  var eL = axNSL * axNSL + ayNSL * ayNSL;
  var pL = semiMajorAxisNow * (1 - eL);
  var r = semiMajorAxisNow * (1 - eCosE);

  console.log(rad2deg(Math.atan2(eSinE, eCosE)));
  var sinU = semiMajorAxisNow / r * (Math.sin(keplerRes) - ayNSL - axNSL * eSinE / (1 + Math.sqrt(1 - eL)));
  var cosU = semiMajorAxisNow / r * (Math.cos(keplerRes) - axNSL + ayNSL * eSinE / (1 + Math.sqrt(1 - eL)));
  var u = Math.atan2(sinU, cosU);

  //console.log(rad2deg(this.meanAnomaly));

  var rk = r + 1 / 4 * J2 * EARTH_RADIUS * EARTH_RADIUS / pL * Math.pow(Math.sin(this.inclination), 2) * Math.cos(2 * u);
  var uk = u - 1 / 8 * J2 * EARTH_RADIUS * EARTH_RADIUS / (pL * pL) * (7 * Math.pow(Math.cos(this.inclination), 2) - 1) * Math.sin(2 * u);


  var sigmaK = longitudeOfNodeNow + 3 / 4 * J2 * EARTH_RADIUS * EARTH_RADIUS / (pL * pL)
   * Math.cos(this.inclination) * Math.sin(2 * u);

  var iK = this.inclination + 3 / 4 * J2 * EARTH_RADIUS * EARTH_RADIUS / (pL * pL)
   * Math.sin(this.inclination) * Math.cos(this.inclination) * Math.cos(2 * u);


  var M = {
    x: -Math.sin(sigmaK) * Math.cos(iK),
    y: Math.cos(sigmaK) * Math.cos(iK),
    z: Math.sin(iK),
  }

  var N = {
    x: Math.cos(sigmaK),
    y: Math.sin(sigmaK),
    z: 0
  }

  var pos = {};
  pos.x = rk * (M.x * Math.sin(uk) + N.x * Math.cos(uk));
  pos.y = rk * (M.y * Math.sin(uk) + N.y * Math.cos(uk));
  pos.z = rk * (M.z * Math.sin(uk) + N.z * Math.cos(uk));

  console.log(rk)

  return pos;
}

function solveKeplerEquation(startResult, axNSL, ayNSL){
  var result0 = startResult;
  var result = computeIteration(startResult, axNSL, ayNSL, result0);

  var iter = 0;
  while(Math.abs(result - result0) > 1E-4 && iter < 20){
    console.log("uaie")

    result0 = result;
    result = computeIteration(startResult, axNSL, ayNSL, result0);
    console.log(result + " " + result0);

    iter++;
  }
  return result;
}

function computeIteration(U, axNSL, ayNSL, result0){
  return (U - ayNSL * Math.cos(result0) + axNSL  * Math.sin(result0) - result0) /
   (- ayNSL * Math.sin(result0) - axNSL * Math.cos(result0) + 1);
}

Satellite.prototype.getCartesianCoordinates = function(eccentricAnomaly){
  var trueAnomaly = this.getTrueAnomaly(eccentricAnomaly);
  var radius = this.semiMajorAxis * (1 - this.eccentricity*this.eccentricity) / (1 + this.eccentricity * Math.cos(trueAnomaly));
  var x = radius * (Math.cos(this.longitudeOfNode) * Math.cos(this.argOfPericenter + trueAnomaly) - Math.sin(this.longitudeOfNode) * Math.sin(trueAnomaly + this.argOfPericenter) * Math.cos(this.inclination));
  var y = radius * (Math.sin(this.longitudeOfNode) * Math.cos(this.argOfPericenter + trueAnomaly) + Math.cos(this.longitudeOfNode) * Math.sin(trueAnomaly + this.argOfPericenter) * Math.cos(this.inclination));
  var z = radius * Math.sin(this.argOfPericenter + trueAnomaly) * Math.sin(this.inclination);
  return {x: x, y: y, z: z};
};

Satellite.prototype.getTrueAnomaly = function(eccentricAnomaly){
  var trueAnomaly = Math.atan2(Math.sqrt(1 - this.eccentricity * this.eccentricity) * Math.sin(eccentricAnomaly), Math.cos(eccentricAnomaly) - this.eccentricity);
  if(trueAnomaly < 0){
    trueAnomaly += 2 * Math.PI;
  }
  return trueAnomaly
};
