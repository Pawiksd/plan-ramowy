<?php
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



function render_kongres_prezentacja_sala_metabox($post) {
    wp_nonce_field('save_kongres_prezentacja_sala_metabox', 'kongres_prezentacja_sala_nonce');
    $value = get_post_meta($post->ID, '_kongres_prezentacja_sala', true);
    echo '<label for="kongres_prezentacja_sala">'.__('Sala', 'textdomain').'</label>';
    echo '<input type="text" id="kongres_prezentacja_sala" name="kongres_prezentacja_sala" value="' . esc_attr($value) . '" size="25" />';
}

function congress_presentation_times_meta_box_callback($post)
{
    
    $start = new DateTime('08:00');
    $end = new DateTime('23:00');
    $interval = new DateInterval('PT15M');
    $period = new DatePeriod($start, $interval, $end);
    foreach ($period as $time) {
        $next_time = clone $time;
        $next_time->add($interval);
        $timeslots[] = $time->format('H:i') . ' - ' . $next_time->format('H:i');
    }
    
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

function congress_day_scenes_meta_box_callback($post)
{
    
    $scene_order_str = get_post_meta($post->ID, 'scene_order', true);
    
    $scene_ids = explode(',', $scene_order_str);
    
    foreach ($scene_ids as $scene_id) {
        //$scc = get_post($scene_id);
        //if($scc->post_type === 'kongres_scena'){
            $scene_order[] = $scene_id ;
       // }
       
       
    }
    
    if (!is_array($scene_order)) {
        $scene_order = [];
    }
    
    $sceny = get_posts(['post_type' => 'kongres_scena', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);

    echo '<div class="scene-container"><select id="scene_select" style="width: 700px;margin-left: 100px;" multiple>';
    foreach ($sceny as $scena) {
        echo '<option value="' . esc_attr($scena->ID) . '">' . esc_html(get_the_title($scena->ID)) . '</option>';
    }
    echo '</select>';
    echo '<button type="button" id="add_scene_button">Dodaj Scenę</button></div>';
    
    echo '<ul id="scene_order" style="width: 700px;margin-left: 100px;margin-top: 20px;">';
    echo '<li><span style="width: 30px;">&nbsp;</span><span style="width: 180px;">Nazwa</span><span style="width: 150px;">Kolor Tła</span><span style="width: 150px;">Kolor Tekstu</span><span>Rozmiar Tekstu (px)</span><span>Usuń</span></li>';
    foreach ($scene_order as $scene_id) {
        $scene_name = get_post_meta($post->ID, 'scene_name_' . $scene_id, true);
        $bg_color = get_post_meta($post->ID, 'scene_bg_color_' . $scene_id, true);
        $text_color = get_post_meta($post->ID, 'scene_text_color_' . $scene_id, true);
        $text_size = get_post_meta($post->ID, 'scene_text_size_' . $scene_id, true);
        echo '<li data-id="' . esc_attr($scene_id) . '">';
        echo '<span class="handle">☰</span>';
        echo '<input type="text" name="scene_name_' . esc_attr($scene_id) . '" value="' . esc_attr($scene_name) . '" placeholder="' . esc_html(get_the_title($scene_id)) . '">';
        echo '<input type="text" class="hex-color" maxlength="7" pattern="#[a-fA-F0-9]{6}" name="scene_bg_color_' . esc_attr($scene_id) . '" value="' . esc_attr($bg_color) . '" placeholder="Kolor tła">';
        echo '<input type="text" class="hex-color" maxlength="7" pattern="#[a-fA-F0-9]{6}" name="scene_text_color_' . esc_attr($scene_id) . '" value="' . esc_attr($text_color) . '" placeholder="Kolor tekstu">';
        echo '<input type="text" name="scene_text_size_' . esc_attr($scene_id) . '" value="' . esc_attr($text_size) . '" placeholder="Wielkość tekstu">';
        echo '<button type="button" class="remove_scene_button">Usuń</button>';
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
