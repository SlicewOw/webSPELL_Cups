module.exports = function (grunt) {
    'use strict';

    grunt.initConfig({

        concat: {
            js_main: {
                src: [
                    'cup/js/pnotify.custom.min.js',
                    'cup/js/bootstrap.js',
                    'cup/js/ckeditor.js',
                    'cup/js/config.js',
                    'cup/js/flipclock.js',
                    'cup/js/google_graphs.js',
                    'cup/js/jquery.countdown.js',
                    'cup/js/bootstrap-select.js',
                    'cup/js/jquery-sortable.js',
                    'cup/js/cup_functions.js'
                ],
                dest: 'cup/dist/js/scripts.js'
            },
            css_main: {
                src: [
                    'cup/css/bootstrap-select.css',
                    'cup/css/flipclock.css',
                    'cup/css/pnotify.custom.min.css',
                    'cup/css/cup.css',
                    'cup/css/pages.css',
                    'cup/css/layout.css',
                    'cup/css/font.css'
                ],
                dest: 'cup/dist/css/styles.css'
            }
        },
        uglify: {
            js_main: {
                files: {
                    'cup/dist/js/scripts.min.js': [
                        'cup/dist/js/scripts.js'
                    ]
                }
            }
        },
        cssmin: {
            css: {
                files: {
                    'cup/dist/css/styles.min.css': [
                        'cup/dist/css/styles.css'
                    ]
                }
            }
        },
        watch: {
            js: {
                files: ['cup/js/**/*.js'],
                tasks: [
                    'concat:js_main',
                    'uglify:js_main'
                ]
            },
            css_static: {
                files: [
                    'cup/css/**/*.css'
                ],
                tasks: [
                    'concat:css_main',
                    'cssmin:css'
                ]
            }
        }
    });

    grunt.event.on('watch', function(action, filepath, target) {
        grunt.log.writeln(target + ': ' + filepath + ' has ' + action);
    });

    grunt.registerTask('default', ['concat']);
    grunt.registerTask(
        'all',
        [
            'concat',
            'cssmin',
            'uglify'
        ]
    );

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

};