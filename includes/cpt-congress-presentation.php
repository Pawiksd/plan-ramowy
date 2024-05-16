<?php
function register_congress_presentation_cpt()
{
    register_post_type('kongres_prezentacja', [
        'labels' => [
            'name' => 'Prezentacje Kongresu',
            'singular_name' => 'Prezentacja Kongresu',
            'add_new' => 'Dodaj Nową Prezentację',
            'add_new_item' => 'Dodaj Nową Prezentację Kongresu',
            'edit_item' => 'Edytuj Prezentację Kongresu',
            'new_item' => 'Nowa Prezentacja Kongresu',
            'view_item' => 'Zobacz Prezentację Kongresu',
            'search_items' => 'Szukaj Prezentacji Kongresu',
            'not_found' => 'Nie znaleziono Prezentacji Kongresu',
            'not_found_in_trash' => 'Nie znaleziono Prezentacji Kongresu w koszu'
        ],
        'public' => true,
        'publicly_queryable' => true,
        'rewrite' => ['slug' => 'sesja', 'with_front' => false],
        'has_archive' => true,
        'supports' => ['title', 'editor', 'custom-fields', 'excerpt'],
        'menu_position' => 5,
        'show_in_rest' => true
    ]);
}

add_action('init', 'register_congress_presentation_cpt');

// Meta boxes for Congress Presentation
function add_congress_presentation_meta_boxes()
{
    add_meta_box(
        'congress_presentation_day',
        'Dzień Prezentacji',
        'congress_presentation_day_callback',
        'kongres_prezentacja',
        'side',
        'high'
    );
    
    add_meta_box(
        'congress_presentation_scena',
        'Scena',
        'congress_presentation_scena_meta_box_callback',
        'kongres_prezentacja',
        'side',
        'high'
    );
    
    add_meta_box(
        'congress_presentation_times',
        'Czas Prezentacji',
        'congress_presentation_times_meta_box_callback',
        'kongres_prezentacja',
        'side',
        'default'
    );
    
    add_meta_box(
        'congress_presentation_colors',
        'Ustawienia Prezentacji',
        'congress_presentation_colors_meta_box_callback',
        'kongres_prezentacja',
        'side',
        'default'
    );
    
    add_meta_box(
        'congress_presentation_prelegenci',
        'Prelegenci',
        'congress_presentation_prelegenci_meta_box_callback',
        'kongres_prezentacja',
        'side',
        'default'
    );
}

add_action('add_meta_boxes', 'add_congress_presentation_meta_boxes');

function save_congress_presentation_meta_data($post_id)
{
    if (isset($_POST['czas_start'])) {
        update_post_meta($post_id, 'czas_start', sanitize_text_field($_POST['czas_start']));
    }
    
    if (isset($_POST['czas_zakonczenia'])) {
        update_post_meta($post_id, 'czas_zakonczenia', sanitize_text_field($_POST['czas_zakonczenia']));
    }
    
    if (isset($_POST['bg_color'])) {
        update_post_meta($post_id, 'bg_color', sanitize_hex_color($_POST['bg_color']));
    }
    
    if (isset($_POST['border_color'])) {
        update_post_meta($post_id, 'border_color', sanitize_hex_color($_POST['border_color']));
    }
    
    if (isset($_POST['text_color'])) {
        update_post_meta($post_id, 'text_color', sanitize_hex_color($_POST['text_color']));
    }
    
    if (isset($_POST['presentation_day_id'])) {
        update_post_meta($post_id, 'presentation_day_id', sanitize_text_field($_POST['presentation_day_id']));
    }
    
    if (isset($_POST['scena_ids'])) {
        $scena_ids = array_map('sanitize_text_field', $_POST['scena_ids']);
        update_post_meta($post_id, 'scena_ids', $scena_ids);
    } else {
        delete_post_meta($post_id, 'scena_ids');
    }
    
    if (isset($_POST['prelegenci'])) {
        $prelegenci_ids = array_map('sanitize_text_field', $_POST['prelegenci']);
        update_post_meta($post_id, 'prelegenci', $prelegenci_ids);
    } else {
        delete_post_meta($post_id, 'prelegenci');
    }
    
    if (!empty($_POST['podsumowanie'])) {
        update_post_meta($post_id, '_podsumowanie', $_POST['podsumowanie']);
    }
}

add_action('save_post', 'save_congress_presentation_meta_data');
