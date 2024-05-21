<?php
/* Template Name: Conference Schedule */

get_header();

?>
<style>
    body{background-color: #fff !important;}
    .gdpr.gdpr-privacy-bar, #header, #footer{display: none !important;}
    body.page-template-conference-schedule header,
    body.page-template-conference-schedule .site-header {
        display: none;
    }
    .conference-schedule-content {
        max-width: 1200px;
        margin: 0 auto;
    }

    .a4-page {
        display: block;
        width: 100%;
        height: auto;
        page-break-after: always;
        object-fit: contain;
        text-align: center;
    }
    
    
    
</style>
    <div id="page" class="conference-schedule-content">
<?php
// Wyświetlenie pierwszej strony (strona tytułowa)
$title_page_url = get_option('title_page_url');
if ($title_page_url) {
    echo '<div class="title-page">';
    echo '<img src="' . esc_url($title_page_url) . '" alt="Title Page" class="a4-page">';
    echo '</div>';
}

// Wyświetlenie shortcodu conference_schedule
echo '<div class="conference-schedule">';
echo do_shortcode('[conference_schedule]');
echo '</div>';

// Wyświetlenie stron końcowych
$end_pages_urls = get_option('end_pages_urls', []);
if (!empty($end_pages_urls)) {
    echo '<div class="end-pages">';
    foreach ($end_pages_urls as $url) {
        echo '<div class="end-page a4-page">';
        echo '<img src="' . esc_url($url) . '" alt="End Page" style="max-width: 100%; height: auto;">';
        echo '</div>';
    }
    echo '</div>';
} ?>

</div>

<?php
get_footer();
