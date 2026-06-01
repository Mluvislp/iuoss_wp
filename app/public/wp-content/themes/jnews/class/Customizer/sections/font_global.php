<?php

$options = array();

$options[] = array(
	'id'          => 'jnews_body_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Body Font', 'jnews' ),
	'description' => esc_html__( 'Site global font.', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => 'body,input,textarea,select,.chosen-container-single .chosen-single,.btn,.button',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_header_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Header Font', 'jnews' ),
	'description' => esc_html__( 'Set font for your header', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.jeg_header, .jeg_mobile_wrapper',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_main_menu_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Main Menu Font', 'jnews' ),
	'description' => esc_html__( 'Set font for your main menu', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.jeg_main_menu > li > a',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_block_heading_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Block Heading', 'jnews' ),
	'description' => esc_html__( 'Block module and widget title.', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => 'h3.jeg_block_title, .jeg_footer .jeg_footer_heading h3, .jeg_footer .widget h2, .jeg_tabpost_nav li',
		),
	),
);

$options[] = array(
	'id'    => 'jnews_blog_page_font_header',
	'type'  => 'jnews-header',
	'label' => esc_html__( 'Blog Page Font', 'jnews' ),
);


$options[] = array(
	'id'          => 'jnews_h1_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Post Title', 'jnews' ),
	'description' => esc_html__( 'Set font for post title.', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.jeg_post_title, .entry-header .jeg_post_title, .jeg_single_tpl_2 .entry-header .jeg_post_title, .jeg_single_tpl_3 .entry-header .jeg_post_title, .jeg_single_tpl_6 .entry-header .jeg_post_title, .jeg_content .jeg_custom_title_wrapper .jeg_post_title',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_p_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Paragraph Font', 'jnews' ),
	'description' => esc_html__( 'Paragraph font.', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.jeg_post_excerpt p, .content-inner p',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_li_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Lists Font', 'jnews' ),
	'description' => esc_html__( 'Font for HTML Lists in Bloga Page', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.jeg_post_excerpt li, .content-inner li',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_blobkquote_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Quote Font', 'jnews' ),
	'description' => esc_html__( 'Font for Quote element in Bloga Page', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.entry-content blockquote * ,.entry-content blockquote p',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_blog_h1_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Heading H1 Font', 'jnews' ),
	'description' => esc_html__( 'Font for Heading with H1 Tag in Blog Page.', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.entry-content h1',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_blog_h2_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Heading H2 Font', 'jnews' ),
	'description' => esc_html__( 'Font for Heading with H2 Tag in Blog Page.', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.entry-content h2',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_blog_h3_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Heading H3 Font', 'jnews' ),
	'description' => esc_html__( 'Font for Heading with H3 Tag in Blog Page.', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.entry-content h3',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_blog_h4_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Heading H4 Font', 'jnews' ),
	'description' => esc_html__( 'Font for Heading with H4 Tag in Blog Page.', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.entry-content h4',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_blog_h5_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Heading H5 Font', 'jnews' ),
	'description' => esc_html__( 'Font for Heading with H5 Tag in Blog Page.', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.entry-content h5',
		),
	),
);

$options[] = array(
	'id'          => 'jnews_blog_h6_font',
	'transport'   => 'postMessage',
	'type'        => 'jnews-typography',
	'label'       => esc_html__( 'Heading H6 Font', 'jnews' ),
	'description' => esc_html__( 'Font for Heading with H6 Tag in Blog Page.', 'jnews' ),
	'default'     => array(
		'font-family'      => '',
		'variant'          => '',
		'font-size'        => '',
		'font-size-unit'   => '',
		'line-height'      => '',
		'line-height-unit' => '',
		'subsets'          => array(),
		'color'            => '',
	),
	'output'      => array(
		array(
			'method'  => 'typography',
			'element' => '.entry-content h6',
		),
	),
);

return $options;
