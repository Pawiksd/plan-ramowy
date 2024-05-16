<?php
function register_speakers_cpt()
{
    register_post_type('prelegenci', [
        'labels' => [
            'name' => 'Prelegenci',
            'singular_name' => 'Prelegent',
            'add_new' => 'Dodaj Nowego Prelegenta',
            'add_new_item' => 'Dodaj Nowego Prelegenta',
            'edit_item' => 'Edytuj Prelegenta',
            'new_item' => 'Nowy Prelegent',
            'view_item' => 'Zobacz Prelegenta',
            'search_items' => 'Szukaj Prelegentów',
            'not_found' => 'Nie znaleziono Prelegentów',
            'not_found_in_trash' => 'Nie znaleziono Prelegentów w koszu'
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_position' => 5,
        'show_in_rest' => true
    ]);
    
    register_taxonomy('prelegent_role', 'prelegenci', [
        'labels' => [
            'name' => 'Role Prelegentów',
            'singular_name' => 'Rola Prelegenta',
            'search_items' => 'Szukaj Ról',
            'all_items' => 'Wszystkie Role',
            'edit_item' => 'Edytuj Rolę',
            'update_item' => 'Aktualizuj Rolę',
            'add_new_item' => 'Dodaj Nową Rolę',
            'new_item_name' => 'Nazwa Nowej Roli',
            'menu_name' => 'Role'
        ],
        'hierarchical' => true,
        'show_in_rest' => true
    ]);
}

add_action('init', 'register_speakers_cpt');
