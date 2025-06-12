<?php
class Dynamic_Slider_Shortcode {
    protected static $instance_count = 0;

    public function __construct() {
        add_shortcode('dynamic-slider', [ $this, 'render_slider' ]);
    }

    public function render_slider($atts) {
        $defaults = [
            'post_type'   => '',
            'taxonomies'  => '',      // stringa con tassonomie separate da virgola, es: "coordinatori,people-category"
            'tags'        => '',      // stringa con slug separati da virgola, uno per ciascuna tassonomia
            'per_page'    => -1,
            'post_per_page' => 4,
        ];
        $atts = shortcode_atts($defaults, $atts, 'dynamic-slider');

        $taxonomies = array_filter(array_map('sanitize_text_field', explode(',', $atts['taxonomies'])));
        $tags       = array_filter(array_map('sanitize_title', explode(',', $atts['tags'])));

        $args = [
          'post_type'      => sanitize_text_field($atts['post_type']),
          'posts_per_page' => intval($atts['post_per_page']),
        ];

        // Costruiamo tax_query solo se entrambi i campi sono presenti e corrispondenti
        if (!empty($taxonomies) && count($taxonomies) === count($tags)) {
          $tax_query = ['relation' => 'AND'];
          foreach ($taxonomies as $i => $taxonomy) {
            $term = get_term_by('slug', $tags[$i], $taxonomy);
            if ($term) {
              $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => [$term->slug],
              ];
            }
          }
          if (count($tax_query) > 1) { // almeno una condizione + relation
            $args['tax_query'] = $tax_query;
          }
        }

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            return '<p>Nessun contenuto trovato.</p>';
        }

        $id = 'splide-' . (++self::$instance_count);

        ob_start(); ?>
        <div id="<?php echo esc_attr($id); ?>" class="splide class-slider">
          <div class="splide__track">
            <ul class="splide__list">
              <?php while ($query->have_posts()): $query->the_post(); ?>
              <li class="splide__slide">
                <a href="<?php the_permalink(); ?>">
                  <?php if (has_post_thumbnail()): ?>
                  <?php the_post_thumbnail('medium'); ?>
                  <?php endif; ?>
                  <h3><?php the_title(); ?></h3>
                </a>
              </li>
              <?php endwhile; wp_reset_postdata(); ?>
            </ul>
          </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  new Splide('#<?php echo esc_js($id); ?>', {
    perPage: <?php echo intval($atts['per_page']); ?>,
    gap: '1rem',
    rewind: true,
    breakpoints: {
      768: { perPage: 1 },
      1024: { perPage: <?php echo intval($atts['per_page']); ?> }
    }
  }).mount();
});
</script>
<?php
        return ob_get_clean();
    }
}

new Dynamic_Slider_Shortcode();
?>
