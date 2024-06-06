<?php

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
        //$output .= '<div class="session-excerpt">' . get_the_excerpt() . '</div>';

// Pobieranie dodatkowych danych sesji
        $start_time = get_post_meta($post_id, 'czas_start', true);
        $end_time = get_post_meta($post_id, 'czas_zakonczenia', true);
        $scena = get_post_meta($post_id, 'scena_ids', true);
        $kongres_dzien = get_post_meta($post_id, 'presentation_day_id', true);
        $date = get_post_title_by_id($kongres_dzien);
        
        // Wyświetlenie dodatkowych danych sesji
        $output .= '<div class="session-details">';
        if ($date) {
            $output .= '<p><strong>Data:</strong> ' . esc_html($date) . '</p>';
        }
        if ($start_time && $end_time) {
            $output .= '<p><strong>Godzina:</strong> ' . esc_html($start_time) . ' - ' . esc_html($end_time) . '</p>';
        }
        if ($scena) {
            $output .= '<p><strong>Scena:</strong> ';
            foreach($scena as $scena_id){
                $output .= get_post_title_by_id($scena_id).', ';
            }
            $output = substr($output, 0, -2);
            $output .=  '</p>';
        }

        $output .= '</div>';

        $prelegenci = get_post_meta($post_id, 'prelegenci', true);
        if (is_array($prelegenci) && !empty($prelegenci)) {
            
            
            // Wyświetlenie prelegentów
            $output .= '<div class="session-speakers">';
            $output .= '<h2>Prelegenci</h2>';
            $output .= '<ul>';
            
            
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
            
            $output .= '</ul>';
            $output .= '</div>'; // .session-speakers
            $output .= '</div>'; // .container single-session
            
        }
    
        return $output;
    }
    
    return $content;
}
add_filter('the_content', 'modify_kongres_prezentacja_content');

function plan_ramowy_register_endpoints() {
    add_rewrite_rule('^conference-schedule$', 'index.php?conference_schedule=true', 'top');
    add_rewrite_tag('%conference_schedule%', '([^&]+)');
}
add_action('init', 'plan_ramowy_register_endpoints');

function plan_ramowy_endpoint_template($template) {
    global $wp_query;
    
    if (isset($wp_query->query_vars['conference_schedule'])) {
        $custom_template = plugin_dir_path(__FILE__) . '../public/templates/conference-schedule-template.php';
        if ($custom_template) {
            return $custom_template;
        }
    }
    
    return $template;
}
add_filter('template_include', 'plan_ramowy_endpoint_template');

function plan_ramowy_basic_authenticate() {
    global $wp_query;
    
    if (isset($wp_query->query_vars['conference_schedule'])){
        $username = 'your_username';
        $password = 'your_password';
        
        if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== $username || $_SERVER['PHP_AUTH_PW'] !== $password) {
            header('WWW-Authenticate: Basic realm="Conference Schedule"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Authorization required.';
            exit;
        }
    }
}
add_action('template_redirect', 'plan_ramowy_basic_authenticate');

function display_custom_fields_and_sessions($content) {
    if (is_singular('kongres_scena')) {
        global $post;

        // Sprawdź, czy parametr `day` jest ustawiony w URL
        if (isset($_GET['kd'])) {
            $day_id = intval($_GET['kd']);
            
            // Pobierz sesje związane z tą sceną i dniem
            $args = array(
                'post_type' => 'kongres_prezentacja',
                'meta_query' => array(
                    array(
                        'key' => 'scena_ids',
                        'value' => $post->ID,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'presentation_day_id',
                        'value' => $day_id,
                        'compare' => '='
                    )
                )
            );
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                $content .= '<h3>Sesje na tej scenie w wybranym dniu:</h3><ul>';
                while ($query->have_posts()) {
                    $query->the_post();
                    $content .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
                }
                $content .= '</ul>';
            } else {
                $content .= '<p>Brak sesji na tej scenie w wybranym dniu.</p>';
            }
            
            wp_reset_postdata();
        }
    }
    
    return $content;
}
add_filter('the_content', 'display_custom_fields_and_sessions');

// Funkcja do uruchamiania generowania PDF przy zapisywaniu/aktualizowaniu postów
function trigger_pdf_generation_on_save($post_id, $post, $update) {
    // Sprawdź, czy jest to zapis/aktualizacja postów typu kongres_dzien, kongres_scena lub kongres_prezentacja
    if (in_array($post->post_type, array('kongres_dzien', 'kongres_scena', 'kongres_prezentacja'))) {
        /*
         * ToDo: Check for new event creation
         * */
        // Wywołaj AJAX do generowania PDF
        generate_pdf_request(true);
    }
}
add_action('save_post', 'trigger_pdf_generation_on_save', 10, 3);
