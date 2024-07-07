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

// Function to round time to the nearest previous slot
function round_to_previous_slot($time) {
    $datetime = DateTime::createFromFormat('H:i', $time);
    if ($datetime === false) {
        return $time;
    }
    
    $minutes = (int) $datetime->format('i');
    if ($minutes < 30) {
        $datetime->setTime($datetime->format('H'), 0);
    } else {
        $datetime->setTime($datetime->format('H'), 30);
    }
    
    return $datetime->format('H:i');
}


// Register the shortcode
function conference_schedule_shortcode() {
    $output = '<div style="text-align: center;max-width:1200px;">';
    
    $upload_dir = wp_upload_dir();
    $pdf_url = $upload_dir['baseurl'] . '/program-ramowy.pdf';
    if (file_exists($upload_dir['basedir'] . '/program-ramowy.pdf')) {
        $output .= '<a href="' . esc_url($pdf_url) . '" download="program-ramowy.pdf" class="button">Pobierz Program</a>';
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
        
        // Loop through each timeslot and scene to generate the presentation cells
        foreach ($timeslots1 as $timeslotIndex => $timeslot) {
            $output .= '<tr>';
            $output .= '<th scope="row">' . $timeslot . '</th>';
            
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
                
                // Find all presentations in the current timeslot
                $prezentacje = znajdzPrezentacje($dzien->ID, $scena_id, $timeslot);
                if (!empty($prezentacje)) {
                    usort($prezentacje, function($a, $b) {
                        return strtotime(get_post_meta($a->ID, 'czas_start', true)) - strtotime(get_post_meta($b->ID, 'czas_start', true));
                    });
                    
                    $common_rowspan = max(array_map('obliczRowspan', $prezentacje));
                    $colspan = max(array_map('obliczColspan', $prezentacje));
                    $output .= '<td scope="cell" class="wss-nb" rowspan="' . $common_rowspan . '" colspan="' . $colspan . '">';
                    
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
                        
                        $scene_name = get_post_meta($dzien->ID, 'scene_name_' . $scena->ID, true) ?: get_the_title($scena->ID);
                        $tooltip_content = sprintf('Dzień: %s<br>Godzina: %s - %s',
                            get_the_title($dzien->ID),
                            $czas_start,
                            $czas_zakonczenia
                        );
                        
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
                        $div_height = ($minutes / ($common_rowspan * 30)) * 100 . '%'; // Calculate height percentage based on the duration
                        
                        $style = 'style="background-color:' . esc_attr($bg_color) . ';color:' . esc_attr($text_color) . '; border-radius: 10px; border: 3px solid ' . esc_attr($border_color) . ';height:' . $div_height . ';"';
                        $style2 = 'style="color:' . esc_attr($text_color) . ';"';
                        
                        $output .= '<div ' . $style . ' data-tooltip="' . esc_attr($tooltip_content) . '" >';
                        $output .= '<a href="' . get_permalink($prezentacja->ID) . '" ' . $style2 . '>' . get_the_title($prezentacja->ID) . '</a>';
                        $output .= '</div>';
                        
                        // Mark presentation as active with its remaining rowspan
                        for ($i = 0; $i < $common_rowspan; $i++) {
                            if (!isset($activePresentations[$scena_index + $i])) {
                                $activePresentations[$scena_index + $i] = [];
                            }
                            $activePresentations[$scena_index + $i][$timeslotIndex + $i] = [
                                'id' => $prezentacja->ID,
                                'remaining_rowspan' => $common_rowspan - $i
                            ];
                        }
                    }
                    
                    $output .= '</td>';
                    // Mark occupied columns
                    for ($i = 0; $i < $colspan; $i++) {
                        $occupiedColumns[$scena_index + $i] = $common_rowspan;
                    }
                    $scena_index += $colspan; // increment the scene index by the colspan value to skip over the spanned columns
                } else {
                    $output .= '<td></td>';
                    $scena_index++; // increment the scene index by 1 to move to the next scene
                }
            }
            $output .= '</tr>';
        }
        $output .= '</tbody></table>';
    }
    $output .= '</div>';
    
    // Return the HTML output
    return $output;
}

// Add the shortcode [conference_schedule] that calls the conference_schedule_shortcode function
add_shortcode('conference_schedule', 'conference_schedule_shortcode');

// Function to find a presentation
function znajdzPrezentacje($dzienId, $scenaId, $timeslot) {
    list($start, $end) = explode(' - ', $timeslot);
    
    // Convert start to DateTime object
    $start_time = DateTime::createFromFormat('H:i', $start);
    if ($start_time === false) {
        return [];
    }
    
    // Create 15 minutes later time
    $start_time_15min = clone $start_time;
    $start_time_15min->add(new DateInterval('PT15M'));
    
    // Format times back to strings
    $start = $start_time->format('H:i');
    $start_15min = $start_time_15min->format('H:i');
    
    // Find presentations that start at $start or $start_15min
    $prezentacje = get_posts([
        'post_type' => 'kongres_prezentacja',
        'numberposts' => -1,
        'meta_query' => [
            'relation' => 'AND',
            [
                'relation' => 'OR',
                ['key' => 'czas_start', 'value' => $start, 'compare' => '='],
                ['key' => 'czas_start', 'value' => $start_15min, 'compare' => '=']
            ],
            ['key' => 'scena_ids', 'value' => serialize(strval($scenaId)), 'compare' => 'LIKE'],
            ['key' => 'presentation_day_id', 'value' => $dzienId, 'compare' => '=']
        ]
    ]);
    
    if (!empty($prezentacje)) {
        foreach ($prezentacje as $prezentacja) {
            $prezentacja->tooltip_content = apply_filters('the_content', $prezentacja->post_content);
        }
        return $prezentacje;
    }
    
    return [];
}

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
    return ceil($minutes / 30);
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
        return ['09:30 - 10:00', '10:00 - 10:30', '10:30 - 11:00', '11:00 - 11:30', '11:30 - 12:00', '12:00 - 12:30', '12:30 - 13:00', '13:00 - 13:30', '13:30 - 14:00', '14:00 - 14:30', '14:30 - 15:00', '15:00 - 15:30', '15:30 - 16:00', '16:00 - 16:30', '16:30 - 17:00', '17:00 - 17:30', '17:30 - 18:00', '18:00 - 18:30', '18:30 - 19:00', '19:00 - 23:00'];
    }
    
    $earliest_start = round_to_nearest_half_hour(min($start_times));
    $latest_end = max($end_times);
    
    $timeslots1 = [];
    $start = DateTime::createFromFormat('H:i', $earliest_start)->sub(new DateInterval('PT30M'));
    $end = DateTime::createFromFormat('H:i', $latest_end)->add(new DateInterval('PT30M'));
    $interval1 = new DateInterval('PT30M');
    $period1 = new DatePeriod($start, $interval1, $end);
    
    // Generate timeslots1 (30-minute intervals)
    foreach ($period1 as $time) {
        $next_time = clone $time;
        $next_time->add($interval1);
        $timeslots1[] = $time->format('H:i') . ' - ' . $next_time->format('H:i');
    }
    
    return $timeslots1;
}
