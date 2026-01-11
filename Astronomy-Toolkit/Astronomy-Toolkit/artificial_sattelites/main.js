var scene, camera, controls;

var earthMesh, cloudMesh;
var EARTH_RADIUS_IN_SCREEN_UNITS = 100;
var SCREEN_UNIT_IN_M = EARTH_RADIUS / EARTH_RADIUS_IN_SCREEN_UNITS;

var satArray = [];

var sun;

window.onload = function(){
  init();

  sun = new THREE.DirectionalLight( 0xffffff, 1 );
  sun.position.set(1, 0, 0 ).normalize();
  sun.target.position.set( 0, 0, 0);
  scene.add(sun);

  createEarthMesh();
  loadTLEFile("res/gps-ops.txt");
  loadTLEFile("res/iridium.txt");
  render();
}

window.onresize = function(e){
  canvasWidth = getBrowserWidth();
  canvasHeight = getBrowserHeight();

  renderer.setSize(canvasWidth, canvasHeight);
  camera.aspect = canvasWidth / canvasHeight;
  camera.updateProjectionMatrix();
}


function render(){
  controls.update();
  renderer.render(scene, camera);
  var t = Date.now() / 1000;
  var axis = new THREE.Vector3( 0, 1, 0 );
  sun.position.normalize();
//  sun.position.applyAxisAngle( axis, Math.PI / 400 );
  earthMesh.rotateY(Math.PI / 1000 );
  cloudMesh.rotateY(Math.PI / 5000);

  requestAnimationFrame(render);

}

function addOrbit(satellite){
    var orbit = new Orbit(satellite, 0xacb941)
    scene.add(orbit.mesh);
  //  console.log(orbit.mesh);
}

function createEarthMesh(){
    var geometry = new THREE.SphereGeometry(EARTH_RADIUS_IN_SCREEN_UNITS, 50, 50);

    var day = THREE.ImageUtils.loadTexture("res/8081-earthmap4k.jpg");
  //  var night = THREE.ImageUtils.loadTexture("res/earthlights1k.jpg");

  //  var day = THREE.ImageUtils.loadTexture("res/8081-earthmap4k.jpg");
  //  var night = THREE.ImageUtils.loadTexture("res/8081-earthlights4k.jpg");
  //  var bump = THREE.ImageUtils.loadTexture("res/earthbump1k.jpg");
  //  var specular = THREE.ImageUtils.loadTexture("res/earthspec1k.jpg");


  var material =  new  THREE.MeshBasicMaterial({
      map: day,
      transparent: false,
    /*  bumpMap: bump,
      bumpScale: 0.004,
      specularMap: specular,
      specular: new THREE.Color('grey'),
      shininess: 10,*/
   });

  earthMesh = new THREE.Mesh(geometry, material);
  earthMesh.position.set(0, 0, 0);

  var cloudGeometry = new THREE.SphereGeometry(EARTH_RADIUS_IN_SCREEN_UNITS + 1, 50, 50);
  var cloudMaterial = new THREE.MeshBasicMaterial({
    map: new THREE.ImageUtils.loadTexture("res/fair_clouds_8k.jpg"),
    //side: THREE.DoubleSide,
    opacity: 0.2,
    //alphaMap: new THREE.ImageUtils.loadTexture("res/earthcloudmaptrans.jpg"),
    transparent: true,
    depthWrite: false
  })

  cloudMesh = new THREE.Mesh(cloudGeometry, cloudMaterial);
  earthMesh.add(cloudMesh);

  scene.add(earthMesh);


}

function loadTLEFile(file){
  $.get(file, function(data){
    var lines  = data.split("\n");
    var length = lines.length;
    for(var i = 0; i < length - 1; i += 3){
      var name = lines[i].trim();
      var line1 = lines[i + 1];
      var line2  = lines[i + 2];
      var satellite = new Satellite(name, line1, line2);
      addOrbit(satellite);
      satArray.push(satellite);
    }
  });
}

function init(){
  scene = new THREE.Scene();
  var canvasWidth = getBrowserWidth();
  var canvasHeight = getBrowserHeight();
  var aspect_ratio = canvasWidth / canvasHeight;
  camera = new THREE.PerspectiveCamera(75, aspect_ratio, 0.1, 10000);
  camera.lookAt(new THREE.Vector3(0,0,0));
  camera.position.z = - 4 * EARTH_RADIUS_IN_SCREEN_UNITS;
  renderer = new THREE.WebGLRenderer({antialias:true, sortObjects: true});

  renderer.setSize(canvasWidth, canvasHeight);
//  scene.background = new THREE.Color(0x020202);

  renderer.domElement.style="position:absolute; top:0px; left:0px; margin:0px; "

  document.body.appendChild(renderer.domElement);

  controls = new THREE.TrackballControls(camera, renderer.domElement);
  controls.rotateSpeed = 10;
  controls.zoomSpeed = 0.10;
  controls.rotateCamera();
  controls.minDistance = 0.1;
  controls.maxDistance = 60 * EARTH_RADIUS_IN_SCREEN_UNITS;
  controls.dynamicDampingFactor = 0.3;
}
