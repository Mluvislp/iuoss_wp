<?php
/**
 * @author : Jegtheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Theme JNews_Post_Split
 */
class JNews_Split_Type_List extends JNews_Split_Type_Abstract {
	/**
	 * @var string
	 */
	protected $template;

	protected $list_style = array( '6', '7', '8', '9', '10', '11', '12', '13', '14' );

	public function __construct( $splitter, $page, $numbering, $mode, $template, $tag, $enable_toc, $toc_type ) {
		parent::__construct( $splitter, $page, $numbering, $mode, $tag, $enable_toc, $toc_type );

		$this->template = $template;
	}

	public function render() {
		$output  = $this->table_of_content();
		$output .= $this->before_content;
		$output .= $this->render_content();

		return $output;
	}

	public function render_content() {
		$output = null;

		$contents = $this->splitter->get_all_result();
		$contents = $contents['content'];

		foreach ( $contents as $id => $content ) {
			$output .= "<div class='split-wrapper split-postlist active' data-id='{$id}'>";
			$output .= $this->render_title( $id );
			$output .= apply_filters( 'jnews_split_content_description', $content['description'], $id, $this->max_page );
			$output .= '</div>';
		}

		$output = "<div class='split-container split-template-{$this->get_split_type()}'>{$output}</div>";

		return $output;
	}

	public function render_title( $id ) {
		$current = $this->page_span( $id );
		$heading = $this->header_tag;

		return '<' . $heading . " class=\"current_title\">{$current}</" . $heading . '>';
	}

	public function page_span( $index ) {
		$title  = $this->all_title[ $index ];
		$number = $this->get_page_number( $index + 1 );

		$page_heading = '';
		switch ( $this->template ) {
			case '6':
				$page_heading = '
                    <div class="pageinfo">
                        <div class="pagenum" id="' . $this->get_title_id( $title ) . '">' . str_pad( $number, 2, '0', STR_PAD_LEFT ) . '</div>
                        <div class="pagetotal">
                            <span>' . jnews_return_translation( 'of', 'jnews-split', 'of' ) . '</span> ' . str_pad( $this->max_page, 2, '0', STR_PAD_LEFT ) . '
                        </div>
                    </div>
                    <div class="pagetitle"><span>' . $title . '</span></div>';
				break;
			case '9':
			case '10':
			case '11':
			case '12':
			case '13':
				$page_heading = '<span class="pagenum" id="' . $this->get_title_id( $title ) . '">' . $number . '</span> ' . $title;
				break;
			default:
				$page_heading = '<span class="pagenum" id="' . $this->get_title_id( $title ) . '">' . $number . '.</span> ' . $title;
				break;
		}

		return $page_heading;
	}

	public function get_split_type() {
		return $this->template;
	}

	public function get_title_id( $title ) {
		$title_id = strip_tags( $title );
		$title_id = preg_replace( '/\p{P}/', '', $title_id );
		$title_id = str_replace( array( ' ', '$', '.', '#', '"', '\'' ), '-', $title_id );

		if ( ! mb_check_encoding( $title_id, 'UTF-8' ) ) {
			$title_id = htmlentities( $title_id, ENT_QUOTES | ENT_IGNORE, 'UTF-8' );
		}

		return $title_id;
	}

	public function table_of_content() {
		$output = null;

		if ( in_array( $this->template, $this->list_style ) && $this->enable_toc ) {
			$contents = $this->splitter->get_all_result();
			$contents = $contents['content'];
			$display  = 'floating' === $this->toc_type ? 'style="display:none"' : '';

			$output = '<div class="jeg table-of-contents ' . $this->toc_type . ' split-template-' . $this->template . '" ' . $display . ' data-type="' . $this->toc_type . '"><div class="toc-wrapper"><div class="toc-wrapper-header"><p>' . jnews_return_translation( 'Table of Contents', 'jnews-split', 'table_of_contents' ) . '</p></div><nav><ul class="toc-wrapper-item-wrap">';

			if ( 'asc' === $this->numbering ) {
				foreach ( $contents as $id => $content ) {
					$output .= '<li class="toc-wrapper-item item"><a class="toc-wrapper-link" href="' . '#' . $this->get_title_id( $content['title'] ) . '"><span class="toc-wrapper-item-number">' . strval( $id + 1 ) . '. </span><span class="toc-wrapper-item-text">' . $content['title'] . '</span></a></li>';
				}
			} else {
				$number = count( $contents );
				foreach ( $contents as $id => $content ) {
					$output .= '<li class="toc-wrapper-item item"><a class="toc-wrapper-link" href="' . '#' . $this->get_title_id( $content['title'] ) . '"><span class="toc-wrapper-item-number">' . strval( $number ) . '. </span><span class="toc-wrapper-item-text">' . $content['title'] . '</span></a></li>';
					$number -= 1;
				}
			}

			$output .= ' </ul></nav>';
			$output .= ( get_theme_mod( 'jnews_accent_color', '' ) ? '<style scoped>.toc-wrapper a:hover { color: ' . get_theme_mod( 'jnews_accent_color', '' ) . '; } .jeg.table-of-contents-floating.show a:hover { color: ' . get_theme_mod( 'jnews_accent_color', '' ) . '; }</style>' : '' );
			$output .= '</div></div>';

			if ( ( 'floating' === $this->toc_type || 'both' === $this->toc_type ) && ! defined( 'JNEWS_AUTOLOAD_POST' ) ) {
				$output .= '<div class="jeg table-of-contents-floating split-template-' . $this->template . '"><div class="floating-bar"><i class="fa fa-bars"></i></div><div class="floating-navigation"></div></div>';
				$output .= '<div class="jeg table-of-contents-spacer"></div>';
			}
		}

		return $output;
	}
}
