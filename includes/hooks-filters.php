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
//add_filter('single_template', 'load_single_template');

// Redirect to the correct archive template
function load_archive_template($template)
{
    if (is_post_type_archive('prelegenci')) {
        $template = plugin_dir_path(__FILE__) . '../public/templates/archive-prelegenci.php';
    }
    
    return $template;
}
//add_filter('archive_template', 'load_archive_template');

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
    if (is_singular('kongres_prezentacja')) {
        $post_id = get_the_ID();
        $output = '<div class="container single-session">';
        
        // Dodanie tytułu sesji
        //$output .= '<h1>' . get_the_title() . '</h1>';
        
        // Dodanie obrazka sesji
        if (has_post_thumbnail()) {
            $output .= get_the_post_thumbnail($post_id, 'large', ['loading' => 'lazy']);
        }
        
        // Dodanie treści sesji
        $output .= '<div class="session-content">' . get_the_content() . '</div>';
        
        // Dodanie excerpt sesji
        $output .= '<div class="session-excerpt">' . get_the_excerpt() . '</div>';
        
        // Pobieranie dodatkowych danych sesji
        $start_time = get_post_meta($post_id, 'czas_start', true);
        $end_time = get_post_meta($post_id, 'czas_zakonczenia', true);
        $scena = get_post_meta($post_id, 'scena_ids', true);
        $kongres_dzien = get_post_meta($post_id, 'kongres_dzien', true);
        $date = get_the_title($kongres_dzien);
        
        // Wyświetlenie dodatkowych danych sesji
        $output .= '<div class="session-details">';
        if ($start_time && $end_time) {
            $output .= '<p><strong>Godzina:</strong> ' . esc_html($start_time) . ' - ' . esc_html($end_time) . '</p>';
        }
        if ($scena) {
            $output .= '<p><strong>Scena:</strong> ' . esc_html($scena[0]) . '</p>';
        }
        if ($date) {
            $output .= '<p><strong>Data:</strong> ' . esc_html($date) . '</p>';
        }
        $output .= '</div>';
        
        // Wyświetlenie prelegentów
        $output .= '<div class="session-speakers">';
        $output .= '<h2>Prelegenci</h2>';
        $output .= '<ul>';
        
        $prelegenci = get_post_meta($post_id, 'prelegenci', true);
        if (is_array($prelegenci) && !empty($prelegenci)) {
            $prelegenci_data = get_posts([
                'post_type' => 'prelegenci',
                'post__in' => $prelegenci,
                'orderby' => 'post__in',
                'posts_per_page' => -1
            ]);
            
            foreach ($prelegenci_data as $prelegent) {
                $thumbnail = get_the_post_thumbnail($prelegent->ID, 'thumbnail', ['loading' => 'lazy']);
                $biografia = get_post_meta($prelegent->ID, 'biografia', true);
                $excerpt = $prelegent->post_excerpt;
                $prelegent_link = get_permalink($prelegent->ID);
                
                $output .= '<li>';
                $output .= $thumbnail;
                $output .= '<div><h3>' . '<a href="' . esc_url($prelegent_link) . '">';
                $output .= esc_html($prelegent->post_title);
                $output .= '</a>' . '</h3>';
                $output .= '<p class="prelegent-excerpt">' . esc_html($excerpt) . '</p></div>';
                $output .= '</li>';
            }
        } else {
            $output .= '<li>Brak prelegentów przypisanych do tej sesji.</li>';
        }
        
        $output .= '</ul>';
        $output .= '</div>'; // .session-speakers
        $output .= '</div>'; // .container single-session
        
        return $output;
    }
    
    return $content;
}
add_filter('the_content', 'modify_kongres_prezentacja_content');


function remove_date_from_post( $the_date, $format, $post ) {
    if ( is_admin() ) {
        return $the_date;
    }
    
    if ( 'kongres_prezentacja' !== $post->post_type ) {
        return $the_date;
    }
    return '';
}

function remove_time_from_post( $the_time, $format, $post ) {
    if ( is_admin() ) {
        return $the_time;
    }
    
    if ( 'kongres_prezentacja' !== $post->post_type ) {
        return $the_time;
    }
    return '';
}

add_filter( 'get_the_date', 'remove_date_from_post', 10, 3 );
add_filter( 'get_the_time', 'remove_time_from_post', 10, 3 );
