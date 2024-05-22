<?php
function register_kongres_scena_cpt() {
    $labels = array(
        'name'               => 'Sceny',
        'singular_name'      => 'Scena',
        'menu_name'          => 'Sceny',
        'name_admin_bar'     => 'Scena',
        'add_new'            => 'Dodaj nową',
        'add_new_item'       => 'Dodaj nową scenę',
        'new_item'           => 'Nowa scena',
        'edit_item'          => 'Edytuj scenę',
        'view_item'          => 'Zobacz scenę',
        'all_items'          => 'Wszystkie sceny',
        'search_items'       => 'Szukaj scen',
        'parent_item_colon'  => 'Scena nadrzędna:',
        'not_found'          => 'Nie znaleziono scen',
        'not_found_in_trash' => 'Nie znaleziono scen w koszu'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'scena', 'with_front' => false),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position' => 5,
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')
    );

    register_post_type('kongres_scena', $args);
}

add_action('init', 'register_kongres_scena_cpt');
