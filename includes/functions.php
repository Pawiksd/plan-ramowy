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
        'rewrite' => array('slug' => 'sesja', 'with_front' => false),
        'has_archive' => true,
        'supports' => ['title', 'editor', 'custom-fields'],
        'menu_position' => 5,
        'show_in_rest' => true
    ]);
}

add_action('init', 'register_congress_custom_post_types');

// Register the shortcode
function conference_schedule_shortcode()
{
    // Time slots array
    $timeslots = [
        '9:30 - 10:00', '10:00 - 10:30', '10:30 - 11:00', '11:00 - 11:30', '11:30 - 12:00',
        '12:00 - 12:30', '12:30 - 13:00', '13:00 - 13:30', '13:30 - 14:00', '14:00 - 14:30',
        '14:30 - 15:00', '15:00 - 15:30', '15:30 - 16:00', '16:00 - 16:30', '16:30 - 17:00',
        '17:00 - 17:30', '17:30 - 18:00', '18:00 - 18:30', '18:30 - 19:00', '19:00 - 23:00'
    ];
    
    // Retrieve post data
    $sceny = get_posts(['post_type' => 'kongres_scena', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
    $dni = get_posts(['post_type' => 'kongres_dzien', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
    
    $activePresentations = [];
    
    $output = '<table id="plan-ramowy" aria-describedby="conference-schedule">';
    $output .= '<caption>Plan Ramowy</caption>';
    foreach ($dni as $dzien) {
        $output .= '<thead>';
        $output .= '<tr><th scope="col" class="wss-nb" colspan="' . (count($sceny) + 1) . '"><strong>Dzień: ' . get_the_title($dzien->ID) . '</strong></th></tr>';
        $output .= '<tr><th scope="col" id="pr-godzina" class="wss-nb">Godzina</th>';
        foreach ($sceny as $scena) {
            $output .= '<th scope="col">' . esc_html(get_the_title($scena->ID)) . '</th>';
        }
        $output .= '</tr></thead>';
        $output .= '<tbody>';
        
        // Loop through each timeslot and scene to generate the presentation cells
        foreach ($timeslots as $timeslotIndex => $timeslot) {
            $output .= '<tr>';
            $output .= '<th scope="row">' . $timeslot . '</th>';
            
            foreach ($sceny as $scenaIndex => $scena) {
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
                    $output .= '<td scope="cell" class="wss-nb" colspan="' . $colspan . '" rowspan="' . $rowspan . '" data-tooltip="' . esc_attr(wp_strip_all_tags(get_post_field('post_content', $prezentacja->ID))) . '"><div ' . $style . '>' . get_the_title($prezentacja->ID) . '</div></td>';
                    
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

function plan_ramowy_settings_page()
{
    add_menu_page(
        'Plan Ramowy Settings',
        'Plan Ramowy',
        'manage_options',
        'plan-ramowy-settings',
        'plan_ramowy_settings_page_html',
        'dashicons-admin-generic',
        20
    );
}

add_action('admin_menu', 'plan_ramowy_settings_page');

function plan_ramowy_settings_page_html()
{
    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Save uploaded images
    if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
        $uploaded_header = wp_handle_upload($_FILES['header_image'], ['test_form' => false]);
        if (!isset($uploaded_header['error'])) {
            update_option('plan_ramowy_header_image', $uploaded_header['url']);
        }
    }
    
    if (isset($_FILES['footer_image']) && $_FILES['footer_image']['error'] === UPLOAD_ERR_OK) {
        $uploaded_footer = wp_handle_upload($_FILES['footer_image'], ['test_form' => false]);
        if (!isset($uploaded_footer['error'])) {
            update_option('plan_ramowy_footer_image', $uploaded_footer['url']);
        }
    }
    
    echo '<div class="wrap">';
    echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
    
    // Sprawdź, czy formularz został wysłany
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'generate_pdf') {
        // Tutaj wywołujemy funkcję generującą PDF
        $pdf_path = generate_plan_ramowy_pdf();
        echo '<div class="updated"><p>PDF generated successfully. <a href="' . esc_url($pdf_path) . '">Download PDF</a></p></div>';
    }
    
    // Formularz do przesyłania obrazów i generowania PDF
    ?>
    <form method="post" action="" enctype="multipart/form-data">
        <?php
        // Nonce field for security
        wp_nonce_field('plan_ramowy_update_images', 'plan_ramowy_images_nonce');
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="header_image">Header Image</label></th>
                <td><input type="file" name="header_image" id="header_image" accept="image/png, image/jpeg"></td>
            </tr>
            <tr>
                <th scope="row"><label for="footer_image">Footer Image</label></th>
                <td><input type="file" name="footer_image" id="footer_image" accept="image/png, image/jpeg"></td>
            </tr>
        </table>
        <?php submit_button('Upload Images'); ?>

        <table class="form-table">
            <tr>
                <th scope="row"><label for="header_image">Generuj PDF</label></th>
                <td><input type="hidden" name="action" value="generate_pdf"></td>
            </tr>

        </table>
        <?php submit_button('Generate PDF'); ?>
    </form></div>
    <?php
}


function generate_plan_ramowy_pdf()
{
    // Zaladowanie biblioteki mPDF
    require_once(plugin_dir_path(__FILE__) . '/..//vendor/autoload.php');
    /*
    var_dump(wp_upload_dir());
    exit;
    */
    // Pobierz URL-e obrazów nagłówka i stopki z opcji WordPress
    $header_image = get_option('plan_ramowy_header_image');
    $footer_image = get_option('plan_ramowy_footer_image');
    
    // Utwórz instancję mPDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_header' => 5,
        'margin_footer' => 5,
        'orientation' => 'L'
    ]);
    
    // Dodaj obrazy nagłówka i stopki jako HTML header & footer w PDF
    if ($header_image) {
       $mpdf->SetHTMLHeader('<div style="text-align:center;"><img src="' . $header_image . '" width="100%"></div>');
    }
    if ($footer_image) {
       $mpdf->SetHTMLFooter('<div style="text-align:center;"><img src="' . $footer_image . '" width="100%"></div>');
    }
    
    // Dodanie CSS ze stylami z front endu
    $stylesheet = file_get_contents(get_template_directory_uri() . '/style.css'); // Upewnij się, że ta ścieżka jest prawidłowa
    $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
    
    
    
    // Dodanie CSS ze stylami z pluginu
    $stylesheet2 = file_get_contents(plugin_dir_path(__FILE__) . '/../public/styles/pdf.css'); // Upewnij się, że ta ścieżka jest prawidłowa
    $mpdf->WriteHTML($stylesheet2, \Mpdf\HTMLParserMode::HEADER_CSS);
    
    // Dodanie CSS ze stylami z pluginu
    $stylesheet1 = file_get_contents(plugin_dir_path(__FILE__) . '/../public/styles/styles.css'); // Upewnij się, że ta ścieżka jest prawidłowa
    $mpdf->WriteHTML($stylesheet1, \Mpdf\HTMLParserMode::HEADER_CSS);


// Pobierz treść HTML z shortcode'u
    $content_html = do_shortcode('[conference_schedule]');

// Wykonaj shortcode w kontekście HTML
    $content_html_executed = apply_filters('the_content', $content_html);
    
    $mpdf->WriteHTML($content_html_executed, \Mpdf\HTMLParserMode::HTML_BODY);
    
    // Generowanie PDF
    $file_path = wp_upload_dir()['basedir'] . '/plan_ramowy.pdf'; // Ścieżka zapisu pliku PDF
    $mpdf->Output($file_path, 'F');
    
    return wp_upload_dir()['baseurl'] . '/plan_ramowy.pdf';
}
