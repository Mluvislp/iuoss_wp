<?php

/**
 * Load parent theme style
 */
add_action( 'wp_enqueue_scripts', 'jnews_child_enqueue_parent_style' );

function jnews_child_enqueue_parent_style()
{
    wp_enqueue_style( 'jnews-parent-style', get_parent_theme_file_uri('/style.css'));
}
/**
 * Change link Lost your password
 */
add_filter('lostpassword_url', function($lostpassword_url, $redirect) {
    return 'https://ldap.hcmiu.edu.vn';
}, 10, 2);
