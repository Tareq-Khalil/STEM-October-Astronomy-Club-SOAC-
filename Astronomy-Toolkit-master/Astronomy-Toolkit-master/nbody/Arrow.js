var arrowHeadLength = 0.09;
var arrowHeadAngle = Math.PI / 2.7;

function Arrow(startVector, endVector){
  var lineMaterial = new THREE.LineBasicMaterial({color: 0xffffff});
  var lineGeom = new THREE.Geometry();
  var alpha =  Math.atan2(startVector.y - endVector.y, startVector.x - endVector.x);
  var head1 = getUpperHeadVector(endVector);
  var head2 = getUpperHeadVector(endVector);
  this.startVector = startVector;
  lineGeom.vertices = [startVector, endVector, head1, endVector, head2];
  this.mesh = new THREE.Line(lineGeom, lineMaterial);
}

Arrow.prototype.updateEnd = function(endVector){
  var alpha =  Math.atan2(this.startVector.y - endVector.y, this.startVector.x - endVector.x);
  var head1 = getUpperHeadVector(endVector, alpha);
  var head2 = getDownHeadVector(endVector, alpha);

  this.mesh.geometry.vertices[1] = endVector;
  this.mesh.geometry.vertices[2] = head1;
  this.mesh.geometry.vertices[3] = endVector;
  this.mesh.geometry.vertices[4] = head2;

  this.mesh.geometry.verticesNeedUpdate = true;
}

function getUpperHeadVector(endVector, alpha){
  return new THREE.Vector3(endVector.x - arrowHeadLength * Math.sin(-alpha - arrowHeadAngle),
   endVector.y - arrowHeadLength * Math.cos(-alpha - arrowHeadAngle));
}

function getDownHeadVector(endVector, alpha){
  return new THREE.Vector3(endVector.x - arrowHeadLength * Math.sin(Math.PI - alpha + arrowHeadAngle),
   endVector.y - arrowHeadLength * Math.cos(Math.PI - alpha + arrowHeadAngle));
}
