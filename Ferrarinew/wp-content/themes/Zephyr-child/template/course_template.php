<?php /*

Template Name: Course Template
Template Post Type: corsi
*/ ?>

<?php
function get_page_ancestors_links($post_id) {
    $ancestors = get_post_ancestors($post_id);
    $ancestors = array_reverse($ancestors); // dal più vecchio al più recente
    $links = [];

    foreach ($ancestors as $ancestor_id) {
        $url = get_permalink($ancestor_id);
        $title = get_the_title($ancestor_id);
        $links[] = '<a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
    }

    // Aggiungi la pagina corrente
    $current_url = get_permalink($post_id);
    $current_title = get_the_title($post_id);
    $links[] = '<a href="' . esc_url($current_url) . '">' . esc_html($current_title) . '</a>';

    return $links;
}

// Uso:
$breadcrumb_links = get_page_ancestors_links(get_the_ID());
?>


<?php get_header();  ?>

<?php 
$gruppo_campi = get_field('contenuti_del_corso'); 
$immagine = $gruppo_campi['immagine_corso'];
?>

<div  style="height: var(--header-height)"></div>

<main id="main">
    <div class="course-above grid grid--full">
        <div class="link-parent page-padding col-100 sma-none">
            <p style="text-transform: uppercase;"><?php echo implode(' / ', $breadcrumb_links); ?></p>
        </div>
        <div class="above-content col-100 grid grid--full">
            <div class="col-65 img-content desktop-none">
                <?php echo '<img src="' . esc_url($immagine['url']) . '" alt="' . esc_attr($immagine['alt']) . '">'; ?>
            </div>
            <div class="col-35 page-padding course-information grid">
                <div class="col-100">
                    <span class="tag-style"><?php echo $gruppo_campi['course_typology'];?></span>
                    <h1 class="mt-2"><?php echo get_the_title() ?></h1>
                </div>
            <?php HIGHLIGHT::render(); ?>
            </div>
            <div class="col-65 img-content sma-none">
                <?php echo '<img src="' . esc_url($immagine['url']) . '" alt="' . esc_attr($immagine['alt']) . '">'; ?>
            </div>
        </div>
    </div>

    <?php MenuAlternativo::render(); ?>
       





    <div style="margin: 0 auto" class="page-padding">
        <?php the_content(); ?>
    </div>
    
</main>


<?php get_footer(); ?>