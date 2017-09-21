require('es6-promise').polyfill();

var gulp = require('gulp'),
    cssimport = require('gulp-cssimport'),
    sass = require('gulp-sass'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    rev = require('gulp-rev'),
    revDel = require('rev-del'),
    revOriginDel = require('gulp-rev-delete-original'),
    prism = [
        'core',
        'markup',
        'css',
        'clike',
        'javascript',
        'apacheconf',
        'bash',
        'batch',
        'css-extras',
        'diff',
        'docker',
        'git',
        'handlebars',
        'http',
        'ini',
        'json',
        'less',
        'makefile',
        'markdown',
        'nginx',
        'php',
        'php-extrasx',
        'powershell',
        'puppet',
        'rest',
        'sass',
        'scss',
        'smarty',
        'sql',
        'twig',
        'vim',
        'yaml'
    ];

gulp.task('scripts', function () {
    var prismComponents = [];
    for (var component in prism) {
        prismComponents[component] = 'node_modules/prismjs/components/prism-' + prism[component] + '.min.js';
    }

    return gulp.src(prismComponents.concat([
            'node_modules/prismjs/plugins/normalize-whitespace/prism-normalize-whitespace.min.js',
            'node_modules/imagesloaded/imagesloaded.pkgd.js',
            'node_modules/isotope-layout/dist/isotope.pkgd.min.js',
            'js/portfolio.js',
            'js/jquery.hoverex.min.js'
        ]))
        .pipe(concat({path: 'scripts.js'}))
        .pipe(uglify({mangle: false}))
        .pipe(gulp.dest('../public/js/'));
});

gulp.task('styles', function () {
    return gulp.src('sass/*.scss')
        .pipe(cssimport({filter: /^..\/node_modules\//gi}))
        .pipe(sass({outputStyle: 'compressed'}))
        .pipe(gulp.dest('../public/css/'));
});

gulp.task('revision', ['styles', 'scripts'], function () {
    gulp.src([
            '../public/css/styles.css',
            '../public/js/scripts.js'
        ], {base: '../public'})
        .pipe(rev())
        .pipe(revOriginDel())
        .pipe(gulp.dest('../public/'))
        .pipe(rev.manifest())
        .pipe(revDel({
            oldManifest: 'rev-manifest.json',
            dest: '../public/',
            force: true
        }))
        .pipe(gulp.dest('./'));
});

gulp.task('default', ['revision']);
