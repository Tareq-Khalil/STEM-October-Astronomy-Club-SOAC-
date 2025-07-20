var AU_IN_SCREEN_UNITS = 1;

var lastTime = Date.now();
var center = {x: 0, y: 0, z: 0};
var deltaT = 0;
var fps;
var centuriesPerSecond = 0.0002;
var followedObjectId = -1; //-1 is the center;

var scene, aspect_ratio, camera, renderer, controls;

var ASTEROID_BELT_FLAG = 'asteroid_belt'

var epoch = unixToEpoch(Date.now() / 1000.0);

var showObjectsCloseToSun = true;
var maxDistanceOfCamera = 40; //from this distance the objects close to the sun dissapear
var maxDistanceOfObject = 5;  //objects from this distance dissapear

var showOrbits = true;

var doNotUpdate = false;

//new CelestialBody(name,semiMajorAxis, eccentricty, inclination, longitudeOfNode, longitudeOfPericenter, meanAnomaly2000)

var planets = [
  new CelestialBody("Mercury",      0.3870, 0.205635, deg2rad(7.0050), deg2rad(48.331300), deg2rad(77.456450), deg2rad(174.794787)),
  new CelestialBody("Venus",        0.7230, 0.006773, deg2rad(3.3947), deg2rad(76.679900), deg2rad(131.53298), deg2rad(50.4160980)),
  new CelestialBody("Earth",        1.0000, 0.016700, deg2rad(0.0000), deg2rad(-11.26064), deg2rad(102.94719), deg2rad(357.529109)),
  new CelestialBody("Mars",         1.5240, 0.093405, deg2rad(1.8510), deg2rad(49.557400), deg2rad(336.04084), deg2rad(19.3727660)),
  new CelestialBody("Jupiter",      5.2030, 0.048498, deg2rad(1.3050), deg2rad(100.45420), deg2rad(14.753850), deg2rad(20.0203120)),
  new CelestialBody("Saturn",       9.5230, 0.055546, deg2rad(2.4840), deg2rad(113.66340), deg2rad(92.431940), deg2rad(317.020207)),
  new CelestialBody("Uranus",       19.208, 0.047318, deg2rad(0.7700), deg2rad(74.000500), deg2rad(170.96424), deg2rad(141.049714)),
  new CelestialBody("Neptune",      30.087, 0.008606, deg2rad(1.7690), deg2rad(131.78060), deg2rad(44.971350), deg2rad(256.228389)),
  new CelestialBody("Pluto",        39.746, 0.250000, deg2rad(17.142), deg2rad(110.30340), deg2rad(224.43738), deg2rad(14.0921741)),
];

var followablePlanets = planets;

var colors = [
  0x385535,  //Mercury
  0x83853b,  //Venus
  0x364e60,  //Earth
  0x753030,  //Mars
  0x3b5e59,  //Jupiter
  0x9e7a2b,  //Saturn
  0x3b2f52,  //Uranus
  0x6e4444,  //Neptune
  0x497147,  //Pluto
];

var celestialBodySprites = [];
var orbits = [];

var skyboxMesh;
var planetSpriteTexture = THREE.ImageUtils.loadTexture("./res/planet.png");
var asteroidSpriteTexture = THREE.ImageUtils.loadTexture("./res/asteroid.png");

var canvasWidth, canvasHeight;

window.onload = function(){
  loadGUI();
  try{
    init();
  }
  catch(err){
    document.getElementById("error").style.display = "block ";
  }

  resize();
  createSkybox();
  createOrbits();
  createSun();

  createSprites();
  updatePositionAndRotation();

  addOrbitsToScene();
  addSpritesToScene();

  addNumberedBodies('./res/numberedBodies.dat');
  addUnumberedBodies('./res/unnumberedBodies.dat');

  render();

}

function render(){
  updateScales();
  updateSkybox();
  checkForOverlap();

  var now = new Date().getTime();
  var sinceLastFrame = now - lastTime;

  if(!doNotUpdate){
    epoch += sinceLastFrame / 1000 * centuriesPerSecond;
    updatePositionAndRotation();
  }

  updateCamera();
  document.getElementById('date').innerHTML=unixToString(epochToUnixTime(epoch));
  controls.update();

  fps++;

  deltaT += sinceLastFrame;
  lastTime = now;
  if(deltaT > 200){
    document.getElementById('fps').innerHTML=fps * 5;
    deltaT = 0;
    fps = 0;
  }

  renderer.render(scene, camera);
  requestAnimationFrame(render);

}

function checkForOverlap(){
  var dist = getDistance(center, camera.position);
	if(dist > maxDistanceOfCamera && showObjectsCloseToSun){
    disableObjectsCloseToSun();
    showObjectsCloseToSun = false;
  }
  if(dist < maxDistanceOfCamera && !showObjectsCloseToSun){
    enableObjectsCloseToSun();
    showObjectsCloseToSun = true;
  }
}

function disableObjectsCloseToSun(){
  for(sprite of celestialBodySprites){
    if(sprite.planet.semiMajorAxis < maxDistanceOfObject){
      sprite.removeFrom(scene);
    }
  }

}

function enableObjectsCloseToSun(){
  for(sprite of celestialBodySprites){
    if(sprite.planet.semiMajorAxis < maxDistanceOfObject && sprite.shouldDisplay){
      sprite.addTo(scene);
    }
  }
}

function createSprites(){
  for(planet of planets){
    celestialBodySprites.push(new CelestialBodySprite(planet, planetSpriteTexture, 20, true));
  }
}

function addSpritesToScene(){
  for(bodySprite of celestialBodySprites){
    bodySprite.addTo(scene);
  }
}

function updateScales(){
  for(bodySprite of celestialBodySprites){
    bodySprite.updateScale(camera, canvasWidth, canvasHeight);
  }
}

function addBodyByName(name){
  for(bodySprite of celestialBodySprites){
    if(bodySprite.planet.name == name){
      bodySprite.addTo(scene);
    }
  }
}

function removeBodyByName(name){
  for(bodySprite of celestialBodySprites){
    if(bodySprite.planet.name == name){
      bodySprite.addTo(scene);
    }
  }
}

function updateCamera(){
  if(followedObjectId == -1){
    controls.target.set(0, 0, 0)
  }
  else{
    var position = celestialBodySprites[followedObjectId].symbol.position;
    controls.target.set(position.x, position.y, position.z)
  }
}

function updatePositionAndRotation(){
  for(bodySprite of celestialBodySprites){
    if(bodySprite.shouldDisplay)
      bodySprite.updatePosition(epoch);
  }
}

function updateSkybox(){
  skyboxMesh.position.set(camera.position.x, camera.position.y, camera.position.z);
}

function createOrbits(){
  for(var i = 0; i < planets.length; i++){
    orbits.push(new Orbit(planets[i], colors[i]));
  }
}

function addOrbitsToScene(){
  for (orbit of orbits){
    scene.add(orbit.mesh);
  }
}

function toogleOrbits(){
  showOrbits = !showOrbits;

  if(showOrbits){
    enableOrbits();
  }
  else{
    disableOrbits();
  }
}


function createSkybox(){
  var geometry = new THREE.SphereGeometry(180, 90, 90);
  var texture = new THREE.ImageUtils.loadTexture('./res/milky_way.jpg');
  var material = new THREE.MeshBasicMaterial({
    map:  texture,
    side: THREE.BackSide,
  });

  skyboxMesh = new THREE.Mesh(geometry, material);
  //
  skyboxMesh.rotateX(deg2rad(150));
  skyboxMesh.rotateY(deg2rad(-96.33733));

  scene.add(skyboxMesh);
}

function createSun(){
  var flareTexture = THREE.ImageUtils.loadTexture("./res/flare.png");
  var flareMaterial = new THREE.SpriteMaterial({map: flareTexture, blending:THREE.AdditiveBlending, useScreenCoordinates: false, color: 0xffffff});
  var flareSprite = new THREE.Sprite(flareMaterial);
  flareSprite.scale.set(0.8, 0.8, 0.8);
  flareSprite.position.set(0, 0, 0);
  scene.add(flareSprite);

}

window.onresize = function(e){
  resize();
}

function resize(){

  canvasWidth = Math.floor(getBrowserWidth() * window.devicePixelRatio);
  canvasHeight = Math.floor(getBrowserHeight() * window.devicePixelRatio);
  console.log(canvasWidth + "  " + canvasHeight + " " + getBrowserWidth() + " " + window.devicePixelRatio);
  renderer.setSize(canvasWidth, canvasHeight);
  renderer.domElement.style.width = getBrowserWidth() + "px";
  renderer.domElement.style.height = getBrowserHeight() + "px";
  camera.aspect = canvasWidth / canvasHeight;
  camera.updateProjectionMatrix();
}

function addNumberedBodies(file){
  $.get(file, function(data){
    var lines = data.split("\n");

    for(line of lines){
      var id = parseInt(line.substring(0, 6));

      if(id > 0){
        var name = line.substring(7, 24).replace(" ", "");
        var epochEphem = parseInt(line.substring(25, 30));
        var semiMajorAxis = parseFloat(line.substring(31, 41));
        var eccentricty = parseFloat(line.substring(42, 52));
        var inclination = deg2rad(parseFloat(line.substring(53, 62)));
        var argOfPericenter = deg2rad(parseFloat(line.substring(63, 72)));
        var longitudeOfNode = deg2rad(parseFloat(line.substring(73, 82)));
        var meanAnomalyAtEpoch = deg2rad(parseFloat(line.substring(83, 94)));

        var longitudeOfPericenter = argOfPericenter + longitudeOfNode;

        meanAngularMotion = 2.0 * Math.PI / Math.sqrt(semiMajorAxis * semiMajorAxis * semiMajorAxis);
        meanAnomaly2000 = meanAnomalyAtEpoch - meanAngularMotion * (epochEphem - MJD_AT_J2000) / 365.25;
        while(meanAnomaly2000 < 0) meanAnomaly2000 += 2 * Math.PI;

        var asteroid = new CelestialBody(name, semiMajorAxis, eccentricty, inclination, longitudeOfNode, longitudeOfPericenter, meanAnomaly2000);
        var asteroidSprite = new CelestialBodySprite(asteroid, asteroidSpriteTexture, 4, false, ASTEROID_BELT_FLAG);
        planets.push(asteroid);
        celestialBodySprites.push(asteroidSprite);
        scene.add(asteroidSprite.symbol);

      }
    }
  });
}

function addUnumberedBodies(file){
  $.get(file, function(data){
    var lines = data.split("\n");

    for(line of lines){
      var firstChar = line.charAt(0);

      if(firstChar != " " && firstChar != "-" && firstChar != "D"){
        var name = line.substring(0, 11).replace(" ", "");
        var epochEphem = parseInt(line.substring(12, 17));
        var semiMajorAxis = parseFloat(line.substring(18, 29));
        var eccentricty = parseFloat(line.substring(30, 40));
        var inclination = deg2rad(parseFloat(line.substring(41, 50)));
        var argOfPericenter = deg2rad(parseFloat(line.substring(51, 60)));
        var longitudeOfNode = deg2rad(parseFloat(line.substring(61, 70)));
        var meanAnomalyAtEpoch = deg2rad(parseFloat(line.substring(71, 82)));

        var longitudeOfPericenter = argOfPericenter + longitudeOfNode;

        meanAngularMotion = 2.0 * Math.PI / Math.sqrt(semiMajorAxis * semiMajorAxis * semiMajorAxis);
        meanAnomaly2000 = meanAnomalyAtEpoch - meanAngularMotion * (epochEphem - MJD_AT_J2000) / 365.25;
        while(meanAnomaly2000 < 0) meanAnomaly2000 += 2 * Math.PI;

        var asteroid = new CelestialBody(name, semiMajorAxis, eccentricty, inclination, longitudeOfNode, longitudeOfPericenter, meanAnomaly2000);
        var asteroidSprite = new CelestialBodySprite(asteroid, asteroidSpriteTexture, 4, false, ASTEROID_BELT_FLAG);
        planets.push(asteroid);
        celestialBodySprites.push(asteroidSprite);
    //    scene.add(asteroidSprite.label);
        asteroidSprite.addTo(scene);

      }
    }
  });
}


function init(){
  scene = new THREE.Scene();

  canvasWidth = getBrowserWidth();
  canvasHeight = getBrowserHeight();
  aspect_ratio = canvasWidth / canvasHeight;
  camera = new THREE.PerspectiveCamera(75, aspect_ratio, 0.01, 200);
  camera.lookAt(new THREE.Vector3(0,0,0));
  renderer = new THREE.WebGLRenderer({antialias:true, });


  renderer.setSize(canvasWidth, canvasHeight);
  scene.background = new THREE.Color(0x020202);

  renderer.domElement.style="position:absolute; top:0px; left:0px; margin:0px; width: 100%; height: 100%;"

  document.getElementById('bellowAbout').appendChild(renderer.domElement);


  camera.position.z = 2;

  controls = new THREE.TrackballControls(camera, renderer.domElement);
  controls.rotateSpeed = 10;
  controls.zoomSpeed = 0.12;
  controls.rotateCamera();
  controls.minDistance = 0.1;
  controls.maxDistance = 90;
  controls.dynamicDampingFactor = 0.3;
}
