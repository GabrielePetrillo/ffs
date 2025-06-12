<?php
function load_flickr_gallery_assets() {
    wp_enqueue_script('splide-js', 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.3/dist/js/splide.min.js', array(), null, true);
    wp_enqueue_style('splide-css', 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.3/dist/css/splide.min.css');
}
add_action('wp_enqueue_scripts', 'load_flickr_gallery_assets');

function flickr_gallery_shortcode($atts) {
    $atts = shortcode_atts(array(
        'api_key'     => 'ae7389a574ad799ce76de32153d17041',
        'user_id'     => '78794974@N08', 
        'photoset_id' => '72177720326681994',
    ), $atts, 'flickr-gallery');

    ob_start(); ?>

    <div id="flickr-gallery-slider" class="splide" aria-label="Flickr Photo Carousel">
        <div class="splide__track">
            <ul class="splide__list" id="flickr-gallery-list">
            </ul>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const apiKey = '<?php echo esc_js($atts['api_key']); ?>';
        const userId = '<?php echo esc_js($atts['user_id']); ?>';
        const photosetId = '<?php echo esc_js($atts['photoset_id']); ?>';

        const apiUrl = `https://api.flickr.com/services/rest/?method=flickr.photosets.getPhotos&api_key=${apiKey}&photoset_id=${photosetId}&user_id=${userId}&format=json&nojsoncallback=1`;

        fetch(apiUrl)
            .then(res => res.json())
            .then(data => {
                if (!data.photoset || !data.photoset.photo || data.photoset.photo.length === 0) {
                    throw new Error("Nessuna foto trovata.");
                }

                const photos = data.photoset.photo;
                const selected = photos.sort(() => 0.5 - Math.random()).slice(0, 8);
                const list = document.getElementById('flickr-gallery-list');

                selected.forEach((photo, index) => {
                    const imgSrc = `https://live.staticflickr.com/${photo.server}/${photo.id}_${photo.secret}_w.jpg`;

                    const li = document.createElement('li');
                    li.className = 'splide__slide';

                    const img = document.createElement('img');
                    img.src = imgSrc;
                    img.alt = photo.title || `Foto ${index + 1}`;
                    img.loading = 'lazy';
                    img.style.width = '100%';

                    li.appendChild(img);
                    list.appendChild(li);
                });

                new Splide('#flickr-gallery-slider', {
                    perPage: 4,
                    gap: '1rem',
                    rewind: true,
                    breakpoints: {
                        768: { perPage: 1 },
                        1024: { perPage: 2 }
                    }
                }).mount();
            })
            .catch(err => {
                console.error("Errore Flickr API:", err.message);
            });
    });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('flickr-gallery', 'flickr_gallery_shortcode');