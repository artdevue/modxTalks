var gulp = require('gulp'),
    minify = require('gulp-minify-css'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename');

var paths = {
    dev: {
        css: 'assets/components/modxtalks/css/web/',
        js: 'assets/components/modxtalks/js/web/'
    },
    prod: {
        css: 'assets/components/modxtalks/css/web/',
        js: 'assets/components/modxtalks/js/web/'
    }
};

// CSS
gulp.task('css', function () {
    return gulp.src([
        paths.dev.css + 'bbcode/bbcode.css',
        paths.dev.css + '*.css'
    ])
        .pipe(concat('styles.min.css'))
        .pipe(gulp.dest(paths.prod.css))
        .pipe(minify({keepSpecialComments: 0}))
        .pipe(gulp.dest(paths.prod.css));
});

// JS
gulp.task('js', function () {
    return gulp.src([
        paths.dev.js + 'bbcode/bbcode.js',
        paths.dev.js + '*.js'
    ])
        .pipe(concat('scripts.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(paths.prod.js));
});

gulp.task('watch', function () {
    gulp.watch(paths.dev.css + '/*.css', ['css']);
    gulp.watch(paths.dev.js + '/*.js', ['js']);
});

gulp.task('default', ['css', 'js', 'watch']);
