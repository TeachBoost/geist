var gulp = require( 'gulp' );
var minifyCSS = require( 'gulp-minify-css' );
var concat = require( 'gulp-concat' );
var uglify  = require( 'gulp-uglify' );

// minify CSS to build.css
gulp.task( 'build-css', function () {

    var cssFiles = [
        './public/css/base.css',
        './public/css/helpers.css',
        './public/css/forms.css',
        './public/css/buttons.css',
        './public/css/tables.css',
        './public/css/auth.css',
        './public/css/home.css',
        './public/css/posts.css',
        './public/css/admin.css',
        './public/css/font-awesome.css',
        './public/css/pikaday.css',
        './public/css/timepicker.css',
        './public/css/media.css'
    ];

    var opts = {
        keepBreaks: true
    };

    gulp.src( cssFiles )
        .pipe( concat( 'build.css' ) )
        .pipe( minifyCSS( opts ) )
        .pipe( gulp.dest( './public/css/' ) );

});

// minify vendor JS into build.js
gulp.task( 'build-js', function () {

    var jsFiles = [
        './public/js/vendor/jquery.js',
        './public/js/vendor/jquery.scrollTo.js',
        './public/js/vendor/jquery.timepicker.js',
        './public/js/vendor/pikaday.js',
        './public/js/vendor/jquery.pikaday.js',
        './public/js/vendor/underscore.js',
        './public/js/vendor/markdown.js',
        './public/js/vendor/cookies.min.js'
    ];

    gulp.src( jsFiles )
      .pipe( concat( 'build.js' ) )
      .pipe( uglify() )
      .pipe( gulp.dest( './public/js/dist/' ) );

});
