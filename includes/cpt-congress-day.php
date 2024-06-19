<?php
function register_congress_day_cpt()
{
    register_post_type('kongres_dzien', [
        'labels' => [
            'name' => 'Dni Kongresu',
            'singular_name' => 'Dzień Kongresu',
            'add_new' => 'Dodaj Nowy Dzień',
            'add_new_item' => 'Dodaj Nowy Dzień Kongresu',
            'edit_item' => 'Edytuj Dzień Kongresu',
            'new_item' => 'Nowy Dzień Kongresu',
            'view_item' => 'Zobacz Dzień Kongresu',
            'search_items' => 'Szukaj Dni Kongresu',
            'not_found' => 'Nie znaleziono Dni Kongresu',
            'not_found_in_trash' => 'Nie znaleziono Dni Kongresu w koszu'
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor'],
        'menu_position' => 5,
        'show_in_rest' => true
    ]);
}

add_action('init', 'register_congress_day_cpt');

// Meta boxes for Congress Day
function add_congress_day_meta_boxes()
{
    add_meta_box(
        'congress_day_scenes',
        'Lista scen',
        'congress_day_scenes_meta_box_callback',
        'kongres_dzien',
        'normal',
        'default'
    );
    
    add_meta_box(
        'congress_day_times',
        'Czas Otwarcia i Zamknięcia',
        'congress_day_times_meta_box_callback',
        'kongres_dzien',
        'side',
        'default'
    );
}

add_action('add_meta_boxes', 'add_congress_day_meta_boxes');

function save_congress_day_meta_data($post_id)
{
    
    if (isset($_POST['scene_order'])) {
    /*    $sco = trim(',',$_POST['scene_order']);
        $sco = str_replace(',,',',',$sco);
    */
        update_post_meta($post_id, 'scene_order', sanitize_text_field($_POST['scene_order']));
    }
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'scene_name_') === 0 || strpos($key, 'scene_text_size_') === 0) {
            update_post_meta($post_id, $key, sanitize_text_field($value));
        } elseif (strpos($key, 'scene_bg_color_') === 0 || strpos($key, 'scene_text_color_') === 0 ) {
            update_post_meta($post_id, $key, sanitize_hex_color($value));
        }
    }
    
    if (isset($_POST['otwarcie'])) {
        update_post_meta($post_id, 'otwarcie', sanitize_text_field($_POST['otwarcie']));
    }
    
    if (isset($_POST['zamkniecie'])) {
        update_post_meta($post_id, 'zamkniecie', sanitize_text_field($_POST['zamkniecie']));
    }
}

add_action('save_post', 'save_congress_day_meta_data');
