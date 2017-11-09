module.exports = function(grunt) {
	'use strict';

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		copy: {
			main: {
				src: [
					'assets/**',
					'!assets/css/scss/**',
					'inc/**',
					'languages/**',
					'composer.json',
					'CHANGELOG.md',
					'LICENSE.txt',
					'readme.txt',
					'woosimple.php'
				],
				dest: 'dist/',
				expand: true
			},
		},

		eslint: {
			options: {
				configFile: '.eslintrc'
			},
			target: [
				'assets/js/product-edit.js'
			]
		},

		uglify: {
			options: {
				banner: '/*! WooSimple - v<%= pkg.version %> */',
				sourceMap: true
			},
			main: {
				files: {
					'assets/js/product-edit.min.js': [
						'assets/js/product-edit.js'
					]
				}
			}
		},

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
			options: {
				outputStyle: 'compressed',
				sourceMap: true
			},
			dist: {
				files: [{
					expand: true,
					cwd: 'assets/css/scss',
					src: ['*.scss'],
					dest: 'assets/css',
					ext: '.css'
				}]
			}
		}
	});

	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	grunt.registerTask( 'build', [ 'eslint', 'i18n', 'sass', 'uglify', 'copy' ] );
	grunt.registerTask( 'i18n', [ 'makepot' ] );
	grunt.registerTask( 'default', [ 'eslint', 'sass', 'uglify' ] );

	grunt.util.linefeed = '\n';
};
