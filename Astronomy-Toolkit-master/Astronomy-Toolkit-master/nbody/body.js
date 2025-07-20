var MAX_POINTS = 1000;

function Body(mass, x, y, velocityX, velocityY, density, color){
	this.mass = mass;
	this.x = x;
	this.y = y;
	this.velocityX = velocityX;
	this.velocityY = velocityY;
	this.trailVertices = [];
	this.accelerationX = 0;
	this.accelerationY = 0;
	this.density = density;
	this.size = this.initialSize = this.getRadius(mass, density);
	this.color = color;
	this.mesh = this.createBodyMesh();
	this.trail = this.createTrail();

	this.index = 0;
}

Body.prototype.dispose = function(){
	this.mesh.material.dispose();
	this.mesh.geometry.dispose();
	this.trail.material.dispose();
	this.trail.geometry.dispose();
	delete(this.mesh);
	delete(this.trail);
}

Body.prototype.addTo = function(scene){
	scene.add(this.mesh);
	scene.add(this.trail);
}

Body.prototype.removeFrom = function(scene){
	scene.remove(this.mesh);
	scene.remove(this.trail);
}

Body.prototype.addPosition = function(){
	this.positionsX.push(this.x);
	this.positionsY.push(this.y);
	var length = this.positionsX.length;
	if(length > trailLength){
		for(var i = 0; i < length - trailLength; i++){
			this.positionsX.shift();
			this.positionsY.shift();
		}
	}

};

Body.prototype.updateRadius = function(){
	this.size = this.getRadius(this.mass, this.density);
	var scale = this.size / this.initialSize;
	this.mesh.scale.set(scale, scale, scale);
}

Body.prototype.getRadius = function(bodyMass, bodyDensity){
	return Math.pow(bodyMass / bodyDensity, 1/3);
}

Body.prototype.update = function(step, trailLimit, scene){
	this.velocityX += step * this.accelerationX;
	this.velocityY += step * this.accelerationY;

	this.x += this.velocityX * step;
	this.y += this.velocityY * step;

	this.mesh.position.set(this.x, this.y, 0);

	this.updateTrail(this.x, this.y, -0.01, trailLimit);
}

Body.prototype.updateTrail = function(x, y, z, limit){
	var positions = this.trail.geometry.attributes.position.array;

	var diff = this.index / 3.0 - limit;
	if(diff >= 0){
		for(var i = 0; i < limit * 3 - 3; i++){
			positions[i] = positions[i + 3 * (diff + 1)];
		}
		this.index = limit * 3 - 3;
	}

	positions[this.index++] = x;
	positions[this.index++] = y;
	positions[this.index++] = z;
	this.trail.geometry.setDrawRange(0, this.index / 3.0);
	this.trail.geometry.attributes.position.needsUpdate = true;
	this.trail.geometry.computeBoundingSphere();

}

Body.prototype.collideWith = function(anotherBody){
	var totalMass = this.mass + anotherBody.mass;
	this.velocityX = (this.mass * this.velocityX + anotherBody.mass * anotherBody.velocityX) / totalMass;
	this.velocityY = (this.mass * this.velocityY + anotherBody.mass * anotherBody.velocityY) / totalMass;
	this.mass = totalMass;
	this.updateRadius();
}

Body.prototype.resetAcceleration = function(){
	this.accelerationY = 0;
	this.accelerationX = 0;
}

Body.prototype.setPositions = function(positionsX, positionsY){
	this.positionsX = positionsX;
	this.positionsY = positionsY;
}

Body.prototype.setMass = function(mass){
	this.mass = mass;
}

Body.prototype.distanceToBody = function(body){
	return Math.sqrt((this.x - body.x) * (this.x - body.x) + (this.y - body.y) * (this.y - body.y));
}

Body.prototype.distanceTo = function(posX, posY){
	return Math.sqrt((this.x - posX) * (this.x - posX) + (this.y - posY) * (this.y - posY));
}

Body.prototype.createBodyMesh = function(){
	material = new THREE.MeshBasicMaterial( { color: this.color, side: THREE.DoubleSide } );
	geometry = new THREE.CircleGeometry(this.size, 50 );
	mesh = new THREE.Mesh( geometry, material );
	mesh.position.set(this.x, this.y, 0);
	return mesh;

}

Body.prototype.createTrail = function(){
	var material = new THREE.LineBasicMaterial({color: this.color});
	var geometry = new THREE.BufferGeometry;
	this.trailVertices = new Float32Array(MAX_POINTS * 3);3
	geometry.addAttribute('position', new THREE.BufferAttribute(this.trailVertices, 3));
	geometry.vertices = this.trailVertices;
	var mesh = new THREE.Line( geometry, material );
	//mesh.frustumCulled = false;
	return mesh;
}
