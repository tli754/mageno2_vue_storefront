var gulp = require('gulp');
var sass = require('gulp-sass');
var watch = require('gulp-watch');
var uglify = require('gulp-uglify');
var tsify = require('tsify');
var sourcemaps = require("gulp-sourcemaps");
var _ = require('lodash');
var nodeSass = require('node-sass');
var cssmin = require('gulp-minify-css');
var liveReload = require("gulp-livereload");
var autoPrefixer = require("gulp-autoprefixer");

var configs = {
    source_dir: '/gulp/src',
    build_dir: '/assets',
    public_dir: '/assets',
    production: false,
};

gulp.task('watch', function() {
    liveReload.listen();
    watch(`${configs.source_dir}/sass/**/*.sass`, {}, gulp.series('css'));
    // watch(`${configs.source_dir}/js/**/*`, {}, gulp.series('js'));
    // watch(`${configs.source_dir}/icons/**/*`, {}, gulp.series('icons'));
    watch(`${configs.source_dir}/images/**/*`, {}, gulp.series('static'));
});

gulp.task('static', function() {
    return gulp.src(`${configs.source_dir}/images/**/*`, {base: configs.source_dir})
        .pipe(gulp.dest(configs.public_dir));
});

gulp.task('css', function() {
    stream = gulp.src(configs.source_dir+"/sass/**/[^_]*.sass")
        .pipe(sourcemaps.init())
        .pipe(sass({
            indentedSyntax: true,
            includePaths: "path/to/code",
            sourceComments: true,
            errLogToConsole: true,
            functions: {
                'glyph($code)': function(code) {
                    return new nodeSass.types.String("'\\"+obj[code.getValue()]+"'");
                },
                'image_url($uri)': function(uri) {
                    return new nodeSass.types.String("url('/images/"+uri.getValue()+"')");
                },
                'image_height($path)': function(path) {
                    var dimensions = sizeOf(path.getValue());
                    return new nodeSass.types.String(dimensions.height+'px');
                },
                'image_width($path)': function(path) {
                    var dimensions = sizeOf(path.getValue());
                    return new nodeSass.types.String(dimensions.width+'px');
                }
            }
        }));

    if (configs.production) {
        stream = stream.pipe(autoPrefixer())
            .pipe(cssmin({ keepBreaks: false }))
        ;
    } else {
        stream = stream.pipe(sourcemaps.write())
    }

    return stream.pipe(gulp.dest(configs.public_dir+'/css')).pipe(liveReload());
    }
);

gulp.task("default", gulp.series(gulp.parallel('static', 'css')));
