/**
 * Created by recchia on 28/09/16.
 */
var gulp = require('gulp'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify');
    uglifycss = require('gulp-uglifycss');

gulp.task('default', ['css', 'js', 'fonts']);

gulp.task('css', function () {
    gulp.src([
        'bower_components/bootstrap/dist/css/bootstrap.css',
        'bower_components/humane-js/themes/original.css',
        'app/Resources/assets/css/custom.css'
    ])
        .pipe(concat('app.css'))
        .pipe(uglifycss())
        .pipe(gulp.dest('web/css'))
});

gulp.task('js', function () {
    gulp.src([
        'bower_components/jquery/dist/jquery.js',
        'bower_components/bootstrap/dist/js/bootstrap.js',
        'bower_components/humane-js/humane.js',
        'app/Resources/assets/css/custom.js'
    ])
        .pipe(concat('app.js'))
        .pipe(uglify())
        .pipe(gulp.dest('web/js'))
});

gulp.task('fonts', function () {
    gulp.src('bower_components/bootstrap/dist/fonts/*.{eot,svg,ttf,woff,woff2}')
        .pipe(gulp.dest('web/fonts'))
});