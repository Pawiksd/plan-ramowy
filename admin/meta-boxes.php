<?php

add_action('save_post', 'save_congress_presentation_meta_data');
add_action('add_meta_boxes', 'add_congress_presentation_meta_boxes');
//add_filter('theme_page_templates', 'register_custom_page_templates');
//add_filter('template_include', 'load_custom_template');

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
    
    if (!empty($_POST['polaczenie'])) {
        update_post_meta(
            $post_id,
            '_polaczenie',
            $_POST['polaczenie']
        );
    }
}

function add_congress_presentation_meta_boxes()
{
    
    add_meta_box(
        'congress_presentation_day',       // ID meta boxa
        'Dzień Prezentacji',               // Tytuł meta boxa
        'congress_presentation_day_callback',  // Callback wyświetlający zawartość meta boxa
        'kongres_prezentacja',             // Typ postu, do którego dodawany jest meta box
        'side',                            // Położenie meta boxa
        'high'                          // Priorytet gdzie meta box powinien się pojawić
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
}

function congress_presentation_day_callback($post) {
    // Pobieranie zapisanej wartości
    $selected_day = get_post_meta($post->ID, 'presentation_day_id', true);
    
    // Pobieranie wszystkich dni zdefiniowanych jako CPT `kongres_dzien`
    $days = get_posts(['post_type' => 'kongres_dzien', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
    
    // Tworzenie rozwijanej listy dni
    echo '<select name="presentation_day_id" id="presentation_day_id">';
    echo '<option value="">Wybierz dzień...</option>';
    foreach ($days as $day) {
        $selected = ($selected_day == $day->ID) ? 'selected' : '';
        echo '<option value="' . esc_attr($day->ID) . '" ' . $selected . '>' . esc_html(get_the_title($day->ID)) . '</option>';
    }
    echo '</select>';
}

function congress_presentation_scena_meta_box_callback($post) {
    // Pobranie zapisanych wartości
    $selected_sceny = get_post_meta($post->ID, 'scena_ids', true);
    if (!is_array($selected_sceny)) {
        $selected_sceny = [];
    }
    
    $sceny = get_posts(['post_type' => 'kongres_scena', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
    
    // Dodaj pole wyboru z możliwością zaznaczenia wielu opcji
    echo '<select name="scena_ids[]" id="scena_ids" multiple size="' . count($sceny) . '" style="width: 100%;">';
    foreach ($sceny as $scena) {
        $selected = in_array($scena->ID, $selected_sceny) ? 'selected' : '';
        echo '<option value="' . esc_attr($scena->ID) . '" ' . $selected . '>' . esc_html(get_the_title($scena->ID)) . '</option>';
    }
    echo '</select>';
    echo '<p>Trzymaj klawisz Ctrl (Cmd na Mac) aby zaznaczyć więcej niż jedną scenę.</p>';
}

function congress_presentation_times_meta_box_callback($post)
{
    $czas_start = get_post_meta($post->ID, 'czas_start', true);
    $czas_zakonczenia = get_post_meta($post->ID, 'czas_zakonczenia', true);
    
    $timeslots = [
        '9:30 - 10:00', '10:00 - 10:30', '10:30 - 11:00', '11:00 - 11:30', '11:30 - 12:00',
        '12:00 - 12:30', '12:30 - 13:00', '13:00 - 13:30', '13:30 - 14:00', '14:00 - 14:30',
        '14:30 - 15:00', '15:00 - 15:30', '15:30 - 16:00', '16:00 - 16:30', '16:30 - 17:00',
        '17:00 - 17:30', '17:30 - 18:00', '18:00 - 18:30', '18:30 - 19:00', '19:00 - 23:00'
    ];
    
    echo '<label for="czas_start">Czas rozpoczęcia:</label>';
    echo '<select name="czas_start" id="czas_start">';
    foreach ($timeslots as $timeslot) {
        $times = explode(' - ', $timeslot);
        $selected = ($czas_start == $times[0]) ? 'selected' : '';
        echo '<option value="' . esc_attr($times[0]) . '" ' . $selected . '>' . esc_html($timeslot) . '</option>';
    }
    echo '</select>';
    
    echo '<label for="czas_zakonczenia">Czas zakończenia:</label>';
    echo '<select name="czas_zakonczenia" id="czas_zakonczenia">';
    foreach ($timeslots as $timeslot) {
        $times = explode(' - ', $timeslot);
        $selected = ($czas_zakonczenia == $times[1]) ? 'selected' : '';
        echo '<option value="' . esc_attr($times[1]) . '" ' . $selected . '>' . esc_html($timeslot) . '</option>';
    }
    echo '</select>';
}

function congress_presentation_colors_meta_box_callback($post)
{
    $bg_color = get_post_meta($post->ID, 'bg_color', true);
    $border_color = get_post_meta($post->ID, 'border_color', true);
    $text_color = get_post_meta($post->ID, 'text_color', true);
    
    echo '<p><label for="bg_color">Kolor tła:</label>';
    echo '<input type="color" id="bg_color" name="bg_color" value="' . esc_attr($bg_color) . '"></p>';
    
    echo '<p><label for="border_color">Kolor obramowania:</label>';
    echo '<input type="color" id="border_color" name="border_color" value="' . esc_attr($border_color) . '"></p>';
    
    echo '<p><label for="border_color">Kolor tekstu:</label>';
    echo '<input type="color" id="text_color" name="text_color" value="' . esc_attr($text_color) . '"></p>';
}

function register_custom_page_templates($templates)
{
    
    $new_template = plugin_dir_path(__FILE__) . '../public/templates/plan-ramowy-template.php';
    
    $templates[$new_template] = 'Program Ramowy';
    return $templates;
}

function load_custom_template($template)
{
    $post = get_post();
    $page_template = get_post_meta($post->ID, '_wp_page_template', true);
    
    if ($page_template && 'default' !== $page_template && file_exists($page_template)) {
        return $page_template;
    }
    
    return $template;
}
