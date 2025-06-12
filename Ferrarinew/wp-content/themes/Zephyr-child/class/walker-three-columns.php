<?php

class Custom_Walker_Nav_Menu extends Walker_Nav_Menu {

    function start_lvl(&$output, $depth = 0, $args = null) {
        $classes = array('sub-menu');

        if ($depth === 0) {
            $classes[] = 'level-2'; // Primo livello
        } elseif ($depth === 1) {
            $classes[] = 'level-3'; // Secondo livello
        } elseif ($depth === 2) {
            $classes[] = 'level-4';
        }

        $class_names = implode(' ', $classes);

        $output .= '<ul class="' . esc_attr($class_names) . '">';
    }


    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $class_names = implode(' ', array_map('esc_attr', $classes));

        $output .= '<li class="' . $class_names . '">';
        $output .= '<a href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a>';
    }

    function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
    }

    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }

}
