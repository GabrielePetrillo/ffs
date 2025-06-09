<?php 
namespace home_slider;

class home_shortcode {

    private static $instance;

    function __construct(){
        
        add_action('wp_enqueue_scripts', array($this, 'custom_code'));
        add_shortcode('home_slider', array($this, 'slider_cb'));
    }

    public function custom_code(){
        wp_register_style('home_slider-ferrari', plugins_url('/home slider/public/css/home-slider-ferrari.css', SLIDER_PLUGIN_DIR), array(), '1.0.0', false);
        wp_register_script('slider-custom',plugins_url('/home slider/public/js/slider-custom.js', SLIDER_PLUGIN_DIR), array('splide_js'), '1.0.0', true);
        wp_enqueue_style('home_slider-ferrari',20);
        wp_enqueue_script('slider-custom');
    }

    public function slider_cb(){
        

        ob_start();
        ?>
        <div class="home_slider-container">
            <section id="image-carousel" class="splide" aria-label="Beautiful Images">
            <div class="home_slider_grid home_slider_grid--full home_slider_text">
                <div class="">
                    <p class="home_slider-tag">News</p>
                </div>
                <h1>Title of page</h1>
                <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quaerat reprehenderit doloremque eum, quo quidem.</p>
                <div class="splide__arrows home_slider_col-100">
                    <button class="splide__arrow splide__arrow--prev" aria-label="Slide precedente">
                        <img src="<?= plugins_url('home slider/public/img/right-arrow.png') ?>" alt="" style="transform: rotate(180deg); width: 70%">
                    </button>
                    <button class="splide__arrow splide__arrow--next" aria-label="Slide successiva">
                        <img src="<?= plugins_url('home slider/public/img/right-arrow.png') ?>" alt="" style=" width: 70%">
                    </button>
                </div>
            </div>



  <div class="splide__track">
    <ul class="splide__list">
      <li class="splide__slide">
        <img src="https://www.ferrarifashionschool.it/wp-content/uploads/2023/11/luxury3.jpg" alt="Montagna innevata">
      </li>
      <li class="splide__slide">
        <img src="https://www.ferrarifashionschool.it/wp-content/uploads/2025/05/Borse-di-studio.webp" alt="Spiaggia al tramonto">
      </li>
      <li class="splide__slide">
        <img src="https://www.ferrarifashionschool.it/wp-content/uploads/2023/11/Fashion-Design-1.jpg" alt="Foresta autunnale">
      </li>
    </ul>
  </div>
</section>

        </div>
        <?php
        return ob_get_clean();
    }



    public static function getInstance() {
        if (Self::$instance == null) {
            Self::$instance = new home_shortcode();

        }
        return Self::$instance;
    }

}

 home_shortcode::getInstance();