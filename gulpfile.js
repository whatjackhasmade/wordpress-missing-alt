"use strict"

const pluginAssets = `./assets`
const gulp = require("gulp")
const sass = require("gulp-sass")
const sassGlob = require("gulp-sass-glob")
const plumber = require("gulp-plumber")
const notify = require("gulp-notify")
const cleanCSS = require("gulp-clean-css")
const autoprefixer = require("gulp-autoprefixer")
const concat = require("gulp-concat")
const path = require("path")
const sourcemaps = require("gulp-sourcemaps")
const uglify = require("gulp-uglify-es").default
const babel = require("gulp-babel")
const imagemin = require("gulp-imagemin")
const ts = require("gulp-typescript")

let tsProject = null
if (!tsProject) {
  tsProject = ts.createProject("./tsconfig.json", {
    allowJs: true,
    noImplicitAny: true,
    outFile: "index-post-ts.js",
  })
}

gulp.task("sass", function () {
  return gulp
    .src([`${pluginAssets}/scss/index.scss`])
    .pipe(customPlumber("Error running Sass"))
    .pipe(sassGlob())
    .pipe(sass())
    .pipe(
      autoprefixer({
        cascade: false,
      })
    )
    .pipe(
      cleanCSS(
        {
          debug: true,
        },
        (details) => {
          console.log(`${details.name}: ${details.stats.originalSize}`)
          console.log(`${details.name}: ${details.stats.minifiedSize}`)
        }
      )
    )
    .pipe(gulp.dest(`./admin/css`))
    .pipe(gulp.dest(`./public/css`))
})

gulp.task("scripts", function () {
  return gulp
    .src([
      "node_modules/@babel/polyfill/dist/polyfill.js",
      `${pluginAssets}/scripts/polyfill/*.ts`,
      `${pluginAssets}/scripts/**/*.ts`,
    ])
    .pipe(tsProject())
    .pipe(sourcemaps.init())
    .pipe(
      babel({
        presets: ["@babel/env"],
      })
    )
    .pipe(concat("index.js"))
    .pipe(uglify())
    .pipe(sourcemaps.write("."))
    .pipe(gulp.dest(`./admin/js`))
    .pipe(gulp.dest(`./public/js`))
})

function customPlumber(errTitle) {
  return plumber({
    errorHandler: notify.onError({
      title: errTitle || "Error running Gulp",
      message: "Error: <%= error.message %>",
    }),
  })
}

gulp.task("serve", () => {
  gulp.watch(
    [`${pluginAssets}/scripts/**/*.js`, `${pluginAssets}/scripts/**/*.ts`],
    gulp.series("scripts")
  )
  gulp.watch([`${pluginAssets}/styles/**/*.scss`], gulp.series("sass"))
})

gulp.task("build", gulp.series(["sass", "scripts"]))

gulp.task("default", gulp.series(["sass", "scripts", "serve"]))
