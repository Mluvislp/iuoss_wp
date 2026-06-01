<?php
/*
Plugin Name: IUOSS Ticket API
Description: REST API for wordpress-helpdesk tickets
Version: 1.2.0
Author: IUOSS
*/

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', 'iuoss_api_register_routes' );

function iuoss_api_register_routes() {
    $args = array(
        'page'      => array( 'default' => 1,  'sanitize_callback' => 'absint' ),
        'per_page'  => array( 'default' => 20, 'sanitize_callback' => 'iuoss_api_sanitize_per_page' ),
        'status'    => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
        'agent'     => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
        'priority'  => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
        'type'      => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
        'search'    => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
        'date'      => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
        'date_from' => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
        'date_to'   => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
    );

    register_rest_route( 'iuoss/v1', '/tickets', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'iuoss_api_get_tickets',
        'permission_callback' => 'iuoss_api_check_permission',
        'args'                => $args,
    ) );

    register_rest_route( 'iuoss/v1', '/tickets/(?P<id>\d+)', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'iuoss_api_get_ticket',
        'permission_callback' => 'iuoss_api_check_permission',
        'args'                => array(
            'id' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
        ),
    ) );
}

function iuoss_api_sanitize_per_page( $value ) {
    $int = intval( $value );
    if ( $int === -1 ) return -1;
    if ( $int <= 0 ) return 20;
    return min( $int, 500 );
}

function iuoss_api_check_permission() {
    return current_user_can( 'edit_posts' );
}

function iuoss_api_get_tickets( WP_REST_Request $request ) {
    $page      = (int) $request->get_param( 'page' );
    $per_page  = $request->get_param( 'per_page' );
    $status    = $request->get_param( 'status' );
    $agent     = $request->get_param( 'agent' );
    $priority  = $request->get_param( 'priority' );
    $type      = $request->get_param( 'type' );
    $search    = $request->get_param( 'search' );
    $date      = $request->get_param( 'date' );
    $date_from = $request->get_param( 'date_from' );
    $date_to   = $request->get_param( 'date_to' );

    $args = array(
        'post_type'      => 'ticket',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if ( $search ) {
        $args['s'] = $search;
    }

    $tax_query = array();
    if ( $status )   $tax_query[] = array( 'taxonomy' => 'ticket_status',   'field' => 'slug', 'terms' => $status );
    if ( $priority ) $tax_query[] = array( 'taxonomy' => 'ticket_priority', 'field' => 'slug', 'terms' => $priority );
    if ( $type )     $tax_query[] = array( 'taxonomy' => 'ticket_type',     'field' => 'slug', 'terms' => $type );
    if ( count( $tax_query ) > 1 ) $tax_query['relation'] = 'AND';
    if ( ! empty( $tax_query ) ) $args['tax_query'] = $tax_query;

    if ( $agent ) {
        $args['meta_query'] = array( array( 'key' => 'agent', 'value' => $agent ) );
    }

    if ( $date ) {
        $args['date_query'] = array( array(
            'year'  => (int) substr( $date, 0, 4 ),
            'month' => (int) substr( $date, 5, 2 ),
            'day'   => (int) substr( $date, 8, 2 ),
        ) );
    } elseif ( $date_from || $date_to ) {
        $dq = array( 'inclusive' => true );
        if ( $date_from ) $dq['after']  = $date_from . ' 00:00:00';
        if ( $date_to )   $dq['before'] = $date_to   . ' 23:59:59';
        $args['date_query'] = array( $dq );
    }

    $query   = new WP_Query( $args );
    $tickets = array();
    foreach ( $query->posts as $post ) {
        $tickets[] = iuoss_api_format_ticket( $post );
    }

    return rest_ensure_response( array(
        'total'       => (int) $query->found_posts,
        'total_pages' => (int) $query->max_num_pages,
        'page'        => $page,
        'per_page'    => $per_page,
        'tickets'     => $tickets,
    ) );
}

function iuoss_api_get_ticket( WP_REST_Request $request ) {
    $id   = (int) $request->get_param( 'id' );
    $post = get_post( $id );

    if ( ! $post || $post->post_type !== 'ticket' ) {
        return new WP_Error( 'not_found', 'Ticket not found', array( 'status' => 404 ) );
    }

    $ticket = iuoss_api_format_ticket( $post );

    $comments = get_comments( array(
        'post_id' => $id,
        'status'  => 'approve',
        'orderby' => 'comment_date',
        'order'   => 'ASC',
    ) );

    $replies = array();
    foreach ( $comments as $c ) {
        $replies[] = array(
            'id'      => (int) $c->comment_ID,
            'author'  => $c->comment_author,
            'email'   => $c->comment_author_email,
            'content' => wp_strip_all_tags( $c->comment_content ),
            'date'    => $c->comment_date,
            'user_id' => (int) $c->user_id,
        );
    }
    $ticket['replies'] = $replies;

    return rest_ensure_response( $ticket );
}

function iuoss_api_format_ticket( WP_Post $post ) {
    $meta     = get_post_meta( $post->ID );
    $skip     = array( 'agent', 'source', 'feedback', 'satisfied', 'website_url',
                       'purchase_code', 'order', 'product', 'post_subtitle',
                       'post_subtitle_flag', 'classic-editor-remember',
                       'wordpress_helpdesk_log', 'n-a' );

    $get_meta = function( $key ) use ( $meta ) {
        return isset( $meta[ $key ][0] ) ? $meta[ $key ][0] : null;
    };

    // Agent
    $agent_data = null;
    $agent_id   = (int) $get_meta( 'agent' );
    if ( $agent_id ) {
        $u = get_user_by( 'id', $agent_id );
        if ( $u ) $agent_data = array( 'id' => $agent_id, 'name' => $u->display_name, 'email' => $u->user_email );
    }

    // Author
    $author_data = null;
    if ( $post->post_author ) {
        $u = get_user_by( 'id', $post->post_author );
        if ( $u ) $author_data = array( 'id' => (int) $post->post_author, 'name' => $u->display_name, 'email' => $u->user_email );
    }

    // Taxonomy helper
    $get_term = function( $tax ) use ( $post ) {
        $terms = wp_get_post_terms( $post->ID, $tax );
        if ( is_wp_error( $terms ) || empty( $terms ) ) return null;
        return array( 'id' => $terms[0]->term_id, 'name' => $terms[0]->name, 'slug' => $terms[0]->slug );
    };

    // Attachments
    $attachments = array();
    $att_ids     = maybe_unserialize( $get_meta( 'wordpress_helpdesk_attachments' ) );
    if ( is_array( $att_ids ) ) {
        foreach ( $att_ids as $att_id ) {
            $url = wp_get_attachment_url( $att_id );
            if ( $url ) $attachments[] = array( 'id' => (int) $att_id, 'url' => $url );
        }
    }

    // Custom fields
    $custom = array();
    foreach ( $meta as $key => $vals ) {
        if ( $key[0] === '_' ) continue;
        if ( strpos( $key, 'rank_math' ) === 0 ) continue;
        if ( $key === 'wordpress_helpdesk_attachments' ) continue;
        if ( in_array( $key, $skip, true ) ) continue;
        $val = isset( $vals[0] ) ? $vals[0] : '';
        if ( $val === '' ) continue;
        $custom[ $key ] = $val;
    }

    return array(
        'id'            => $post->ID,
        'title'         => $post->post_title,
        'content'       => wp_strip_all_tags( $post->post_content ),
        'date'          => $post->post_date,
        'modified'      => $post->post_modified,
        'post_status'   => $post->post_status,
        'author'        => $author_data,
        'status'        => $get_term( 'ticket_status' ),
        'type'          => $get_term( 'ticket_type' ),
        'priority'      => $get_term( 'ticket_priority' ),
        'system'        => $get_term( 'ticket_system' ),
        'agent'         => $agent_data,
        'source'        => $get_meta( 'source' ),
        'feedback'      => $get_meta( 'feedback' ),
        'satisfied'     => $get_meta( 'satisfied' ),
        'website_url'   => $get_meta( 'website_url' ),
        'purchase_code' => $get_meta( 'purchase_code' ),
        'attachments'   => $attachments,
        'custom_fields' => $custom,
    );
}
