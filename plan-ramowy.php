<?php
/**
 * Plugin Name: Plan Ramowy
 * Description: Plugin do zarządzania harmonogramem kongresu.
 * Version: 1.0
 * Author: Paweł Juchim
 */

// Define constants
define('CONGRESS_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include Custom Post Types
include_once CONGRESS_PLUGIN_DIR . 'includes/cpt-congress-day.php';
include_once CONGRESS_PLUGIN_DIR . 'includes/cpt-congress-scene.php';
include_once CONGRESS_PLUGIN_DIR . 'includes/cpt-congress-presentation.php';
include_once CONGRESS_PLUGIN_DIR . 'includes/cpt-speakers.php';

// Include Meta Boxes
include_once CONGRESS_PLUGIN_DIR . 'includes/meta-boxes.php';
include_once CONGRESS_PLUGIN_DIR . 'includes/option-page.php';
include_once CONGRESS_PLUGIN_DIR . 'includes/shortcodes.php';
include_once CONGRESS_PLUGIN_DIR . 'includes/hooks-filters.php';

// Dodaj skrypty i style
function pr_enqueue_scripts() {
    wp_enqueue_style('pr-style', plugin_dir_url(__FILE__) . 'public/styles/style.css');
    wp_enqueue_script('pr-script', plugin_dir_url(__FILE__) . 'public/scripts/script.js', array('jquery'), null, true);
    wp_enqueue_script('pr-html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array(), null, true);
    wp_enqueue_script('pr-jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js', array(), null, true);
    wp_enqueue_script('pr-table', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js', array(), null, true);
    //wp_enqueue_script('pr-jspdf', '//cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.min.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'pr_enqueue_scripts');

function pr_load_custom_wp_admin_script() {
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_media();
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js', ['jquery'], null, true);
    wp_enqueue_script('custom-admin-js', plugin_dir_url(__FILE__) . 'admin/scripts/admin.js', ['jquery', 'jquery-ui-sortable', 'select2-js'], false, true);
    wp_enqueue_style('custom-admin-css', plugin_dir_url(__FILE__) . 'admin/styles/admin.css', false, '1.0.0', 'all');
}
add_action('admin_enqueue_scripts', 'pr_load_custom_wp_admin_script');
