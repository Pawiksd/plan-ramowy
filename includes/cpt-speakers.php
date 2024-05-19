<?php
function register_speakers_post_type()
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
        'rewrite' => ['slug' => 'prelegenci', 'with_front' => false],
        'supports' => ['title', 'editor','excerpt'],
        'menu_position' => 5,
        'show_in_rest' => true
    ]);

    // Add custom image size for speaker photos
    add_image_size('speaker-thumbnail', 500, 500, true);

    // Register the custom fields
    if (function_exists('register_field_group')) {
        register_field_group([
            'id' => 'acf_speakers',
            'title' => 'Prelegent',
            'fields' => [
                [
                    'key' => 'field_5a7b9a8c1d8df',
                    'label' => 'Biografia',
                    'name' => 'biografia',
                    'type' => 'textarea',
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => '',
                    'formatting' => 'br',
                ],
                [
                    'key' => 'field_5a7b9a8c1d8e0',
                    'label' => 'Image',
                    'name' => 'image',
                    'type' => 'image',
                    'save_format' => 'id',
                    'preview_size' => 'thumbnail',
                    'library' => 'all',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'prelegenci',
                    ],
                ],
            ],
        ]);
    }
}
add_action('init', 'register_speakers_post_type');
?>
