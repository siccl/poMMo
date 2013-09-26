module.exports = function(grunt) {
  grunt.initConfig({
    phplint: {
      all: [
        '*.php',
        'ajax/*.php',
        'classes/*.php'
      ]
    },
    phpcs: {
      all: {
        dir: '*.php'
      },
      ajax: {
        dir: 'ajax/*.php'
      }
    }
  });

  grunt.loadNpmTasks('grunt-phplint');
  grunt.loadNpmTasks('grunt-phpcs');

  grunt.registerTask(
    'default',
    [
      'phplint:all',
      'phpcs'
    ]
  );
};
