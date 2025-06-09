<?php
namespace home_slider;

/*
* Plugin Name:       Home Slider
* Plugin URI:        https://
* Description:       This is the Home Slider Pluginn.
* Version:           1.0
* Requires at least: 6.8
* Requires PHP:      8.3.14
* Author:            Gabriele Petrillo
* Author URI:        https://gabrielepetrillo.com/
* License:           GPL v2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
* Update URI:        https://example.com/my-plugin/
* Text Domain:       home_slider
* Domain Path:       /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define('SLIDER_PLUGIN_DIR', __DIR__);

require_once SLIDER_PLUGIN_DIR . '/includes/class_activation.php';
require_once SLIDER_PLUGIN_DIR . '/includes/class_deactivator.php';
require_once SLIDER_PLUGIN_DIR . '/includes/class_plugin.php';
require_once SLIDER_PLUGIN_DIR . '/public/class/class-shortcode.php';

function activation() {
	\home_slider\Activator::attivazione();
}
function deactivation() {
	\home_slider\Deactivator::disattivazione();
}

register_activation_hook(__FILE__, __NAMESPACE__ . '\\activation');

register_activation_hook(__FILE__, __NAMESPACE__ . '\\deactivation');

$plugin = new \home_slider\Plugin();