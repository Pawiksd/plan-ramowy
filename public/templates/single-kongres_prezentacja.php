<?php
// Load Elementor header
if (function_exists('elementor_location_exits') && elementor_location_exits('header')) {
    elementor_location_render('header');
} else {
    get_header();
}

if (have_posts()) : while (have_posts()) : the_post(); ?>
    <div class="container single-session">
        <h1><?php the_title(); ?></h1>
        <div class="session-details">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('large'); ?>
            <?php endif; ?>
            <div class="session-content">
                <?php the_content(); ?>
            </div>
            <div class="session-excerpt">
                <?php the_excerpt(); ?>
            </div>
            <div class="session-speakers">
                <h2>Prelegenci</h2>
                <ul>
                    <?php
                    $prelegenci = get_post_meta(get_the_ID(), 'prelegenci', true);
                    if (is_array($prelegenci) && !empty($prelegenci)) {
                        foreach ($prelegenci as $prelegent_id) {
                            $prelegent = get_post($prelegent_id);
                            $thumbnail = get_the_post_thumbnail($prelegent_id, 'thumbnail');
                            $biografia = get_post_meta($prelegent_id, 'biografia', true);
                            echo '<li>';
                            echo $thumbnail;
                            echo '<h3>' . esc_html($prelegent->post_title) . '</h3>';
                            echo '<p>' . esc_html($biografia) . '</p>';
                            echo '</li>';
                        }
                    } else {
                        echo '<li>Brak prelegentów przypisanych do tej sesji.</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
<?php endwhile; endif;

// Load Elementor footer
if (function_exists('elementor_location_exits') && elementor_location_exits('footer')) {
    elementor_location_render('footer');
} else {
    get_footer();
}
?>
