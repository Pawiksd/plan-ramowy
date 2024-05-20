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

function register_plan_ramowy_settings() {
    register_setting('plan_ramowy_settings_group', 'title_page_url');
    register_setting('plan_ramowy_settings_group', 'end_pages_urls');
}
add_action('admin_init', 'register_plan_ramowy_settings');

function handle_uploaded_files() {
    if (isset($_POST['title_page'])) {
        update_option('title_page_url', esc_url_raw($_POST['title_page']));
    }

    if (isset($_POST['end_pages'])) {
        update_option('end_pages_urls', array_map('esc_url_raw', $_POST['end_pages']));
    }
}
add_action('admin_post_update', 'handle_uploaded_files');

function plan_ramowy_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        check_admin_referer('plan_ramowy_update_images', 'plan_ramowy_images_nonce');
        handle_uploaded_files();
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'generate_pdf') {
        $pdf_path = generate_plan_ramowy_pdf();
        echo '<div class="updated"><p>PDF generated successfully. <a href="' . esc_url($pdf_path) . '">Download PDF</a></p></div>';
    }
    ?>

    <form method="post" action="">
        <?php wp_nonce_field('plan_ramowy_update_images', 'plan_ramowy_images_nonce'); ?>
        
        <h2>Strona Tytułowa</h2>
        <button type="button" id="upload_title_page" class="button">Wybierz Stronę Tytułową</button>
        <input type="hidden" name="title_page" id="title_page" value="<?php echo esc_url(get_option('title_page_url')); ?>">
        <?php if (get_option('title_page_url')) : ?>
            <div>
                <img src="<?php echo get_option('title_page_url'); ?>" alt="Title Page" style="max-width: 100%; height: auto;">
            </div>
        <?php endif; ?>
        
        <h2>Strony Końcowe</h2>
        <div id="end_pages_container" class="sortable">
            <button type="button" id="add_end_page" class="button">Dodaj Stronę Końcową</button>
            <?php
            $end_pages = get_option('end_pages_urls', []);
            if (!empty($end_pages)) {
                foreach ($end_pages as $index => $url) {
                    echo '<div class="end_page">';
                    echo '<button type="button" class="remove_end_page button">Usuń</button>';
                    echo '<button type="button" class="upload_end_page button">Wybierz Stronę Końcową</button>';
                    echo '<input type="hidden" name="end_pages[]" value="' . esc_url($url) . '">';
                    echo '<img src="' . esc_url($url) . '" alt="End Page ' . ($index + 1) . '" style="max-width: 100%; height: auto;">';
                    echo '</div>';
                }
            }
            ?>
        </div>
        
        <?php submit_button('Zapisz Ustawienia'); ?>
        
        <h2>Generuj PDF</h2>
        <input type="hidden" name="action" value="generate_pdf">
        <?php submit_button('Generate PDF'); ?>
    </form>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const titlePageButton = document.getElementById('upload_title_page');
        const titlePageInput = document.getElementById('title_page');

        titlePageButton.addEventListener('click', function () {
          wp.media.editor.send.attachment = function (props, attachment) {
            titlePageInput.value = attachment.url;
            const img = document.querySelector('img[alt="Title Page"]');
            if (img) {
              img.src = attachment.url;
            } else {
              const newImg = document.createElement('img');
              newImg.src = attachment.url;
              newImg.alt = 'Title Page';
              newImg.style.maxWidth = '100%';
              newImg.style.height = 'auto';
              titlePageButton.insertAdjacentElement('afterend', newImg);
            }
          };
          wp.media.editor.open(titlePageButton);
          return false;
        });

        const endPagesContainer = document.getElementById('end_pages_container');

        document.getElementById('add_end_page').addEventListener('click', function () {
          const endPageDiv = document.createElement('div');
          endPageDiv.className = 'end_page';

          const removeButton = document.createElement('button');
          removeButton.type = 'button';
          removeButton.className = 'remove_end_page button';
          removeButton.textContent = 'Usuń';

          const uploadButton = document.createElement('button');
          uploadButton.type = 'button';
          uploadButton.className = 'upload_end_page button';
          uploadButton.textContent = 'Wybierz Stronę Końcową';

          const hiddenInput = document.createElement('input');
          hiddenInput.type = 'hidden';
          hiddenInput.name = 'end_pages[]';

          endPageDiv.appendChild(removeButton);
          endPageDiv.appendChild(uploadButton);
          endPageDiv.appendChild(hiddenInput);
          endPagesContainer.appendChild(endPageDiv);

          uploadButton.addEventListener('click', function () {
            wp.media.editor.send.attachment = function (props, attachment) {
              hiddenInput.value = attachment.url;
              let img = endPageDiv.querySelector('img');
              if (img) {
                img.src = attachment.url;
              } else {
                img = document.createElement('img');
                img.src = attachment.url;
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                endPageDiv.appendChild(img);
              }
            };
            wp.media.editor.open(uploadButton);
            return false;
          });

          removeButton.addEventListener('click', function () {
            endPageDiv.remove();
          });
        });

        document.querySelectorAll('.upload_end_page').forEach(function (button) {
          button.addEventListener('click', function () {
            const hiddenInput = button.nextElementSibling;
            wp.media.editor.send.attachment = function (props, attachment) {
              hiddenInput.value = attachment.url;
              let img = button.parentNode.querySelector('img');
              if (img) {
                img.src = attachment.url;
              } else {
                img = document.createElement('img');
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                img.src = attachment.url;
                button.parentNode.appendChild(img);
              }
            };
            wp.media.editor.open(button);
            return false;
          });
        });

        document.querySelectorAll('.remove_end_page').forEach(function (button) {
          button.addEventListener('click', function () {
            button.parentNode.remove();
          });
        });

        jQuery('#end_pages_container').sortable({
          animation: 150,
          ghostClass: 'sortable-ghost',
        });
      });

    </script>
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
