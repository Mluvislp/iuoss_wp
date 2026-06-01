<?php
/**
 * @author : Jegtheme
 */
namespace JNews\Multilang;

/**
 * Integration with WPML
 */
Class WPML
{
    /**
     * @var WPML
     */
    private static $instance;

    /**
     * @return WPML
     */
    public static function getInstance()
    {
        if ( null === static::$instance ) 
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        add_action( 'admin_init',           array( $this, 'option_string_translation' ) );
        add_action( 'admin_init',           array( $this, 'mod_string_translation' ) );

        add_filter( 'vp_option_key',        array( $this, 'option_key' ) );
        add_filter( 'vp_get_option_key',    array( $this, 'get_option_key' ) );
    }

    public function option_key( $key )
    {
        if ( defined('ICL_LANGUAGE_CODE') )
        {
            $current_language = ICL_LANGUAGE_CODE;

            if ( empty( $current_language ) )
            {
                global $sitepress;
                $current_language = $sitepress->get_default_language();
            }

            return $key . '_' . $current_language;
        }

        return $key;
    }
    
    public function get_option_key( $keys )
    {
        if ( defined('ICL_LANGUAGE_CODE') )
        {
            $keys[0] = $this->option_key($keys[0]);
        }

        return $keys;
    }

    public function option_string_translation()
    {
        if ( function_exists('icl_register_string') )
        {
            $buttons = jnews_get_option('single_social_share_main', array(
                array(
                    'social_share'  => 'facebook',
                    'social_text'   => esc_html__('Share on Facebook', 'jnews')
                ),
                array(
                    'social_share'   => 'twitter',
                    'social_text'    => esc_html__('Share on Twitter', 'jnews')
                ),
            ));

            foreach( $buttons as $button )
            {
                if ( !empty( $button['social_text'] ) )
                {
                    icl_register_string( 'jnews', $button['social_text'], $button['social_text']);
                }
            }
        }
    }

	public function mod_string_translation() {
		if ( function_exists( 'icl_register_string' ) ) {
            $strings = array(
				'footer_copyright'                        => array(
                    'label' => 'Copyright',
                    'value' => wp_kses( get_theme_mod( 'jnews_footer_copyright', jnews_get_footer_copyright_text() ), wp_kses_allowed_html() ),
                    'group' => 'Customizer',
                ),
				'footer_menu_title'                       => array(
                    'label' => 'Footer Menu Title',
                    'value' => wp_kses( get_theme_mod( 'jnews_footer_menu_title', 'Navigate Site' ), wp_kses_allowed_html() ),
                    'group' => 'Customizer',
                ),
				'footer_social_title'                     => array(
                    'label' => 'Footer Social Title',
                    'value' => wp_kses( get_theme_mod( 'jnews_footer_social_title', 'Follow Us' ), wp_kses_allowed_html() ),
                    'group' => 'Customizer',
				),
				'jnews_gdpr_cookie_button'                => array(
					'label' => 'Cookie Law Policy Button',
					'value' => get_theme_mod( 'jnews_gdpr_cookie_button', 'I Agree' ),
					'group' => 'Customizer',
				),
				'jnews_gdpr_cookie_text'                  => array(
					'label' => 'Cookie Law Policy Text',
					'value' => wp_kses( get_theme_mod( 'jnews_gdpr_cookie_text', 'This website uses cookies. By continuing to use this website you are giving consent to cookies being used. Visit our <a href="#">Privacy and Cookie Policy</a>.' ), wp_kses_allowed_html() ),
					'group' => 'Customizer',
				),
				'jnews_gdpr_comment_label'                => array(
					'label' => 'Privacy Policy Label',
					'value' => get_theme_mod( 'jnews_gdpr_comment_label', 'Privacy Policy Agreement' ),
					'group' => 'Customizer',
				),
				'jnews_gdpr_comment_text'                 => array(
					'label' => 'Privacy Policy Text on Comment Form',
					'value' => wp_kses( get_theme_mod( 'jnews_gdpr_comment_text', 'I agree to the Terms &amp; Conditions and <a href="#">Privacy Policy</a>.' ), wp_kses_allowed_html() ),
					'group' => 'Customizer',
				),
				'jnews_gdpr_register_text'                => array(
					'label' => 'Privacy Policy Text on Register Form',
					'value' => wp_kses( get_theme_mod( 'jnews_gdpr_register_text', '<span class="required">*</span>By registering into our website, you agree to the Terms &amp; Conditions and <a href="#">Privacy Policy</a>' ), wp_kses_allowed_html() ),
					'group' => 'Customizer',
				),
				'jnews_single_post_related_ftitle'        => array(
					'label' => 'First title of related post',
					'value' => get_theme_mod( 'jnews_single_post_related_ftitle', 'Related' ),
					'group' => 'Customizer',
				),
				'jnews_single_post_related_stitle'        => array(
					'label' => 'Secondary title of related post',
					'value' => get_theme_mod( 'jnews_single_post_related_stitle', 'Posts' ),
					'group' => 'Customizer',
				),
				'jnews_single_post_inline_related_ftitle' => array(
					'label' => 'First title of inline related post',
					'value' => get_theme_mod( 'jnews_single_post_inline_related_ftitle', 'Related' ),
					'group' => 'Customizer',
				),
				'jnews_single_post_inline_related_stitle' => array(
					'label' => 'Secondary title of inline related post',
					'value' => get_theme_mod( 'jnews_single_post_inline_related_stitle', 'Posts' ),
					'group' => 'Customizer',
				),
				'jnews_header_html_mobile'                => array(
					'label' => 'Mobile HTML',
					'value' => wp_kses( get_theme_mod( 'jnews_header_html_mobile', '' ), wp_kses_allowed_html() ),
					'group' => 'Customizer',
				),
				'jnews_header_html_drawer'                => array(
					'label' => 'Drawer HTML',
					'value' => wp_kses( get_theme_mod( 'jnews_header_html_drawer', '' ), wp_kses_allowed_html() ),
					'group' => 'Customizer',
				),
				'jnews_header_html_1'                     => array(
					'label' => 'HTML Element 1',
					'value' => wp_kses( get_theme_mod( 'jnews_header_html_1', '' ), wp_kses_allowed_html() ),
					'group' => 'Customizer',
				),
				'jnews_header_html_2'                     => array(
					'label' => 'HTML Element 2',
					'value' => wp_kses( get_theme_mod( 'jnews_header_html_2', '' ), wp_kses_allowed_html() ),
					'group' => 'Customizer',
				),
				'jnews_header_html_3'                     => array(
					'label' => 'HTML Element 3',
					'value' => wp_kses( get_theme_mod( 'jnews_header_html_3', '' ), wp_kses_allowed_html() ),
					'group' => 'Customizer',
				),
				'jnews_header_html_4'                     => array(
					'label' => 'HTML Element 4',
					'value' => wp_kses( get_theme_mod( 'jnews_header_html_4', '' ), wp_kses_allowed_html() ),
					'group' => 'Customizer',
				),
				'jnews_header_html_5'                     => array(
					'label' => 'HTML Element 5',
					'value' => wp_kses( get_theme_mod( 'jnews_header_html_5', '' ), wp_kses_allowed_html() ),
					'group' => 'Customizer',
				),
				'jnews_header_logo_text'                  => array(
					'label' => 'Header Logo Text',
					'value' => get_theme_mod( 'jnews_header_logo_text', 'Logo' ),
					'group' => 'Customizer',
				),
				'jnews_header_logo'                       => array(
					'label' => 'Header Logo',
					'value' => get_theme_mod( 'jnews_header_logo', get_parent_theme_file_uri( 'assets/img/logo.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_header_logo_retina'                => array(
					'label' => 'Header Logo Retina',
					'value' => get_theme_mod( 'jnews_header_logo_retina', get_parent_theme_file_uri( 'assets/img/logo@2x.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_header_logo_darkmode'              => array(
					'label' => 'Header Logo Dark Mode',
					'value' => get_theme_mod( 'jnews_header_logo_darkmode', get_parent_theme_file_uri( 'assets/img/logo_darkmode.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_header_logo_retina_darkmode'       => array(
					'label' => 'Header Logo Retina Dark Mode',
					'value' => get_theme_mod( 'jnews_header_logo_retina_darkmode', get_parent_theme_file_uri( 'assets/img/logo_darkmode@2x.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_header_logo_alt'                   => array(
					'label' => 'Header Logo Alt',
					'value' => get_theme_mod( 'jnews_header_logo_alt', get_bloginfo( 'name' ) ),
					'group' => 'Customizer',
				),
				'jnews_sticky_logo_text'                  => array(
					'label' => 'Sticky Header Logo Text',
					'value' => get_theme_mod( 'jnews_sticky_logo_text', 'Logo' ),
					'group' => 'Customizer',
				),
				'jnews_sticky_menu_logo'                  => array(
					'label' => 'Sticky Menu Logo',
					'value' => get_theme_mod( 'jnews_sticky_menu_logo', get_parent_theme_file_uri( 'assets/img/sticky_logo.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_sticky_menu_logo_retina'           => array(
					'label' => 'Sticky Menu Logo Retina',
					'value' => get_theme_mod( 'jnews_sticky_menu_logo_retina', get_parent_theme_file_uri( 'assets/img/sticky_logo@2x.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_sticky_menu_logo_darkmode'         => array(
					'label' => 'Sticky Menu Logo Dark Mode',
					'value' => get_theme_mod( 'jnews_sticky_menu_logo_darkmode', get_parent_theme_file_uri( 'assets/img/logo_darkmode.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_sticky_menu_logo_retina_darkmode'  => array(
					'label' => 'Sticky Menu Logo Retina',
					'value' => get_theme_mod( 'jnews_sticky_menu_logo_retina_darkmode', get_parent_theme_file_uri( 'assets/img/logo_darkmode@2x.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_sticky_menu_alt'                   => array(
					'label' => 'Sticky Menu Alt',
					'value' => get_theme_mod( 'jnews_sticky_menu_alt', get_bloginfo( 'name' ) ),
					'group' => 'Customizer',
				),
				'jnews_mobile_logo_text'                  => array(
					'label' => 'Mobile Logo Text',
					'value' => get_theme_mod( 'jnews_mobile_logo_text', 'Logo' ),
					'group' => 'Customizer',
				),
				'jnews_mobile_logo'                       => array(
					'label' => 'Mobile Device Logo',
					'value' => get_theme_mod( 'jnews_mobile_logo', get_parent_theme_file_uri( 'assets/img/logo_mobile.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_mobile_logo_retina'                => array(
					'label' => 'Mobile Device Logo Retina',
					'value' => get_theme_mod( 'jnews_mobile_logo_retina', get_parent_theme_file_uri( 'assets/img/logo_mobile@2x.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_mobile_logo_darkmode'              => array(
					'label' => 'Mobile Device Logo Dark Mode',
					'value' => get_theme_mod( 'jnews_mobile_logo_darkmode', get_parent_theme_file_uri( 'assets/img/logo_darkmode.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_mobile_logo_retina_darkmode'       => array(
					'label' => 'Mobile Device Logo Retina Dark Mode',
					'value' => get_theme_mod( 'jnews_mobile_logo_retina_darkmode', get_parent_theme_file_uri( 'assets/img/logo_darkmode@2x.png' ) ),
					'group' => 'Customizer',
				),
				'jnews_mobile_logo_alt'                   => array(
					'label' => 'Mobile Logo Alt',
					'value' => get_theme_mod( 'jnews_mobile_logo_alt', get_bloginfo( 'name' ) ),
					'group' => 'Customizer',
				),

			);

			$mobile_buttons = array( 1, 2, 3, 'mobile', 'drawer' );
			foreach ( $mobile_buttons as $key ) {
				$strings[ 'jnews_header_button_' . $key . '_text' ] = array(
					'label' => 'Button Text for Button' . $key,
					'value' => get_theme_mod( 'jnews_header_button_' . $key . '_text', 'Your text' ),
					'group' => 'Customizer',
				);
			}

			foreach ( $strings as $string ) {
                icl_register_string( 'jnews', $string['value'], $string['value'] );
            }
        }
    }
}