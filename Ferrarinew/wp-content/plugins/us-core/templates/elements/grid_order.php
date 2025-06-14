<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: us_grid_order
 */

// Never output inside Grid items or specific Reusable Blocks
global $us_grid_outputs_items, $us_is_page_block_in_no_results, $us_is_page_block_in_menu;
if (
	$us_grid_outputs_items
	OR $us_is_page_block_in_no_results
	OR $us_is_page_block_in_menu
) {
	return;
}

// Never output Grid Order on AMP
if ( us_amp() ) {
	return;
}

// Don't output the Grid Order if there are no items for it
if ( empty( $orderby_items ) AND ! usb_is_post_preview() ) {
	return;
}

if ( is_string( $orderby_items ) ) {
	$orderby_items = json_decode( urldecode( $orderby_items ), TRUE );
}
if ( ! is_array( $orderby_items ) ) {
	$orderby_items = array();
}

global $us_grid_order_index;
$us_grid_order_index = ( isset( $us_grid_order_index ) AND is_numeric( $us_grid_order_index ) )
	? $us_grid_order_index++
	: 1;

// Unique identifier for orderby
$unique_id = sprintf( 'us_grid_order_%d', $us_grid_order_index );

// Get all options
$orderby_options = us_grid_get_orderby_options();

// Get options
$text_before = isset( $text_before )
	? $text_before
	: '';
$first_label = ! empty( $first_label )
	? trim( $first_label )
	: us_translate( 'Default' );

/**
 * @var bool
 */
$is_woocommerce = FALSE;

// Get post_type of the first grid on a page
if (
	! is_archive()
	AND $first_grid_post_types = (array) get_post_meta( get_the_ID(), '_us_first_grid_post_type', TRUE )
) {
	// Checking for signs of goods for WooCommerce
	$wc_post_types = array_merge(
		array( 'product' ),
		(array) us_config( 'group-params.products_show_values', array() )
	);
	foreach( $first_grid_post_types as $post_type ) {
		if ( strpos( $post_type, 'product' ) !== FALSE ) {
			$is_woocommerce = TRUE;
			break;
		}
	}
} else {
	// The check if this is the WooCommerce archive page
	$is_woocommerce = (
		$queried_object = get_queried_object()
		AND $queried_object instanceof WP_Term
		AND function_exists( 'is_product_category' )
		AND is_product_category( $queried_object )
	);
}

// Remove options for WooCommerce
if ( ! $is_woocommerce ) {
	foreach ( array_keys( us_config( 'group-params.products_orderby_values', array() ) ) as $key ) {
		if ( isset( $orderby_options[ $key ] ) ) {
			unset( $orderby_options[ $key ] );
		}
	}
}

$_atts = array(
	'class' => 'w-order for_grid',
	'action' => '',
	'method' => 'post',
	'onsubmit' => 'return false;',
);
$_atts['class'] .= $width_full ? ' width_full' : '';
$_atts['class'] .= $classes ?? '';

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Output the element
$output = '<form' . us_implode_atts( $_atts ) . '>';

// Label
if ( $text_before OR usb_is_post_preview() ) {
	$label_atts = array(
		'for' => $unique_id,
		'class' => 'w-order-label',
	);

	// Hide empty label in Live Builder preview
	if ( usb_is_post_preview() AND $text_before === '' ) {
		$label_atts['class'] .= ' hidden';
	}

	$output .= '<label' . us_implode_atts( $label_atts ) . '>' . strip_tags( $text_before ) . '</label>';
}

// Attributes for the select tag
$select_atts = array(
	'id' => $unique_id,
	'name' => us_get_grid_url_prefix( 'order' ),
	'aria-label' => us_translate( 'Order' ),
);

// Begin select
$output .= '<div class="w-order-select">';
$output .= '<select' . us_implode_atts( $select_atts ) . '>';

// Add default label
$output .= '<option value="">' . strip_tags( $first_label ) . '</option>';

// Add selected items
foreach ( $orderby_items as $item ) {
	if ( ! isset( $item['value'] ) OR ! in_array( $item['value'], array_keys( $orderby_options ) ) ) {
		continue;
	}

	// Attributes for the option tag
	$option_atts = array(
		'value' => us_arr_path( $item, 'value', '' ),
	);

	$custom_field = us_arr_path( $item, 'custom_field', '' );

	// Get text for every option
	if ( ! empty( $item['label'] ) ) {
		$text = $item['label'];
	} else {
		$text = $orderby_options[ $item['value'] ];
		if ( $item['value'] == 'custom' AND $custom_field ) {
			$text .= ': ' . $custom_field;
		}
		if ( ! empty( $item['invert'] ) ) {
			$text .= ' | ' . __( 'Invert order', 'us' );
		}
	}

	$option_params = array();

	// For default custom field
	if ( $option_atts['value'] === 'custom' ) {
		$option_atts['value'] = esc_attr( $custom_field );
		$option_params[] = 'field';
	}

	// Skip empty value
	if ( empty( $option_atts['value'] ) ) {
		continue;
	}

	// Additional parameters for value
	if ( us_arr_path( $item, 'custom_field_numeric', FALSE ) ) {
		$option_atts['value'] .= ',numeric';
	}
	if ( us_arr_path( $item, 'invert', FALSE ) ) {
		$option_atts['value'] .= ',asc';
	}

	// Checking the selected option
	global $us_get_orderby;
	if ( $option_atts['value'] == us_arr_path( $_GET, us_get_grid_url_prefix( 'order' ), $us_get_orderby ) ) {
		$option_atts['selected'] = 'selected';
	}

	$output .= '<option' . us_implode_atts( $option_atts ) . '>' . trim( strip_tags( $text ) ) . '</option>';
}

$output .= '</select>';
$output .= '</div>';
$output .= '</form>';

echo $output;
