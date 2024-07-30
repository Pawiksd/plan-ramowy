<?php

// Function to round time to the nearest half hour
function round_to_nearest_half_hour($time) {
    $datetime = DateTime::createFromFormat('H:i', $time);
    if ($datetime === false) {
        return $time;
    }

    $minutes = (int) $datetime->format('i');
    if ($minutes < 15) {
        $datetime->setTime($datetime->format('H'), 0);
    } elseif ($minutes >= 15 && $minutes < 45) {
        $datetime->setTime($datetime->format('H'), 30);
    } else {
        $datetime->setTime($datetime->format('H') + 1, 0);
    }

    return $datetime->format('H:i');
}

// Function to round time to the nearest 15 minutes
function round_to_nearest_quarter_hour($time) {
    $datetime = DateTime::createFromFormat('H:i', $time);
    if ($datetime === false) {
        return $time;
    }

    $minutes = (int) $datetime->format('i');
    $minutes = round($minutes / 15) * 15;
    $datetime->setTime($datetime->format('H'), $minutes);

    return $datetime->format('H:i');
}

// Function to round time to the nearest previous slot
function round_to_previous_slot($time) {
    $datetime = DateTime::createFromFormat('H:i', $time);
    if ($datetime === false) {
        return $time;
    }

    $minutes = (int) $datetime->format('i');
    $minutes = floor($minutes / 15) * 15;
    $datetime->setTime($datetime->format('H'), $minutes);

    return $datetime->format('H:i');
}

// Register the shortcode
function conference_schedule_shortcode() {
    $output = '<div style="text-align: center;max-width:1200px;">';

    $upload_dir = wp_upload_dir();
    $pdf_url = $upload_dir['baseurl'] . '/program.pdf';
    if (file_exists($upload_dir['basedir'] . '/program.pdf')) {
        $output .= '<a href="' . esc_url($pdf_url) . '" download="program.pdf" class="button">Pobierz Program</a>';
    }

    // Retrieve post data
    $dni = get_posts(['post_type' => 'kongres_dzien', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);

    foreach ($dni as $dzien) {
        $activePresentations = [];
        $occupiedColumns = [];
        $output .= '<table class="conference-day-schedule">';
        // Retrieve scene order and settings for the day
        $scene_order = get_post_meta($dzien->ID, 'scene_order', true);

        if ($scene_order) {
            $scene_ids = explode(',', $scene_order);
            $sceny = [];
            foreach ($scene_ids as $scene_id) {
                $scc = get_post($scene_id);
                if ($scc->post_type === 'kongres_scena') {
                    $sceny[] = get_post($scene_id);
                }
            }
        } else {
            // Default to all scenes if no specific order is set
            $sceny = get_posts(['post_type' => 'kongres_scena', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
        }

        // Calculate time slots based on the presentations for the day
        $presentations = get_posts(['post_type' => 'kongres_prezentacja', 'numberposts' => -1, 'meta_query' => [
            ['key' => 'presentation_day_id', 'value' => $dzien->ID, 'compare' => '=']
        ]]);

        $timeslots1 = calculate_timeslots($presentations);

        $output .= '<thead>';
        $output .= '<tr><th scope="col" class="wss-nb" colspan="' . (count($sceny) + 1) . '"><strong>' . get_the_title($dzien->ID) . '</strong></th></tr>';
        $output .= '<tr><th scope="col" id="pr-godzina" class="wss-nb"></th>';
        foreach ($sceny as $scena) {
            $scene_name = get_post_meta($dzien->ID, 'scene_name_' . $scena->ID, true) ?: get_the_title($scena->ID);
            $scene_text_color = get_post_meta($dzien->ID, 'scene_text_color_' . $scena->ID, true) ?: '#000';
            $scene_text_size = get_post_meta($dzien->ID, 'scene_text_size_' . $scena->ID, true) ?: '16px';
            $scene_bg_color = get_post_meta($dzien->ID, 'scene_bg_color_' . $scena->ID, true) ?: '#fff';
            if (strpos($scene_text_size, 'px') === false) {
                $scene_text_size .= 'px';
            }
            $scene_style = 'style="font-size:' . esc_attr($scene_text_size) . ';background-color:' . esc_attr($scene_bg_color) . ';"';
            $scene_link = get_permalink($scena->ID) . '?kd=' . $dzien->ID;
            $output .= '<th scope="col" ' . $scene_style . '><a href="' . esc_url($scene_link) . '" style="color:' . esc_attr($scene_text_color) . ';">' . esc_html($scene_name) . '</a></th>';
        }
        $output .= '</tr></thead>';
        $output .= '<tbody>';

        // Initialize occupiedColumns array
        $occupiedColumns = array_fill(0, count($sceny), 0);

        // Retrieve all presentations for each scene
        $all_presentations = [];
        foreach ($sceny as $scena) {
            $scena_id = $scena->ID;
            $all_presentations[$scena_id] = get_posts([
                'post_type' => 'kongres_prezentacja',
                'numberposts' => -1,
                'meta_query' => [
                    ['key' => 'presentation_day_id', 'value' => $dzien->ID, 'compare' => '='],
                    ['key' => 'scena_ids', 'value' => $scena_id, 'compare' => 'LIKE']
                ]
            ]);
        }

        // Loop through each timeslot and scene to generate the presentation cells
        for ($i = 0; $i < count($timeslots1); $i++) {
            list($slot_start, $slot_end) = explode(' - ', $timeslots1[$i]);

            
                $output .= '<tr>';
            if ($i % 2 == 0) {
                $output .= '<th scope="row" rowspan="2">' . get_timeslot_display($timeslots1[$i]) . '</th>';
            }
            

                $scena_index = 0; // index to track the scene position
                while ($scena_index < count($sceny)) {
                    // Skip occupied columns
                    if ($occupiedColumns[$scena_index] > 0) {
                        $occupiedColumns[$scena_index]--;
                        $scena_index++;
                        continue;
                    }
    
                    $scena = $sceny[$scena_index];
                    $scena_id = $scena->ID;
    
                    // Find presentations that start within the current timeslot
                    $prezentacje = array_filter($all_presentations[$scena_id], function($prezentacja) use ($slot_start, $slot_end) {
                        $czas_start = get_post_meta($prezentacja->ID, 'czas_start', true);
                        
                        $start_time = DateTime::createFromFormat('H:i', $czas_start);
                        $s_start = DateTime::createFromFormat('H:i', $slot_start);
                        $s_end = DateTime::createFromFormat('H:i', $slot_end);
                        /*if($prezentacja->post_title == 'Wywiad (2)'){
                            echo '<pre>';
                            echo '<br/>';
                            var_dump($prezentacja->post_title);
                            var_dump($start_time);
                            var_dump($s_start);
                            var_dump($s_end);
                            var_dump($start_time >= $s_start);
                            var_dump($start_time < $s_end);
                            echo '</pre>';
                        }*/
                        return ($start_time >= $s_start && $start_time < $s_end);
                    });
    
                    if (!empty($prezentacje)) {
                        usort($prezentacje, function($a, $b) {
                            return strtotime(get_post_meta($a->ID, 'czas_start', true)) - strtotime(get_post_meta($b->ID, 'czas_start', true));
                        });
    
                        $common_rowspan = max(array_map('obliczRowspan', $prezentacje));
                        $colspan = max(array_map('obliczColspan', $prezentacje));
                        $output .= '<td scope="cell" class="wss-nb" rowspan="' . ceil($common_rowspan / 2) . '" colspan="' . $colspan . '">';
    
                        foreach ($prezentacje as $prezentacja) {
                            // Check if the presentation has already been displayed
                            if (in_array($prezentacja->ID, array_column($activePresentations[$scena_index] ?? [], 'id'))) {
                                continue;
                            }
    
                            $presentation_colspan = obliczColspan($prezentacja);
                            $rowspan = obliczRowspan($prezentacja);
                            $bg_color = get_post_meta($prezentacja->ID, 'bg_color', true);
                            $border_color = get_post_meta($prezentacja->ID, 'border_color', true);
                            $text_color = get_post_meta($prezentacja->ID, 'text_color', true);
                            $font_size = get_post_meta($prezentacja->ID, 'font_size', true) ?: '12';
                            $font_size .='px';
    
                            $prelegenci = get_post_meta($prezentacja->ID, 'prelegenci', true) ?: [];
                            $prelegenci_names = '';
                            if (!is_array_empty($prelegenci)) {
                                $prelegenci_names = array_map('get_the_title', $prelegenci);
                            }
    
                            $czas_start = get_post_meta($prezentacja->ID, 'czas_start', true);
                            $czas_zakonczenia = get_post_meta($prezentacja->ID, 'czas_zakonczenia', true);
    
                            $sala = get_post_meta($prezentacja->ID, '_kongres_prezentacja_sala', true);
                            $moderators = '';
                            $moderators_tmp = get_post_meta($prezentacja->ID, 'moderators', true);
    
                            if (!is_array_empty($moderators_tmp) && $moderators_tmp !== '') {
                                $moderators_list = [];
                                foreach ($moderators_tmp as $moderator_id) {
                                    $moderator = get_post($moderator_id);
                                    $moderators_list[] = esc_html($moderator->post_title);
                                }
                                $moderators = implode(', ', $moderators_list);
                            }
    
                            $tooltip_content = sprintf('<a href="%s" target="_blank" class="display-mobile">Dowiedz się więcej o sesji</a><br>', get_permalink($prezentacja->ID) );
    
                            $tooltip_content .= sprintf('Dzień: %s<br>Godzina: %s - %s',
                                get_the_title($dzien->ID),
                                $czas_start,
                                $czas_zakonczenia
                            );
    
                            $scene_name = get_post_meta($dzien->ID, 'scene_name_' . $scena->ID, true) ?: get_the_title($scena->ID);
                            if (!empty($scene_name)) {
                                $tooltip_content .= sprintf('<br>Scena: %s', $scene_name);
                            }
    
                            if (!empty($sala)) {
                                $tooltip_content .= sprintf('<br>Sala: %s', $sala);
                            }
    
                            if ($moderators !== '') {
                                $tooltip_content .= sprintf('<br>Moderacja: %s', $moderators);
                            }
    
                            if ($prelegenci_names) {
                                $tooltip_content .= sprintf('<br>Prelegenci: %s', implode(', ', $prelegenci_names));
                            }
    
                            $summary = esc_html(get_the_excerpt($prezentacja->ID));
                            if (!empty($summary)) {
                                $tooltip_content .= sprintf('<br>Podsumowanie: %s', $summary);
                            }
    
                            // Determine height of the div based on the duration
                            $start_time = DateTime::createFromFormat('H:i', $czas_start);
                            $end_time = DateTime::createFromFormat('H:i', $czas_zakonczenia);
                            $interval = $start_time->diff($end_time);
                            $minutes = $interval->h * 60 + $interval->i;
                            $div_height = ($minutes / 30) * 100 . '%'; // Calculate height percentage based on the duration
    
                            $style = 'style="background-color:' . esc_attr($bg_color) . ';color:' . esc_attr($text_color) . '; border-radius: 10px; border: 3px solid ' . esc_attr($border_color) . ';height:100%;"';
                            //. $div_height . ';"';
                            $style2 = 'style="color:' . esc_attr($text_color) . '; font-size:' . esc_attr($font_size) . ';"';
    
                            $output .= '<div ' . $style . ' data-tooltip="' . esc_attr($tooltip_content) . '" >';
                            $output .= '<a href="' . get_permalink($prezentacja->ID) . '" ' . $style2 . '>' . get_the_title($prezentacja->ID) . '</a>';
                            $output .= '</div>';
    
                            // Mark presentation as active with its remaining rowspan
                            for ($j = 0; $j < $common_rowspan; $j++) {
                                if (!isset($activePresentations[$scena_index + $j])) {
                                    $activePresentations[$scena_index + $j] = [];
                                }
                                if ($i / 2 + $j < count($timeslots1) / 2) {
                                    $activePresentations[$scena_index + $j][$i / 2 + $j] = [
                                        'id' => $prezentacja->ID,
                                        'remaining_rowspan' => $common_rowspan - $j
                                    ];
                                }
                            }
                        }
    
                        $output .= '</td>';
                        // Mark occupied columns
                        for ($j = 0; $j < $colspan; $j++) {
                            $occupiedColumns[$scena_index + $j] = ceil($common_rowspan / 2) - 1; // Subtract 1 to not occupy the next row
                        }
                        $scena_index += $colspan; // increment the scene index by the colspan value to skip over the spanned columns
                    } else {
                        $output .= '<td></td>';
                        $scena_index++; // increment the scene index by 1 to move to the next scene
                    }
                }
                $output .= '</tr>';
            
            /*            $output .='<tr>';
                        for ($j = 0; $j < count($sceny); $j++) {
                            if ($occupiedColumns[$j] > 0) {
                                $occupiedColumns[$j]--;
                                continue;
                            }
                            $output .= '<td></td>';
                        }
                        $output .= '</tr>';*/
        }
        $output .= '</tbody></table>';
    }
    $output .= '</div>';

    // Return the HTML output
    return $output;
}

// Add the shortcode [conference_schedule] that calls the conference_schedule_shortcode function
add_shortcode('conference_schedule', 'conference_schedule_shortcode');

// Function to calculate the colspan for a presentation
function obliczColspan($prezentacja) {
    return count(get_post_meta($prezentacja->ID, 'scena_ids', true));
}

// Function to calculate the rowspan for a presentation
function obliczRowspan($prezentacja) {
    $czasStart = get_post_meta($prezentacja->ID, 'czas_start', true);
    $czasZakonczenia = get_post_meta($prezentacja->ID, 'czas_zakonczenia', true);
    $startDateTime = DateTime::createFromFormat('H:i', $czasStart);
    $endDateTime = DateTime::createFromFormat('H:i', $czasZakonczenia);
    if ($startDateTime === false || $endDateTime === false) {
        return 1;
    }
    $interval = $startDateTime->diff($endDateTime);
    $minutes = $interval->h * 60 + $interval->i;
    return ceil($minutes / 15) * 2; // Divide by 15 to get the number of 15-minute slots
}

// Function to calculate timeslots based on presentations
function calculate_timeslots($presentations) {
    $start_times = array_map(function ($p) {
        return get_post_meta($p->ID, 'czas_start', true);
    }, $presentations);
    $end_times = array_map(function ($p) {
        return get_post_meta($p->ID, 'czas_zakonczenia', true);
    }, $presentations);

    $start_times = array_filter($start_times);
    $end_times = array_filter($end_times);

    if (empty($start_times) || empty($end_times)) {
        return generate_default_timeslots();
    }

    $earliest_start = round_to_nearest_half_hour(min($start_times));
    $latest_end = round_to_nearest_half_hour(max($end_times));

    $timeslots1 = [];
    $start = DateTime::createFromFormat('H:i', $earliest_start);
    $end = DateTime::createFromFormat('H:i', $latest_end);
    $interval1 = new DateInterval('PT15M');
    $period1 = new DatePeriod($start, $interval1, $end);

    // Generate timeslots1 (15-minute intervals)
    foreach ($period1 as $time) {
        $next_time = clone $time;
        $next_time->add($interval1);
        $timeslots1[] = $time->format('H:i') . ' - ' . $next_time->format('H:i');
    }

    return $timeslots1;
}

function generate_default_timeslots() {
    return [
        '09:00 - 09:15', '09:15 - 09:30', '09:30 - 09:45', '09:45 - 10:00',
        '10:00 - 10:15', '10:15 - 10:30', '10:30 - 10:45', '10:45 - 11:00',
        '11:00 - 11:15', '11:15 - 11:30', '11:30 - 11:45', '11:45 - 12:00',
        '12:00 - 12:15', '12:15 - 12:30', '12:30 - 12:45', '12:45 - 13:00',
        '13:00 - 13:15', '13:15 - 13:30', '13:30 - 13:45', '13:45 - 14:00',
        '14:00 - 14:15', '14:15 - 14:30', '14:30 - 14:45', '14:45 - 15:00',
        '15:00 - 15:15', '15:15 - 15:30', '15:30 - 15:45', '15:45 - 16:00',
        '16:00 - 16:15', '16:15 - 16:30', '16:30 - 16:45', '16:45 - 17:00',
        '17:00 - 17:15', '17:15 - 17:30', '17:30 - 17:45', '17:45 - 18:00',
        '18:00 - 18:15', '18:15 - 18:30', '18:30 - 18:45', '18:45 - 19:00'
    ];
}

// Function to get the display time for a 30-minute slot
function get_timeslot_display($timeslot1) {
    list($start1, $end1) = explode(' - ', $timeslot1);

    $start_time = DateTime::createFromFormat('H:i', $start1);
    $start_minutes = (int) $start_time->format('i');

    if ($start_minutes % 30 === 0) {
        // Slot starts at full hour or half-hour
        $end_time = clone $start_time;
        $end_time->add(new DateInterval('PT30M'));
    } else {
        // Slot starts at quarter-hour, round to nearest half-hour
        $start_time->sub(new DateInterval('PT15M'));
        $end_time = clone $start_time;
        $end_time->add(new DateInterval('PT30M'));
    }

    return $start_time->format('H:i') . ' - ' . $end_time->format('H:i');
}

?>
