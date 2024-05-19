<?php
// Load Elementor header
if ( function_exists( 'elementor_location_exits' ) && elementor_location_exits( 'header' ) ) {
    elementor_location_render( 'header' );
} else {
    get_header();
}
?>

<div class="container">
    <h1><?php post_type_archive_title(); ?></h1>
    <div class="speakers-archive">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <div class="speaker">
                <a href="<?php the_permalink(); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('speaker-thumbnail'); ?>
                    <?php endif; ?>
                    <h2><?php the_title(); ?></h2>
                    <p><?php the_excerpt(); ?></p>
                </a>
            </div>
        <?php endwhile; endif; ?>
    </div>
</div>

<?php
// Load Elementor footer
if ( function_exists( 'elementor_location_exits' ) && elementor_location_exits( 'footer' ) ) {
    elementor_location_render( 'footer' );
} else {
    get_footer();
}
?>
