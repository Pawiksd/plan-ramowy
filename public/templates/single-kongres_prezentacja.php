<?php
$template_html = get_the_block_template_html();

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open();

//$template_html = get_the_block_template_html();

// Create a new DOMDocument instance
$dom = new DOMDocument();

@$dom->loadHTML($template_html);

// Find the content area to modify
$xpath = new DOMXPath($dom);
$content_div = $xpath->query("//div[contains(@class, 'wp-block-group')]")->item(0);

if ($content_div) {
// Create new content
$new_content = $dom->createDocumentFragment();
ob_start();
if (have_posts()) : while (have_posts()) :
the_post(); ?>
<div class="container single-session">
    <h1><?php the_title(); ?></h1>
    <div class="session-details">
        <?php if (has_post_thumbnail()) : ?><?php the_post_thumbnail('large'); ?><?php endif; ?>
        <div class="session-content">
            <?php the_content(); ?>
        </div>
        <div class="session-excerpt">
            <?php the_excerpt(); ?>
        </div>
        <div class="session-speakers">
            <h2>Prelegenci</h2>
            <ul>
                <?php
                $prelegenci = get_post_meta(get_the_ID(), 'prelegenci', true);
                if (is_array($prelegenci) && !empty($prelegenci)) {
                    foreach ($prelegenci as $prelegent_id) {
                        $prelegent = get_post($prelegent_id);
                        $thumbnail = get_the_post_thumbnail($prelegent_id, 'thumbnail');
                        $biografia = get_post_meta($prelegent_id, 'biografia', true);
                        echo '<li>';
                        echo $thumbnail;
                        echo '<h3>' . esc_html($prelegent->post_title) . '</h3>';
                        echo '<p>' . esc_html($biografia) . '</p>';
                        echo '</li>';
                    }
                } else {
                    echo '<li>Brak prelegentów przypisanych do tej sesji.</li>';
                }
                ?>
            </ul>
        </div>
    </div>
</div>
<?php endwhile;
    endif;
    
    
    
    // Check if the content div has at least two children
$dom->textContent ='';
    if ($content_div->childNodes->length > 1) {
        
        
        var_dump($content_div->childNodes->length);
        // Replace the second child
        //$content_div->removeChild($content_div->childNodes->item(0));
        //$content_div->removeChild($content_div->childNodes->item(1));
        //$content_div->removeChild($content_div->childNodes->item(2));
        
        
        //$content_div->removeChild($content_div->childNodes->item(3));
    }
$new_content->appendXML(ob_get_clean());
    
    var_dump($new_content);
$content_div->load($new_content);
}

// Output the modified HTML
/*echo '<pre>';
var_dump($dom);
exit;*/
echo $dom->saveHTML();

get_footer();
