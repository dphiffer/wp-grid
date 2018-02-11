module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		jshint: {
			dist: {
				src: ['js/header-grid.js']
			},
			options: {
				reporterOutput: ""
			}
		},
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
			},
			dist: {
				src: [
					'node_modules/socket.io/node_modules/socket.io-client/socket.io.js',
					'js/header-grid.js',
					'js/embed-placeholder.js'
				],
				dest: 'js/grid.js'
			}
		},
		sass: {
			dist: {
				src: ['css/grid.scss'],
				dest: 'style.css'
			}
		}
	});
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.registerTask('default', ['jshint', 'uglify', 'sass']);
};
