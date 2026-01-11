  const element = document.body;
localStorage.setItem("darkStatic", "dark");

if (localStorage.getItem("mode") === null) {
  localStorage.setItem("mode", "dark");
}
else if (localStorage.getItem("mode") !== localStorage.getItem("darkStatic"))  {
  element.classList.toggle("light-mode");
}

function modeFunction() {
   
  
  if (localStorage.getItem("mode") == localStorage.getItem("darkStatic")) {
    localStorage.setItem("mode", "light");
    console.log(localStorage.getItem("mode"))
  }
  else{
    localStorage.setItem("mode", "dark");
    localStorage.getItem("mode")
    console.log(localStorage.getItem("mode"))
  }
   element.classList.toggle("light-mode");
    
}