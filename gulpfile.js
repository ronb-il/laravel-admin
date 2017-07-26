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

elixir(function(mix) {
    mix.less('app.less', 'public/dist/css/sb-admin-2.css');
    mix.copy('public/components/bootstrap/dist/fonts', 'public/fonts');
    mix.copy('public/components/font-awesome/fonts', 'public/fonts');

    // bootstrap-datepicker
    mix.copy('public/components/bootstrap-daterangepicker/daterangepicker.js', 'public/scripts/');
    mix.copy('public/components/moment/moment.js', 'public/scripts/');
    mix.copy('public/components/bootstrap-daterangepicker/daterangepicker.css', 'public/styles/');

    mix.styles([
        'public/components/bootstrap/dist/css/bootstrap.css',
        'public/components/font-awesome/css/font-awesome.css',
        'public/dist/css/sb-admin-2.css',
        'public/dist/css/personali.css',
        'public/dist/css/timeline.css'
    ], 'public/styles/frontend.css', './');
    mix.scripts([
        'public/components/jquery/dist/jquery.js',
        'public/components/bootstrap/dist/js/bootstrap.js',
        'public/components/metisMenu/dist/metisMenu.js',
        'public/dist/js/sb-admin-2.js'
    ], 'public/scripts/frontend.js', './');
});
