<?php
function register_congress_presentation_post_type()
{
    register_post_type('kongres_prezentacja', [
        'labels' => [
            'name' => 'Sesje',
            'singular_name' => 'Sesja',
            'add_new' => 'Dodaj Nową Sesję',
            'add_new_item' => 'Dodaj Nową Sesję',
            'edit_item' => 'Edytuj Sesję',
            'new_item' => 'Nowa Sesja',
            'view_item' => 'Zobacz Sesję',
            'search_items' => 'Szukaj Sesji',
            'not_found' => 'Nie znaleziono Sesji',
            'not_found_in_trash' => 'Nie znaleziono Sesji w koszu'
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
add_action('init', 'register_congress_presentation_post_type');

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
        'kongres_prezentacja_sala',
        __('Sala', 'textdomain'),
        'render_kongres_prezentacja_sala_metabox',
        'kongres_prezentacja',
        'side',
        'default'
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
        'kongres_prezentacja_moderators',
        __('Moderacja', 'textdomain'),
        'congress_presentation_moderators_meta_box_callback',
        'kongres_prezentacja',
        'side',
        'default'
    );
    
    add_meta_box(
        'congress_presentation_prelegenci',
        'Prelegenci',
        'congress_presentation_prelegenci_meta_box_callback',
        'kongres_prezentacja',
        'normal',
        'high'
    );
}

add_action('add_meta_boxes', 'add_congress_presentation_meta_boxes');

function save_congress_presentation_meta_data($post_id)
{
    if (!isset($_POST['kongres_prezentacja_sala_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['kongres_prezentacja_sala_nonce'], 'save_kongres_prezentacja_sala_metabox')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
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
        $prelegenci_ids = array_map('sanitize_text_field', explode(',', $_POST['prelegenci']));
        update_post_meta($post_id, 'prelegenci', $prelegenci_ids);
    } else {
        delete_post_meta($post_id, 'prelegenci');
    }
    
    if (!empty($_POST['podsumowanie'])) {
        update_post_meta($post_id, '_podsumowanie', $_POST['podsumowanie']);
    }
    
    if (isset($_POST['kongres_prezentacja_sala'])) {
        $sala = sanitize_text_field($_POST['kongres_prezentacja_sala']);
        update_post_meta($post_id, '_kongres_prezentacja_sala', $sala);
    }
    
    if (isset($_POST['moderators'])) {
        $moderator_ids = array_map('sanitize_text_field', explode(',', $_POST['moderators']));
        update_post_meta($post_id, 'moderators', $moderator_ids);
    } else {
        delete_post_meta($post_id, 'moderators');
    }
}

add_action('save_post', 'save_congress_presentation_meta_data');

function congress_presentation_prelegenci_meta_box_callback($post)
{
    $selected_prelegenci = get_post_meta($post->ID, 'prelegenci', true);
    
    if (!is_array($selected_prelegenci) || is_array_empty($selected_prelegenci)) {
        $selected_prelegenci = [];
    }
    
    
    
    echo '<div class="congress-presentation-prelegenci-meta-box">';
    echo '<ul id="prelegenci_list">';
    foreach ($selected_prelegenci as $prelegent_id) {
        
            $prelegent = get_post($prelegent_id);
            $thumbnail = get_the_post_thumbnail($prelegent_id, [50, 50], ['class' => 'prelegent-thumbnail']);
            echo '<li data-id="' . esc_attr($prelegent_id) . '">';
            echo '<span class="handle">☰</span>';
            echo $thumbnail;
            echo '<span>' . esc_html($prelegent->post_title) . '</span>';
            echo '<a href="#" class="remove-prelegent">Usuń</a>';
            echo '</li>';
        
    }
    echo '</ul>';
    echo '<select id="prelegent_select">';
    echo '<option value="">Wybierz prelegenta...</option>';
    
    $prelegenci = get_posts(['post_type' => 'prelegenci', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
    
    foreach ($prelegenci as $prelegent) {
        $thumbnail_url = get_the_post_thumbnail_url($prelegent->ID, [50, 50]);
        echo '<option value="' . esc_attr($prelegent->ID) . '" data-thumbnail="' . esc_attr($thumbnail_url) . '">' . esc_html($prelegent->post_title) . '</option>';
    }
    echo '</select>';
    echo '<button id="add_prelegent" class="button">Dodaj Prelegenta</button>';
    
    echo '<input type="hidden" name="prelegenci" id="prelegenci_input" value="' . esc_attr(implode(',', $selected_prelegenci)) . '">';
    echo '</div>';
}

function congress_presentation_moderators_meta_box_callback($post) {
    $selected_moderators = get_post_meta($post->ID, 'moderators', true);
    
    if (!is_array($selected_moderators) || is_array_empty($selected_moderators)) {
        $selected_moderators = [];
    }
    $moderators = get_posts(['post_type' => 'prelegenci', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
    echo '<div class="congress-presentation-moderators-meta-box">';
    echo '<ul id="moderators_list">';
    foreach ($selected_moderators as $moderator_id) {
        if(intVal($moderator_id)) {
            $moderator = get_post($moderator_id);
            $thumbnail = get_the_post_thumbnail($moderator_id, [50, 50], ['class' => 'moderator-thumbnail']);
            echo '<li data-id="' . esc_attr($moderator_id) . '">';
            echo '<span class="handle">☰</span>';
            echo $thumbnail;
            echo '<span>' . esc_html($moderator->post_title) . '</span>';
            echo '<a href="#" class="remove-moderator">Usuń</a>';
            echo '</li>';
        }
    }
    echo '</ul>';
    echo '<select id="moderator_select">';
    echo '<option value="">Wybierz moderatora...</option>';
    foreach ($moderators as $moderator) {
        $thumbnail_url = get_the_post_thumbnail_url($moderator->ID, [50, 50]);
        echo '<option value="' . esc_attr($moderator->ID) . '" data-thumbnail="' . esc_attr($thumbnail_url) . '">' . esc_html($moderator->post_title) . '</option>';
    }
    echo '</select>';
    echo '<button id="add_moderator" class="button">Dodaj Moderatora</button>';
    echo '<input type="hidden" name="moderators" id="moderators_input" value="' . esc_attr(implode(',', $selected_moderators)) . '">';
    echo '</div>';
}
