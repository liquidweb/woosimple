module.exports = function(grunt) {
	'use strict';

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					exclude: [
						'\.git/*',
						'bin/*',
						'node_modules/*',
						'tests/*'
					],
					mainFile: 'woosimple.php',
					potFilename: 'woosimple.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: false
				}
			}
		},

		sass: {
			dist: {
				files: [{
					expand: true,
					cwd: 'assets/scss',
					src: ['*.scss'],
					dest: 'assets',
					ext: '.css'
				}]
			}
		}
	});

	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-wp-i18n');

	grunt.registerTask('i18n', ['makepot']);
	grunt.registerTask('default', ['sass']);

	grunt.util.linefeed = '\n';
};
