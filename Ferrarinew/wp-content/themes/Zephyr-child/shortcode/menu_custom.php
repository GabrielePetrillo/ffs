<?php 

function menu_custom(){
    
    ob_start(); ?>
<button class="hb-menu" id="hbMenu" aria-label="Apri menu" aria-expanded="false">
    <div class="hb-menu-icon">
        <span></span>
        <span></span>
    </div>

</button>
<div class="menu-container grid grid--full" id="menuContainer">
    <nav class="main-navigation" role="navigation" aria-label="Menu principale">
            <?php
                wp_nav_menu(array(
                    'theme_location' => 'menu-principale',
                    'container' => false,

                    'walker' => new Custom_Walker_Nav_Menu()
                ));
            ?>
    </nav>
</div>

    <?php
    return ob_get_clean();
}
add_shortcode('menu-custom', 'menu_custom');
