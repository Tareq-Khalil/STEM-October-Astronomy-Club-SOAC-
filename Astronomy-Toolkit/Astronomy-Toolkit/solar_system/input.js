var datGUI;
var pauseButton;
var followDropDown;
var followedObjectName = "None";
var followableNames = []

var showAsteroids = true;
var showSkybox = true;

var searchString = "Ceres";
var searchResultsShow = [];
var searchResultsFolder;
var searchResultsSprites = [];
var searchResultsOrbits = [];
var resultIndex = 0;

createFollowablePlanetsArray();

function loadGUI(){
  datGUI = new dat.GUI({autoplace: false});
  var container = document.getElementById("guiContainer");
  container.appendChild(datGUI.domElement);

  datGUI.add(this, 'showHelp').name("Help")
  datGUI.add(this, 'centuriesPerSecond', -0.1, +0.1).name("Speed").step(0.00001).listen();
  pauseButton = datGUI.add(this, 'tooglePause').name("Pause").listen();
  datGUI.add(this, 'showAsteroids').name("Show Belts").onChange(toogleAsteroidBelts);
  datGUI.add(this, 'showSkybox').name("Show Skybox").onChange(toogleSkybox);

  followDropDown = datGUI.add(this, 'followedObjectName', followableNames).name("Follow").onChange(followListener).listen();

  searchFolder = datGUI.addFolder("Search");
  searchFolder.add(this, 'searchString').name("Designation");
  searchFolder.add(this, 'search').name("Search").listen();
  searchFolder.open();
}

function toogleAsteroidBelts(){
  if(showAsteroids){
    enableAsteroidBelts();
  }
  else{
    disableAsteroidBelts();
  }
}

function showHelp(){
  var aboutDiv = document.getElementById('about');
  var bellowDiv = document.getElementById('bellowAbout');

  aboutDiv.classList.add('showAbout');
  bellowDiv.classList.add('blurBellow');

  doNotUpdate = true;
}

function closeHelp(){
  var aboutDiv = document.getElementById('about');
  var bellowDiv = document.getElementById('bellowAbout');

  aboutDiv.classList.remove('showAbout');
  bellowDiv.classList.remove('blurBellow');

  doNotUpdate = false;
}

function toogleSkybox(){
  if(showSkybox){
    scene.add(skyboxMesh);
  }
  else{
    scene.remove(scene.getObjectById(skyboxMesh.id));
  }
}

function enableAsteroidBelts(){
  for(sprite of celestialBodySprites){
    if(sprite.flag == ASTEROID_BELT_FLAG){
      sprite.addTo(scene);
      sprite.shouldDisplay = true;
    }
  }
}

function disableAsteroidBelts(){
  for(sprite of celestialBodySprites){
    if(sprite.flag == ASTEROID_BELT_FLAG){
      sprite.removeFrom(scene);
      sprite.shouldDisplay = false;
    }
  }
}

function search(){
  resultIndex = 0;
  searchResultsShow = [];
  searchResultsSprites = [];
  searchResultsOrbits = [];
  deleteGUI();
  loadGUI();
  searchResultsFolder = datGUI.addFolder("Search Results");
  searchResultsFolder.add(this, 'showAll').name("Show All");
  searchResultsFolder.add(this, 'hideAll').name("Hide All");
  getSearchResults(searchString);
}

function showAll(){
  for (var i = 0; i < searchResultsShow.length; i++){
    searchResultsShow[i] = true;
  }
  updateSearchResult();
}

function hideAll(){
  for (var i = 0; i < searchResultsShow.length; i++){
    searchResultsShow[i] = false;
  }
  updateSearchResult();
}

function addResult(resultSprite, resultOrbit){
  resultSprite.shouldDisplay = true;
  searchResultsShow.push(true);
  searchResultsFolder.add(searchResultsShow, resultIndex).name(resultSprite.planet.name).listen().onChange(updateSearchResult);
  searchResultsSprites.push(resultSprite);
  searchResultsOrbits.push(resultOrbit);
  updateSearchResult();
  resultIndex++;
}

function finishSearch(){
  searchResultsFolder.open();
}

function deleteGUI(){
  var container = document.getElementById("guiContainer");
  container.removeChild(datGUI.domElement);

}

function updateSearchResult(){
  for(var i = 0; i < searchResultsShow.length; i++){
    if(searchResultsShow[i]){
      searchResultsSprites[i].addTo(scene);
      scene.add(searchResultsOrbits[i].mesh);
      searchResultsSprites[i].shouldDisplay = true;
    }
    else{
      searchResultsSprites[i].removeFrom(scene);
      scene.remove(scene.getObjectById(searchResultsOrbits[i].mesh.id));
      searchResultsSprites[i].shouldDisplay = false;
    }

  }
}

function tooglePause(){
  if(centuriesPerSecond != 0){
    pauseButton.name("Start");
    centuriesPerSecond = 0;
  }
  else{
    pauseButton.name("Pause");
    centuriesPerSecond = 0.0002;
  }
}

function createFollowablePlanetsArray(){
  followableNames = ["None"];
  for(body of followablePlanets){
      followableNames.push(body.name);
  }
}

function followListener(){
  if(followedObjectName == "None"){
    followedObjectId = -1;
  }
  else{
    for(var i = 0; i < celestialBodySprites.length; i++){
      if(celestialBodySprites[i].planet.name == followedObjectName){
        followedObjectId = i;
        console.log(i);
        break;
      }
    }
  }
}

window.onkeydown = function (e) {
    var code = e.keyCode ? e.keyCode : e.which;
    if (code === 32) { //SPACE key
      tooglePause();
    }
    else if(code === 39){ //RIGHT ARROW
      centuriesPerSecond *= 1.1;
    }
    else if(code === 37){  //LEFT ARROW
      centuriesPerSecond /= 1.1;
    }

    else if(code === 79){
      toogleOrbits();
    }

    if(code >= 49 && code <= 57){
      isZooming = true;
      zoomedFrames = 0;
      dollyOut(5);
    }

};
