module Shader exposing (run)

import Math.Vector2 exposing (Vec2, vec2)
import WebGL exposing (Mesh, Shader)


type alias Vertex =
    { coord : Vec2 }


vert : Float -> Float -> Vertex
vert x y =
    { coord = vec2 x y }


mesh : Mesh Vertex
mesh =
    WebGL.triangles
        [ ( vert 0 0, vert 0 1, vert 1 1 )
        , ( vert 1 1, vert 1 0, vert 0 0 )
        ]


vertexShader : Shader Vertex { uniforms | aspect : Float } { xy : Vec2 }
vertexShader =
    [glsl| 

    attribute vec2 coord;
    varying vec2 xy;

    uniform float aspect;

    void main() {
        xy = coord * 2. - 1.;
        gl_Position = vec4(xy, 0., 1.);
        xy.x *= aspect;
    }

|]


run : Shader {} { uniforms | aspect : Float } { xy : Vec2 } -> { uniforms | aspect : Float } -> WebGL.Entity
run shader uniforms =
    WebGL.entity vertexShader shader mesh uniforms
