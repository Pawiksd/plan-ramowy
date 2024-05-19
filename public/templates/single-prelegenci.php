<?php
// Load Elementor header
if ( function_exists( 'elementor_location_exits' ) && elementor_location_exits( 'header' ) ) {
    elementor_location_render( 'header' );
} else {
    get_header();
}
?>

<div class="container">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="single-speaker">
            <h1><?php the_title(); ?></h1>
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('speaker-thumbnail'); ?>
            <?php endif; ?>
            <div class="biografia">
                <?php the_field('biografia'); ?>
            </div>
            <div class="content">
                <?php the_content(); ?>
            </div>
        </div>
    <?php endwhile; endif; ?>
</div>

<?php
// Load Elementor footer
if ( function_exists( 'elementor_location_exits' ) && elementor_location_exits( 'footer' ) ) {
    elementor_location_render( 'footer' );
} else {
    get_footer();
}
?>
