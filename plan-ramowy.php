<?php
/**
 * Plugin Name: Plan Ramowy
 * Description: Plugin do zarządzania harmonogramem kongresu.
 * Version: 1.0
 * Author: Paweł Juchim
 */

// Hook do rejestracji typów postów i taksonomii
include( plugin_dir_path( __FILE__ ) . 'includes/functions.php');

// Hook do dodawania meta boxów
include( plugin_dir_path( __FILE__ ) . 'admin/meta-boxes.php');

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
