stars = [];
bounds = [];
milkyway = [];
constellation = []


$.get("./variables.txt", function(data) {
    var lines = data.split('\n');
	id=0;
	for (var i = 0; i < lines.length; i++) {
        data = lines[i].split(",");
		if (data[2] < 5.5){
			stars[id] = new Object();
			stars[id].ra=parseFloat(data[0]);
			stars[id].dec=parseFloat(data[1]);
			stars[id].mag=parseFloat(data[2]);
			stars[id].name=data[3]+"";
			id++;
		}
	}
});

$.get("./constNames.txt", function(data) {
    var lines = data.split('\n');
	for (var i = 0; i < lines.length; i++) {
        data = lines[i].split(",");
		constellation[i] = new Object();
		constellation[i].name = data[0];
		constellation[i].code = data[1];
		constellation[i].ra = parseFloat(data[2]);
		constellation[i].dec = parseFloat(data[3]);

	}
});

function isEmpty(str) {
    return (!str || 0 === str.length);
}

$.get("./milkyway.txt", function(data) {
    var lines = data.split('\n');
	for (var i = 0; i < lines.length; i++) {
        data = lines[i].split(",");
		milkyway[i]=new Object();
		milkyway[i].mode = data[0];
        milkyway[i].ra = parseFloat(data[1]);
		milkyway[i].de = parseFloat(data[2]);
	}
});


	