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
