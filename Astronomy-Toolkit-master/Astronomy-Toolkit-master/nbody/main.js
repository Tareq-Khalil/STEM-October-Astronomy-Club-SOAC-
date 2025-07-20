var scene, controls, renderer;

var COLOR_OPTIONS = {format: 'hex'};

var smoothingFactor = 0.001;
var k = 0.05;
var timePerStep = 0.01;
var stepsPerFrame = 1;
var bodyMass = 0.1;
var bodyDensity = 3000;
var trailLength = 500;

var canvasWidth, canvasHeight;

var arrowMesh;

var beginX, beginY, beginVector, endX = 0, endY = 0, isPressed = false;

var lastTime = Date.now();
var deltaT = 0;
var fps;

var paused = false;

var sunPreset = [new Body(1.000, 0.0, 0.00, 0.00, 0.00, 900, 0xF0C400)]

var bodies = [];

window.onload = function(){
  initGUI();
  updateBodyNumber();

  try{
    init();
  }
  catch(err){
    document.getElementById("error").style.display = "block ";
  }

  resize();
  renderer.domElement.addEventListener("mousedown", getDownPosition, false);
  renderer.domElement.addEventListener("mouseup", getUpPosition, false);
  renderer.domElement.addEventListener("mousemove", getMovePosition, false);

  addSolarSystem();
  render();

}

function render(){
  fps++;
  var now = new Date().getTime();
  var sinceLastFrame = now - lastTime;
  deltaT += sinceLastFrame;
  lastTime = now;
  if(deltaT > 200){
    document.getElementById('fps').innerHTML = fps * 5;
    deltaT = 0;
    fps = 0;
  }

  controls.update();
  if(!paused){
    for(var i = 0; i < stepsPerFrame; i++){
    computeAccelerations();
    checkCollisions();
    updateBodies();
    }
  }
  renderer.render(scene, camera);
  requestAnimationFrame(render);

}

function updateBodies(){
  for(var i = 0; i < bodies.length; i++){
    bodies[i].update(timePerStep, trailLength, scene);
  }
}

function addMeshes(){
  for(body of bodies){
    scene.add(body.mesh);
    scene.add(body.trail);
  }
}

function computeAccelerations(){
  var n = bodies.length;
	for(var  i = 0; i < n; i++){
		bodies[i].resetAcceleration();
		for(var j = 0; j < n; j++){
			if(i != j){
				var deltaX = bodies[j].x - bodies[i].x;
				var deltaY = bodies[j].y - bodies[i].y;
				var d = Math.sqrt(deltaX * deltaX + deltaY * deltaY) + smoothingFactor;
				var acc = k * bodies[j].mass / (d * d);
				bodies[i].accelerationX += acc * deltaX / d;
				bodies[i].accelerationY += acc * deltaY / d;
			}
		}
  }
}

function checkCollisions(){
	for(var i = 0; i < bodies.length; i++){
		for(var j = 0; j < bodies.length; j++){
			if(i != j && bodies[i].distanceToBody(bodies[j]) < bodies[i].size + bodies[j].size){
				if(bodies[i].mass > bodies[j].mass){
					bodies[i].collideWith(bodies[j]);
          bodies[j].removeFrom(scene);
          bodies[j].dispose();
					bodies.splice(j, 1);
          j--;
				}
				else{
          bodies[j].collideWith(bodies[i]);
          bodies[i].removeFrom(scene);
          bodies[i].dispose();
					bodies.splice(i, 1);
          i--;
          break;
				}
        updateBodyNumber();
			}
		}
	}
}

function getDownPosition(e){
	var x = e.clientX;
	var y = e.clientY;
	if(e.which == 1){

		beginX = endX = x;
		beginY = endY = y;

    beginVector = screenToWorldCoordinates(beginX, beginY);
    arrowMesh = new Arrow(beginVector, beginVector);
    scene.add(arrowMesh.mesh);

    isPressed = true;
	}

}

function getMovePosition(e){
	if(isPressed){
		endX = e.clientX;
		endY = e.clientY;
    var endVector = screenToWorldCoordinates(endX, endY);
    arrowMesh.updateEnd(endVector)
	}
}

function getUpPosition(e){
	endX = e.clientX;
	endY = e.clientY;
	if(e.which == 1){
		isPressed = false;
    var endVector = screenToWorldCoordinates(endX, endY);
		var velX = 0.5 * (endVector.x - beginVector.x);
		var velY = 0.5 * (endVector.y - beginVector.y);
    scene.remove(arrowMesh.mesh);
		var body = new Body(bodyMass, beginVector.x, beginVector.y, velX, velY, bodyDensity, randomColor(COLOR_OPTIONS));
    scene.add(body.mesh);
    scene.add(body.trail);
		bodies.push(body);
    updateBodyNumber();
	}
}

function screenToWorldCoordinates(x, y){
  var aspectRatio = canvasWidth / canvasHeight;
  var worldX = (aspectRatio * (2.0 * x / getBrowserWidth() - 1.0)  / camera.zoom + camera.position.x);
  var worldY = ((1.0 - 2.0 * y / getBrowserHeight())  / camera.zoom + camera.position.y);

  return new THREE.Vector3(worldX, worldY, 0);
}

function cloneBodies(array){
	var returnedArray = [];
	for(var  i = 0; i < array.length; i++){
		body = new Body(array[i].mass, array[i].x, array[i].y, array[i].velocityX, array[i].velocityY, array[i].density, array[i].color);
		body.setPositions(array[i].positionsX, array[i].positionsY);
		returnedArray.push(body);
	}
	return returnedArray;
}

window.onresize = function(e){
  resize();
}

function resize(){

  canvasWidth = Math.floor(getBrowserWidth() * window.devicePixelRatio);
  canvasHeight = Math.floor(getBrowserHeight() * window.devicePixelRatio);
  var aspect = canvasWidth / canvasHeight;
  renderer.setSize(canvasWidth, canvasHeight);
  renderer.domElement.style.width = getBrowserWidth() + "px";
  renderer.domElement.style.height = getBrowserHeight() + "px";
  camera.left = -aspect;
  camera.right = aspect;
  camera.top = 1;
  camera.bottom = -1;
  camera.updateProjectionMatrix();
}

function initGUI(){
  var gui = new dat.GUI({autoplace: false});
  var container = document.getElementById("guiContainer");
  container.appendChild(gui.domElement);

  gui.add(this, 'showHelp').name("Help");
  var bodyFolder = gui.addFolder("Body Settings");
  bodyFolder.add(this, 'bodyMass', 0.001, 2).step(0.001).name("Mass");
  bodyFolder.add(this, 'bodyDensity', 90, 5000).step(0.001).name("Density");
  bodyFolder.open();

  var worldFolder = gui.addFolder("Simulation Settings");
  worldFolder.add(this, 'k', 0, 0.06).step(0.000001).name("G Constant");
  worldFolder.add(this, 'timePerStep', 0, 0.05).step(0.0001).name("Time Per Step");
  worldFolder.add(this, 'stepsPerFrame', 0, 10).step(1).name("Steps Per Frame").listen();
  worldFolder.add(this, 'trailLength', 0, 1000).step(1).name("Trail Length").listen();
  worldFolder.open();

  var presetFolder = gui.addFolder("Presets");
  presetFolder.add(this, 'clear').name("Empty Universe");
  presetFolder.add(this, 'addSun').name("A Single Star");
  presetFolder.add(this, 'addSolarSystem').name("Solar System");
  presetFolder.add(this, 'addBinaryStars').name("Binary Star System");
  presetFolder.add(this, 'addTripleStars').name("Triple Star System");
  presetFolder.add(this, 'addMoonSystem').name("Planet w/ Satellite");
	presetFolder.add(this, 'addProtoDisk').name("Protoplanetary Disk");
  presetFolder.open();
}

function showHelp(){
  var aboutDiv = document.getElementById('about');
  var bellowDiv = document.getElementById('bellowAbout');

  aboutDiv.classList.add('showAbout');
  bellowDiv.classList.add('blurBellow');

  stepsPerFrame = 0;
}

function closeHelp(){
  var aboutDiv = document.getElementById('about');
  var bellowDiv = document.getElementById('bellowAbout');

  aboutDiv.classList.remove('showAbout');
  bellowDiv.classList.remove('blurBellow');

  stepsPerFrame = 1;

}

function clear(){
  for(var i = 0; i < bodies.length; i++){
    bodies[i].removeFrom(scene);
    bodies[i].dispose();
  }
  bodies = [];
  updateBodyNumber();
  controls.reset();
}



function addSolarSystem(){
  clear();
  var sun = new Body(1.0, 0, 0, 0, 0, 1500, 0xF0C400);
  bodies.push(sun);
  sun.addTo(scene);
  var x = Math.random() / 10;
  var y = Math.random() / 10;

	for(var i = 0; i < 4; i++){
    x += Math.random() / 8 + 0.1;
    y += Math.random() / 8 + 0.1;
		var radius = Math.sqrt(x * x + y * y);
		var speed = circularOrbitVelocity(sun.mass, radius);
		var speedX = speed * y / radius;
		var speedY = - speed * x / radius;
		var planet = new Body(0.001, x, y, speedX, speedY, 90, randomColor(COLOR_OPTIONS));
		bodies.push(planet);
		planet.addTo(scene);
	}
  updateBodyNumber();
}

function addSun(){
  clear();
  bodies = cloneBodies(sunPreset);
  addMeshes();
  updateBodyNumber();
}

function addBinaryStars(){
  clear();
  var distance = 0.3 + Math.random() / 15;
  var mass = Math.random() + 0.5;
  var vel = circularOrbitVelocity(mass / 4.0, distance);
  var star1 = new Body(mass, 0, +distance, -vel, 0, 1500, randomColor({format: 'hex', luminosity: 'dark'}));
  var star2 = new Body(mass, 0, -distance, vel, 0, 1500, randomColor({format: 'hex', luminosity: 'light'}));
  bodies.push(star1);
  bodies.push(star2);
  star1.addTo(scene);
  star2.addTo(scene);
  updateBodyNumber();
}

function addTripleStars(){
  clear();
  var distance = 0.1 + Math.random() / 20;
  var mass = Math.random() / 2 + 0.25;
  var vel = Math.sqrt(0.25 * k * mass/ distance);
  var star1 = new Body(mass, 0, +distance, -vel, 0, 1500, randomColor({format: 'hex', luminosity: 'dark'}));
  var star2 = new Body(mass, 0, -distance, +vel, 0, 1500, randomColor({format: 'hex', luminosity: 'light'}));

  var distance1 = 1.0 + Math.random() / 3;
  var vel1 = circularOrbitVelocity(2 * mass, distance1);
  var star3 = new Body(mass, 0, distance1, vel1, 0, 1500, randomColor(COLOR_OPTIONS));

  bodies.push(star1);
  bodies.push(star2);
  bodies.push(star3);
  star1.addTo(scene);
  star2.addTo(scene);
  star3.addTo(scene)
  updateBodyNumber();
}

function addMoonSystem(){
  clear();
  var planetDistance = 0.45 + Math.random() / 3;
  var sunMass = 0.7 + Math.random() / 2;
  var planetMass = 0.01 + Math.random() / 100;
  var satDistance = 0.05;
  var planetVel = circularOrbitVelocity(sunMass, planetDistance);
  var satVel = circularOrbitVelocity(planetMass, satDistance);

  var sun = new Body(sunMass, 0, 0, 0, 0, 1500, 0xF0C400);
  var planet = new Body(planetMass, 0, planetDistance, planetVel, 0, 2000, randomColor({format: 'hex', luminosity: 'dark'}));
  var sat = new Body(0.0001, 0, planetDistance + satDistance, satVel + planetVel, 0, 90, randomColor({format: 'hex', luminosity: 'light'}));

  bodies.push(sun, planet, sat);
  sun.addTo(scene);
  planet.addTo(scene);
  sat.addTo(scene);
  updateBodyNumber();
}

function addProtoDisk(){
	clear();
	var sunMass = 1.0;
	var sun = new Body(sunMass, 0, 0, 0, 0, 1500, 0xF0C400);
  bodies.push(sun);
  sun.addTo(scene);
	var particleMass = 0.00007;
	for(var i = 0; i < 200; i++){
    var x = 1 - 2 * Math.random() + 0.1;
		var y = 1 - 2 * Math.random() + 0.1;
		var radius = Math.sqrt(x * x + y * y);
		var speed = circularOrbitVelocity(sunMass, radius);
		var speedX = speed * y / radius;
		var speedY = - speed * x / radius;
		var particle = new Body(particleMass, x, y, speedX, speedY, 500, randomColor(COLOR_OPTIONS));
		bodies.push(particle);
		particle.addTo(scene);
	}
  updateBodyNumber();
  trailLength = 0;
}

function circularOrbitVelocity(mass, distance){
  return Math.sqrt(k * mass / distance);
}

function updateBodyNumber(){
  document.getElementById('bodies').innerHTML = bodies.length;
}

function init(){
  scene = new THREE.Scene();

  var aspect_ratio = getBrowserWidth() / getBrowserHeight();
  camera = new THREE.OrthographicCamera(-aspect_ratio, aspect_ratio, 1, -1, 1, 1000 );
  //camera = new THREE.PerspectiveCamera(75, aspect_ratio, 0.01, 200)
  camera.lookAt(new THREE.Vector3(0,0,0));
  renderer = new THREE.WebGLRenderer({antialias:true, });


  renderer.setSize(canvasWidth, canvasHeight);
  scene.background = new THREE.Color(0x020202);

  renderer.domElement.style="position:absolute; top:0px; left:0px; margin:0px; width: 100%; height: 100%;"

  document.getElementById('bellowAbout').appendChild(renderer.domElement);

  camera.position.z = 1;
  camera.zoom = 1;
  camera.updateProjectionMatrix();

  controls = new THREE.OrthographicTrackballControls(camera, renderer.domElement);
  controls.noRotate = true;
  controls.zoomSpeed = 0.12;
  controls.rotateCamera();
  controls.minDistance = 2;
  controls.maxDistance = 10;
  controls.dynamicDampingFactor = 0.95;
}
