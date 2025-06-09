<?php
namespace home_slider;

class Plugin {
    public function __construct() {
       add_action('wp_enqueue_scripts', array($this, 'registerAsset'));
    }

        public function registerAsset(){
        wp_register_script('splide_js', plugins_url('/home slider/public/js/splide.js', SLIDER_PLUGIN_DIR), array(), '1.0.0', true);
        wp_register_style('spide_css', plugins_url('/home slider/public/css/splide.css', SLIDER_PLUGIN_DIR), array(), '1.0.0', false);

        wp_enqueue_style('spide_css');
        wp_enqueue_script('splide_js');
    }
}
