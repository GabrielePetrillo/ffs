<?php /*

Template Name: Home Template
*/ ?>

<?php get_header();  ?>

<main>

    <div class="">
		<?php echo do_shortcode('[home_slider]'); ?>
	</div>



        <?php the_content(); ?>

   

</main>


<?php get_footer(); ?>