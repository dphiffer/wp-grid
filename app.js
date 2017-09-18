/*

Usage:
	nodejs app.js

*/

var io = require('socket.io')();
//io.set('origins', '*');

var grid = [];

for (var i = 0; i < 16; i++) {
	grid[i] = [];
}

io.on('connection', function(socket) {
	socket.emit('header-grid-init', grid);
	socket.on('header-grid-updates', function(updates) {
		socket.broadcast.emit('header-grid-updates', {
			from: socket.id,
			updates: updates
		});
		var row;
		for (var i = 0; i < 16; i++) {
			if (updates[i]) {
				row = updates[i];
				for (var j = 0; j < row.length; j++) {
					if (!grid[i][j]) {
						grid[i][j] = Math.min(255, row[j]);
					} else if (grid[i][j] < 255){
						grid[i][j] = Math.min(255, grid[i][j] + row[j]);
					}
				}
			}
		}
	});
	socket.on('header-grid-position', function(position) {
		socket.broadcast.emit('header-grid-position', {
			from: socket.id,
			i: position.i,
			j: position.j,
			href: position.href,
			title: position.title
		});
	});
	socket.on('disconnect', function() {
		socket.broadcast.emit('header-grid-disconnect', {
			from: socket.id
		});
	});
});
io.listen(3000);

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
