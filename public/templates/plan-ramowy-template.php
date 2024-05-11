<?php
/*
 * Template Name: Program Ramowy
 * Description: Szablon dla programu ramowego.
 */


get_header();


$timeslots = [
    '9:30 - 10:00', '10:00 - 10:30', '10:30 - 11:00', '11:00 - 11:30', '11:30 - 12:00',
    '12:00 - 12:30', '12:30 - 13:00', '13:00 - 13:30', '13:30 - 14:00', '14:00 - 14:30',
    '14:30 - 15:00', '15:00 - 15:30', '15:30 - 16:00', '16:00 - 16:30', '16:30 - 17:00',
    '17:00 - 17:30', '17:30 - 18:00', '18:00 - 18:30', '18:30 - 19:00', '19:00 - 23:00'
];

$sceny = get_posts(['post_type' => 'kongres_scena', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
$dni = get_posts(['post_type' => 'kongres_dzien', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);

$activePresentations = [];

echo '<table id="plan-ramowy1" aria-describedby="conference-schedule">';
echo '<caption>Plan Ramowy</caption>';
foreach ($dni as $dzien) {
    echo '<thead>';
    echo '<tr><th scope="col" class="wss-nb" colspan="' . (count($sceny) + 1) . '"><strong>Dzień: ' . get_the_title($dzien->ID) . '</strong></th></tr>';
    echo '<tr><th scope="col" id="pr-godzina" class="wss-nb">Godzina</th>';
    foreach ($sceny as $scena) {
        echo '<th scope="col">' . esc_html(get_the_title($scena->ID)) . '</th>';
    }
    echo '</tr></thead>';
    echo '<tbody>';
    
    foreach ($timeslots as $timeslotIndex => $timeslot) {
        echo '<tr>';
        echo '<th scope="row">' . $timeslot . '</th>';
        
        foreach ($sceny as $scenaIndex => $scena) {
            $scena_id = $scena->ID;
            
            // Sprawdzanie aktywności prezentacji
            if (!empty($activePresentations[$scena_id][$timeslotIndex])) {
                // Pomijanie komórki, jeśli prezentacja jest aktywna
                continue;
            }
            
            $prezentacja = znajdzPrezentacje($dzien->ID, $scena_id, $timeslot);
            if ($prezentacja) {
                $colspan = obliczColspan($prezentacja);
                $rowspan = obliczRowspan($prezentacja);
                $bg_color = get_post_meta($prezentacja->ID, 'bg_color', true);
                $border_color = get_post_meta($prezentacja->ID, 'border_color', true);
                $text_color = get_post_meta($prezentacja->ID, 'text_color', true);
                $style = 'style="background-color:' . esc_attr($bg_color) . ';color: '.esc_attr($text_color).'; border-radius: 10px; border: 3px solid ' . esc_attr($border_color) . ';"';
                echo '<td scope="cell" class="wss-nb"  colspan="' . $colspan . '" rowspan="' . $rowspan . '" data-tooltip="' . esc_attr(wp_strip_all_tags(get_post_field('post_content', $prezentacja->ID))) . '"><div ' . $style . '>' . get_the_title($prezentacja->ID) . '</div></td>';
                
                // Ustawienie stanu aktywności dla prezentacji
                for ($i = $timeslotIndex; $i < $timeslotIndex + $rowspan; $i++) {
                    for ($j = 0; $j < $colspan; $j++) {
                        $activePresentations[$scena_id + $j][$i] = true;
                    }
                }
            } else {
                echo '<td></td>';
            }
        }
        echo '</tr>';
    }
    echo '</tbody>';
}
echo '</table>';

get_footer();

function znajdzPrezentacje($dzienId, $scenaId, $timeslot) {
    list($start, $end) = explode(' - ', $timeslot);
    $prezentacje = get_posts([
        'post_type' => 'kongres_prezentacja',
        'numberposts' => -1,
        'meta_query' => [
            [
                'key' => 'czas_start',
                'value' => $start,
                'compare' => '=',
            ],
            [
                'key' => 'scena_ids',
                'value' => serialize(strval($scenaId)),
                'compare' => 'LIKE'
            ],
            [
                'key' => 'presentation_day_id',
                'value' => $dzienId,
                'compare' => '='
            ]
        ]
    ]);
    
    if (!empty($prezentacje)) {
        $prezentacja = $prezentacje[0];
        $prezentacja->tooltip_content = apply_filters('the_content', $prezentacja->post_content);
        return $prezentacja;
    }
    return null;
}

function obliczColspan($prezentacja) {
    return count(get_post_meta($prezentacja->ID, 'scena_ids', true));
}

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
