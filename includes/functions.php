<?php
function get_post_title_by_id($post_id) {
    $post = get_post($post_id);
    
    if ($post) {
        return get_the_title($post);
    } else {
        return 'Post not found';
    }
}

function is_array_empty($array) {
    if (!is_array($array)) {
        return false;
    }
    
    // Sprawdź czy tablica jest całkowicie pusta
    if (empty($array)) {
        return true;
    }
    
    // Sprawdź czy wszystkie elementy tablicy są pustymi stringami
    foreach ($array as $value) {
        if (!is_string($value) || $value !== "") {
            return false;
        }
    }
    
    return true;
}
