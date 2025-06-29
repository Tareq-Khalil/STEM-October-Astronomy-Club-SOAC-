/**
 * jQuery Ripples plugin v0.6.3 / https://github.com/sirxemic/jquery.ripples
 * MIT License
 * @author sirxemic / https://sirxemic.com/
 */

!(function (e, t) {
    "object" == typeof exports && "undefined" != typeof module
      ? t(require("jquery"))
      : "function" == typeof define && define.amd
      ? define(["jquery"], t)
      : t(e.$);
  })(this, function (e) {
    "use strict";
    function t(e) {
      return "%" == e[e.length - 1];
    }
    function r(e) {
      var t = e.split(" ");
      if (1 !== t.length)
        return t.map(function (t) {
          switch (e) {
            case "center":
              return "50%";
            case "top":
            case "left":
              return "0";
            case "right":
            case "bottom":
              return "100%";
            default:
              return t;
          }
        });
      switch (e) {
        case "center":
          return ["50%", "50%"];
        case "top":
          return ["50%", "0"];
        case "bottom":
          return ["50%", "100%"];
        case "left":
          return ["0", "50%"];
        case "right":
          return ["100%", "50%"];
        default:
          return [e, "50%"];
      }
    }
    function i(e, t, r) {
      function i(e, t) {
        var r = s.createShader(e);
        if (
          (s.shaderSource(r, t),
          s.compileShader(r),
          !s.getShaderParameter(r, s.COMPILE_STATUS))
        )
          throw new Error("compile error: " + s.getShaderInfoLog(r));
        return r;
      }
      var o = {};
      if (
        ((o.id = s.createProgram()),
        s.attachShader(o.id, i(s.VERTEX_SHADER, e)),
        s.attachShader(o.id, i(s.FRAGMENT_SHADER, t)),
        s.linkProgram(o.id),
        !s.getProgramParameter(o.id, s.LINK_STATUS))
      )
        throw new Error("link error: " + s.getProgramInfoLog(o.id));
      (o.uniforms = {}),
        (o.locations = {}),
        s.useProgram(o.id),
        s.enableVertexAttribArray(0);
      for (
        var n, a, u = /uniform (\w+) (\w+)/g, h = e + t;
        null != (n = u.exec(h));
  
      )
        (a = n[2]), (o.locations[a] = s.getUniformLocation(o.id, a));
      return o;
    }
    function o(e, t) {
      s.activeTexture(s.TEXTURE0 + (t || 0)), s.bindTexture(s.TEXTURE_2D, e);
    }
    function n(e) {
      var t = /url\(["']?([^"']*)["']?\)/.exec(e);
      return null == t ? null : t[1];
    }
    function a(e) {
      return e.match(/^data:/);
    }
    var s,
      u = (e = e && "default" in e ? e.default : e)(window),
      h = (function () {
        function e(e, t, i) {
          var o = "OES_texture_" + e,
            n = o + "_linear",
            a = n in r,
            s = [o];
          return (
            a && s.push(n),
            { type: t, arrayType: i, linearSupport: a, extensions: s }
          );
        }
        var t = document.createElement("canvas");
        if (!(s = t.getContext("webgl") || t.getContext("experimental-webgl")))
          return null;
        var r = {};
        if (
          ([
            "OES_texture_float",
            "OES_texture_half_float",
            "OES_texture_float_linear",
            "OES_texture_half_float_linear",
          ].forEach(function (e) {
            var t = s.getExtension(e);
            t && (r[e] = t);
          }),
          !r.OES_texture_float)
        )
          return null;
        var i = [];
        i.push(e("float", s.FLOAT, Float32Array)),
          r.OES_texture_half_float &&
            i.push(
              e("half_float", r.OES_texture_half_float.HALF_FLOAT_OES, null)
            );
        var o = s.createTexture(),
          n = s.createFramebuffer();
        s.bindFramebuffer(s.FRAMEBUFFER, n),
          s.bindTexture(s.TEXTURE_2D, o),
          s.texParameteri(s.TEXTURE_2D, s.TEXTURE_MIN_FILTER, s.NEAREST),
          s.texParameteri(s.TEXTURE_2D, s.TEXTURE_MAG_FILTER, s.NEAREST),
          s.texParameteri(s.TEXTURE_2D, s.TEXTURE_WRAP_S, s.CLAMP_TO_EDGE),
          s.texParameteri(s.TEXTURE_2D, s.TEXTURE_WRAP_T, s.CLAMP_TO_EDGE);
        for (var a = null, u = 0; u < i.length; u++)
          if (
            (s.texImage2D(
              s.TEXTURE_2D,
              0,
              s.RGBA,
              32,
              32,
              0,
              s.RGBA,
              i[u].type,
              null
            ),
            s.framebufferTexture2D(
              s.FRAMEBUFFER,
              s.COLOR_ATTACHMENT0,
              s.TEXTURE_2D,
              o,
              0
            ),
            s.checkFramebufferStatus(s.FRAMEBUFFER) === s.FRAMEBUFFER_COMPLETE)
          ) {
            a = i[u];
            break;
          }
        return a;
      })(),
      d = (function (e, t) {
        try {
          return new ImageData(e, t);
        } catch (r) {
          return document
            .createElement("canvas")
            .getContext("2d")
            .createImageData(e, t);
        }
      })(32, 32);
    e("head").prepend(
      "<style>.jquery-ripples { position: relative; z-index: 0; }</style>"
    );
    var c = function (t, r) {
      function i() {
        o.destroyed || (o.step(), requestAnimationFrame(i));
      }
      var o = this;
      (this.$el = e(t)),
        (this.interactive = r.interactive),
        (this.resolution = r.resolution),
        (this.textureDelta = new Float32Array([
          1 / this.resolution,
          1 / this.resolution,
        ])),
        (this.perturbance = r.perturbance),
        (this.dropRadius = r.dropRadius),
        (this.crossOrigin = r.crossOrigin),
        (this.imageUrl = r.imageUrl);
      var n = document.createElement("canvas");
      (n.width = this.$el.innerWidth()),
        (n.height = this.$el.innerHeight()),
        (this.canvas = n),
        (this.$canvas = e(n)),
        this.$canvas.css({
          position: "absolute",
          left: 0,
          top: 0,
          right: 0,
          bottom: 0,
          zIndex: -1,
        }),
        this.$el.addClass("jquery-ripples").append(n),
        (this.context = s =
          n.getContext("webgl") || n.getContext("experimental-webgl")),
        h.extensions.forEach(function (e) {
          s.getExtension(e);
        }),
        (this.updateSize = this.updateSize.bind(this)),
        e(window).on("resize", this.updateSize),
        (this.textures = []),
        (this.framebuffers = []),
        (this.bufferWriteIndex = 0),
        (this.bufferReadIndex = 1);
      for (
        var a = h.arrayType,
          u = a ? new a(this.resolution * this.resolution * 4) : null,
          d = 0;
        d < 2;
        d++
      ) {
        var c = s.createTexture(),
          f = s.createFramebuffer();
        s.bindFramebuffer(s.FRAMEBUFFER, f),
          s.bindTexture(s.TEXTURE_2D, c),
          s.texParameteri(
            s.TEXTURE_2D,
            s.TEXTURE_MIN_FILTER,
            h.linearSupport ? s.LINEAR : s.NEAREST
          ),
          s.texParameteri(
            s.TEXTURE_2D,
            s.TEXTURE_MAG_FILTER,
            h.linearSupport ? s.LINEAR : s.NEAREST
          ),
          s.texParameteri(s.TEXTURE_2D, s.TEXTURE_WRAP_S, s.CLAMP_TO_EDGE),
          s.texParameteri(s.TEXTURE_2D, s.TEXTURE_WRAP_T, s.CLAMP_TO_EDGE),
          s.texImage2D(
            s.TEXTURE_2D,
            0,
            s.RGBA,
            this.resolution,
            this.resolution,
            0,
            s.RGBA,
            h.type,
            u
          ),
          s.framebufferTexture2D(
            s.FRAMEBUFFER,
            s.COLOR_ATTACHMENT0,
            s.TEXTURE_2D,
            c,
            0
          ),
          this.textures.push(c),
          this.framebuffers.push(f);
      }
      (this.quad = s.createBuffer()),
        s.bindBuffer(s.ARRAY_BUFFER, this.quad),
        s.bufferData(
          s.ARRAY_BUFFER,
          new Float32Array([-1, -1, 1, -1, 1, 1, -1, 1]),
          s.STATIC_DRAW
        ),
        this.initShaders(),
        this.initTexture(),
        this.setTransparentTexture(),
        this.loadImage(),
        s.clearColor(0, 0, 0, 0),
        s.blendFunc(s.SRC_ALPHA, s.ONE_MINUS_SRC_ALPHA),
        (this.visible = !0),
        (this.running = !0),
        (this.inited = !0),
        (this.destroyed = !1),
        this.setupPointerEvents(),
        requestAnimationFrame(i);
    };
    (c.DEFAULTS = {
      imageUrl: null,
      resolution: 256,
      dropRadius: 20,
      perturbance: 0.03,
      interactive: !0,
      crossOrigin: "",
    }),
      (c.prototype = {
        setupPointerEvents: function () {
          function e() {
            return r.visible && r.running && r.interactive;
          }
          function t(t, i) {
            e() &&
              r.dropAtPointer(t, r.dropRadius * (i ? 1.5 : 1), i ? 0.14 : 0.01);
          }
          var r = this;
          this.$el
            .on("mousemove.ripples", function (e) {
              t(e);
            })
            .on("touchmove.ripples touchstart.ripples", function (e) {
              for (
                var r = e.originalEvent.changedTouches, i = 0;
                i < r.length;
                i++
              )
                t(r[i]);
            })
            .on("mousedown.ripples", function (e) {
              t(e, !0);
            });
        },
        loadImage: function () {
          var e = this;
          s = this.context;
          var t =
            this.imageUrl ||
            n(this.originalCssBackgroundImage) ||
            n(this.$el.css("backgroundImage"));
          if (t != this.imageSource)
            if (((this.imageSource = t), this.imageSource)) {
              var r = new Image();
              (r.onload = function () {
                function t(e) {
                  return 0 == (e & (e - 1));
                }
                s = e.context;
                var i = t(r.width) && t(r.height) ? s.REPEAT : s.CLAMP_TO_EDGE;
                s.bindTexture(s.TEXTURE_2D, e.backgroundTexture),
                  s.texParameteri(s.TEXTURE_2D, s.TEXTURE_WRAP_S, i),
                  s.texParameteri(s.TEXTURE_2D, s.TEXTURE_WRAP_T, i),
                  s.texImage2D(
                    s.TEXTURE_2D,
                    0,
                    s.RGBA,
                    s.RGBA,
                    s.UNSIGNED_BYTE,
                    r
                  ),
                  (e.backgroundWidth = r.width),
                  (e.backgroundHeight = r.height),
                  e.hideCssBackground();
              }),
                (r.onerror = function () {
                  (s = e.context), e.setTransparentTexture();
                }),
                (r.crossOrigin = a(this.imageSource) ? null : this.crossOrigin),
                (r.src = this.imageSource);
            } else this.setTransparentTexture();
        },
        step: function () {
          (s = this.context),
            this.visible &&
              (this.computeTextureBoundaries(),
              this.running && this.update(),
              this.render());
        },
        drawQuad: function () {
          s.bindBuffer(s.ARRAY_BUFFER, this.quad),
            s.vertexAttribPointer(0, 2, s.FLOAT, !1, 0, 0),
            s.drawArrays(s.TRIANGLE_FAN, 0, 4);
        },
        render: function () {
          s.bindFramebuffer(s.FRAMEBUFFER, null),
            s.viewport(0, 0, this.canvas.width, this.canvas.height),
            s.enable(s.BLEND),
            s.clear(s.COLOR_BUFFER_BIT | s.DEPTH_BUFFER_BIT),
            s.useProgram(this.renderProgram.id),
            o(this.backgroundTexture, 0),
            o(this.textures[0], 1),
            s.uniform1f(
              this.renderProgram.locations.perturbance,
              this.perturbance
            ),
            s.uniform2fv(
              this.renderProgram.locations.topLeft,
              this.renderProgram.uniforms.topLeft
            ),
            s.uniform2fv(
              this.renderProgram.locations.bottomRight,
              this.renderProgram.uniforms.bottomRight
            ),
            s.uniform2fv(
              this.renderProgram.locations.containerRatio,
              this.renderProgram.uniforms.containerRatio
            ),
            s.uniform1i(this.renderProgram.locations.samplerBackground, 0),
            s.uniform1i(this.renderProgram.locations.samplerRipples, 1),
            this.drawQuad(),
            s.disable(s.BLEND);
        },
        update: function () {
          s.viewport(0, 0, this.resolution, this.resolution),
            s.bindFramebuffer(
              s.FRAMEBUFFER,
              this.framebuffers[this.bufferWriteIndex]
            ),
            o(this.textures[this.bufferReadIndex]),
            s.useProgram(this.updateProgram.id),
            this.drawQuad(),
            this.swapBufferIndices();
        },
        swapBufferIndices: function () {
          (this.bufferWriteIndex = 1 - this.bufferWriteIndex),
            (this.bufferReadIndex = 1 - this.bufferReadIndex);
        },
        computeTextureBoundaries: function () {
          var e,
            i = this.$el.css("background-size"),
            o = this.$el.css("background-attachment"),
            n = r(this.$el.css("background-position"));
          if (
            ("fixed" == o
              ? (((e = {
                  left: window.pageXOffset,
                  top: window.pageYOffset,
                }).width = u.width()),
                (e.height = u.height()))
              : (((e = this.$el.offset()).width = this.$el.innerWidth()),
                (e.height = this.$el.innerHeight())),
            "cover" == i)
          )
            var a = Math.max(
                e.width / this.backgroundWidth,
                e.height / this.backgroundHeight
              ),
              s = this.backgroundWidth * a,
              h = this.backgroundHeight * a;
          else if ("contain" == i)
            var a = Math.min(
                e.width / this.backgroundWidth,
                e.height / this.backgroundHeight
              ),
              s = this.backgroundWidth * a,
              h = this.backgroundHeight * a;
          else {
            var s = (i = i.split(" "))[0] || "",
              h = i[1] || s;
            t(s)
              ? (s = (e.width * parseFloat(s)) / 100)
              : "auto" != s && (s = parseFloat(s)),
              t(h)
                ? (h = (e.height * parseFloat(h)) / 100)
                : "auto" != h && (h = parseFloat(h)),
              "auto" == s && "auto" == h
                ? ((s = this.backgroundWidth), (h = this.backgroundHeight))
                : ("auto" == s &&
                    (s = this.backgroundWidth * (h / this.backgroundHeight)),
                  "auto" == h &&
                    (h = this.backgroundHeight * (s / this.backgroundWidth)));
          }
          var d = n[0],
            c = n[1];
          (d = t(d)
            ? e.left + ((e.width - s) * parseFloat(d)) / 100
            : e.left + parseFloat(d)),
            (c = t(c)
              ? e.top + ((e.height - h) * parseFloat(c)) / 100
              : e.top + parseFloat(c));
          var f = this.$el.offset();
          (this.renderProgram.uniforms.topLeft = new Float32Array([
            (f.left - d) / s,
            (f.top - c) / h,
          ])),
            (this.renderProgram.uniforms.bottomRight = new Float32Array([
              this.renderProgram.uniforms.topLeft[0] + this.$el.innerWidth() / s,
              this.renderProgram.uniforms.topLeft[1] + this.$el.innerHeight() / h,
            ]));
          var l = Math.max(this.canvas.width, this.canvas.height);
          this.renderProgram.uniforms.containerRatio = new Float32Array([
            this.canvas.width / l,
            this.canvas.height / l,
          ]);
        },
        initShaders: function () {
          var e = [
            "attribute vec2 vertex;",
            "varying vec2 coord;",
            "void main() {",
            "coord = vertex * 0.5 + 0.5;",
            "gl_Position = vec4(vertex, 0.0, 1.0);",
            "}",
          ].join("\n");
          (this.dropProgram = i(
            e,
            [
              "precision highp float;",
              "const float PI = 3.141592653589793;",
              "uniform sampler2D texture;",
              "uniform vec2 center;",
              "uniform float radius;",
              "uniform float strength;",
              "varying vec2 coord;",
              "void main() {",
              "vec4 info = texture2D(texture, coord);",
              "float drop = max(0.0, 1.0 - length(center * 0.5 + 0.5 - coord) / radius);",
              "drop = 0.5 - cos(drop * PI) * 0.5;",
              "info.r += drop * strength;",
              "gl_FragColor = info;",
              "}",
            ].join("\n")
          )),
            (this.updateProgram = i(
              e,
              [
                "precision highp float;",
                "uniform sampler2D texture;",
                "uniform vec2 delta;",
                "varying vec2 coord;",
                "void main() {",
                "vec4 info = texture2D(texture, coord);",
                "vec2 dx = vec2(delta.x, 0.0);",
                "vec2 dy = vec2(0.0, delta.y);",
                "float average = (",
                "texture2D(texture, coord - dx).r +",
                "texture2D(texture, coord - dy).r +",
                "texture2D(texture, coord + dx).r +",
                "texture2D(texture, coord + dy).r",
                ") * 0.25;",
                "info.g += (average - info.r) * 2.0;",
                "info.g *= 0.995;",
                "info.r += info.g;",
                "gl_FragColor = info;",
                "}",
              ].join("\n")
            )),
            s.uniform2fv(this.updateProgram.locations.delta, this.textureDelta),
            (this.renderProgram = i(
              [
                "precision highp float;",
                "uniform sampler2D samplerBackground;",
                "uniform sampler2D samplerRipples;",
                "uniform vec2 delta;",
                "uniform float perturbance;",
                "varying vec2 ripplesCoord;",
                "varying vec2 backgroundCoord;",
                "void main() {",
"  float height = texture2D(samplerRipples, ripplesCoord).r;",
"  float heightX = texture2D(samplerRipples, vec2(ripplesCoord.x + delta.x, ripplesCoord.y)).r;",
"  float heightY = texture2D(samplerRipples, vec2(ripplesCoord.x, ripplesCoord.y + delta.y)).r;",
"  vec3 dx = vec3(delta.x, heightX - height, 0.0);",
"  vec3 dy = vec3(0.0, heightY - height, delta.y);",
"  vec2 offset = -normalize(cross(dy, dx)).xz;",
"  float visibility = length(offset);",
"  if (visibility < 0.005) {",
"    gl_FragColor = vec4(0.0, 0.0, 0.0, 1.0);", // Fully black where there's no ripple
"  } else {",
"    vec4 bg = texture2D(samplerBackground, backgroundCoord + offset * perturbance);",
"    gl_FragColor = bg;",
"  }",
"}"

                ].join("\n")
                
              
            )),
            s.uniform2fv(this.renderProgram.locations.delta, this.textureDelta);
        },
        initTexture: function () {
          (this.backgroundTexture = s.createTexture()),
            s.bindTexture(s.TEXTURE_2D, this.backgroundTexture),
            s.pixelStorei(s.UNPACK_FLIP_Y_WEBGL, 1),
            s.texParameteri(s.TEXTURE_2D, s.TEXTURE_MAG_FILTER, s.LINEAR),
            s.texParameteri(s.TEXTURE_2D, s.TEXTURE_MIN_FILTER, s.LINEAR);
        },
        setTransparentTexture: function () {
          s.bindTexture(s.TEXTURE_2D, this.backgroundTexture),
            s.texImage2D(s.TEXTURE_2D, 0, s.RGBA, s.RGBA, s.UNSIGNED_BYTE, d);
        },
        hideCssBackground: function () {
          var e = this.$el[0].style.backgroundImage;
          "none" != e &&
            ((this.originalInlineCss = e),
            (this.originalCssBackgroundImage = this.$el.css("backgroundImage")),
            this.$el.css("backgroundImage", "none"));
        },
        restoreCssBackground: function () {
          this.$el.css("backgroundImage", this.originalInlineCss || "");
        },
        dropAtPointer: function (e, t, r) {
          var i = parseInt(this.$el.css("border-left-width")) || 0,
            o = parseInt(this.$el.css("border-top-width")) || 0;
          this.drop(
            e.pageX - this.$el.offset().left - i,
            e.pageY - this.$el.offset().top - o,
            t,
            r
          );
        },
        drop: function (e, t, r, i) {
          s = this.context;
          var n = this.$el.innerWidth(),
            a = this.$el.innerHeight(),
            u = Math.max(n, a);
          r /= u;
          var h = new Float32Array([(2 * e - n) / u, (a - 2 * t) / u]);
          s.viewport(0, 0, this.resolution, this.resolution),
            s.bindFramebuffer(
              s.FRAMEBUFFER,
              this.framebuffers[this.bufferWriteIndex]
            ),
            o(this.textures[this.bufferReadIndex]),
            s.useProgram(this.dropProgram.id),
            s.uniform2fv(this.dropProgram.locations.center, h),
            s.uniform1f(this.dropProgram.locations.radius, r),
            s.uniform1f(this.dropProgram.locations.strength, i),
            this.drawQuad(),
            this.swapBufferIndices();
        },
        updateSize: function () {
          var e = this.$el.innerWidth(),
            t = this.$el.innerHeight();
          (e == this.canvas.width && t == this.canvas.height) ||
            ((this.canvas.width = e), (this.canvas.height = t));
        },
        destroy: function () {
          this.$el
            .off(".ripples")
            .removeClass("jquery-ripples")
            .removeData("ripples"),
            (s = null),
            e(window).off("resize", this.updateSize),
            this.$canvas.remove(),
            this.restoreCssBackground(),
            (this.destroyed = !0);
        },
        show: function () {
          (this.visible = !0), this.$canvas.show(), this.hideCssBackground();
        },
        hide: function () {
          (this.visible = !1), this.$canvas.hide(), this.restoreCssBackground();
        },
        pause: function () {
          this.running = !1;
        },
        play: function () {
          this.running = !0;
        },
        set: function (e, t) {
          switch (e) {
            case "dropRadius":
            case "perturbance":
            case "interactive":
            case "crossOrigin":
              this[e] = t;
              break;
            case "imageUrl":
              (this.imageUrl = t), this.loadImage();
          }
        },
      });
    var f = e.fn.ripples;
    (e.fn.ripples = function (t) {
      if (!h)
        throw new Error(
          "Your browser does not support WebGL, the OES_texture_float extension or rendering to floating point textures."
        );
      var r =
        arguments.length > 1 ? Array.prototype.slice.call(arguments, 1) : void 0;
      return this.each(function () {
        var i = e(this),
          o = i.data("ripples"),
          n = e.extend({}, c.DEFAULTS, i.data(), "object" == typeof t && t);
        (o || "string" != typeof t) &&
          (o
            ? "string" == typeof t && c.prototype[t].apply(o, r)
            : i.data("ripples", (o = new c(this, n))));
      });
    }),
      (e.fn.ripples.Constructor = c),
      (e.fn.ripples.noConflict = function () {
        return (e.fn.ripples = f), this;
      });
  });
  