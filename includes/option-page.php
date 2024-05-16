<?php


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
    
    /*
     * ToDo: wyslij html pobierz pdf i zapisz lokalnie
     * */
    
    // Dodaj obrazy nagłówka i stopki jako HTML header & footer w PDF
    /*  if ($header_image) {
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
      
      return wp_upload_dir()['baseurl'] . '/plan_ramowy.pdf';*/
}
