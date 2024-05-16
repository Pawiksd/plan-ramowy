<?php
function register_congress_scene_cpt()
{
    register_post_type('kongres_scena', [
        'labels' => [
            'name' => 'Sceny Kongresu',
            'singular_name' => 'Scena Kongresu',
            'add_new' => 'Dodaj Nową Scenę',
            'add_new_item' => 'Dodaj Nową Scenę Kongresu',
            'edit_item' => 'Edytuj Scenę Kongresu',
            'new_item' => 'Nowa Scena Kongresu',
            'view_item' => 'Zobacz Scenę Kongresu',
            'search_items' => 'Szukaj Scen Kongresu',
            'not_found' => 'Nie znaleziono Scen Kongresu',
            'not_found_in_trash' => 'Nie znaleziono Scen Kongresu w koszu'
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor'],
        'menu_position' => 5,
        'show_in_rest' => true
    ]);
}

add_action('init', 'register_congress_scene_cpt');
