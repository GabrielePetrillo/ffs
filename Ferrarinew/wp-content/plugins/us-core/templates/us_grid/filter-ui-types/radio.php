<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Type: Radio Buttons
 *
 * @action Before the template: 'us_before_template:templates/us_grid/filter-ui-types/radio'
 * @action After the template: 'us_after_template:templates/us_grid/filter-ui-types/radio'
 * @filter Template variables: 'us_template_vars:templates/us_grid/filter-ui-types/radio'
 */

if ( empty( $item_values ) ) {
	return;
}

$output = '';

foreach ( $item_values as $i => $item_value ) {

	$_value = $item_value['value'] ?? $item_value;

	if ( $_value == '' ) {
		continue;
	}

	// Replace comma to escaped QUOTATION MARK, cause comma is used in URL to separate different values
	$encoded_value = rawurlencode( str_replace( ',', /*U+0201A*/'\‚', $_value ) );

	$_atts = array(
		'class' => 'w-filter-item-value',
		'data-value' => $_value,
	);

	if ( ! empty( $show_amount ) AND isset( $item_value['count'] ) ) {
		$_atts['data-post-count'] = (int) $item_value['count'];
	}
	if ( ! empty( $item_value['depth'] ) ) {
		$_atts['class'] .= ' depth_' . $item_value['depth'];
	}

	$_atts = apply_filters( 'us_list_filter_value_html_atts', $_atts, $item_value, $item_name );

	$output .= '<div' . us_implode_atts( $_atts ) . '>';
	$output .= '<label>';

	$input_atts = array(
		'type' => 'radio',
		'value' => $encoded_value,
		'name' => $item_name,
	);

	// First value is checked by default
	if ( $i == 0 ) {
		$input_atts['checked'] = 'checked';
	}

	$output .= '<input' . us_implode_atts( $input_atts ) . '>';

	$_label = esc_html( $item_value['label'] ?? $_value );

	$output .= '<span class="w-filter-item-value-label">' . apply_filters( 'us_list_filter_value_label', $_label, $item_value, $item_name ) . '</span>';

	if ( ! empty( $show_amount ) AND isset( $item_value['count'] ) ) {
		$output .= '<span class="w-filter-item-value-amount">' . $item_value['count'] . '</span>';
	}

	$output .= '</label>';
	$output .= '</div>'; // w-filter-item-value
}

echo $output;
