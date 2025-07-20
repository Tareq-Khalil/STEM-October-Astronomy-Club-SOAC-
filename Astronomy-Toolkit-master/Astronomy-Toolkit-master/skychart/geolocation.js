var x = document.getElementById('main');

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(writeVariables);
    } else {
       
    }
}
function writeVariables(position) {
   lat = (position.coords.latitude)*Math.PI/180;
   longitude = (position.coords.longitude)*Math.PI/180;
   document.cookie="lat="+lat;
   document.cookie="lon="+longitude;
   checkifUndefined();
   document.getElementById("latitude").value=Math.round(lat*180000/Math.PI)/1000;
   document.getElementById("long").value=Math.round(longitude*180000/Math.PI)/1000;
}

function displayError() {
    ctx.fillStyle = 'white';
    ctx.fillText("Please enbale geolocation in your browser! :)",350,350)
}

function getManualLocation(){
        var data = document.getElementById("latitude").value;
        lat=parseFloat(data)*Math.PI/180;
        data = document.getElementById("long").value;
        longitude=parseFloat(data)*Math.PI/180;
        checkifUndefined();
        document.cookie="lat="+lat;
        document.cookie="lon="+longitude;
}

function startUp(){
    if (getCookie("lat")!="" && getCookie("lon")!="") {
        lat = parseFloat(getCookie("lat"));
        longitude = parseFloat(getCookie("lon"));
        document.getElementById("latitude").value=Math.round(lat*180000/Math.PI)/1000;
        document.getElementById("long").value=Math.round(longitude*180000/Math.PI)/1000;
    }
    else getLocation();
}

function checkifUndefined(){
    if (lat == Math.PI/2) lat=1.57079632498;
    else if (lat == -Math.PI/2) lat=-1.57079632498;
           
}