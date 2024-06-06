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
        'supports' => ['title', 'editor','excerpt','thumbnail'],
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
                ]
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

function modify_prelegenci_content($content) {
    if (is_singular('prelegenci')) {
        $post_id = get_the_ID();
        $output = '<div class="container single-prelegent">';
        
        // Dodanie obrazka prelegenta
        /*if (has_post_thumbnail()) {
            $output .= '<div class="prelegent-image">' . get_the_post_thumbnail($post_id, 'large', ['loading' => 'lazy']) . '</div>';
        }*/
        
        // Dodanie tytułu prelegenta
        $output .= '<div class="prelegent-details">';
        //$output .= '<h1>' . get_the_title() . '</h1>';
        
        // Dodanie treści prelegenta
        $output .= '<div class="prelegent-content">' . get_the_content() . '</div>';
        
        // Dodanie excerpt prelegenta
        //$output .= '<div class="prelegent-excerpt">' . get_the_excerpt() . '</div>';
        
        // Wyświetlenie wszystkich custom fields
        $custom_fields = get_post_custom($post_id);
        if (!empty($custom_fields)) {
            $output .= '<div class="prelegent-custom-fields"><h2>Dodatkowe informacje</h2><ul>';
            foreach ($custom_fields as $key => $value) {
                if (!is_protected_meta($key) && $key!='image') {
                    $output .= '<li>' . esc_html(implode(', ', $value)) . '</li>';
                }
            }
            $output .= '</ul></div>';
        }
        
        // Sekcja "Bierze udział w sesjach"
        $output .= '<div class="prelegent-sessions">';
        $output .= '<h2>Bierze udział w sesjach</h2>';
        $sessions = new WP_Query(array(
            'post_type' => 'kongres_prezentacja',
            'meta_query' => array(
                array(
                    'key' => 'prelegenci',
                    'value' => '"' . $post_id . '"',
                    'compare' => 'LIKE'
                )
            )
        ));
        
        if ($sessions->have_posts()) {
            $output .= '<ul>';
            while ($sessions->have_posts()) {
                $sessions->the_post();
                $output .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            }
            $output .= '</ul>';
        } else {
            $output .= '<p>Brak przypisanych sesji.</p>';
        }
        wp_reset_postdata();
        
        $output .= '</div>'; // .prelegent-sessions
        $output .= '</div>'; // .prelegent-details
        $output .= '</div>'; // .container single-prelegent
        
        return $output;
    }
    
    return $content;
}
add_filter('the_content', 'modify_prelegenci_content');

function modify_prelegenci_archive_content($query) {
    if ($query->is_main_query() && !is_admin() && $query->is_post_type_archive('prelegenci')) {
        add_action('loop_start', 'add_prelegenci_archive_container');
    }
}
//add_action('pre_get_posts', 'modify_prelegenci_archive_content');

function add_prelegenci_archive_container($query) {
    if ($query->is_post_type_archive('prelegenci') && $query->is_main_query()) {
        echo '<div class="container archive-prelegent">';
        echo '<h1>Prelegenci</h1>';
        echo '<div class="prelegent-list">';
    }
}

function close_prelegenci_archive_container() {
    if (is_post_type_archive('prelegenci')) {
        echo '</div>'; // .prelegent-list
        echo '</div>'; // .container archive-prelegent
    }
}
add_action('loop_end', 'close_prelegenci_archive_container');

function modify_arch_prelegenci_content($content) {
    if (is_post_type_archive('prelegenci') && in_the_loop() && is_main_query()) {
        $post_id = get_the_ID();
        ob_start();
        ?>
        <div class="prelegent-item">
            <?php if (has_post_thumbnail()) : ?>
                <div class="prelegent-image">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail('thumbnail', ['loading' => 'lazy']); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="prelegent-details">
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <div class="prelegent-excerpt"><?php the_excerpt(); ?></div>
                <?php
                $custom_fields = get_post_custom();
                if (!empty($custom_fields)) {
                    echo '<div class="prelegent-custom-fields"><ul>';
                    foreach ($custom_fields as $key => $value) {
                        if (!is_protected_meta($key)) {
                            echo '<li><strong>' . esc_html($key) . ':</strong> ' . esc_html(implode(', ', $value)) . '</li>';
                        }
                    }
                    echo '</ul></div>';
                }
                ?>
            </div> <!-- .prelegent-details -->
        </div> <!-- .prelegent-item -->
        <?php
        $content = ob_get_clean();
    }
    return $content;
}
add_filter('the_content', 'modify_arch_prelegenci_content');
