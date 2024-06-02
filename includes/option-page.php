<?php

function plan_ramowy_settings_page()
{
    add_menu_page(
        'Program Ramowy Ustawienia',
        'Program Ramowy',
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
    register_setting('plan_ramowy_settings_group', 'congress_logo_url'); // Dodajemy nowe pole
    register_setting('plan_ramowy_settings_group', 'congress_logo_position'); // Dodajemy nowe pole
}
add_action('admin_init', 'register_plan_ramowy_settings');

function handle_uploaded_files() {
    if (isset($_POST['title_page'])) {
        update_option('title_page_url', esc_url_raw($_POST['title_page']));
    }

    if (isset($_POST['end_pages'])) {
        update_option('end_pages_urls', array_map('esc_url_raw', $_POST['end_pages']));
    }

    if (isset($_POST['congress_logo'])) {
        update_option('congress_logo_url', esc_url_raw($_POST['congress_logo']));
    }

    if (isset($_POST['congress_logo_position'])) {
        update_option('congress_logo_position', sanitize_text_field($_POST['congress_logo_position']));
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
        
        <h2>Logo Kongresu</h2>
        <button type="button" id="upload_congress_logo" class="button">Wybierz Logo Kongresu</button>
        <input type="hidden" name="congress_logo" id="congress_logo" value="<?php echo esc_url(get_option('congress_logo_url')); ?>">
        <?php if (get_option('congress_logo_url')) : ?>
            <div>
                <img src="<?php echo get_option('congress_logo_url'); ?>" alt="Congress Logo" style="max-width: 100%; height: auto;">
            </div>
        <?php endif; ?>

        <h2>Pozycja Logo</h2>
        <select name="congress_logo_position" id="congress_logo_position">
            <option value="left" <?php selected(get_option('congress_logo_position'), 'left'); ?>>Lewa</option>
            <option value="center" <?php selected(get_option('congress_logo_position'), 'center'); ?>>Środek</option>
            <option value="right" <?php selected(get_option('congress_logo_position'), 'right'); ?>>Prawa</option>
        </select>
        
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
    </form>

    <div style="display: flex; align-items: flex-start;">
        <?php
        $upload_dir = wp_upload_dir();
        $pdf_url = $upload_dir['baseurl'] . '/program-ramowy.pdf';
        ?>
        <div id="pdf_preview" style="margin-right: 20px;">
            <?php if (file_exists($upload_dir['basedir'] . '/program-ramowy.pdf')) : ?>
                <h3>Ostatnio wygenerowany PDF</h3>
                <embed src="<?php echo esc_url($pdf_url); ?>" type="application/pdf" width="800px" height="1200px"/>
            <?php endif; ?>
        </div>


        <form method="post" id="generate_pdf_form" action="<?php echo admin_url('admin-ajax.php'); ?>" style="margin-right: 20px;">
            <h2>Generuj PDF</h2>
            <input type="hidden" name="action" value="generate_pdf">
            <?php submit_button('Generate PDF'); ?>
            <div id="spinner" style="display:none;">
                <img src="<?php echo esc_url(admin_url('images/spinner.gif')); ?>" alt="Loading..."/>
            </div>
        </form>


    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const logoButton = document.getElementById('upload_congress_logo');
        const logoInput = document.getElementById('congress_logo');
        const titlePageButton = document.getElementById('upload_title_page');
        const titlePageInput = document.getElementById('title_page');

        logoButton.addEventListener('click', function () {
          wp.media.editor.send.attachment = function (props, attachment) {
            logoInput.value = attachment.url;
            const img = document.querySelector('img[alt="Congress Logo"]');
            if (img) {
              img.src = attachment.url;
            } else {
              const newImg = document.createElement('img');
              newImg.src = attachment.url;
              newImg.alt = 'Congress Logo';
              newImg.style.maxWidth = '100%';
              newImg.style.height = 'auto';
              logoButton.insertAdjacentElement('afterend', newImg);
            }
          };
          wp.media.editor.open(logoButton);
          return false;
        });

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

        // Handle PDF generation without redirect
        jQuery('#generate_pdf_form').on('submit', function (e) {
          e.preventDefault();
          var formData = jQuery(this).serialize();
          jQuery('#spinner').show();

          jQuery.post(ajaxurl, formData, function (response) {
            jQuery('#spinner').hide();
            if (response.success) {
              var pdfUrl = response.data.pdf_url;
              var embedHtml = '<h3>Ostatnio wygenerowany PDF</h3><embed src="' + pdfUrl + '" type="application/pdf" width="800px" height="1200px" />';
              jQuery('#pdf_preview').html(embedHtml);
            } else {
              alert('Error generating PDF: ' + response.data.message);
            }
          });
        });
      });

    </script>
    <?php
}

function generate_pdf_request() {
    if (isset($_POST['action']) && $_POST['action'] === 'generate_pdf') {
        $url = 'https://kongres.wpdevelopers.eu/conference-schedule/';
        $username = 'your_username'; // Zastąp rzeczywistą nazwą użytkownika
        $password = 'your_password'; // Zastąp rzeczywistym hasłem
        $logo_url = esc_url(get_option('congress_logo_url'));
        $logo_position = sanitize_text_field(get_option('congress_logo_position'));

        $response = wp_remote_post('http://pdf.wpdevelopers.eu/generate-pdf', array(
            'method'    => 'POST',
            'body'      => json_encode(array(
                'url' => $url,
                'username' => $username,
                'password' => $password,
                'logo_url' => $logo_url, // Dodajemy URL logo do danych wysyłanych do serwera
                'logo_position' => $logo_position // Dodajemy pozycję logo do danych wysyłanych do serwera
            )),
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'timeout' => 60, // Zwiększ czas oczekiwania
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }
        
        $pdf_content = wp_remote_retrieve_body($response);
        
        if ($pdf_content) {
            $upload_dir = wp_upload_dir();
            $pdf_path = $upload_dir['basedir'] . '/program-ramowy.pdf';
            $pdf_url = $upload_dir['baseurl'] . '/program-ramowy.pdf';
            
            // Save the PDF content to a file
            file_put_contents($pdf_path, $pdf_content);
            
            wp_send_json_success(array('pdf_url' => $pdf_url));
        } else {
            wp_send_json_error(array('message' => 'Error generating PDF'));
        }
    }
}
add_action('wp_ajax_generate_pdf', 'generate_pdf_request');
?>
