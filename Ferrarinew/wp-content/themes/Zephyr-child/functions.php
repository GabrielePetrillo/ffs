<?php
function my_child_theme_scripts() {
    // Carica lo script.js dal child theme
    wp_enqueue_script(
        'child-custom-script', // nome handle
        get_stylesheet_directory_uri() . '/scripts.js', // percorso
        array(), // dipendenze (es: array('jquery'))
        null, // versione
        true // in footer
    );
}
add_action('wp_enqueue_scripts', 'my_child_theme_scripts');


function mostra_titolo_pagina() {
    return get_the_title();
}
add_shortcode('titolo_pagina', 'mostra_titolo_pagina');




function tema_zaphyr_register_menus() {
  register_nav_menus([
    'menu-principale' => __('Menu principale', 'zaphyr')
  ]);
}
add_action('after_setup_theme', 'tema_zaphyr_register_menus');



include_once('shortcode/menu_custom.php' );
include_once('shortcode/polylang-switcher.php' );
include_once('shortcode/faculty.php' );
include_once('shortcode/flickr.php' );
include_once('class/walker-three-columns.php' );
include_once('class/MenuAlternativo.php' );
include_once('class/Highlight.php' );
include_once('class/Dynamic_Slider_Shortcode.php' );