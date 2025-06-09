<?php 

class Highlight {
    public static function render($field_name = 'highlights') {
        $highlights = get_field($field_name);
        if (!$highlights || !is_array($highlights)) return;

        echo '<div class="grid grid--full col-100 highlights mt-2">';
        echo '<div class="col-100 grid">';

       
        foreach ($highlights as $row) {
            $type = !empty($row['highlight-type']) ? esc_html($row['highlight-type']) : '';
            $value = !empty($row['highlight-value']) ? esc_html($row['highlight-value']) : '';
            if ($type || $value) {
                 echo '<div class="col-33 sma-33 mb-1">';
                echo '<p>' . $type . '</p>';
                echo '<p><strong>' . $value . '</strong> </p>';
                echo '</div>'; // col-33
            }
        }
        

        echo '</div>'; // col-100 grid
        echo '</div>'; // grid grid--full
    }
}
