module Math exposing (Player, accelerate, defaultPlayer, hud, moveSpace, moveTime, render, stats)

import Math.Vector2 as Vec2 exposing (Vec2, vec2)
import Math.Vector3 as Vec3 exposing (Vec3, vec3)
import Shader
import WebGL


{-| The player position can be stored in two modes.

    Direct mode just stores the position, velocity, and time. This is used for accelerating or moving in spacial directions.
    Parameterized mode stores a parameterization of the orbit. This is used for moving forward in time without numerical error.

    I switch modes only when necessary. So if you're just walking forward in time, there is no numerical error!

-}
type Player
    = Direct DirectMode
    | Parameterized ParameterizedMode


type alias DirectMode =
    { position : Vec2
    , velocity : Vec2
    , time : Float
    }


type alias ParameterizedMode =
    { bound : Bool
    , coefficients :
        { one : Vec3
        , sMinusSin : Vec3
        , sin : Vec3
        , oneMinusCos : Vec3
        }
    , parameter : Float
    }


toDirect : Player -> DirectMode
toDirect p =
    case p of
        Direct player ->
            player

        Parameterized { bound, coefficients, parameter } ->
            let
                ( sin_s, cos_s ) =
                    if bound then
                        ( sin parameter, cos parameter )

                    else
                        let
                            exp =
                                e ^ parameter

                            nexp =
                                1 / exp
                        in
                        ( (exp - nexp) / 2, (exp + nexp) / 2 )

                sMinusSin =
                    if bound then
                        parameter - sin_s

                    else
                        sin_s - parameter

                oneMinusCos =
                    if bound then
                        1 - cos_s

                    else
                        cos_s - 1

                val =
                    Vec3.toRecord <|
                        Vec3.add coefficients.one <|
                            Vec3.add (Vec3.scale sMinusSin coefficients.sMinusSin) <|
                                Vec3.add (Vec3.scale sin_s coefficients.sin) <|
                                    Vec3.scale oneMinusCos coefficients.oneMinusCos

                diff =
                    Vec3.toRecord <|
                        Vec3.add (Vec3.scale oneMinusCos coefficients.sMinusSin) <|
                            Vec3.add (Vec3.scale cos_s coefficients.sin) <|
                                Vec3.scale sin_s coefficients.oneMinusCos
            in
            { position = vec2 val.x val.y
            , time = val.z
            , velocity =
                Vec2.scale (1 / diff.z) (vec2 diff.x diff.y)
            }


toParameterized : Player -> ParameterizedMode
toParameterized p =
    case p of
        Parameterized player ->
            player

        Direct { position, velocity, time } ->
            let
                v3 v2 t =
                    vec3 (Vec2.getX v2) (Vec2.getY v2) t

                r =
                    Vec2.length position

                energy =
                    0.5 * Vec2.lengthSquared velocity - 1 / r

                vv =
                    2 * abs energy

                v =
                    sqrt vv

                vvv =
                    v * vv
            in
            { bound = energy < 0
            , coefficients =
                { one = v3 position time
                , sMinusSin = vec3 0 0 (1 / vvv)
                , sin = Vec3.scale (r / v) (v3 velocity 1)
                , oneMinusCos =
                    Vec3.scale (1 / vv) <|
                        Vec3.sub
                            (Vec3.scale (Vec2.dot position velocity) (v3 velocity 1))
                            (Vec3.scale (1 / r) (v3 position 0))
                }
            , parameter = 0
            }



----


defaultPlayer : Player
defaultPlayer =
    Direct
        { position = vec2 2.5 0.3
        , velocity = vec2 0 0.65
        , time = 0.5
        }


accelerate : Vec2 -> Player -> Player
accelerate dv p =
    if dv == vec2 0 0 then
        p

    else
        let
            player =
                toDirect p
        in
        Direct { player | velocity = Vec2.add player.velocity dv }


moveSpace : Vec2 -> Player -> Player
moveSpace dx p =
    if dx == vec2 0 0 then
        p

    else
        let
            player =
                toDirect p
        in
        Direct { player | position = Vec2.add player.position dx }


moveTime : Float -> Player -> Player
moveTime dt p =
    if dt == 0 then
        p

    else
        let
            player =
                toParameterized p

            goal =
                (toDirect p).time + dt

            go iterations ( mn, mx ) =
                let
                    md =
                        (mn + mx) / 2

                    tmp =
                        Parameterized { player | parameter = md }
                in
                if iterations == 0 then
                    tmp

                else
                    go (iterations - 1)
                        (if goal < (toDirect tmp).time then
                            ( mn, md )

                         else
                            ( md, mx )
                        )

            go2 step =
                if (toDirect (Parameterized { player | parameter = player.parameter + step })).time > goal then
                    go 20 ( player.parameter, player.parameter + step )

                else
                    go2 (2 * step)

            go3 step =
                if (toDirect (Parameterized { player | parameter = player.parameter - step })).time < goal then
                    go 20 ( player.parameter - step, player.parameter )

                else
                    go3 (2 * step)
        in
        if dt > 0 then
            go2 dt

        else
            go3 -dt


type alias Stats =
    { energy : Float
    , angularMomentum : Float
    , eccentricity : Float
    , periapsis : Float
    , apoapsis : Float
    , position : Vec2
    , velocity : Vec2
    , time : Float
    , orbital_period : Float
    }


stats : Player -> Stats
stats p =
    let
        { position, velocity, time } =
            toDirect p

        r =
            Vec2.length position

        energy =
            0.5 * Vec2.lengthSquared velocity - 1 / r

        h =
            Vec2.getX position * Vec2.getY velocity - Vec2.getY position * Vec2.getX velocity

        a =
            r / (2 - r * Vec2.lengthSquared velocity)

        eccentricity =
            Vec2.add
                (Vec2.scale (1 / r) position)
                (Vec2.scale h
                    (vec2
                        -(Vec2.getY velocity)
                        (Vec2.getX velocity)
                    )
                )

        e =
            Vec2.length eccentricity
    in
    { energy = energy
    , angularMomentum = h
    , eccentricity = e
    , periapsis = a * (1 - e)
    , apoapsis = a * (1 + e)
    , position = position
    , velocity = velocity
    , time = time
    , orbital_period = 2 * pi * (-2 * energy) ^ -1.5
    }



----


type alias Uniforms =
    { player_three_position : Vec3
    , player_velocity : Vec2
    , aspect : Float
    , edgeWidth : Float
    }


type alias Info =
    { player : Player
    , aspect : Float
    , edgeWidth : Float
    }


uniforms : Info -> Uniforms
uniforms { aspect, player, edgeWidth } =
    let
        { position, time, velocity } =
            toDirect player

        { x, y } =
            Vec2.toRecord position
    in
    { player_three_position = vec3 x y time
    , player_velocity = velocity
    , aspect = aspect
    , edgeWidth = edgeWidth
    }


render : Info -> WebGL.Entity
render info =
    Shader.run renderShader (uniforms info)


renderShader : WebGL.Shader {} Uniforms { xy : Vec2.Vec2 }
renderShader =
    [glsl|

    precision mediump float;
    varying vec2 xy;

    uniform vec3 player_three_position;
    uniform vec2 player_velocity;
    uniform float edgeWidth;

    // ELLIPSE //

    // Given function f(s) = α + β s + γ sin(s) + δ cos(s),
    // point (s₀, f(s₀)), and bounds f_min < f(s₀) < f_max:
    //
    // Return an s > s₀ such that f_min < f(s) < f_max.
    // 
    // Strategy:
    // f(s) is between f(s₀) + (β ± √(γ²+δ²)) (s - s₀)
    float soonest_possible_collision_elliptical(vec4 coefficients, vec2 current_point, vec2 bounds) {
        float wave_size = length(coefficients.zw);

        // f(s₀) + (β ± √(γ²+δ²)) (s - s₀) = bound
        // -(f(s₀) - bound) / (β ± √(γ²+δ²)) = (s - s₀)

        vec2 slopes = coefficients.yy + vec2(-1., 1.) * wave_size;

        // If the line slopes away from the bound, the bound will never be hit!
        return min(
            slopes.x < 0. ? (bounds.x - current_point.y) / slopes.x : 1.0e30,
            slopes.y > 0. ? (bounds.y - current_point.y) / slopes.y : 1.0e30
        ) + current_point.x;
    }

    // HYPERBOLA //

    // Solve `y = c sinh x + d cosh x` for x.
    vec2 hyperbola_exact(float c, float d, float y) {
        float discriminant = y*y + c*c - d*d;
        if (discriminant < 0.) {return vec2(1.0e30);}
        vec2 inside = (vec2(y) + vec2(-1., 1.) * sqrt(discriminant)) / (c+d);
        return vec2(
            inside.x <= 0. ? 1.0e30 : log(inside.x),
            inside.y <= 0. ? 1.0e30 : log(inside.y)
        );
    }

    float soonest_possible_collision_hyperbolic(vec4 coefficients, vec2 current_point, vec2 bounds) {
        // Well, it handles all the cases I need it to.
        if (abs(coefficients.y) < 1.0e-12) {
            // exact solution
            vec2 solns1 = hyperbola_exact(coefficients.z, coefficients.w, bounds.x - coefficients.x);
            vec2 solns2 = hyperbola_exact(coefficients.z, coefficients.w, bounds.y - coefficients.x);

            float result = 1.0e30;

            if (solns1.x >= current_point.x - 1.0e-3) { result = min(result, solns1.x); }
            if (solns1.y >= current_point.x - 1.0e-3) { result = min(result, solns1.y); }
            if (solns2.x >= current_point.x - 1.0e-3) { result = min(result, solns2.x); }
            if (solns2.y >= current_point.x - 1.0e-3) { result = min(result, solns2.y); }

            return result;
        } else {
            // works for monotonic functions
            return current_point.x + log(
                1. + (bounds.y - current_point.y) / (
                    max(0., coefficients.y) 
                    + 0.5*(coefficients.z + coefficients.w)*exp(current_point.x)
                    + 0.5*(coefficients.z - coefficients.w)*exp(-current_point.x)
                )
            );
        }
    }


    // RAYMARCHER //

    vec2 position = player_three_position.xy;
    float r = length(position);

    bool crosshair() {
        return dot(xy, xy) < 0.0002;
    }

    vec2 walls(float x) {
        float lower = min(x, floor(x) + 0.5 * edgeWidth);
        float upper = max(x, ceil(x) - 0.5 * edgeWidth);
        return vec2(lower, upper);
    }

    void main() {
        if (crosshair()) {
            gl_FragColor = vec4(1., 1., 1., 1.);
            return;
        }

        vec2 velocity = player_velocity + xy;
        vec3 three_velocity = vec3(velocity, 1.0);
        float energy = 0.5 * dot(velocity, velocity) - 1. / r;

        bool bound = energy < 0.0;

        float vv = 2.0 * abs(energy);
        float v = sqrt(vv);
        float vvv = v * vv;

        // I wanted this to be a mat4x3, but it was not working.
        mat4 coefficients = mat4(
            player_three_position, 0.,                                                  // coefficient of 1
            0., 0., 1. / vvv, 0.,                                                       // coefficient of ±(s - sinₕ(s))
            three_velocity * r / v, 0.,                                                 // coefficient of sin(s)
            (three_velocity * dot(position, velocity) - vec3(position, 0.) / r) / vv, 0.// coefficient of ±(1 - cosₕ(s))
        );

        // remove the ±s
        if (!bound) {
            coefficients[1] *= -1.;
            coefficients[3] *= -1.;
        }

        coefficients[2] -= coefficients[1];
        coefficients[0] += coefficients[3];
        coefficients[3] *= -1.;

        // matrix columns now represent coefficients of 1, s, sinₕ(s), and cosₕ(s).



        float parameter = 0.;
        vec3 marcher_position = player_three_position;

        for (int i = 0; i < 64; i++) {

            // large steps in s that stop short of each coordinate plane
            vec3 next_parameter = bound
                ? vec3(
                    soonest_possible_collision_elliptical(vec4(1., 0., 0., 0.) * coefficients, vec2(parameter, marcher_position.x), walls(marcher_position.x)), 
                    soonest_possible_collision_elliptical(vec4(0., 1., 0., 0.) * coefficients, vec2(parameter, marcher_position.y), walls(marcher_position.y)), 
                    soonest_possible_collision_elliptical(vec4(0., 0., 1., 0.) * coefficients, vec2(parameter, marcher_position.z), walls(marcher_position.z))
                )
                : vec3(
                    soonest_possible_collision_hyperbolic(vec4(1., 0., 0., 0.) * coefficients, vec2(parameter, marcher_position.x), walls(marcher_position.x)), 
                    soonest_possible_collision_hyperbolic(vec4(0., 1., 0., 0.) * coefficients, vec2(parameter, marcher_position.y), walls(marcher_position.y)), 
                    soonest_possible_collision_hyperbolic(vec4(0., 0., 1., 0.) * coefficients, vec2(parameter, marcher_position.z), walls(marcher_position.z))
                );


            // Bubble Sort
            if (next_parameter.x > next_parameter.y) {
                next_parameter = next_parameter.yxz;
            }
            if (next_parameter.y > next_parameter.z) {
                next_parameter = next_parameter.xzy;
            }
            if (next_parameter.x > next_parameter.y) {
                next_parameter = next_parameter.yxz;
            }


            // step forward
            parameter = next_parameter.y;

            if (bound) {
                marcher_position = (coefficients * vec4(1., parameter, sin(parameter), cos(parameter))).xyz;
            } else {
                float a = exp(parameter);
                float b = 1. / a;
                float sinh = 0.5 * (a - b);
                float cosh = 0.5 * (a + b);
                marcher_position = (coefficients * vec4(1., parameter, sinh, cosh)).xyz;
            }
        }



        float distance = marcher_position.z - player_three_position.z;
        vec3 surfaceColor = 2. * dot(marcher_position.xy, marcher_position.xy) < (edgeWidth * edgeWidth)
            ? vec3(0.5, 0., 0.) 
            : vec3(0., 0., 0.);
        vec3 fogColor = vec3(0., 1., bound ? 0. : 1.);
        gl_FragColor = vec4(mix(surfaceColor, fogColor, distance * 0.1), 1.);
    }


|]



----


hud : Info -> WebGL.Entity
hud info =
    Shader.run hudShader (uniforms info)


hudShader : WebGL.Shader {} Uniforms { xy : Vec2.Vec2 }
hudShader =
    [glsl|
    // https://en.wikipedia.org/wiki/Elliptic_orbit#From_Initial_Position_and_Velocity

    precision mediump float;
    varying vec2 xy;

    uniform vec3 player_three_position;
    uniform vec2 player_velocity;

    float r = length(player_three_position.xy);
    float h = player_three_position.x * player_velocity.y - player_three_position.y * player_velocity.x;
    float semimajor = r / (2. - r * dot(player_velocity, player_velocity));
    vec2 eccentricity = player_three_position.xy / r + h * vec2(-player_velocity.y, player_velocity.x);
    vec2 focus = 2. * semimajor * eccentricity;

    void main() {
        vec2 coord = xy * 10.;

        float value = length(coord) + distance(coord, focus) * sign(semimajor) - 2. * semimajor;
        vec2 grad_value = normalize(coord) + normalize(coord - focus) * sign(semimajor);
        vec3 ellipse_color = vec3(0., 0.5, 1.) * exp(-4. * value*value / dot(grad_value, grad_value));

        vec3 planet_color = vec3(1., 0., 0.) * exp(-0.5*dot(coord, coord));

        vec3 self_color = vec3(0., 1., 0.) * exp(-2. * dot(coord - player_three_position.xy, coord - player_three_position.xy));

        gl_FragColor = vec4(ellipse_color + planet_color + self_color, 1.);
    }

    |]
