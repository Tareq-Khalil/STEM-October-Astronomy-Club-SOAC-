'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var G = 6.67428e-11;
var AU = 149.6e6 * 1000;
var scalevalue = 50;
var speedvalue = 5;
var SCALE = scalevalue / AU;
var timestep = 24 * 3600 / speedvalue;

var viewx = 0;
var viewy = 0;
var mass = 3e+24;
var massIndex = 24;

var _sling = null;
var rect;

var Scene = function () {
  function Scene() {
    var size = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : { width: 900, height: 600 };

    _classCallCheck(this, Scene);

    this.objects = [];
    this.tickInterval = undefined;
    this.width = size.width;
    this.height = size.height;

    var canvas = document.getElementById('canvas');
    canvas.width = $(window).width();
    canvas.height = $(window).height();
    this.canvas = canvas;
    document.body.appendChild(canvas);
    this.context = canvas.getContext('2d');
    rect = canvas.getBoundingClientRect();
  }

  _createClass(Scene, [{
    key: 'addObject',
    value: function addObject(object) {
      this.objects.push(object);
    }
  }, {
    key: 'removeObject',
    value: function removeObject(obj) {
      this.objects = this.objects.filter(function (item) {
        return item !== obj;
      });
    }
  }, {
    key: 'resetObject',
    value: function resetObject() {
      this.objects = [];
    }

    // returns force exerted on this body by the other body

  }, {
    key: 'attraction',
    value: function attraction(self, other) {
      if (self === other) return false;

      // compute distance between bodies
      var sx = self.options.px;
      var sy = self.options.py;
      var ox = other.options.px;
      var oy = other.options.py;
      var dx = ox - sx;
      var dy = oy - sy;
      var d = Math.sqrt(Math.pow(dx, 2) + Math.pow(dy, 2));

      if (d == 0) return [0, 0];

      // compute force
      var F = G * self.options.mass * other.options.mass / Math.pow(d, 2);

      // compute direction
      var o = Math.atan2(dy, dx);
      var Fx = Math.cos(o) * F;
      var Fy = Math.sin(o) * F;

      //console.log(sx, sy, ox, oy, dx, dy, d, F, o, Fx, Fy);
      return [Fx, Fy];
    }
  }, {
    key: 'render',
    value: function render(paused) {
      var _this = this;

      this.context.clearRect(0, 0, this.context.canvas.width, this.context.canvas.height);
      if (!paused) {
        (function () {
          var changes = [];
          _this.objects.forEach(function (o) {
            var total = [0, 0];
            _this.objects.forEach(function (o2) {
              if (o !== o2) {
                var tmp = _this.attraction(o, o2);
                total[0] += tmp[0];
                total[1] += tmp[1];
              }
            });
            changes.push(total);
          });
          changes.forEach(function (change, index) {
            _this.objects[index].updateV(change[0], change[1]);
          });
        })();
      }
      this.objects.forEach(function (o) {
        o.draw(_this.context);
      });
      if (_sling != null) _sling.draw(this.context);
    }
  }, {
    key: 'sling',
    value: function sling() {
      window.addEventListener('mousedown', function (e) {
        _sling = new Slingshot({
          px: e.pageX - rect.left,
          py: e.pageY - rect.top
        });
        _sling.followToMouse();
      });

      window.addEventListener('mouseup', function (e) {
        if (_sling.options.px != e.pageX - rect.left) {
          _sling.shoot();
        }
        _sling.unfollow();
        _sling = null;
      });

      document.body.onkeyup = function (e) {};

      document.body.onkeyup = function (e) {
        console.log(e.keyCode);
        if (e.keyCode == 66) {
          console.log(mousex, mousey);
          var blackhole = new Body({
            name: "Blackhole",
            mass: 1.989e30 * 5,
            vx: 0,
            vy: 0,
            px: (mousex - scene.canvas.width / 2 - viewx) / SCALE,
            py: (mousey - scene.canvas.height / 2 - viewy) / SCALE,
            radius: 3,
            linewidth: 3,
            strokestyle: 'white'
          });
          scene.addObject(blackhole);
          console.log(scene.objects);
        } else if (e.keyCode == 32) {
          var body = new Body({
            name: "White hole",
            mass: 1.989e30,
            vx: 20000,
            vy: 0,
            px: 0,
            py: -1 * AU,
            radius: 5,
            linewidth: 1,
            strokestyle: '#FF0'
          });
          scene.addObject(body);
          var body2 = new Body({
            name: "White hole",
            mass: 1.989e30,
            vx: -20000,
            vy: 0,
            px: 0,
            py: 1 * AU,
            radius: 5,
            linewidth: 1,
            strokestyle: '#FF0'
          });
          scene.addObject(body2);
        }
      };
    }
  }]);

  return Scene;
}();

var Body = function () {
  function Body(options) {
    _classCallCheck(this, Body);

    this.options = options;
  }

  _createClass(Body, [{
    key: 'updateV',
    value: function updateV(x, y) {
      this.options.vx += x / this.options.mass * timestep;
      this.options.vy += y / this.options.mass * timestep;
      if (Math.sqrt(Math.pow(this.options.vy, 2) + Math.pow(this.options.vx, 2)) > 299792458) {
        console.log("Error exceeded speed of light", Math.sqrt(Math.pow(this.options.vy, 2) + Math.pow(this.options.vx, 2)));
      }
      this.updateP();
    }
  }, {
    key: 'updateP',
    value: function updateP() {
      this.options.px += this.options.vx * timestep;
      this.options.py += this.options.vy * timestep;
    }
  }, {
    key: 'draw',
    value: function draw(context) {
      if (context == null) return false;

      context.lineWidth = this.options.linewidth;
      context.fillStyle = this.options.strokestyle;

      context.beginPath();
      context.arc(this.options.px * SCALE + scene.canvas.width / 2 + viewx, this.options.py * SCALE + scene.canvas.height / 2 + viewy, this.options.radius, 0, 2 * Math.PI, false);
      context.fill();
    }
  }]);

  return Body;
}();

var Slingshot = function () {
  function Slingshot(options) {
    _classCallCheck(this, Slingshot);

    this.options = options;
  }

  _createClass(Slingshot, [{
    key: 'unfollow',
    value: function unfollow() {
      var _this2 = this;

      window.removeEventListener('mousemove', function (e) {
        _this2.options.px2 = e.pageX - rect.left;
        _this2.options.py2 = e.pageY - rect.top;
      });
    }
  }, {
    key: 'followToMouse',
    value: function followToMouse() {
      var _this3 = this;

      window.addEventListener('mousemove', function (e) {
        _this3.options.px2 = e.pageX - rect.left;
        _this3.options.py2 = e.pageY - rect.top;
      });
    }
  }, {
    key: 'shoot',
    value: function shoot() {
      var size = massIndex / 2 - 9 + Math.random() * 3;
      var body = new Body({
        name: randomName(Math.ceil(Math.random() * 5) + 3),
        mass: mass,
        vx: (this.options.px - this.options.px2) * 500,
        vy: (this.options.py - this.options.py2) * 500,
        px: (this.options.px - scene.canvas.width / 2 - viewx) / SCALE,
        py: (this.options.py - scene.canvas.height / 2 - viewy) / SCALE,
        radius: Math.ceil(size),
        linewidth: 1,
        strokestyle: '#' + (Math.floor(Math.random() * 156) + 100).toString(16) + (Math.floor(Math.random() * 156) + 100).toString(16) + (Math.floor(Math.random() * 156) + 100).toString(16)
      });
      scene.addObject(body);
    }
  }, {
    key: 'draw',
    value: function draw(context) {
      this.context = context;
      if (this.context == null) return false;

      this.context.beginPath();
      this.context.moveTo(this.options.px, this.options.py);
      context.lineTo(this.options.px2, this.options.py2);
      this.context.strokeStyle = '#FF4136';
      context.stroke();
    }
  }]);

  return Slingshot;
}();

function randomName(length) {
  var x = "";
  var possible = "abcdefghijklmnopqrstuvwxyz0123456789";
  for (var i = 0; i < length; i++) {
    x += possible.charAt(Math.floor(Math.random() * possible.length));
    if (i == 0) x.toUpperCase();
  }
  return x;
}

var scene = new Scene();
scene.sling();

function setState(id) {
  scalevalue = 50;
  SCALE = scalevalue / AU;
  viewx = 0;
  viewy = 0;

  scene.resetObject();
  if (id === 1) {
    var sun = new Body({
      name: "Sun",
      mass: 1.989e30,
      vx: 0,
      vy: 0,
      px: 0,
      py: 0,
      radius: 8,
      linewidth: 3,
      strokestyle: 'yellow'
    });

    var earth = new Body({
      name: "Earth",
      mass: 5.972e24,
      vx: 0,
      vy: 30290,
      px: -147.09e6 * 1000,
      py: 0,
      radius: 3,
      linewidth: 1,
      strokestyle: '#60a6d4'
    });

    var moon = new Body({
      name: "Moon",
      mass: 0.07346e24,
      vx: 0,
      vy: 31366,
      px: -147.4533e6 * 1000,
      py: 0,
      radius: 1,
      linewidth: 1,
      strokestyle: '#E2E6E7'
    });

    var mercury = new Body({
      name: "Mercury",
      mass: 0.330e24,
      vx: 0,
      vy: 58980,
      px: -46e6 * 1000,
      py: 0,
      radius: 2,
      linewidth: 1,
      strokestyle: '#736E52'
    });

    var jupiter = new Body({
      name: "Jupiter",
      mass: 1898.19e24,
      vx: 0,
      vy: 13720,
      px: -740.52e6 * 1000,
      py: 0,
      radius: 10,
      linewidth: 1,
      strokestyle: '#CEA089'
    });

    var saturn = new Body({
      name: "saturn",
      mass: 568.34e24,
      vx: 0,
      vy: 10180,
      px: -1352.55e6 * 1000,
      py: 0,
      radius: 5,
      linewidth: 1,
      strokestyle: '#91987B'
    });

    var uranus = new Body({
      name: "uranus",
      mass: 86.813e24,
      vx: 0,
      vy: 7110,
      px: -2741.30e6 * 1000,
      py: 0,
      radius: 5,
      linewidth: 1,
      strokestyle: '#90979E'
    });

    var neptune = new Body({
      name: "Neptune",
      mass: 102.413e24,
      vx: 0,
      vy: 5500,
      px: -4444.45e6 * 1000,
      py: 0,
      radius: 7,
      linewidth: 1,
      strokestyle: '#2D30CC'
    });

    var mars = new Body({
      name: "Mars",
      mass: 0.64171e24,
      vx: 0,
      vy: 26500,
      px: -206.62e6 * 1000,
      py: 0,
      radius: 2,
      linewidth: 1,
      strokestyle: '#BF5B2D'
    });

    var venus = new Body({
      name: "Venus",
      mass: 4.87e24,
      vx: 0,
      vy: 35260,
      px: -107.48e6 * 1000,
      py: 0,
      radius: 2,
      linewidth: 1,
      strokestyle: '#CFAA60'
    });

    scene.addObject(sun);
    scene.addObject(earth);
    scene.addObject(moon);
    scene.addObject(mercury);
    scene.addObject(mars);
    scene.addObject(jupiter);
    scene.addObject(saturn);
    scene.addObject(uranus);
    scene.addObject(neptune);
    scene.addObject(venus);
  }
  scene.render();
}

$(window).bind("resize", function () {
  scene.canvas.width = $(window).width();
  scene.canvas.height = $(window).height();
});

var mousex = 0;
var mousey = 0;

$(window).mousemove(function (e) {
  mousex = e.pageX - rect.left;
  mousey = e.pageY - rect.top;
});

$(window).keydown(function (e) {
  console.log(e.which);
  switch (e.which) {
    case 171:
      if (scalevalue < 5) {
        scalevalue = scalevalue * 2;
      } else {
        scalevalue += 3;
      }
      SCALE = scalevalue / AU;
      break;
    case 173:
      if (scalevalue < 5) {
        scalevalue = scalevalue / 2;
      } else {
        scalevalue -= 3;
      }
      SCALE = scalevalue / AU;
      break;
    case 37:
      viewx += 10;
      break;
    case 39:
      viewx -= 10;
      break;
    case 38:
      viewy += 10;
      break;
    case 40:
      viewy -= 10;
      break;
    case 82:
      setMass(24);
      setState(1);
      break;
    case 80:
      running = !running;
      break;

    case 72:
      scene.removeObject();
      break;

    case 48:
      setMass(18);
      break;
    case 49:
      setMass(20);
      break;
    case 50:
      setMass(22);
      break;
    case 51:
      setMass(24);
      break;
    case 52:
      setMass(26);
      break;
    case 53:
      setMass(28);
      break;
    case 54:
      setMass(30);
      break;
    case 55:
      setMass(32);
      break;
    case 56:
      setMass(34);
      break;
    case 57:
      setMass(36);
      break;

    default:
      return;
  }
  e.preventDefault();
});

function setMass(m) {
  massIndex = m;
  mass = eval('3e+' + m);
  document.querySelector("#mass").innerHTML = '3e+' + m;
}

function changespeed(e) {
  speedvalue = e;
  timestep = 24 * 3600 / speedvalue;
  document.querySelector("#time").innerHTML = Math.round(fps / speedvalue * 10) / 10 + 'earthdays / second';
}

function changefps(e) {
  fps = e;
}

var fps = 60;

var running = false;

function draw() {
  setTimeout(function () {
    requestAnimationFrame(draw);

    scene.render(running);
  }, 1000 / fps);
}
setState(1, scene);
draw();
