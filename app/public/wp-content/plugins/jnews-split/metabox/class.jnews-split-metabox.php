<?php
/**
 * @author : Jegtheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JNews Split Metabox
 */
class JNews_Split_Metabox {
	/**
	 * @var JNews_Split_Metabox
	 */
	private static $instance;

	/**
	 * @return JNews_Split_Metabox
	 */
	public static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Method __construct
	 *
	 * @return void
	 */
	private function __construct() {
		\JNews\MetaboxBuilder::set_meta( $this->register_metabox() );
	}

	/**
	 * Method register_metabox
	 *
	 * @param array $metabox $metabox metabox data.
	 *
	 * @return array
	 */
	public function register_metabox() {
		return array(
			'id'         => 'jnews_post_split',
			'post_types' => array( 'post' ),
			'label'      => 'Split Post',
			'priority'   => 'high',
			'icon'       => 'LayoutSplitSvg',
			'fields'     => array(
				array(
					'type'        => 'jnews-toggle',
					'id'          => 'enable_post_split',
					'label'       => esc_html__( 'Enable Post Split on this Post', 'jnews' ),
					'description' => esc_html__( 'Enable post split on this page. Please don\'t use this feature with WordPress <!--nextpage-->. This plugin will only get first matched tag criteria (on same wrapper). If you have multiple row / column or child with tag that build by page builder, it will be ignored.', 'jnews' ),
				),
				array(
					'type'        => 'jnews-group',
					'id'          => 'post_split',
					'label'       => esc_html__( 'Post Split Option', 'jnews' ),
					'description' => esc_html__( 'Post Split Option', 'jnews' ),
					'dependency'  => array(
						array(
							'field'    => 'enable_post_split',
							'operator' => '==',
							'value'    => '1',
						),
					),
					'fields'      => array(
						array(
							'type'            => 'jnews-radio-image',
							'id'              => 'template',
							'label'           => esc_html__( 'Post Split Template', 'jnews' ),
							'description'     => esc_html__( 'Choose post split template', 'jnews' ),
							'item_max_width'  => '118',
							'item_max_height' => '93',
							'options'         => array(
								'1'  => array(
									'label' => esc_html__( 'Split Post Template 1', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/1',
								),
								'2'  => array(
									'label' => esc_html__( 'Split Post Template 2', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/2',
								),
								'3'  => array(
									'label' => esc_html__( 'Split Post Template 3', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/3',
								),
								'4'  => array(
									'label' => esc_html__( 'Split Post Template 4', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/4',
								),
								'5'  => array(
									'label' => esc_html__( 'Split Post Template 5', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/5',
								),
								'6'  => array(
									'label' => esc_html__( 'Split Post Template 6', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/6',
								),
								'7'  => array(
									'label' => esc_html__( 'Split Post Template 7', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/7',
								),
								'8'  => array(
									'label' => esc_html__( 'Split Post Template 8', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/8',
								),
								'9'  => array(
									'label' => esc_html__( 'Split Post Template 9', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/9',
								),
								'10'  => array(
									'label' => esc_html__( 'Split Post Template 10', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/10',
								),
								'11'  => array(
									'label' => esc_html__( 'Split Post Template 11', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/11',
								),
								'12'  => array(
									'label' => esc_html__( 'Split Post Template 12', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/12',
								),
								'13'  => array(
									'label' => esc_html__( 'Split Post Template 13', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/13',
								),
								'14'  => array(
									'label' => esc_html__( 'Split Post Template 14', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/14',
								),
								'15'  => array(
									'label' => esc_html__( 'Split Post Template 15', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/15',
								),
								'16'  => array(
									'label' => esc_html__( 'Split Post Template 16', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/16',
								),
								'17'  => array(
									'label' => esc_html__( 'Split Post Template 17', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/17',
								),
								'18'  => array(
									'label' => esc_html__( 'Split Post Template 18', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/18',
								),
								'19'  => array(
									'label' => esc_html__( 'Split Post Template 19', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/19',
								),
								'20'  => array(
									'label' => esc_html__( 'Split Post Template 20', 'jnews' ),
									'img' => JNEWS_THEME_URL . '/assets/img/admin/metabox/split_post_template/20',
								),
							),
							'default'         => '1',
						),

						array(
							'type'        => 'jnews-select',
							'id'          => 'tag',
							'label'       => esc_html__( 'Split Post by using this Tag', 'jnews' ),
							'description' => esc_html__( 'Your post will be split with this header as their mark', 'jnews' ),
							'default'     => 'h2',
							'choices'     => array(
								array(
									'value' => 'h1',
									'label' => esc_attr__('Heading 1 ( H1 Tag )', 'jnews' ),
								),
								array(
									'value' => 'h2',
									'label' => esc_attr__('Heading 2 ( H2 Tag )', 'jnews' ),
								),
								array(
									'value' => 'h3',
									'label' => esc_attr__('Heading 3 ( H3 Tag )', 'jnews' ),
								),
								array(
									'value' => 'h4',
									'label' => esc_attr__('Heading 4 ( H4 Tag )', 'jnews' ),
								),
								array(
									'value' => 'h5',
									'label' => esc_attr__('Heading 5 ( H5 Tag )', 'jnews' ),
								),
								array(
									'value' => 'h6',
									'label' => esc_attr__('Heading 6 ( H6 Tag )', 'jnews' ),
								),
							),
						),

						array(
							'type'        => 'jnews-select',
							'id'          => 'numbering',
							'label'       => esc_html__( 'Split Header Numbering', 'jnews' ),
							'description' => esc_html__( 'Choose how your split page number used', 'jnews' ),
							'default'     => 'asc',
							'choices'     => array(
								array(
									'value' => 'desc',
									'label' => esc_attr__( 'Descending (ex : 3, 2, 1)', 'jnews' ),
								),
								array(
									'value' => 'asc',
									'label' => esc_attr__( 'Ascending (ex : 1, 2, 3)', 'jnews' ),
								),
							),
						),

						array(
							'type'        => 'jnews-select',
							'id'          => 'mode',
							'label'       => esc_html__( 'Load Mode for Post Split', 'jnews' ),
							'description' => esc_html__( 'Choose your how your post split will load', 'jnews' ),
							'default'     => 'normal',
							'choices'     => array(
								array(
									'value' => 'normal',
									'label' => esc_attr__( 'Normal Load', 'jnews' ),
								),
								array(
									'value' => 'all',
									'label' => esc_attr__( 'Load All Content', 'jnews' ),
								),
								array(
									'value' => 'ajax',
									'label' => esc_attr__( 'Load Content with Ajax', 'jnews' ),
								),
							),
							'dependency'  => array(
								array(
									'field'    => 'template',
									'operator' => 'in',
									'value'    => array( '1', '2', '3', '4', '5' ),
								),
							),
						),

						array(
							'type'        => 'jnews-toggle',
							'id'          => 'first',
							'label'       => esc_html__( 'Featured Image', 'jnews' ),
							'description' => esc_html__( 'Show featured image only on first pages. Shows on all pages if false', 'jnews' ),
							'default'     => '0',
							'dependency'  => array(
								array(
									'field'    => 'template',
									'operator' => 'in',
									'value'    => array( '1', '2', '3', '4', '5' ),
								),
							),
						),

						array(
							'type'        => 'jnews-toggle',
							'id'          => 'enable_toc',
							'label'       => esc_html__( 'Table of Contents', 'jnews' ),
							'description' => esc_html__( 'Activate table of contents on top of content when using this split post type', 'jnews' ),
							'default'     => '0',
							'dependency'  => array(
								array(
									'field'    => 'template',
									'operator' => 'in',
									'value'    => array( '6', '7', '8', '9', '10', '11', '12', '13', '14' ),
								),
							),
						),

						array(
							'type'        => 'jnews-select',
							'id'          => 'toc_type',
							'label'       => esc_html__( 'Table of Contents Type', 'jnews' ),
							'description' => esc_html__( 'Choose table of contents type', 'jnews' ),
							'default'     => 'normal',
							'choices'     => array(
								array(
									'value' => 'normal',
									'label' => esc_attr__( 'Normal', 'jnews' ),
								),
								array(
									'value' => 'floating',
									'label' => esc_attr__( 'Floating', 'jnews' ),
								),
								array(
									'value' => 'both',
									'label' => esc_attr__( 'Both', 'jnews' ),
								),
							),
							'dependency'  => array(
								array(
									'field'    => 'template',
									'operator' => 'in',
									'value'    => array( '6', '7', '8', '9', '10', '11', '12', '13', '14' ),
								),
								array(
									'field'    => 'enable_toc',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
					),
				),
			),
		);
	}
}
