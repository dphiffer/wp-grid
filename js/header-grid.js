(function() {
	
"use strict";

var grid  = [],
		gridI = null,
		gridJ = null,
		delta = 1,
		width,
		height,
		timeouts = {
			position: null
		},
		updates = [],
		size = (document.body.offsetWidth > 767 ? 15 : 10);

function initGrid() {
	for (var i = 0; i < 16; i++) {
		grid[i] = [];
	}
}
window.grid = grid;
initGrid();
var socketURL = location.protocol + '//' + location.hostname;
var options = {};
if (location.protocol == 'https:') {
	options.secure = true;
}
var socket = io(socketURL, options);

var header = document.getElementsByTagName('header');
if (header.length < 1) {
	return;
}
header = header[0];
width  = header.offsetWidth;
height = header.offsetHeight - 5;

window.addEventListener('resize', function() {
	width  = header.offsetWidth;
	height = header.offsetHeight - 5;
	size = (document.body.offsetWidth > 767 ? 15 : 10);
}, false);

var canvas = document.createElement('canvas');
canvas.setAttribute('id',     'header-grid');
canvas.setAttribute('width',  1280);
canvas.setAttribute('height', 240);
header.appendChild(canvas);

var pos = document.createElement('div');
pos.className = 'header-grid-user';
pos.setAttribute('id', 'header-grid-pos');
header.appendChild(pos);

var ctx = canvas.getContext('2d');

document.addEventListener('mousemove', function(e) {
	var x = e.pageX - header.offsetLeft;
	var y = e.pageY - header.offsetTop - header.offsetHeight;
	if (x < 0 || x >= header.offsetWidth || y < 0) {
		// Out of bounds
		gridI = null;
		gridJ = null;
		if (y < 0 && x > 0 && x <= header.offsetWidth) {
			header.className = 'hover';
		} else {
			header.className = '';
		}
		pos.className = 'header-grid-user';
		return;
	}
	header.className = '';
	var newGridI = Math.floor(16 * x / width);
	var newGridJ = Math.floor(16 * y / width);
	if (newGridI == gridI &&
	    newGridJ == gridJ) {
		return;
	}
	gridI = newGridI;
	gridJ = newGridJ;
	delta = (grid[gridI][gridJ] < 128) ? 1 : -1;
	pos.style.top  = (size * gridI) + 'px';
	pos.style.left = (size * gridJ) + 'px';
	pos.className  = 'header-grid-user visible';
	if (timeouts.position) {
		clearTimeout(timeouts.position);
		timeouts.position = null;
	}
	timeouts.position = setTimeout(function() {
		timeouts.position = null;
		pos.className = 'header-grid-user visible fade';
	}, 250);
	var href = '//' + location.hostname + location.pathname + location.search;
	socket.emit('header-grid-position', {
		i: gridI,
		j: gridJ,
		href: href,
		title: document.title
	});
}, false);

setInterval(function() {
	if (gridI === null || gridJ === null) {
		return;
	}
	if (!grid[gridI][gridJ]) {
		grid[gridI][gridJ] = delta;
	} else if (delta === 1 && grid[gridI][gridJ] < 254 ||
	           delta === -1 && grid[gridI][gridJ] > 1) {
		grid[gridI][gridJ] += delta;
	} else {
		grid[gridI][gridJ] += delta;
		delta = -delta;
	}
	if (!updates[gridI]) {
		updates[gridI] = [];
	}
	if (!updates[gridI][gridJ]) {
		updates[gridI][gridJ] = delta;
	} else {
		updates[gridI][gridJ] += delta;
	}
}, 100);

setInterval(function() {
	if (updates.length === 0) {
		return;
	}
	socket.emit('header-grid-updates', updates);
	updates = [];
}, 1000);

function drawGrid() {
	var row;
	ctx.fillStyle = '#fff';
	ctx.fillRect(0, 0, width, height);
	for (var i = 0; i < 16; i++) {
		row = grid[i];
		for (var j = 0; j < row.length; j++) {
			if (row[j]) {
				ctx.fillStyle = 'rgba(222, 222, 222, ' + (row[j] / 255) + ')';
				ctx.fillRect(j * size, i * size, size, size);
			}
		}
	}
	window.requestAnimationFrame(drawGrid);
}
window.requestAnimationFrame(drawGrid);

socket.on('header-grid-updates', function(data) {
	if (!data.from || !data.updates) {
		return;
	}
	var row;
	for (var i = 0; i < 16; i++) {
		if (data.updates[i]) {
			row = data.updates[i];
			for (var j = 0; j < row.length; j++) {
				if (row[j]) {
					if (!grid[i][j]) {
						grid[i][j] = Math.min(255, row[j]);
					} else if (grid[i][j] < 255) {
						grid[i][j] = Math.min(255, grid[i][j] + row[j]);
					}
				}
			}
		}
	}
});

socket.on('header-grid-init', function(data) {
	//console.log(JSON.stringify(data));
	var row;
	for (var i = 0; i < data.length; i++) {
		if (data[i]) {
			row = data[i];
			for (var j = 0; j < row.length; j++) {
				grid[i][j] = data[i][j];
			}
		}
	}
	canvas.className = 'visible';
});

socket.on('header-grid-position', function(data) {
	if (!data.from || !data.i || !data.j) {
		return;
	}
	var id   = 'header-grid-' + data.from;
	var user = document.getElementById(id);
	if (!user) {
		if (data.href) {
			user = document.createElement('a');
			user.setAttribute('href', data.href);
		} else {
			user = document.createElement('div');
		}
		user.setAttribute('id', id);
		header.appendChild(user);
	}
	user.className = 'header-grid-user visible';
	user.style.top  = (data.i * size) + 'px';
	user.style.left = (data.j * size) + 'px';
	user.title = data.title;
	if (timeouts[id]) {
		clearTimeout(timeouts[id]);
		timeouts[id] = null;
	}
	timeouts[id] = setTimeout(function() {
		timeouts[id] = null;
		user.className = 'header-grid-user visible fade';
	}, 250);
});

socket.on('header-grid-disconnect', function(data) {
	if (!data.from) {
		return;
	}
	var id   = 'header-grid-' + data.from;
	var user = document.getElementById(id);
	if (user) {
		header.removeChild(user);
	}
	if (timeouts[id]) {
		clearTimeout(timeouts[id]);
		timeouts[id] = null;
	}
});

setTimeout(function() {
	var row;
	for (var i = 0; i < 16; i++) {
		row = grid[i];
		for (var j = 0; j < row.length; j++) {
			if (row[j]) {
				row[j]--;
			}
		}
	}
}, 338824);

})();
