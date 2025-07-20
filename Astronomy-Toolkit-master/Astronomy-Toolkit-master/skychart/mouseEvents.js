showSettings=true;
showDate=true;
showAbout = false;
var minTime = -14358470400000;
var maxTime =  48755433600000;

function inputTime(){
  //make date in ISO8601 format
  timeOffset = new Date().getTimezoneOffset();
  var dateString = document.getElementById("year").value+"-";
  dateString+=("0"+document.getElementById("month").value).slice(-2)+"-";
  dateString+=("0"+document.getElementById("day").value).slice(-2)+"T";
  dateString+=("0"+document.getElementById("hour").value).slice(-2)+":";
  dateString+=("0"+document.getElementById("minute").value).slice(-2)+":";
  dateString+=("0"+document.getElementById("second").value).slice(-2)+"Z";
  var tempUnix=new Date(dateString);
  if(tempUnix && tempUnix<maxTime && tempUnix>minTime)
    unixTime = Date.parse(dateString)+timeOffset*60000;
}

function dateInput() {
  var month=document.getElementById("month").value;
  var year=document.getElementById("year").value;
  if(month == 2 && isLeapYear(year)) document.getElementById("day").max=29;
  else if(month == 2) document.getElementById("day").max=28;
  else if(month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12) document.getElementById("day").max=31;
  else if(month == 4 || month == 6 || month == 9 || month == 11) document.getElementById("day").max=30;
}

function isLeapYear(year) {
  var leap=false;
  if((year%4 == 0 && year%400 == 0) || (year%4 == 0 && year%100 != 0)) leap = true;
  return leap;
}
 
function writeDate(){
  var datenow=new Date(unixTime);
  document.getElementById("year").value=datenow.getFullYear();
  document.getElementById("month").value=("0"+(datenow.getMonth()+1)).slice(-2);
  document.getElementById("day").value=("0"+datenow.getDate()).slice(-2);
  document.getElementById("hour").value=("0"+datenow.getHours()).slice(-2);
  document.getElementById("minute").value=("0"+datenow.getMinutes()).slice(-2);
  document.getElementById("second").value=("0"+datenow.getSeconds()).slice(-2);
}

function timeNow(){
  unixTime=Date.now();
}

function timeAdd(seconds){
  //max date is 01.01.2215; min date is 01.01.1815
  unixTime+=seconds*1000;
  drawStuff();
}

function monthCounter(sign){
  var month=document.getElementById("month").value;
  if(month == 2 && isLeapYear(year)) unixTime+=29*24*3600*1000*sign;
  else if(month == 2) unixTime+=28*24*3600*1000*sign;
  else if(month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12) unixTime+=31*24*3600*1000*sign;
  else if(month == 4 || month == 6 || month == 9 || month == 11) unixTime+=30*24*3600*1000*sign;
  drawStuff();
}

function yearCounter(sign) {
    var year=document.getElementById("year").value;
    unixTime+=365*24*3600*1000*sign;
    if(isLeapYear(year)) unixTime+=24*3600*1000*sign;
    drawStuff();
}

function checkArrows(ev, seconds){
  if(ev.keyCode == 38 && unixTime < maxTime){
    timeAdd(seconds);
    writeDate();
  }
  else if(ev.keyCode == 40 && unixTime > minTime){
    timeAdd(-seconds);
    writeDate();
  }
}


function checkYear(ev, sign){
  if(ev.keyCode == 38 && unixTime < maxTime){
    yearCounter(1);
    writeDate();
  }
  else if(ev.keyCode == 40 && unixTime > minTime){
    yearCounter(-1);
    writeDate();
  }
}

function checkMonth(ev){
  if(ev.keyCode == 38 && unixTime < maxTime){
    monthCounter(1);
    writeDate();
  }
  else if(ev.keyCode == 40 && unixTime > minTime){
    monthCounter(-1);
    writeDate();
  }
}

function toggleSettings(){
  var settings = document.getElementById("settings");
  showSettings = !showSettings;
  if(showSettings){
     settings.classList.remove("moveSettings");
     document.getElementById("showButtonSettings").value= "Hide";
  }
  else{
    settings.classList.add("moveSettings");
    document.getElementById("showButtonSettings").value="Settings";
  }
}

function toggleDate(){
  var date = document.getElementById("dateinput");
  showDate = !showDate;
  if(showDate){
     date.classList.remove("moveDate");
     document.getElementById("showButtonDate").value= "Hide";
  }
  else{
    date.classList.add("moveDate");
    document.getElementById("showButtonDate").value="Date";
  }
}

function toggleAbout(){
  var about = document.getElementById("about");
 
  showAbout = !showAbout;
  if (showAbout) {
    
    about.classList.add("moveAbout");
    document.getElementById("showAbout").value= "Hide";
  }
  
  else{
    about.classList.remove("moveAbout");
    document.getElementById("showAbout").value = "About";
  }
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}