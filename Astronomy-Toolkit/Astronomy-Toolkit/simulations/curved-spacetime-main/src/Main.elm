port module Main exposing (main)

import Array exposing (Array)
import Browser
import Browser.Dom exposing (Element)
import Browser.Events exposing (onAnimationFrameDelta, onClick, onKeyDown, onKeyUp, onMouseMove, onResize)
import Element exposing (Element, alignLeft, alignRight, alignTop, behindContent, centerX, column, el, fill, html, padding, paddingEach, paddingXY, paragraph, px, rgb, row, scrollbarY, shrink, spacing, text, textColumn)
import Element.Background as Background
import Element.Border as Border
import Element.Events as Events
import Element.Font as Font
import Element.Input as Input
import Html.Attributes
import Json.Decode as Decode
import Math exposing (Player, accelerate)
import Math.Vector2 exposing (vec2)
import Set exposing (Set)
import WebGL


port onPointerLock : (Bool -> msg) -> Sub msg


port ready : {} -> Cmd msg


type alias Model =
    { player : Player
    , keys : Set String
    , pointerLock : Bool
    , window : Window
    , dropdowns : Array Bool
    , sidebars :
        { left : Bool
        , right : Bool
        }
    , resolution : Float
    , edgeWidth : Float
    }


type alias Window =
    { width : Int
    , height : Int
    }


type Msg
    = Reset
    | Frame Float
    | KeyCode { code : String, down : Bool }
    | MouseMove Int Int
    | PointerLock Bool
    | WindowResize Int Int
    | Dropdown Int Bool
    | LeftSidebar Bool
    | RightSidebar Bool
    | Resolution Float
    | EdgeWidth Float


type alias Flags =
    { window : Window }


main : Program Flags Model Msg
main =
    Browser.document
        { init = \flags -> ( init flags, ready {} )
        , view = view
        , update = \msg model -> ( update msg model, Cmd.none )
        , subscriptions = subscriptions
        }


init : Flags -> Model
init { window } =
    { player = Math.defaultPlayer
    , keys = Set.empty
    , pointerLock = False
    , window = window
    , dropdowns = Array.fromList <| True :: False :: False :: True :: List.repeat 10 False
    , sidebars = { left = True, right = True }
    , resolution = 0.5
    , edgeWidth = 0.058
    }


update : Msg -> Model -> Model
update msg model =
    case msg of
        Reset ->
            { model | player = Math.defaultPlayer }

        Frame dt ->
            let
                ifKey : String -> Float -> Float
                ifKey =
                    \code val ->
                        if Set.member code model.keys then
                            val

                        else
                            0

                space =
                    vec2
                        (ifKey "KeyD" dt - ifKey "KeyA" dt)
                        (ifKey "Space" dt - ifKey "ShiftLeft" dt)

                time =
                    ifKey "KeyW" dt - ifKey "KeyS" dt
            in
            { model | player = model.player |> Math.moveTime time |> Math.moveSpace space }

        KeyCode { code, down } ->
            if down then
                { model | keys = Set.insert code model.keys }

            else
                { model | keys = Set.remove code model.keys }

        MouseMove x y ->
            { model
                | player =
                    Math.accelerate
                        (vec2 (toFloat x / 200) (toFloat y / -200))
                        model.player
            }

        PointerLock lock ->
            { model
                | pointerLock = lock
                , keys = Set.empty
            }

        WindowResize width height ->
            { model | window = { width = width - 1, height = height - 1 } }

        Dropdown which status ->
            { model | dropdowns = Array.set which status model.dropdowns }

        LeftSidebar status ->
            let
                sidebars =
                    model.sidebars
            in
            { model | sidebars = { sidebars | left = status } }

        RightSidebar status ->
            let
                sidebars =
                    model.sidebars
            in
            { model | sidebars = { sidebars | right = status } }

        Resolution resolution ->
            { model | resolution = resolution }

        EdgeWidth edgeWidth ->
            { model | edgeWidth = edgeWidth }


subscriptions : Model -> Sub Msg
subscriptions model =
    Sub.batch
        [ if model.pointerLock then
            runningSubscriptions model

          else
            Sub.none
        , onKeyDown <| Decode.map (\code -> KeyCode { code = code, down = True }) <| Decode.field "code" Decode.string
        , onKeyUp <| Decode.map (\code -> KeyCode { code = code, down = False }) <| Decode.field "code" Decode.string
        , onPointerLock PointerLock
        , onResize WindowResize
        ]


runningSubscriptions : Model -> Sub Msg
runningSubscriptions model =
    Sub.batch
        [ onMouseMove <| Decode.map2 MouseMove (Decode.field "movementX" Decode.int) (Decode.field "movementY" Decode.int)
        , -- Only process frames if actually necessary, to save CPU.
          if Set.isEmpty model.keys then
            Sub.none

          else
            onAnimationFrameDelta (\dt -> Frame (dt / 1000))
        ]



---- VIEW ----


view : Model -> Browser.Document Msg
view model =
    { title = "Gravity"
    , body =
        [ Element.layout
            [ Background.color (rgb 0 0 0)
            , behindContent (viewGame model)
            , Font.size (scaled 1)
            , Font.color (rgb 0.7 0.7 0.7)
            ]
            (viewHud model)
        ]
    }


viewGame : Model -> Element msg
viewGame { player, window, resolution, edgeWidth } =
    el [ Element.pointer ] <|
        html <|
            WebGL.toHtml
                [ Html.Attributes.id "game"
                , Html.Attributes.width (round <| toFloat window.width * resolution)
                , Html.Attributes.height (round <| toFloat window.height * resolution)
                , Html.Attributes.style "width" (String.fromInt window.width)
                , Html.Attributes.style "height" (String.fromInt window.height)
                ]
                [ Math.render
                    { player = player
                    , aspect = toFloat window.width / toFloat window.height
                    , edgeWidth = edgeWidth
                    }
                ]


viewHud : Model -> Element Msg
viewHud model =
    column
        [ Element.width fill
        , Element.height (px 0)
        ]
        [ row
            [ Element.width fill
            , Element.height (px 0)
            ]
            [ viewLeftSidebar model
            , viewRightSidebar model
            ]
        , if model.pointerLock then
            Element.none

          else
            viewReminder
        ]


viewReminder : Element msg
viewReminder =
    paragraph
        [ Border.color borderColor
        , Border.width 5
        , Background.color backgroundColor
        , padding 10
        , centerX
        , Element.alignTop
        , Element.width shrink
        , Font.bold
        , Font.color (rgb 0.8 0.6 0.8)
        , Font.size (scaled 4)
        ]
        [ text "Paused" ]


viewLeftSidebar : Model -> Element Msg
viewLeftSidebar model =
    let
        ( symbol, content ) =
            if model.sidebars.left then
                ( "◂"
                , column
                    [ Element.width <| px <| model.window.width // 9 + 20
                    , Element.height (shrink |> Element.maximum model.window.height)
                    , Background.color borderColor
                    , padding 5
                    , spacing 5
                    , scrollbarY
                    ]
                  <|
                    Input.button
                        [ Background.color (rgb 0.2 0 0.2)
                        , Element.mouseOver [ Border.innerGlow (rgb 0.1 0.3 0.1) 5 ]
                        , Element.mouseDown [ Background.color <| rgb 0.1 0.3 0.1 ]
                        , Element.width fill
                        , Font.center
                        ]
                        { onPress = Just Reset
                        , label =
                            paragraph [] [ text "Click to Reset" ]
                        }
                        :: List.map2 (dropdown { fontScale = 3 })
                            [ { title = "Orbit"
                              , msg = Dropdown 0
                              , content = el [] <| viewOrbit (model.window.width // 9) model.player
                              }
                            , { title = "Stats"
                              , msg = Dropdown 1
                              , content = viewStats model.player
                              }
                            , { title = "Settings"
                              , msg = Dropdown 2
                              , content = viewSettings model
                              }
                            ]
                            (Array.toList model.dropdowns)
                )

            else
                ( "▸", Element.none )
    in
    row [ alignLeft, alignTop ]
        [ content
        , el
            [ Font.bold
            , Font.size (scaled 3)
            , alignTop
            , Background.color backgroundColor
            , Border.color borderColor
            , Border.widthEach { bottom = 5, top = 5, right = 5, left = 0 }
            , paddingXY 5 2
            , Element.pointer
            , Events.onClick (LeftSidebar (not model.sidebars.left))
            ]
            (text symbol)
        ]


viewRightSidebar : Model -> Element Msg
viewRightSidebar model =
    let
        ( symbol, content ) =
            if model.sidebars.right then
                ( "▸"
                , column
                    [ Element.width <| px <| 2 * model.window.width // 9
                    , Background.color borderColor
                    , padding 5
                    , spacing 5
                    , Element.height (shrink |> Element.maximum model.window.height)
                    , scrollbarY
                    ]
                  <|
                    el [ Background.color backgroundColor, Element.width fill ]
                        (Element.link [ centerX, padding 5 ]
                            { url = "https://github.com/finegeometer/curved-spacetime"
                            , label = text "[source code]"
                            }
                        )
                        :: textColumn
                            [ Background.color backgroundColor
                            , Font.size (scaled -1)
                            , padding 5
                            , Font.center
                            , Font.italic
                            , Element.width fill
                            ]
                            [ Element.paragraph [] [ text "Meant for use on a computer. " ]
                            , Element.paragraph [] [ text "Does not support Internet Explorer." ]
                            ]
                        :: List.map2 (dropdown { fontScale = 3 })
                            [ { title = "Explanation"
                              , msg = Dropdown 3
                              , content = viewText (model.window.height // 2) explanation
                              }
                            , { title = "Things to Try or Notice"
                              , msg = Dropdown 4
                              , content = viewText (model.window.height // 2) thingsToTry
                              }
                            , { title = "Q & A"
                              , msg = Dropdown 5
                              , content = viewQA model
                              }
                            ]
                            (List.drop 3 <| Array.toList model.dropdowns)
                )

            else
                ( "◂", Element.none )
    in
    row [ alignRight, alignTop ]
        [ el
            [ Font.bold
            , Font.size (scaled 3)
            , alignTop
            , Background.color backgroundColor
            , Border.color borderColor
            , Border.widthEach { bottom = 5, top = 5, left = 5, right = 0 }
            , paddingXY 5 2
            , Element.pointer
            , Events.onClick (RightSidebar (not model.sidebars.right))
            ]
            (text symbol)
        , content
        ]


viewOrbit : Int -> Player -> Element msg
viewOrbit size player =
    el [ Element.width (px size), Element.height (px size), centerX ] <|
        html <|
            WebGL.toHtml
                [ Html.Attributes.width size
                , Html.Attributes.height size
                ]
                [ Math.hud
                    { player = player
                    , aspect = 1
                    , edgeWidth = 0 -- doesn't matter
                    }
                ]


viewStats : Player -> Element Msg
viewStats player =
    let
        stats =
            Math.stats player
    in
    textColumn
        [ Font.center
        , Element.width fill
        , padding 5
        , spacing 5
        ]
        [ paragraph
            [ Font.bold
            , Font.underline
            , Font.size (scaled 2)
            , Font.color (rgb 0.6 0.3 0.6)
            , paddingEach { top = 10, left = 0, right = 0, bottom = 0 }
            ]
            [ text "Orbital" ]
        , paragraph [ Font.bold ] [ text "Energy" ]
        , text <| String.left 7 <| String.fromFloat stats.energy
        , paragraph [ Font.bold ] [ text "Angular Momentum" ]
        , text <| String.left 7 <| String.fromFloat stats.angularMomentum
        , paragraph [ Font.bold ] [ text "Eccentricity" ]
        , text <| String.left 7 <| String.fromFloat stats.eccentricity
        , paragraph [ Font.bold ] [ text "Periapsis" ]
        , text <| String.left 7 <| String.fromFloat stats.periapsis
        , paragraph [ Font.bold ] [ text "Apoapsis" ]
        , text <|
            if stats.apoapsis > 0 then
                String.left 7 <| String.fromFloat stats.apoapsis

            else
                " "
        , paragraph [ Font.bold ] [ text "Orbital Period" ]
        , text <|
            if stats.energy < 0 then
                String.left 7 <| String.fromFloat stats.orbital_period

            else
                " "
        , paragraph
            [ Font.bold
            , Font.underline
            , Font.size (scaled 2)
            , Font.color (rgb 0.6 0.3 0.6)
            , paddingEach { top = 15, left = 0, right = 0, bottom = 0 }
            ]
            [ text "Other" ]
        , paragraph [ Font.bold ] [ text "Position" ]
        , text <|
            String.concat
                [ String.left 7 <| String.fromFloat <| Math.Vector2.getX stats.position
                , ", "
                , String.left 7 <| String.fromFloat <| Math.Vector2.getY stats.position
                ]
        , paragraph [ Font.bold ] [ text "Velocity" ]
        , text <|
            String.concat
                [ String.left 7 <| String.fromFloat <| Math.Vector2.getX stats.velocity
                , ", "
                , String.left 7 <| String.fromFloat <| Math.Vector2.getY stats.velocity
                ]
        , paragraph [ Font.bold ] [ text "Time" ]
        , text <| String.left 7 <| String.fromFloat stats.time
        ]


viewSettings : Model -> Element Msg
viewSettings { resolution, edgeWidth } =
    column
        [ Element.width fill
        , padding 5
        , spacing 5
        ]
        [ Input.slider
            [ Element.width fill
            , Element.height (px 5)
            , Background.color borderColor
            ]
            { onChange = Resolution
            , label =
                Input.labelAbove [ centerX ] <|
                    textColumn [ Font.center, padding 5, spacing 5, Element.width shrink ]
                        [ paragraph [ Font.bold ]
                            [ text <|
                                String.concat
                                    [ "resolution ("
                                    , String.fromInt <| round <| 100 * resolution
                                    , "%)"
                                    ]
                            ]
                        , paragraph [ Font.size (scaled -1) ] [ text "Performance Option" ]
                        ]
            , min = 0
            , max = 1
            , value = resolution
            , thumb = Input.defaultThumb
            , step = Just 0.01
            }
        , Input.slider
            [ Element.width fill
            , Element.height (px 5)
            , Background.color borderColor
            ]
            { onChange = EdgeWidth
            , label =
                Input.labelAbove [ centerX ] <|
                    paragraph [ Font.bold, Font.center ]
                        [ text <|
                            String.concat
                                [ "Lattice edge width ("
                                , String.fromFloat edgeWidth
                                , ")"
                                ]
                        ]
            , min = 0.01
            , max = 0.2
            , value = edgeWidth
            , thumb = Input.defaultThumb
            , step = Just 0.001
            }
        ]


viewText : Int -> List (Element msg) -> Element msg
viewText height =
    textColumn
        [ Element.height (px height)
        , Element.width fill
        , scrollbarY
        , padding 5
        , spacing 15
        ]


explanation : List (Element msg)
explanation =
    [ paragraph []
        [ text "A central idea of general relativity is that what we feel as a gravitational force "
        , text "is in fact not a force, but simply a result of the curvature of spacetime. "
        ]
    , paragraph []
        [ text "This is typically demonstrated with an analogy involving a heavy object on a rubber sheet. "
        , text "But that's actually a "
        , el [ Font.italic ] <| text "terrible "
        , text "picture of curved spacetime! "
        , text "It's curved, but that's about all it has going for it. "
        , text "So I'm attempting to do better. "
        ]
    , paragraph []
        [ text "In this project, I attempt to show the curved spacetime around a planet. "
        , text "To simplify the picture, I'm only showing two spacial dimensions. "
        , text "If you include the time dimension, that makes three. "
        ]
    , paragraph []
        [ text "The spacial dimensions are shown as left/right and up/down. Time is shown as forward/backward. "
        ]
    , paragraph []
        [ text "I've filled the spacetime with grid-lines, to show the x, y, and t coordinates. "
        , text "The planet, at x=y=0, is shown in red. "
        , text "It looks like a bar, because while it is small in the x and y directions, it extends forever in the time direction. "
        ]
    , paragraph []
        [ text "Far from the planet, the grid mostly looks like an ordinary cubic lattice. "
        , text "But near the planet, spacetime is strongly curved, so everything looks distorted. "
        ]
    , paragraph []
        [ text "Now click on the scene. "
        , text "This will \"lock\" the mouse pointer, and allow you to look around by moving the mouse. "
        , text "Clicking again, or pressing Escape, will unlock the mouse pointer again. "
        ]
    , paragraph []
        [ text "When the pointer is locked, you can also move around the spacetime. "
        , text "The W and S keys will move you forward and backward. (Recall that this is the time direction.) "
        , text "A and D will move you left and right, and the Spacebar and the left Shift key will move you up and down. "
        , text "(If you've played Minecraft, these controls should be familiar.) "
        ]
    , paragraph []
        [ text "If you get lost while looking around the spacetime, "
        , text "click the \"Click to Reset\" button on the upper left. "
        ]
    , paragraph []
        [ text "Now look at the \"Orbit\" panel on the left. "
        , text "This shows what is going on in "
        , el [ Font.italic ] <| text "space, "
        , text "rather than "
        , el [ Font.italic ] <| text "spacetime. "
        , text "The red dot is the planet. The green dot is your location."
        ]
    , paragraph []
        [ text "The blue curve corresponds to the straight line in spacetime "
        , text "that extends directly in front of and behind you. "
        , text "To see this, walk forward in the spacetime, and observe that the green dot follows the blue path. "
        , text "But due to the curvature of spacetime, this straight path in "
        , el [ Font.italic ] <| text "spacetime "
        , text "looks like a curved path in "
        , el [ Font.italic ] <| text "space. "
        , text "Specifically, it appears to bend toward the planet. "
        ]
    , paragraph []
        [ text "Freely falling objects follow straight paths through spacetime. "
        , text "But as we just saw, a straight path in "
        , el [ Font.italic ] <| text "spacetime "
        , text "appears to bend toward the planet when viewed only in "
        , el [ Font.italic ] <| text "space. "
        , text "So the object appears to fall toward the planet. "
        ]
    , paragraph []
        [ text "And that is how gravity arises from the curvature of spacetime. " ]
    , el [ Element.height (px 5) ] Element.none
    ]


thingsToTry : List (Element Msg)
thingsToTry =
    [ el [ Element.width fill, Element.height (px 2), Background.color borderColor ] Element.none
    , paragraph []
        [ text "Watch what happens to the blue curve on the orbit panel "
        , text "as you look between the green and teal regions on the main screen. "
        ]
    , el [ Element.width fill, Element.height (px 2), Background.color borderColor ] Element.none
    , paragraph []
        [ text "Look carefully at the timeward-pointing edges of the lattice. "
        , text "They all bend away from the planet! "
        , text "This corresponds to the fact that if you want to hover in place above a planet, "
        , text "you need to continually accelerate away from it. "
        ]
    , paragraph []
        [ text "Every point on the surface of a planet is constantly accelerating outward. "
        , text "And yet, the planet isn't getting bigger! "
        , text "This is only possible because of the curvature of spacetime. "
        , text "You could describe this as space shrinking inside the planet. "
        ]
    , el [ Element.width fill, Element.height (px 2), Background.color borderColor ] Element.none
    , paragraph []
        [ text "If you look around enough, you may notice that rotation acts a bit weirdly. "
        , text "In particular, it's impossible to turn all the way around. "
        , text "This isn't just a weird quirk of my simulation; "
        , text "spacetime rotations are actually fundamentally different from space rotations. "
        ]
    , paragraph []
        [ text "If spacetime rotations acted \"normally\", time travel would be easy (in theory). "
        , text "You'd just have to accelerate until you made a U-turn in spacetime! "
        ]
    , paragraph []
        [ text "But with spacetime's actual geometry, it's impossible to make a U-turn. "
        , text "So that doesn't work. "
        ]
    , el [ Element.width fill, Element.height (px 2) ] Element.none
    , paragraph []
        [ text "Interestingly, if you assume spacetime rotations act \"normally\", "
        , text "you get the exact "
        , el [ Font.italic ] <| text "opposite "
        , text "of special relativity. Here's an example: "
        ]
    , paragraph []
        [ text "Imagine two people. "
        , text "One stays near Earth. The other flies to a nearby star and back. "
        , text "If you draw their paths in spacetime, you get a triangle. "
        , text "The first person's path forms the base of the triangle, "
        , text "and the second person's path forms the other two sides."
        , text "Clearly, the second person's path is longer. So they experience more time. "
        ]
    , paragraph []
        [ text "But in actual relativity, rotation doesn't act normally. "
        , text "In fact, it acts even more weirdly than in this simulation. "
        , text "This turns out to reverse most relativistic effects. "
        , text "So in actual relativity, the second person experiences "
        , el [ Font.italic ] <| text "less "
        , text "time than the first, not more. This is time dilation. "
        ]
    ]


viewQA : Model -> Element Msg
viewQA model =
    column
        [ Element.width fill
        , Background.color borderColor
        , padding 5
        , spacing 5
        , centerX
        ]
    <|
        List.map2
            (\info ->
                dropdown { fontScale = 2 }
                    { title = info.title
                    , msg = Dropdown (info.msg + 6)
                    , content =
                        textColumn
                            [ Element.width fill
                            , padding 5
                            , spacing 15
                            ]
                            info.content
                    }
            )
            qa
            (List.drop 6 <| Array.toList model.dropdowns)


qa : List { title : String, msg : Int, content : List (Element msg) }
qa =
    [ { title = "What am I seeing?"
      , msg = 0
      , content =
            [ paragraph []
                [ text "This is a view of the spacetime around a planet. "
                , text "You can see two dimensions of space, and one dimension of time. "
                , text "The spacial dimensions are represented by up/down and left/right, "
                , text "and the time direction by forward/backward. "
                ]
            , paragraph []
                [ text "The black grid is effectively graph paper; "
                , text "it shows the x, y, and t coordinate planes. "
                , text "The red line is the planet, at x = y = 0. "
                ]
            ]
      }
    , { title = "What are the controls?"
      , msg = 1
      , content =
            [ paragraph []
                [ text "Click the screen to lock or unlock the mouse pointer. "
                , text "While the pointer is locked, you can move the mouse to look around, "
                , text "and use the W, A, S, D, Space, and LeftShift keys to move around. "
                , text "If the pointer is not locked, movement is disabled. "
                ]
            ]
      }
    , { title = "What is the orbit panel?"
      , msg = 2
      , content =
            [ paragraph []
                [ text "While the main screen shows "
                , el [ Font.italic ] <| text "spacetime, "
                , text "the orbit panel just shows "
                , el [ Font.italic ] <| text "space. "
                , text "The red dot is the planet, at x = y = 0. "
                , text "The green dot is your location. "
                ]
            , paragraph []
                [ text "The blue curve corresponds to the straight line in spacetime "
                , text "that extends straight in front of and behind you. "
                , text "Due to the curvature of spacetime, the path looks curved when plotted only in space."
                ]
            , paragraph []
                [ text "Since freely falling objects follow straight paths through spacetime, "
                , text "the blue curve can also be seen as a possible trajectory for a falling object. "
                ]
            ]
      }
    , { title = "What is an orbit, anyway?"
      , msg = 3
      , content =
            [ paragraph []
                [ text "There are three ways a falling object can behave. "
                , text "If it is moving too slowly, it will hit the planet. "
                , text "This is called a "
                , el [ Font.italic ] <| text "suborbital trajectory. "
                , text "(My simulation does not implement collision.) "
                ]
            , paragraph []
                [ text "If the object is moving faster, it will loop around the planet forever. "
                , text "This is called an "
                , el [ Font.italic ] <| text "orbit. "
                ]
            , paragraph []
                [ text "Finally, if the object has enough speed, it will escape the planet's gravity, "
                , text "and continue flying away forever. "
                , text "This is called an "
                , el [ Font.italic ] <| text "escape trajectory. "
                ]
            , paragraph []
                [ text "Near Earth's surface, if you want to orbit, you must travel faster than 8 kilometers per second. "
                , text "If you reach 11 kilometers per second, you will be on an escape trajectory. "
                ]
            ]
      }
    , { title = "On the main screen, what are the colors?"
      , msg = 4
      , content =
            [ paragraph []
                [ text "Consider the straight line in spacetime "
                , text "that extends straight in front of and behind you. "
                , text "As I just explained, this can be viewed as the trajectory of a hypothetical object. "
                ]
            , paragraph []
                [ text "If you are looking at a green region, this path is an orbit. "
                , text "If you are looking at a teal region, this path is an escape trajectory. "
                , text "To see this, try watching what happens to the path on the orbit panel "
                , text "as you look between the green and teal regions. "
                ]
            ]
      }
    , { title = "What is the stats panel?"
      , msg = 5
      , content =
            [ paragraph []
                [ text "The stats panel contains a collection of information about the aforementioned trajectory. "
                , text "It also tells you the position and velocity at your specific location in spacetime. "
                ]
            , paragraph [] [ text "To explain a few of the terms:" ]
            , paragraph []
                [ text "Eccentricity is a measure of how elliptical an orbit is. "
                , text "Circular orbits have zero eccentricity. "
                , text "As the eccentricity gets closer to one, the ellipse gets flatter. "
                , text "Escape trajectories have eccentricity greater than one."
                ]
            , paragraph []
                [ text "Periapsis is the lowest point in the orbit, "
                , text "and apoapsis is the highest. "
                , text "The terms can also refer to the distances from the planet at those points in the orbit. "
                ]
            ]
      }
    , { title = "Where is the speed of light in this simulation?"
      , msg = 6
      , content =
            [ paragraph []
                [ text "In this project, I use the approximation that the speed of light is infinite. "
                , text "This means that relativistic effects do not show up in this simulation. "
                , text "So no cosmic speed limit, no precession, no time dilation, et cetera. "
                , text "In fact, under this approximation, the curved spacetime just reproduces Newtonian gravity! "
                ]
            ]
      }
    ]


dropdown : { fontScale : Int } -> { title : String, content : Element msg, msg : Bool -> msg } -> Bool -> Element msg
dropdown { fontScale } { title, content, msg } open =
    let
        ( symbol, optContent ) =
            if open then
                ( "▾ ", content )

            else
                ( "▸ ", Element.none )
    in
    column
        [ Background.color backgroundColor
        , Element.width fill
        , padding 5
        , spacing 5
        ]
        [ row
            [ Font.bold
            , Font.size (scaled fontScale)
            , Events.onClick (msg (not open))
            , Element.pointer
            , Element.width fill
            ]
            [ el [ alignTop ] (text symbol)
            , paragraph [] [ text title ]
            ]
        , optContent
        ]


scaled : Int -> Int
scaled =
    round << Element.modular 20 1.25


borderColor : Element.Color
borderColor =
    rgb 0.4 0.2 0.4


backgroundColor : Element.Color
backgroundColor =
    rgb 0.1 0.0 0.1
