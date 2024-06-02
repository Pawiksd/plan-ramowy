<?php
function get_post_title_by_id($post_id) {
    $post = get_post($post_id);
    
    if ($post) {
        return get_the_title($post);
    } else {
        return 'Post not found';
    }
}
