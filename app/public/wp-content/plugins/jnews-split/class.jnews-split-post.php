<?php
/**
 * @author : Jegtheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JNews_Split_Post
 */
class JNews_Split_Post {
	/**
	 * @var JNews_Split_Post
	 */
	private static $instance;

	/**
	 * Split Post Query Var
	 *
	 * @var string
	 */
	private $query_var = 'split-post';

	/**
	 * @return JNews_Split_Post
	 */
	public static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct() {
		add_filter( 'the_content', array( $this, 'post_split' ), 12 );
		add_filter( 'jnews_single_first_split_featured', array( $this, 'first_post_split_featured' ), 10, 2 );
		add_filter( 'pre_handle_404', array( $this, 'pre_handle_404' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

		add_filter( 'query_vars', array( $this, 'ajax_query_vars' ) );
		add_filter( 'template_include', array( $this, 'ajax_template' ) );
	}

	/**
	 * Hook handle 404 allow split post to use page query
	 *
	 * @param bool      $bool
	 * @param \WP_Query $query
	 *
	 * @return bool
	 */
	public function pre_handle_404( $bool, \WP_Query $query ) {
		if ( $query->posts && is_singular() ) {
			if ( $query->post instanceof \WP_Post ) {
				$post   = clone $query->post;
				$enable = jnews_get_metabox_value( 'jnews_post_split.enable_post_split', false, $post->ID );
				$tag    = jnews_get_metabox_value( 'jnews_post_split.post_split.0.tag', 'h2', $post->ID );

				if ( $enable && $enable === '1' ) {
					require_once 'class.jnews-split-tool.php';
					$result   = $this->prepare_split( $post->post_content, $tag );
					$splitter = new JNews_Split_Tool( $result, $tag, $tag );

					if ( $splitter->have_split_content() ) {
						$page    = $this->get_current_page();
						$maxpage = $splitter->get_total_split() + 1;
						$success = (int) $page <= $maxpage;
						if ( $success ) {
							status_header( 200 );
							$bool = true;
						}
					}
				}
			}
		}

		return $bool;
	}


	public function ajax_query_vars( $vars ) {
		$vars[] = $this->query_var;

		return $vars;
	}

	public function is_ajax_request() {
		global $wp;

		if ( is_array( $wp->query_vars ) ) {
			return array_key_exists( $this->query_var, $wp->query_vars );
		}

		return false;
	}

	public function ajax_template( $template ) {
		if ( $this->is_ajax_request() ) {
			add_filter( 'jnews_force_disable_inline_related_post', '__return_true' );

			return JNEWS_SPLIT_DIR . 'split-template.php';
		}

		return $template;
	}

	public function load_scripts() {
		if ( ( is_singular( 'post' ) && jnews_get_metabox_value( 'jnews_post_split.enable_post_split', false ) ) || ( is_singular( 'post' ) && defined( 'JNEWS_AUTOLOAD_POST' ) ) ) {
			$template = jnews_get_metabox_value( 'jnews_post_split.post_split.0.template', '1' );
			$toc_type = jnews_get_metabox_value( 'jnews_post_split.post_split.0.toc_type', 'normal' );

			$dependencies       = array( 'jnews-frontend' );
			$dependencies_style = array();

			if ( SCRIPT_DEBUG || get_theme_mod( 'jnews_load_necessary_asset', false ) ) {
				$dependencies         = array( 'jquery', 'tiny-slider-noconflict' );
				$dependencies_style[] = 'jnews-global-slider';
			}
			wp_enqueue_style( 'jnews-split', JNEWS_SPLIT_URL . '/assets/css/splitpost.css', $dependencies_style, null, false );
			wp_enqueue_script( 'jnews-split', JNEWS_SPLIT_URL . '/assets/js/jquery.split.js', $dependencies, null, true );
			wp_enqueue_style( 'jnews-split-toc', JNEWS_SPLIT_URL . '/assets/css/splitpost-toc.css', null, null, false );

			if ( in_array( $template, array( '6', '7', '8', '9', '10', '11', '12', '13', '14' ) ) && ( 'floating' === $toc_type || 'both' === $toc_type ) ) {
				wp_enqueue_script( 'jnews-toc', JNEWS_SPLIT_URL . '/assets/js/jquery.toc.js', $dependencies, null, true );
			}
		}
	}

	public function wrapper_position( $string, $start ) {
		$wrap_start = strpos( $string, '>' ) + 1;
		$wrap_end   = strrpos( $string, '<' ) - 1;

		return array(
			'start' => $start + $wrap_start,
			'end'   => $start + $wrap_end,
		);
	}

	public function prepare_split( $content, $tag ) {
		$node = ( new JNews_Split_Content_Tag( $content ) )->find_parent_recursive( $tag );

		if ( $node && $node->parent->start && $node->parent->end ) {
			$parent   = $node->parent;
			$string   = substr( $content, $parent->start, $parent->end - $parent->start );
			$position = $this->wrapper_position( $string, $parent->start );
			$result   = substr( $content, $position['start'], $position['end'] - $position['start'] );

			return array(
				'start'   => $position['start'],
				'end'     => $position['end'],
				'result'  => $result,
				'content' => $content,
			);
		} else {
			return array(
				'start'   => 0,
				'end'     => strlen( $content ),
				'result'  => $content,
				'content' => $content,
			);
		}
	}

	public function post_split( $content ) {
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return $content;
		}

		if ( function_exists( 'jpw_pages_list' ) ) {
			$paywall_truncater = \JNews\Paywall\Truncater\Truncater::instance();
			if ( $paywall_truncater->check_status() ) {
				return $content;
			}
		}

		if ( $this->is_ajax_request() ) {
			$enable = jnews_get_metabox_value( 'jnews_post_split.enable_post_split', false );
			$tag    = jnews_get_metabox_value( 'jnews_post_split.post_split.0.tag', 'h2' );
			$page   = sanitize_text_field( $_REQUEST['index'] );

			if ( $enable ) {
				$result = $this->prepare_split( $content, $tag );

				require_once 'class.jnews-split-tool.php';
				$splitter = new JNews_Split_Tool( $result, $tag, $tag );

				if ( $splitter->have_split_content() ) {
					$result      = $splitter->get_all_result();
					$description = $result['content'][ $page ]['description'];

					return apply_filters( 'jnews_split_content_description', $description, $page, $splitter->get_total_split() + 1 );
				}
			}
		} else {
			if ( is_single() && get_post_type() === 'post' ) {
				$enable     = jnews_get_metabox_value( 'jnews_post_split.enable_post_split', false );
				$tag        = jnews_get_metabox_value( 'jnews_post_split.post_split.0.tag', 'h2' );
				$template   = jnews_get_metabox_value( 'jnews_post_split.post_split.0.template', '1' );
				$numbering  = jnews_get_metabox_value( 'jnews_post_split.post_split.0.numbering', 'asc' );
				$mode       = jnews_get_metabox_value( 'jnews_post_split.post_split.0.mode', 'normal' );
				$enable_toc = jnews_get_metabox_value( 'jnews_post_split.post_split.0.enable_toc', false );
				$toc_type   = jnews_get_metabox_value( 'jnews_post_split.post_split.0.toc_type', 'normal' );

				if ( $enable ) {
					$result = $this->prepare_split( $content, $tag );

					require_once 'class.jnews-split-tool.php';
					$splitter = new JNews_Split_Tool( $result, $tag, $tag );

					if ( $splitter->have_split_content() ) {
						$page = $this->get_current_page();

						require_once 'type/class.jnews-split-type-abstract.php';

						switch ( $template ) {
							case '1':
								require_once 'type/class.jnews-split-type-1.php';
								$class = new JNews_Split_Type_1( $splitter, $page, $numbering, $mode, $tag );
								break;
							case '2':
								require_once 'type/class.jnews-split-type-2.php';
								$class = new JNews_Split_Type_2( $splitter, $page, $numbering, $mode, $tag );
								break;
							case '3':
								require_once 'type/class.jnews-split-type-3.php';
								$class = new JNews_Split_Type_3( $splitter, $page, $numbering, $mode, $tag );
								break;
							case '4':
								require_once 'type/class.jnews-split-type-4.php';
								$class = new JNews_Split_Type_4( $splitter, $page, $numbering, $mode, $tag );
								break;
							case '5':
								require_once 'type/class.jnews-split-type-5.php';
								$class = new JNews_Split_Type_5( $splitter, $page, $numbering, $mode, $tag );
								break;
							case '6':
							case '7':
							case '8':
							case '9':
							case '10':
							case '11':
							case '12':
							case '13':
							case '14':
								require_once 'type/class.jnews-split-type-list.php';
								$class = new JNews_Split_Type_List( $splitter, $page, $numbering, $mode, $template, $tag, $enable_toc, $toc_type );
								break;
							case '15':
								require_once 'type/class.jnews-split-type-15.php';
								$class = new JNews_Split_Type_15( $splitter, $page, $numbering, $mode, $tag );
								break;
							case '16':
								require_once 'type/class.jnews-split-type-16.php';
								$class = new JNews_Split_Type_16( $splitter, $page, $numbering, 'all', $tag );
								break;
							case '17':
								require_once 'type/class.jnews-split-type-17.php';
								$class = new JNews_Split_Type_17( $splitter, $page, $numbering, 'all', $tag );
								break;
							case '18':
								require_once 'type/class.jnews-split-type-18.php';
								$class = new JNews_Split_Type_18( $splitter, $page, $numbering, 'all', $tag );
								break;
							case '19':
								require_once 'type/class.jnews-split-type-19.php';
								$class = new JNews_Split_Type_19( $splitter, $page, $numbering, 'all', $tag );
								break;
							case '20':
								require_once 'type/class.jnews-split-type-20.php';
								$class = new JNews_Split_Type_20( $splitter, $page, $numbering, 'all', $tag );
								break;
							default:
								$class = null;
								break;
						}

						if ( $class !== null ) {
							return $class->render_output();
						}
					}
				}
			}
		}

		return $content;
	}

	public function first_post_split_featured( $flag, $post_id ) {
		if ( isset( $post_id ) && is_single( $post_id ) ) {
			return jnews_get_metabox_value( 'jnews_post_split.post_split.0.first', false, $post_id );
		}
	}

	public function get_current_page() {
		$page  = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
		$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

		return max( $page, $paged );
	}
}

