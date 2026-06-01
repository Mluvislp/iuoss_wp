<?php 

$options = array();

$options[] = array(
    'id'          => 'jnews_option[jnews_enable_pages_meta_description]',
    'option_type' => 'option',
    'transport'   => 'postMessage',
    'default'     => false,
    'type'        => 'jnews-toggle',
    'label'       => esc_html__( 'Enable Pages Meta Description', 'jnews-meta-header' ),
    'description' => esc_html__( 'Enable this option to add the JNews default meta description to all your single pages. ', 'jnews-meta-header' ),
);

$options[] = array(
    'id'          => 'jnews_option[jnews_enable_posts_meta_description]',
    'option_type' => 'option',
    'transport'   => 'postMessage',
    'default'     => false,
    'type'        => 'jnews-toggle',
    'label'       => esc_html__( 'Enable Posts Meta Description', 'jnews-meta-header' ),
    'description' => esc_html__( 'Enable this option to add the JNews default meta description to all your single post', 'jnews-meta-header' ),
);

$options[] = array(
    'id'          => 'jnews_option[jnews_enable_tags_meta_description]',
    'option_type' => 'option',
    'transport'   => 'postMessage',
    'default'     => false,
    'type'        => 'jnews-toggle',
    'label'       => esc_html__( 'Enable Tags Meta Description', 'jnews-meta-header' ),
    'description' => esc_html__( 'Enable this option to add the JNews default meta description to your archive tags page.', 'jnews-meta-header' ),
);

$options[] = array(
    'id'          => 'jnews_option[jnews_enable_categories_meta_description]',
    'option_type' => 'option',
    'transport'   => 'postMessage',
    'default'     => false,
    'type'        => 'jnews-toggle',
    'label'       => esc_html__( 'Enable Categories Meta Description', 'jnews-meta-header' ),
    'description' => esc_html__( 'Enable this option to add the JNews default meta description to your archive categories page.', 'jnews-meta-header' ),
);

return $options;
