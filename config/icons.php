<?php

/*
 * This is the configfile for Icon Harvest
 */

return [

    /*
    |--------------------------------------------------------------------------
    | IconHarvest Files
    |--------------------------------------------------------------------------
    */

    'files' => (object) [
        'gmi' => (object) [
            'source'    => 'https://raw.githubusercontent.com/google/material-design-icons/master/iconfont/MaterialIcons-Regular.ijmap',
            'frontend'  => 'https://fonts.googleapis.com/icon?family=Material+Icons'
        ],
        'fa' => (object) [
            'source'   => 'https://raw.githubusercontent.com/FortAwesome/Font-Awesome/master/src/icons.yml',
            'frontend' => 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'
        ],
        '7-stroke' => (object) [
            'source'    => 'https://raw.githubusercontent.com/olimsaidov/pixeden-stroke-7-icon/master/pe-icon-7-stroke/scss/_variables.scss',
            'frontend'  => 'assets/icon-providers/pe-icon-7-stroke/css/pe-icon-7-stroke.css'
        ],
        'wp' => (object) [
            'source'    => 'assets/icon-providers/wp-dashicons/css/dashicons.css',
            'frontend'  => 'assets/icon-providers/wp-dashicons/css/dashicons.min.css'
        ],
    ],

];
