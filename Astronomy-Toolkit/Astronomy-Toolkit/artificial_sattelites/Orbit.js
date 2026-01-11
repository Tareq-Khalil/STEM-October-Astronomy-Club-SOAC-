var Orbit = function(celestialBody, color){
  this.mesh = createOrbitGeometry(celestialBody, color);
  this.body = celestialBody;
}

Orbit.prototype.removeFrom = function (scene){
  scene.remove(scene.getObjectById(this.mesh.id));
}

function createOrbitGeometry(celestialBody, color){
  var geometry = new THREE.Geometry();
  var material = new THREE.LineBasicMaterial({ color: color});

  var segmentCount = Math.ceil(2 * Math.PI * celestialBody.semiMajorAxis / SCREEN_UNIT_IN_M  * 30);
  for(var i = 0; i <= segmentCount; i++){
    var angle = 2 * i * Math.PI / segmentCount;
    var position = celestialBody.getCartesianCoordinates(angle);
    var vector  = new THREE.Vector3(position.x / SCREEN_UNIT_IN_M, position.y / SCREEN_UNIT_IN_M , position.z / SCREEN_UNIT_IN_M);
    geometry.vertices.push(vector);
  }
  var mesh = new THREE.Line(geometry, material);
  return mesh;
}
