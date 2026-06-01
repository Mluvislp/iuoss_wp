<?php
/*
Template Name: Null Template - LadiPage
Template Post Type: post, page, product, property
*/
// @spl_autoload_unregister('autoptimize_autoload');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

function loadContent() {
	global $wpdb;
	
	$url = $_SERVER['REQUEST_URI'];
	$filePath = WP_CONTENT_DIR.'/ladipage'. $url . '.html';
	if (file_exists($filePath)) {
		echo file_get_contents($filePath);
		exit();
	} else {
		$sql = sprintf("SELECT post_title, post_name, post_content FROM %s WHERE ID = %s", $wpdb->posts, get_the_ID());
		$post = $wpdb->get_row($sql);
		$content = $post->post_content;
		$content = str_replace('< !DOCTYPE html>', '<!DOCTYPE html>', $content);
		echo $content;
		exit;
	}
}

loadContent();
?>