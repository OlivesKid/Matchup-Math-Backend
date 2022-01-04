var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
    mix.
        scripts([
            "app.js",
            "common.js",
            "jquery.form.min.js",
        ], './public/assets/js/app.min.js')
        .scripts([
            "jquery-ui.js",
        ], './public/assets/js/jquery.ui.js')
          .scripts([
            "daterangepicker.js",
        ], './public/assets/js/daterangepicker.js')
          .scripts([
            "moment.min.js",
        ], './public/assets/js/moment.min.js')
        
        
        .styles([
            "animate.css",
            "font-awesome.css",
            "waves.css",
            "angular-material.css",
            "bootstrap.css",
            "materialdesignicons.css",
            "material-design-icons.css",
            "font.css",
            "app.css",
            "common.css",
        ], './public/assets/css/app.min.css')
         .styles([
            "daterangepicker.css",
        ], './public/assets/css/daterangepicker.css')
        .styles([
            'jquery-ui.css',
        ],'./public/assets/css/jquery.ui.css');
});
