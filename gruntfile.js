module.exports = function(grunt) {
  grunt.initConfig({
    phplint: {
      all: [
        '*.php',
        'ajax/*.php',
        'classes/*.php',
      ]
    }
  });

  grunt.loadNpmTasks('grunt-phplint');

  grunt.registerTask('default', ['phplint:all']);
};
