<?php
// Redirect to the correct single template
function load_single_template($template)
{
    global $post;
    
    if ($post->post_type == 'prelegenci') {
        $template = plugin_dir_path(__FILE__) . '../public/templates/single-prelegenci.php';
    }
    
    return $template;
}
add_filter('single_template', 'load_single_template');

// Redirect to the correct archive template
function load_archive_template($template)
{
    if (is_post_type_archive('prelegenci')) {
        $template = plugin_dir_path(__FILE__) . '../public/templates/archive-prelegenci.php';
    }
    
    return $template;
}
add_filter('archive_template', 'load_archive_template');

// Hook into the template_include filter
//add_filter('template_include', 'load_custom_template_for_kongres_prezentacja');

// Function to load the custom template
function load_custom_template_for_kongres_prezentacja($template) {
    if (is_singular('kongres_prezentacja')) {
        // Check if the custom template file exists in the theme
        $custom_template = locate_template('single-kongres_prezentacja.php');
        
        // If the custom template file exists, use it
        if ($custom_template) {
            return $custom_template;
        } else {
            // If the custom template file doesn't exist in the theme, use the plugin's template
            return plugin_dir_path(__FILE__) . '../public/templates/single-kongres_prezentacja.php';
        }
    }
    return $template;
}


//add_action('admin_init', 'list_available_templates');

function list_available_templates() {
    if (current_user_can('manage_options')) {
        $templates = get_block_templates([], 'wp_template_part');
        echo '<pre>';
        print_r($templates);
        echo '</pre>';
        //exit;
    }
    
    
}

function modify_kongres_prezentacja_content($content) {
    if (is_singular('kongres_prezentacja') && in_the_loop() && is_main_query()) {
        ob_start();
        ?>
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
        <?php
        return ob_get_clean();
    }
    
    return $content;
}
add_filter('the_content', 'modify_kongres_prezentacja_content');
