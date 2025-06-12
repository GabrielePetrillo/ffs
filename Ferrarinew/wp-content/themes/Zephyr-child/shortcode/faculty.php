<?php 

function faculty($atts){

?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
 
            new Splide('#faculty-slider', {
                perPage: 4,
                gap: '1rem',
                rewind: true,
                breakpoints: {
                768: { perPage: 1 },
                1024: { perPage: 4 }
                }
            }).mount();
        });
    </script>

<?php

    $atts = shortcode_atts(
        array(
            'tag' => '',
        ),
        $atts,
        'faculty-grid'
    );

    $term = get_term_by('slug', sanitize_title($atts['tag']), 'coordinatori');

    $args = array(
        'post_type' => 'people',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'coordinatori',
                'field' => 'slug',
                'terms' => $term->slug,
            ),
        ),
    );

    $args2 = array(
        'post_type' => 'people',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'people-category',
                'field' => 'slug',
                'terms' => $term->slug,
            ),
        ),
    );

    $query = new WP_Query($args);
    $query2 = new WP_Query($args2);

    ob_start(); ?>

        <div id="faculty-slider" class="splide grid grid--full" aria-label="Faculty carousel">
            <div class="splide__track col-100 faculty">
                <ul class="splide__list">
                    <?php if($query->have_posts()) : ?>
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                            <li class="splide__slide col-25 coordinator">
                                <a href="<?php the_permalink(); ?>">
                                    <p class="coordinator-sign">Course leader</p>
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="faculty-thumbnail">
                                            <?php the_post_thumbnail('medium_large'); ?>
                                        </div>
                                        <div class="coordinator-name">
                                            <p><?php the_title(); ?></p>
                                        </div>
                                    <?php endif; ?>  
                                 </a>
                            </li>
                        <?php endwhile; ?>
                    <?php endif ?>
                    <?php if($query2->have_posts()) : ?>
                        <?php while ($query2->have_posts()) : $query2->the_post(); ?>
                            <li class="splide__slide col-25 coordinator">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="faculty-thumbnail">
                                            <?php the_post_thumbnail('medium_large'); ?>
                                        </div>
                                        <div class="faculty-name">
                                            <p><?php the_title(); ?></p>
                                        </div>
                                    <?php endif; ?>
                                 </a>
                            </li>
                        <?php endwhile; ?>
                    <?php endif ?>
                </ul>
            </div>
        </div>

<?php 
    return ob_get_clean();

}
add_shortcode('faculty-grid', 'faculty');