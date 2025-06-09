<?php /*

Template Name: Empty Template
Template Post Type: corsi, news, page, post, people
*/ ?>

<?php get_header();  ?>

<div  style="height: var(--header-height)"></div>

<main id="main">
    <div class="page-padding" style="margin: 0 auto">
        <?php
            if ( function_exists( 'us_load_template' ) ) {

                us_load_template( 'templates/single' );

            } else {
                the_content(); 
            }  
        ?>
    </div>
</main>