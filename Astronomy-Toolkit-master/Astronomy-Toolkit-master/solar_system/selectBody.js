function getSearchResults(searchQuerry){
  $.getJSON('../cgi-bin/getBody.py', { name: searchQuerry }, function(data) {
    $.each(data, function(index, element) {
      var body = new CelestialBody(element.name, element.semiMajorAxis, element.eccentricity,
         element.inclination, element.longitudeOfNode, element.longitudeOfPericenter, element.meanAnomaly2000);
      var bodySprite = new CelestialBodySprite(body, planetSpriteTexture, 20, true);
      var orbit = new Orbit(body, getRandomColorHex());

      planets.push(body);
      celestialBodySprites.push(bodySprite);
      orbits.push(orbit);
      addResult(bodySprite, orbit);
    });
    finishSearch();
  });

}

function getRandomColorHex(){
  return parseInt(randomColor({luminosity: 'dark'}).replace("#", "0x"));
}
