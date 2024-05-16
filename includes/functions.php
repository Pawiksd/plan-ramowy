<?php
function register_congress_custom_post_types()
{
    // Kongres Dzień
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
    
    // Kongres Scena
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
    
    // Kongres Prezentacja
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
    
    // Prelegenci
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
    
    // Taxonomy for Prelegenci
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

add_action('init', 'register_congress_custom_post_types');

// Define time slots in one place
$timeslots = [];
$start = new DateTime('08:00');
$end = new DateTime('23:00');
$interval = new DateInterval('PT15M');
$period = new DatePeriod($start, $interval, $end);
foreach ($period as $time) {
    $next_time = clone $time;
    $next_time->add($interval);
    $timeslots[] = $time->format('H:i') . ' - ' . $next_time->format('H:i');
}

// Register the shortcode
function conference_schedule_shortcode()
{
    global $timeslots;
    
    // Retrieve post data
    $dni = get_posts(['post_type' => 'kongres_dzien', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);

    $activePresentations = [];
    $output = '<table id="plan-ramowy" aria-describedby="conference-schedule">';
    $output .= '<caption>Plan Ramowy</caption>';

    foreach ($dni as $dzien) {
        // Retrieve scene order and settings for the day
        $scene_order = get_post_meta($dzien->ID, 'scene_order', true);
        if ($scene_order) {
            $scene_ids = explode(',', $scene_order);
            $sceny = [];
            foreach ($scene_ids as $scene_id) {
                $sceny[] = get_post($scene_id);
            }
        } else {
            // Default to all scenes if no specific order is set
            $sceny = get_posts(['post_type' => 'kongres_scena', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
        }

        // Calculate time slots based on the presentations for the day
        $presentations = get_posts(['post_type' => 'kongres_prezentacja', 'numberposts' => -1, 'meta_query' => [
            ['key' => 'presentation_day_id', 'value' => $dzien->ID, 'compare' => '=']
        ]]);

        $timeslots = calculate_timeslots($presentations);
        
        $output .= '<thead>';
        $output .= '<tr><th scope="col" class="wss-nb" colspan="' . (count($sceny) + 1) . '"><strong>Dzień: ' . get_the_title($dzien->ID) . '</strong></th></tr>';
        $output .= '<tr><th scope="col" id="pr-godzina" class="wss-nb">Godzina</th>';
        foreach ($sceny as $scena) {
            $scene_name = get_post_meta($dzien->ID, 'scene_name_' . $scena->ID, true) ?: get_the_title($scena->ID);
            $scene_text_color = get_post_meta($dzien->ID, 'scene_text_color_' . $scena->ID, true) ?: '#000';
            $scene_text_size = get_post_meta($dzien->ID, 'scene_text_size_' . $scena->ID, true) ?: '16px';
            $scene_style = 'style="color:' . esc_attr($scene_text_color) . ';font-size:' . esc_attr($scene_text_size) . ';"';
            $scene_link = get_permalink($scena->ID) . '?day=' . $dzien->ID;
            $output .= '<th scope="col" ' . $scene_style . '><a href="' . esc_url($scene_link) . '">' . esc_html($scene_name) . '</a></th>';
        }
        $output .= '</tr></thead>';
        $output .= '<tbody>';

        // Loop through each timeslot and scene to generate the presentation cells
        foreach ($timeslots as $timeslotIndex => $timeslot) {
            $output .= '<tr>';
            $output .= '<th scope="row">' . $timeslot . '</th>';

            foreach ($sceny as $scena) {
                $scena_id = $scena->ID;

                // Check for active presentations
                if (!empty($activePresentations[$scena_id][$timeslotIndex])) {
                    continue; // Skip if the presentation is active
                }

                $prezentacja = znajdzPrezentacje($dzien->ID, $scena_id, $timeslot);
                if ($prezentacja) {
                    $colspan = obliczColspan($prezentacja);
                    $rowspan = obliczRowspan($prezentacja);
                    $bg_color = get_post_meta($prezentacja->ID, 'bg_color', true);
                    $border_color = get_post_meta($prezentacja->ID, 'border_color', true);
                    $text_color = get_post_meta($prezentacja->ID, 'text_color', true);
                    $style = 'style="background-color:' . esc_attr($bg_color) . ';color:' . esc_attr($text_color) . '; border-radius: 10px; border: 3px solid ' . esc_attr($border_color) . ';"';

                    $prelegenci = get_post_meta($prezentacja->ID, 'prelegenci', true);
                    $prelegenci_names = array_map('get_the_title', $prelegenci);
                    $tooltip_content = sprintf(
                        'Dzień: %s<br>Godzina: %s<br>Scena: %s<br>Prelegenci: %s<br>Podsumowanie: %s',
                        get_the_title($dzien->ID),
                        $timeslot,
                        get_post_meta($dzien->ID, 'scene_name_' . $scena->ID, true) ?: get_the_title($scena->ID),
                        implode(', ', $prelegenci_names),
                        esc_html(get_the_excerpt($prezentacja->ID))
                    );

                    $output .= '<td scope="cell" class="wss-nb" colspan="' . $colspan . '" rowspan="' . $rowspan . '" data-tooltip="' . esc_attr($tooltip_content) . '">';
                    $output .= '<div ' . $style . '>';
                    $output .= '<a href="' . get_permalink($prezentacja->ID) . '">' . get_the_title($prezentacja->ID) . '</a>';
                    $output .= '</div></td>';

                    // Set active state for the presentation
                    for ($i = $timeslotIndex; $i < $timeslotIndex + $rowspan; $i++) {
                        for ($j = 0; $j < $colspan; $j++) {
                            $activePresentations[$scena_id + $j][$i] = true;
                        }
                    }
                } else {
                    $output .= '<td></td>';
                }
            }
            $output .= '</tr>';
        }
        $output .= '</tbody>';
    }
    $output .= '</table>';

    // Return the HTML output
    return $output;
}

// Add the shortcode [conference_schedule] that calls the conference_schedule_shortcode function
add_shortcode('conference_schedule', 'conference_schedule_shortcode');

// Function to find a presentation
function znajdzPrezentacje($dzienId, $scenaId, $timeslot)
{
    list($start, $end) = explode(' - ', $timeslot);
    $prezentacje = get_posts([
        'post_type' => 'kongres_prezentacja',
        'numberposts' => -1,
        'meta_query' => [
            ['key' => 'czas_start', 'value' => $start, 'compare' => '='],
            ['key' => 'scena_ids', 'value' => serialize(strval($scenaId)), 'compare' => 'LIKE'],
            ['key' => 'presentation_day_id', 'value' => $dzienId, 'compare' => '=']
        ]
    ]);

    if (!empty($prezentacje)) {
        $prezentacja = $prezentacje[0];
        $prezentacja->tooltip_content = apply_filters('the_content', $prezentacja->post_content);
        return $prezentacja;
    }
    return null;
}

// Function to calculate the colspan for a presentation
function obliczColspan($prezentacja)
{
    return count(get_post_meta($prezentacja->ID, 'scena_ids', true));
}

// Function to calculate the rowspan for a presentation
function obliczRowspan($prezentacja)
{
    $czasStart = get_post_meta($prezentacja->ID, 'czas_start', true);
    $czasZakonczenia = get_post_meta($prezentacja->ID, 'czas_zakonczenia', true);
    $startDateTime = DateTime::createFromFormat('H:i', $czasStart);
    $endDateTime = DateTime::createFromFormat('H:i', $czasZakonczenia);
    if ($startDateTime === false || $endDateTime === false) {
        return 1;
    }
    $interval = $startDateTime->diff($endDateTime);
    $minutes = $interval->h * 60 + $interval->i;
    return ceil($minutes / 30);
}

// Function to calculate timeslots based on presentations
function calculate_timeslots($presentations)
{
    $start_times = array_map(function ($p) {
        return get_post_meta($p->ID, 'czas_start', true);
    }, $presentations);
    $end_times = array_map(function ($p) {
        return get_post_meta($p->ID, 'czas_zakonczenia', true);
    }, $presentations);

    $start_times = array_filter($start_times);
    $end_times = array_filter($end_times);

    if (empty($start_times) || empty($end_times)) {
        return ['09:30 - 10:00', '10:00 - 10:30', '10:30 - 11:00', '11:00 - 11:30', '11:30 - 12:00', '12:00 - 12:30', '12:30 - 13:00', '13:00 - 13:30', '13:30 - 14:00', '14:00 - 14:30', '14:30 - 15:00', '15:00 - 15:30', '15:30 - 16:00', '16:00 - 16:30', '16:30 - 17:00', '17:00 - 17:30', '17:30 - 18:00', '18:00 - 18:30', '18:30 - 19:00', '19:00 - 23:00'];
    }

    $earliest_start = min($start_times);
    $latest_end = max($end_times);

    $timeslots = [];
    $start = DateTime::createFromFormat('H:i', $earliest_start)->sub(new DateInterval('PT30M'));
    $end = DateTime::createFromFormat('H:i', $latest_end)->add(new DateInterval('PT30M'));
    $interval = new DateInterval('PT30M');
    $period = new DatePeriod($start, $interval, $end);

    foreach ($period as $time) {
        $next_time = clone $time;
        $next_time->add($interval);
        $timeslots[] = $time->format('H:i') . ' - ' . $next_time->format('H:i');
    }

    return $timeslots;
}


add_action('save_post', 'save_congress_presentation_meta_data');
add_action('add_meta_boxes', 'add_congress_presentation_meta_boxes');
add_action('add_meta_boxes', 'add_congress_day_meta_boxes');

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

function save_congress_day_meta_data($post_id)
{
    if (isset($_POST['scene_order'])) {
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

function congress_presentation_day_callback($post)
{
    $selected_day = get_post_meta($post->ID, 'presentation_day_id', true);
    $days = get_posts(['post_type' => 'kongres_dzien', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
    
    echo '<select name="presentation_day_id" id="presentation_day_id">';
    echo '<option value="">Wybierz dzień...</option>';
    foreach ($days as $day) {
        $selected = ($selected_day == $day->ID) ? 'selected' : '';
        echo '<option value="' . esc_attr($day->ID) . '" ' . $selected . '>' . esc_html(get_the_title($day->ID)) . '</option>';
    }
    echo '</select>';
}

function congress_presentation_scena_meta_box_callback($post)
{
    $selected_sceny = get_post_meta($post->ID, 'scena_ids', true);
    if (!is_array($selected_sceny)) {
        $selected_sceny = [];
    }
    
    $sceny = get_posts(['post_type' => 'kongres_scena', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
    
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
    global $timeslots;
    
    $czas_start = get_post_meta($post->ID, 'czas_start', true);
    $czas_zakonczenia = get_post_meta($post->ID, 'czas_zakonczenia', true);
    
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

function congress_presentation_prelegenci_meta_box_callback($post)
{
    $selected_prelegenci = get_post_meta($post->ID, 'prelegenci', true);
    if (!is_array($selected_prelegenci)) {
        $selected_prelegenci = [];
    }
    
    $prelegenci = get_posts(['post_type' => 'prelegenci', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
    
    echo '<select name="prelegenci[]" id="prelegenci" multiple size="' . count($prelegenci) . '" style="width: 100%;">';
    foreach ($prelegenci as $prelegent) {
        $selected = in_array($prelegent->ID, $selected_prelegenci) ? 'selected' : '';
        echo '<option value="' . esc_attr($prelegent->ID) . '" ' . $selected . '>' . esc_html(get_the_title($prelegent->ID)) . '</option>';
    }
    echo '</select>';
    echo '<p>Trzymaj klawisz Ctrl (Cmd na Mac) aby zaznaczyć więcej niż jednego prelegenta.</p>';
}

function congress_day_scenes_meta_box_callback($post)
{
    $scene_order = get_post_meta($post->ID, 'scene_order', true);
    if (!is_array($scene_order)) {
        $scene_order = [];
    }
    
    $sceny = get_posts(['post_type' => 'kongres_scena', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
    
    echo '<ul id="scene_order">';
    foreach ($sceny as $scena) {
        $scene_name = get_post_meta($post->ID, 'scene_name_' . $scena->ID, true);
        $bg_color = get_post_meta($post->ID, 'scene_bg_color_' . $scena->ID, true);
        $text_color = get_post_meta($post->ID, 'scene_text_color_' . $scena->ID, true);
        $text_size = get_post_meta($post->ID, 'scene_text_size_' . $scena->ID, true);
        echo '<li data-id="' . esc_attr($scena->ID) . '">';
        echo '<span class="handle">☰</span>';
        echo '<input type="text" name="scene_name_' . esc_attr($scena->ID) . '" value="' . esc_attr($scene_name) . '" placeholder="' . esc_html(get_the_title($scena->ID)) . '">';
        echo '<input type="color" name="scene_bg_color_' . esc_attr($scena->ID) . '" value="' . esc_attr($bg_color) . '" placeholder="Kolor tła">';
        echo '<input type="color" name="scene_text_color_' . esc_attr($scena->ID) . '" value="' . esc_attr($text_color) . '" placeholder="Kolor tekstu">';
        echo '<input type="text" name="scene_text_size_' . esc_attr($scena->ID) . '" value="' . esc_attr($text_size) . '" placeholder="Wielkość tekstu">';
        echo '</li>';
    }
    echo '</ul>';
    echo '<input type="hidden" id="scene_order_input" name="scene_order" value="' . esc_attr(implode(',', $scene_order)) . '">';
}

function congress_day_times_meta_box_callback($post)
{
    global $timeslots;
    
    $otwarcie = get_post_meta($post->ID, 'otwarcie', true);
    $zamkniecie = get_post_meta($post->ID, 'zamkniecie', true);
    
    echo '<label for="otwarcie">Czas Otwarcia:</label>';
    echo '<select name="otwarcie" id="otwarcie">';
    foreach ($timeslots as $timeslot) {
        $times = explode(' - ', $timeslot);
        $selected = ($otwarcie == $times[0]) ? 'selected' : '';
        echo '<option value="' . esc_attr($times[0]) . '" ' . $selected . '>' . esc_html($timeslot) . '</option>';
    }
    echo '</select>';
    
    echo '<label for="zamkniecie">Czas Zamknięcia:</label>';
    echo '<select name="zamkniecie" id="zamkniecie">';
    foreach ($timeslots as $timeslot) {
        $times = explode(' - ', $timeslot);
        $selected = ($zamkniecie == $times[1]) ? 'selected' : '';
        echo '<option value="' . esc_attr($times[1]) . '" ' . $selected . '>' . esc_html($timeslot) . '</option>';
    }
    echo '</select>';
}

// Save Congress Day Meta Data
add_action('save_post', 'save_congress_day_meta_data');

include(plugin_dir_path(__FILE__) . 'option-page.php');

