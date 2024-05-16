<?php
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
            $scene_bg_color = get_post_meta($dzien->ID, 'scene_bg_color_' . $scena->ID, true) ?: '#fff';
            if (strpos($scene_text_size, 'px') === false) {
                $scene_text_size .= 'px';
            }
            $scene_style = 'style="font-size:' . esc_attr($scene_text_size) . ';background-color:' . esc_attr($scene_bg_color) . ';"';
            $scene_link = get_permalink($scena->ID) . '?day=' . $dzien->ID;
            $output .= '<th scope="col" ' . $scene_style . '><a href="' . esc_url($scene_link) . '" style="color:' . esc_attr($scene_text_color) . ';">' . esc_html($scene_name) . '</a></th>';
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

                    $prelegenci = get_post_meta($prezentacja->ID, 'prelegenci', true)?:[];
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
