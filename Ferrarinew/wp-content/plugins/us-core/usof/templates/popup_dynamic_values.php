<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Popup for fields.
 *
 * @var string $id
 * @var $group_buttons array Groups of buttons for selecting dynamic values
 */

$popup_content = '';

// Buttons for select dynamic values
if ( ! empty( $group_buttons ) AND is_array( $group_buttons ) ) {

	// Remove unneeded ACF types
	if ( isset( $group_buttons['acf_types'] ) ) {
		unset( $group_buttons['acf_types'] );
	}

	foreach( $group_buttons as $group_name => $buttons ) {
		if ( empty( $buttons ) ) {
			continue;
		}

		// Predefined Group Names
		$predefined_group_names = array(
			'global' => __( 'Global Values', 'us' ),
			'term' => __( 'Term Data', 'us' ),
			'post' => __( 'Post Data', 'us' ),
			'media' => us_translate( 'Media File' ),
			'user' => __( 'User Data', 'us' ),
		);

		// Swap the slug to the predefined name if exists
		if ( isset( $predefined_group_names[ $group_name ] ) ) {
			$group_name = $predefined_group_names[ $group_name ];
			$is_predefined_group = TRUE;
		} else {
			$is_predefined_group = FALSE;
		}

		$popup_content .= '<div class="usof-popup-group">';
		$popup_content .= '<div class="usof-popup-group-title">' . strip_tags( $group_name ) . '</div>';
		$popup_content .= '<div class="usof-popup-group-values">';
		foreach ( $buttons as $value => $label ) {
			$button_atts = array(
				'class' => 'usof-popup-group-value',
				'data-dynamic-value' => $value,
				'data-dynamic-label' => $is_predefined_group ? $label : sprintf( '%s: %s', $group_name, $label ),
			);
			$popup_content .= '<button' . us_implode_atts( $button_atts ) . '>';
			$popup_content .= strip_tags( $label );
			$popup_content .= '</button>';
		}
		$popup_content .= '</div>'; // .usof-popup-group-values
		$popup_content .= '</div>'; // .usof-popup-group
	}
}

if ( empty( $popup_content ) ) {
	$popup_content .= '<div class="usof-popup-no-results">' . __( 'No relevant dynamic values found.', 'us' ) . '</div>';
}

// Output popup
us_load_template( 'usof/templates/popup', array(
	'id' => $id,
	'title' => __( 'Select Dynamic Value', 'us' ),
	'content' => $popup_content,
) );
