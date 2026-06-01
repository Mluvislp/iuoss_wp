<?php
/*
Plugin Name: TablePress Extension: Shortcode Filter from GET parameter
Plugin URI: https://tablepress.org/extensions/shortcode-filter-get-parameter/
Description: Custom Extension for TablePress to use the Row Filter extension with a filter term from a $_GET parameter
Version: 1.0
Author: Tobias Bäthge
Author URI: https://tobias.baethge.com/
*/

/*
 * The Shortcode: [table_filter id=123 /]
 * will be filtered for http://example.com/?table_filter=myfilterword
 */

function tablepress_filter_shortcode( $attributes, $content ) {
	if ( ! empty( $_GET['table_filter'] ) ) {
		$filter_term = $_GET['table_filter'];
		/*
		 * Only allow characters a-z, A-Z, 0-9, and spaces in the filter term.
		 * This regular expression needs to be extended if other characters shall be allowed.
		 */
		$filter_term = preg_replace( '#[^a-z0-9 ]#i', '', $filter_term );
		$attributes['filter'] = $filter_term;
	}
	return tablepress_get_table( $attributes );
}

if ( ! is_admin() ) {
	add_shortcode( 'table_filter', 'tablepress_filter_shortcode' );
}
