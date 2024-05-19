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
add_filter('template_include', 'load_custom_template_for_kongres_prezentacja');

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
