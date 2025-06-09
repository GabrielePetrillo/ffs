<?php 

class MenuAlternativo {
    public static function render($field_group_name = 'alt-menu') {
        $campi_menu_alt = get_field($field_group_name);
        if (!$campi_menu_alt) return;

        echo '<nav class="alternative-menu grid grid--full">';
        echo '<div style="flex-direction:row" class="col-100 page-padding">';
      
 


        if (!empty($campi_menu_alt['menu_field'])) {
            echo '<ul class="alt-menu-list">';
            foreach ($campi_menu_alt['menu_field'] as $row) {
                $link = $row['field'];
                if ($link) {
                    $title = esc_html($link['title']);
                    $url = esc_url($link['url']);
                    $target = !empty($link['target']) ? ' target="' . esc_attr($link['target']) . '"' : '';
                    echo '<li><a href="' . $url . '"' . $target . '>' . $title . '</a></li>';
                }
            }
            echo '</ul>';
        }

    
        echo '</nav>';
    }
}
