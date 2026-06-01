<?php

use Spatie\SchemaOrg\Schema;

class WordPress_Helpdesk_FAQ_Post_Type extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;
    protected $stop_words;
    protected $schmemaFAQs;
    protected $user_role;


    /**
     * Construct FAQ Post Type Class
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param [type] $plugin_name [description]
     * @param [type] $version     [description]
     */
    public function __construct($plugin_name, $version, $stop_words)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->stop_words = $stop_words;

        $this->schmemaFAQs = array();
    }

    /**
     * Init FAQ Post type Class if enabled
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return [type] [description]
     */
    public function init()
    {
        global $wordpress_helpdesk_options;
        $this->options = $wordpress_helpdesk_options;

        if (!$this->get_option('enableFAQ')) {
            return false;
        }

        $this->register_faq_post_type();
        $this->register_faq_taxonomy();
        $this->add_custom_meta_fields();

        add_action('post_submitbox_start', array( $this, 'show_copy_button' ));
        add_action('admin_action_copy_ticket_to_faq', array( $this, 'copy_ticket_to_faq' ));

        add_shortcode('knowledge_base', array( $this, 'get_knowledge_base' ));
        add_shortcode('faq', array( $this, 'get_faq' ));
        add_shortcode('faqs', array( $this, 'get_faqs' ));
        add_shortcode('faq_search', array( $this, 'get_shortcode_search' ));

        if($this->get_option('FAQByUserRole')) {
            $this->user_role = $this->get_user_role(get_current_user_id());
            add_action('add_meta_boxes', array($this, 'add_custom_metaboxes'), 10, 2);
            add_action('save_post', array($this, 'save_custom_metaboxes'), 1, 2);
        }
        
    }

    /**
     * Get Knowledge Base Shortcode Output 
     * [knowledge_base columns="3" max_faqs="5" orderby="order" order="ASC"]
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param  [type] $atts [description]
     * @return [type]       [description]
     */
    public function get_knowledge_base($atts)
    {
        $args = shortcode_atts(array(
            'columns' => (int) $this->get_option('FAQColumns'),
            'max_faqs' => 5,
            'orderby' => 'order',
            'order' => 'ASC',
        ), $atts);

        $columns = $args['columns'];
        $max_faqs = $args['max_faqs'];
        $orderby = $args['orderby'];
        $order = $args['order'];
        $topicsLoggedInOnly = is_array($this->get_option('FAQTopicsLoggedInOnly')) ? $this->get_option('FAQTopicsLoggedInOnly') : array();

        $topics = get_terms(array(
            'taxonomy'      => 'faq_topics',
            'hide_empty'    => false,
            'parent'        => 0,
            'orderby'       => $orderby,
            'order'         => $order,
        ));

        if (empty($topics)) {
            return '<h2 class="wordpress-helpdesk-no-topics">' . __('No Topics created so far!', 'wordpress-helpdesk') . '</h2>';
        }

        foreach ($topics as $key => $topic) {

            if(in_array($topic->term_id, $topicsLoggedInOnly) && !is_user_logged_in()) {
                unset($topics[$key]);
            }

            $termUserRoles = get_term_meta($topic->term_id, 'wordpress_helpdesk_user_roles', true);
            if(!empty($termUserRoles) && !in_array($this->user_role, $termUserRoles)) {
                unset($topics[$key]);
            }
        }

        $masonry = $this->get_option('FAQMasonry');
        if(!$masonry) {
            $topics = array_chunk($topics, $columns);
        }

        $columns = floor( 12 / intval($columns) );
        $max_faqs = intval($max_faqs);

        $sidebarClass = '';
        $contentClass = '';
        if($this->get_option('supportSidebarPosition') == "left") {
            $sidebarClass = 'wordpress-helpdesk-pull-left';
            $contentClass = 'wordpress-helpdesk-pull-right';
        } elseif($this->get_option('supportSidebarPosition') == "right") {
            $sidebarClass = 'wordpress-helpdesk-pull-right';
            $contentClass = 'wordpress-helpdesk-pull-left';
        }
        
        ob_start();

        $FAQContentBefore = $this->get_option('FAQContentBefore');
        if(!empty($FAQContentBefore)) {
            echo '<div class="wordpress-helpdesk-faq-content-before">';
                echo do_shortcode($FAQContentBefore);
            echo '</div>';
        }

        ?>
        <div class="wordpress-helpdesk wordpress-helpdesk-faq">
            <div class="wordpress-helpdesk-row">
                <?php
                $checks = array('none', 'only_ticket');
                if(in_array($this->get_option('supportSidebarDisplay'), $checks)) {
                    echo '<div class="wordpress-helpdesk-col-sm-12">';
                } else {
                    echo '<div class="wordpress-helpdesk-col-sm-8 ' . $contentClass . '">';
                }

                if ($this->get_option('FAQShowSearch')) {
                    $this->get_search();
                }
                foreach ($topics as $topic) {

                    if(is_array($topic)) {
                        echo '<div class="wordpress-helpdesk-row">';
                        foreach ($topic as $_topic) {
                            $this->get_faq_column($_topic, $columns, $max_faqs);
                        }
                        echo '</div>';
                    } else {
                        $this->get_faq_column($topic, $columns, $max_faqs);
                    }
                }
                ?>
                
                </div>
                <?php
                $checks = array('both', 'only_faq');
                if(in_array($this->get_option('supportSidebarDisplay'), $checks)) {
                ?>
                <div class="wordpress-helpdesk-col-sm-4 wordpress-helpdesk-sidebar <?php echo $sidebarClass ?>">
                    <?php dynamic_sidebar('helpdesk-sidebar'); ?>
                </div>
                <?php
                }
                ?>
            </div>
        </div>
        <?php

        $FAQContentAfter = $this->get_option('FAQContentAfter');
        if(!empty($FAQContentAfter)) {
            echo '<div class="wordpress-helpdesk-faq-content-after">';
                echo do_shortcode($FAQContentAfter);
            echo '</div>';
        }

        $output_string = ob_get_contents();
        ob_end_clean();
        return $output_string;
    }

    /**
     * Get FAQ Column
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $topic    [description]
     * @param   [type]                       $columns  [description]
     * @param   [type]                       $max_faqs [description]
     * @return  [type]                                 [description]
     */
    private function get_faq_column($topic, $columns, $max_faqs)
    {
        $layout = $this->get_option('FAQLayout');
        $loggedInOnlyFAQs = $this->get_option('FAQLoggedInOnly');
        $FAQByUserRole = $this->get_option('FAQByUserRole');

        $loggedInHideInKnowledgeBase = $this->get_option('FAQLoggedInHideInKnowledgeBase');

        $topic_image = get_term_meta($topic->term_id, 'wordpress_helpdesk_image');
        if(!empty($topic_image) && isset($topic_image[0]['url'])) {
            $topic_icon = '<img src="' . $topic_image[0]['url'] . '" alt="' . $topic->name . '" class=" wordpress-helpdesk-faq-topic-icon">';
        } else {

            $topic_icon = get_term_meta($topic->term_id, 'wordpress_helpdesk_icon');
            if (isset($topic_icon) && !empty($topic_icon)) {
                $topic_icon = '<i class="' . $topic_icon[0] . ' fa-4x wordpress-helpdesk-faq-topic-icon" aria-hidden="true"></i>';
            } else {
                $topic_icon = '<i class="fa fa-file-alt fa-4x wordpress-helpdesk-faq-topic-icon" aria-hidden="true"></i>';
            }
        }


        ?>
        <div class="wordpress-helpdesk-faq-column wordpress-helpdesk-col-sm-<?php echo $columns ?> wordpress-helpdesk-col-md-6">
            <?php if($layout == "list") { ?>
                <a href="<?php echo get_term_link($topic->term_id) ?>">
                    <h3 class="wordpress-helpdesk-faq-title"><?php echo $topic->name ?></h3>
                </a>
                <hr class="wordpress-helpdesk-faq-divider">
                <ul class="wordpress-helpdesk-faq-list">
                <?php
                $args = array(
                    'post_type' => 'faq',
                    'orderby' => 'menu_order',
                    'order' => 'ASC',
                    'hierarchical' => false,
                    'posts_per_page' => $max_faqs,
                    'tax_query' => array(
                        array(
                        'taxonomy' => 'faq_topics',
                        'field' => 'id',
                        'terms' => $topic->term_id, // Where term_id of Term 1 is "1".
                        'include_children' => false
                        )
                    )
                );
                $faqs = get_posts($args);
                foreach ($faqs as $faq) {

                    if($loggedInHideInKnowledgeBase == "1" && !is_user_logged_in()){
                        continue;
                    }

                    if(is_array($loggedInOnlyFAQs) && in_array($faq->ID, $loggedInOnlyFAQs) && !is_user_logged_in()) {
                        continue;
                    }

                    if($FAQByUserRole) {
                        $FAQUserRoles = get_post_meta($post->ID, 'user_roles', true);
                        if(!empty($FAQUserRoles) && !in_array($this->user_role, $FAQUserRoles)) {
                            continue;
                        }
                    }

                    echo '<li><a href="' . get_permalink($faq->ID) . '">' . $topic_icon . $faq->post_title . '</a></li>';
                }
                ?>
                </ul>
                <a href="<?php echo get_term_link($topic->term_id) ?>" class="wordpress-helpdesk-faq-list-count">
                    <?php echo sprintf(_n( 'View %s article', 'View %s articles', $topic->count, 'wordpress-helpdesk' ), $topic->count) ?>
                </a>
            <?php } else { ?>
                 <a href="<?php echo get_term_link($topic->term_id) ?>">
                    <div class="wordpress-helpdesk-faq-boxed">
                        <?php echo $topic_icon ?>
                        <h3 class="wordpress-helpdesk-faq-boxed-title"><?php echo $topic->name ?></h3>
                        <p class="wordpress-helpdesk-faq-boxed-description"><?php echo $topic->description ?></p>
                        <p class="wordpress-helpdesk-faq-boxed-count"><?php echo sprintf(_n( 'View %s article', 'View %s articles', $topic->count, 'wordpress-helpdesk' ), $topic->count) ?></p>
                    </div>
                </a>
            <?php
            }
            ?>
        </div>
        <?php
    }

    /**
     * Get FAQ search
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function get_search()
    {
        ?>
        <div class="wordpress-helpdesk-row">
                <div class="wordpress-helpdesk-col-sm-10 wordpress-helpdesk-col-sm-offset-1">
                    <form method="get" class="wordpress-helpdesk-faq-searchform" action="<?php echo site_url('/'); ?>" autocomplete="off">
                        <input style="display:none" type="text" name="fakeusernameremembered"/>
                        <input style="display:none" type="password" name="fakepasswordremembered"/>
                        <input type="search" class="wordpress-helpdesk-faq-searchterm form-control" name="s" autocomplete="off" placeholder="<?php echo __('Search FAQs', 'wordpress-helpdesk') ?>">
                        <input type="hidden" name="post_type" value="faq" />
                        <button type="submit" class="searchform-submit">
                            <span class="fa fa-search" aria-hidden="true"></span><span class="screen-reader-text"><?php echo __('Search FAQs', 'wordpress-helpdesk') ?></span>
                        </button>
                        <div class="wordpress-helpdesk-faq-live-search-results" style="display: none;"></div>
                    </form>
                </div>
            </div>
        <?php
    }

    public function get_shortcode_search()
    {
        ob_start();
        ?>
        <div class="wordpress-helpdesk">
            <?php $this->get_search(); ?>
        </div>
        <?php
        $output_string = ob_get_contents();
        ob_end_clean();
        return $output_string;
    }

    /**
     * Get single FAQ shortcode output 
     * [faq id="X" excerpt="true" content="false" link="true"]
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param  [type] $atts [description]
     * @return [type]       [description]
     */
    public function get_faq($atts)
    {
        $args = shortcode_atts(array(
            'id' => '',
            'excerpt' => 'true',
            'content' => 'false',
            'link' => 'true',
        ), $atts);

        $content = $args['content'];
        $excerpt = $args['excerpt'];
        $link = $args['link'];
        $id = $args['id'];

        if (empty($id)) {
            return __('No FAQ ID set.', 'wordpress-helpdesk');
        }

        $faq = get_post($id);

        if(!isset($faq->post_content)) {
            return __('No FAQ found.', 'wordpress-helpdesk');
        }

        $content = $content == 'true' ?  $content = $faq->post_content :  $content = '';
        $excerpt = $excerpt == 'true' ?  $excerpt = $this->get_excerpt($faq->post_content) :  $excerpt = '';
        $link = $link == 'true' ?  $link = get_permalink($faq->ID) :  $link = '';

        $loggedInOnlyFAQs = $this->get_option('FAQLoggedInOnly');
        $loggedInHideInKnowledgeBase = $this->get_option('FAQLoggedInHideInKnowledgeBase');
        if($loggedInHideInKnowledgeBase == "1" && !is_user_logged_in()){
            return;
        }

        if(is_array($loggedInOnlyFAQs) && in_array($faq->ID, $loggedInOnlyFAQs) && !is_user_logged_in()) {
            return;
        }

        $FAQByUserRole = $this->get_option('FAQByUserRole');
        if($FAQByUserRole) {
            $FAQUserRoles = get_post_meta($id, 'user_roles', true);
            if(!empty($FAQUserRoles) && !in_array($this->user_role, $FAQUserRoles)) {
                return;
            }
        }

        ob_start();
        echo '
        <div class="wordpress-helpdesk-faq">
            <div class="wordpress-helpdesk-row">
                <div class="wordpress-helpdesk-col-sm-12">
                    <h3 class="wordpress-helpdesk-faq-title">' . $faq->post_title . '</h3>
                    <hr class="wordpress-helpdesk-faq-divider">';
                    if(!empty($excerpt)) {
                        echo '<div class="wordpress-helpdesk-faq-excerpt">' . $excerpt . '</div>';
                    }
                    if(!empty($content)) {
                        echo '<div class="wordpress-helpdesk-faq-content">' . $content . '</div>';
                    }
                    echo '<div class="wordpress-helpdesk-faq-link"><a href="' . $link . '">>' . __('View Article', 'wordpress-helpdesk') . '</a></div>
                </div>
            </div>
        </div>';
        $output_string = ob_get_contents();
        ob_end_clean();
        return $output_string;
    }

    /**
     * Get mutiple FAQs by Topic 
     * [faqs topic="ID" content="false" max_faqs="-1" excerpt="true" link="true" show_children="false" show_child_categories="true" columns="2" max_faqs="-1" order="ASC"
     * orderby="menu_order"]
     * If empty topic all FAQs will be rendered
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param  [type] $atts [description]
     * @return [type]       [description]
     */
    public function get_faqs($atts)
    {
        $args = shortcode_atts(array(
            'topic' => '',
            'show_topic_title' => 'false',
            'show_back_to_parent_topic' => 'false',
            'content' => 'false',
            'excerpt' => 'true',
            'link' => 'true',
            'max_faqs' => '-1',
            'show_children' => 'false',
            'hide_faqs_when_subcategories_exists' => 'false',
            'show_child_categories' => 'true',
            'columns' => '',
            'faq_columns' => '',
            'order' => 'ASC',
            'orderby' => 'menu_order',
            'accordion' => 'false',
            'show_faq_icon' => 'true',
            'show_search' => '',
        ), $atts);

        $content = $args['content'];
        $excerpt = $args['excerpt'];
        $link = $args['link'];
        $topic = $args['topic'];
        $show_topic_title = $args['show_topic_title'];
        $show_back_to_parent_topic = $args['show_back_to_parent_topic'];
        $order = $args['order'];
        $orderby = $args['orderby'];
        $hideFAQsWhenSubcategoriesExists = $args['hide_faqs_when_subcategories_exists'];
        $max_faqs = $args['max_faqs'];
        $show_children = $args['show_children'] === 'true' ? true: false;
        $show_child_categories = $args['show_child_categories'] === 'true' ? true: false;
        $accordion = $args['accordion'] === 'true' ? true: false;
        $show_faq_icon = $args['show_faq_icon'] === 'true' ? true: false;
        
        if(isset($args['show_search']) && !empty($args['show_search'])) {
            if($args['show_search'] == "true") {
                $show_search = true;
            } else {
                $show_search = false;
            }

        } else {
            $show_search = $this->get_option('FAQShowSearch');
        }

        if(empty($args['columns'])) {
            $args['columns'] = $this->get_option('FAQColumns');
        }

        if(empty($args['faq_columns'])) {
            $args['faq_columns'] = $this->get_option('FAQItemColumns');
        }

        if(empty($atts['accordion'])) {
            $accordion = $this->get_option('FAQAccordion');
        }

        
        $FAQItemColumns = $args['faq_columns'];
        $FAQItemLayout = $this->get_option('FAQItemLayout');
        $FAQItemMasonry = $this->get_option('FAQItemFAQItemMasonry');

        $originalColumns = $args['columns'];

        $topicsLoggedInOnly = is_array($this->get_option('FAQTopicsLoggedInOnly')) ? $this->get_option('FAQTopicsLoggedInOnly') : array();

        $columns = floor( 12 / intval($originalColumns) );
        $max_faqs = intval($max_faqs);

        $args = array(
            'post_type' => 'faq',
            'orderby' => $orderby,
            'order' => $order,
            'hierarchical' => false,
            'posts_per_page' => $max_faqs,
        );

        if (!empty($topic)) {
            $topics = explode(',', $topic);
            if(empty($topics)){
                return __('Topic missing.', 'wordpress-helpdesk');
            }
            $args['tax_query'] = array(
                array(
                'taxonomy' => 'faq_topics',
                'field' => 'id',
                'terms' => $topics,
                'include_children' => $show_children
                )
            );

            $topic_image = get_term_meta($topic, 'wordpress_helpdesk_image');
            if(!empty($topic_image) && isset($topic_image[0]['url'])) {
                $topic_icon = '<img src="' . $topic_image[0]['url'] . '" alt="' . $topic . '" class=" wordpress-helpdesk-faq-topic-icon">';
            } else {

                $topic_icon = get_term_meta($topic, 'wordpress_helpdesk_icon');
                if (isset($topic_icon) && !empty($topic_icon)) {
                    $topic_icon = '<i class="' . $topic_icon[0] . ' fa-4x wordpress-helpdesk-faq-topic-icon" aria-hidden="true"></i>';
                } else {
                    $topic_icon = '<i class="fa fa-file-alt fa-4x wordpress-helpdesk-faq-topic-icon" aria-hidden="true"></i>';
                }
            }
        }

        if(in_array($topic, $topicsLoggedInOnly) && !is_user_logged_in()) {
            return sprintf(__('Please <a href="%s" title="Login">login to view this topic.</a>', 'wordpress-helpdesk'), wp_login_url(get_permalink()));
        }

        $termUserRoles = get_term_meta($topic, 'wordpress_helpdesk_user_roles', true);
        if(!empty($termUserRoles) && !in_array($this->user_role, $termUserRoles)) {
            return __('You do not have sufficient access rights to see this topic.', 'wordpress-helpdesk');
        }
        
        ob_start();

        echo '<div class="wordpress-helpdesk wordpress-helpdesk-faq">';

        if ($show_search) {
            $this->get_search();
        }

        $topicTerm = get_term($topic);
        if(!empty($topicTerm)) {
            if($show_topic_title == "true") {

                $topicTermName = apply_filters('wordpress_helpdesk_topic_title', $topicTerm->name, $topicTerm);

                $FAQShowTopicTitleAppendix = $this->get_option('FAQShowTopicTitleAppendix');
                if(!empty($FAQShowTopicTitleAppendix)) {
                    $topicTermName .= $FAQShowTopicTitleAppendix;
                }

                echo '<div class="wordpress-helpdesk-faq-topic-title-container">';
                    echo '<h1 class="wordpress-helpdesk-faq-topic-title">' . $topicTermName . '</h1>';
                echo '</div>';
            }
            if(!empty($topicTerm->description)) {

                ?>
                <div class="wordpress-helpdesk-topic-description">
                    <?php echo wpautop( do_shortcode($topicTerm->description) ) ?>
                </div>
                <?php

            }

            if($show_back_to_parent_topic == "true") {
                
                if($topicTerm->parent != 0) {

                    $topicParentTerm = get_term($topicTerm->parent);
                    $topicParentTermLink =  get_term_link($topicParentTerm->term_id);
                    $topicParentTermName = apply_filters('wordpress_helpdesk_topic_title', esc_html__('< Back to ', 'wordpress-helpdesk') . $topicParentTerm->name, $topicParentTerm);

                    $FAQShowTopicTitleAppendix = $this->get_option('FAQShowTopicTitleAppendix');
                    if(!empty($FAQShowTopicTitleAppendix)) {
                        $topicParentTermName .= $FAQShowTopicTitleAppendix;
                    }

                    echo '<div class="wordpress-helpdesk-faq-back-to-parent-topic-container">';
                        echo '<a href="' . $topicParentTermLink . '" class="wordpress-helpdesk-faq-back-to-parent-topic">' . $topicParentTermName . '</a>';
                    echo '</div>';
                } else {

                    $FAQKnowledgeBasePage = $this->get_option('FAQKnowledgeBasePage');
                    $FAQKnowledgeBasePageName = apply_filters('wordpress_helpdesk_topic_title', esc_html__('< Back to ', 'wordpress-helpdesk') . get_the_title($FAQKnowledgeBasePage), $FAQKnowledgeBasePage);

                    if($FAQKnowledgeBasePage) {
                        echo '<div class="wordpress-helpdesk-faq-back-to-parent-topic-container wordpress-helpdesk-faq-back-to-parent-topic-container-knowledge-base">';
                            echo '<a href="' . get_permalink($FAQKnowledgeBasePage) . '" class="wordpress-helpdesk-faq-back-to-parent-topic">' . $FAQKnowledgeBasePageName . '</a>';
                        echo '</div>';
                    }
                }
            }
        }
            
        $masonry = $this->get_option('FAQMasonry');
        if($show_child_categories == "true") {

            $children = get_terms( 'faq_topics', array( 'parent' => $topic, 'hide_empty' => false ) ); //get_term_children( $topic, 'faq_topics');
            if(!empty($children)) {

                if(!$masonry) {
                    $children = array_chunk($children, $originalColumns);
                }

                echo '<div class="wordpress-helpdesk-row" style="margin-bottom: 20px;">';
                foreach ($children as $child) {
                    if(in_array($child, $topicsLoggedInOnly) && !is_user_logged_in()) {
                        continue;
                    }

                    if(is_array($child)) {
                        echo '<div class="wordpress-helpdesk-row">';
                        foreach ($child as $_child) {
                            $_topic_child = get_term($_child);
                            $this->get_faq_column($_topic_child, $columns, $max_faqs);
                        }
                        echo '</div>';
                    } else {
                        $topic_child = get_term($child);
                        $this->get_faq_column($topic_child, $columns, $max_faqs);
                    }

                    
                    // $this->get_faq_column($topic_child, $columns, $max_faqs);
                }
                echo '</div>';

                if($hideFAQsWhenSubcategoriesExists == "true") {
                    echo '</div>';

                    $output_string = ob_get_contents();
                    ob_end_clean();
                    return $output_string;
                }
            }
        }

        $faqs = get_posts($args);
        if(empty($faqs)) {
            echo '<b>' . __('No Articles found.', 'wordpress-helpdesk') . '</b></div>';
            $output_string = ob_get_contents();
            ob_end_clean();
            return $output_string;
        }

        if(!$masonry) {
            $faqs = array_chunk((array) $faqs, $FAQItemColumns);
        }

        $FAQItemColumns = floor( 12 / intval($FAQItemColumns) );

        foreach ($faqs as $faq) {

            if(is_array($faq)) {
                echo '<div class="wordpress-helpdesk-row">';

                foreach ($faq as $faq_row) {
                    $this->get_single_faq_column($faq_row, $FAQItemColumns, $topic_icon, $content, $excerpt, $link, $accordion, $show_faq_icon);
                }

                echo '</div>';
                continue;
            } else {
                $this->get_single_faq_column($faq, $FAQItemColumns, $topic_icon, $content, $excerpt, $link, $accordion, $show_faq_icon);
            }
        }

        if($this->get_option('FAQSchemaSupport')) {
            $SchemaFAQPage = Schema::FAQPage()->mainEntity($this->schmemaFAQs);
            echo $SchemaFAQPage->toScript();
        }

        echo '</div>';

        $output_string = ob_get_contents();
        ob_end_clean();
        return $output_string;
    }

    public function get_single_faq_column($faq, $column, $topic_icon, $content, $excerpt, $link, $accordion, $show_faq_icon)
    {
        $loggedInOnlyFAQs = $this->get_option('FAQLoggedInOnly');
        $loggedInHideInKnowledgeBase = $this->get_option('FAQLoggedInHideInKnowledgeBase');

        $the_content = $content == 'true' ?  $faq->post_content :  '';
        $the_excerpt = $excerpt == 'true' ?  $this->get_excerpt($faq->post_content) : '';
        $the_link = $link == 'true'  || $accordion == true ? get_permalink($faq->ID) : '';

        if($loggedInHideInKnowledgeBase == "1" && !is_user_logged_in()){
            return false;
        }

        if(is_array($loggedInOnlyFAQs) && in_array($faq->ID, $loggedInOnlyFAQs) && !is_user_logged_in()) {
            return false;
        }

        $FAQByUserRole = $this->get_option('FAQByUserRole');
        if($FAQByUserRole) {
            $FAQUserRoles = get_post_meta($faq->ID, 'user_roles', true);
            if(!empty($FAQUserRoles) && !in_array($this->user_role, $FAQUserRoles)) {
                return false;
            }
        }

        $this->schmemaFAQs[] = Schema::question()
                ->name($faq->post_title)
                ->acceptedAnswer(
                    Schema::answer()
                        ->text( str_replace(array("\r", "\n", "\t"), "", strip_tags( $the_excerpt) ) )
                );

        $class = $accordion ? 'wordpress-helpdesk-faq-accordion' : '';

        echo '
        <div class="wordpress-helpdesk-col-sm-' . $column . '">
            <div class="wordpress-helpdesk-faq ' . $class  . '">
                <a class="wordpress-helpdesk-faq-title-link" href="' . $the_link . '">
                    <h3 class="wordpress-helpdesk-faq-title">';
                        if($show_faq_icon) {
                            echo $topic_icon . $faq->post_title;
                        }

                        if($accordion) {
                            echo '<i class="fa fa-chevron-down wordpress-helpdesk-faq-accordion-icon" aria-hidden="true"></i>';
                        }
                    echo '
                    </h3>
                </a>
                <div class="wordpress-helpdesk-faq-content-container">
                    <hr class="wordpress-helpdesk-faq-divider">';
                    if(!empty($the_excerpt)) {
                        echo '<div class="wordpress-helpdesk-faq-excerpt">' . $the_excerpt . '</div>';
                    }
                    if(!empty($the_content)) {
                        echo '<div class="wordpress-helpdesk-faq-content">' . $the_content . '</div>';
                    }
                    echo '<div class="wordpress-helpdesk-faq-link"><a href="' . $the_link . '">> ' . __('View Article', 'wordpress-helpdesk') . '</a></div>
                </div>';

                echo '
            </div>
        </div>';
    }

    /**
     * Register FAQ Post Type
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function register_faq_post_type()
    {
        $redirect_base = "";
        $FAQKnowledgeBasePage = $this->get_option('FAQKnowledgeBasePage');
        if (!empty($FAQKnowledgeBasePage)) {
            $redirect_base = get_post_field('post_name', $FAQKnowledgeBasePage) . '/';
        }

        $singular = __('FAQ', 'wordpress-helpdesk');
        $plural = __('FAQs', 'wordpress-helpdesk');

        $labels = array(
            'name' => __('FAQs', 'wordpress-helpdesk'),
            'all_items' => sprintf(__('All %s', 'wordpress-helpdesk'), $plural),
            'singular_name' => $singular,
            'add_new' => sprintf(__('New %s', 'wordpress-helpdesk'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'wordpress-helpdesk'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'wordpress-helpdesk'), $singular),
            'new_item' => sprintf(__('New %s', 'wordpress-helpdesk'), $singular),
            'view_item' => sprintf(__('View %s', 'wordpress-helpdesk'), $singular),
            'search_items' => sprintf(__('Search %s', 'wordpress-helpdesk'), $plural),
            'not_found' => sprintf(__('No %s found', 'wordpress-helpdesk'), $plural),
            'not_found_in_trash' => sprintf(__('No %s found in trash', 'wordpress-helpdesk'), $plural),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'exclude_from_search' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'menu_position' => 70,
            'rewrite' => array(
                'slug' => $redirect_base . 'faq',
                'with_front' => false
            ),
            'query_var' => 'faqs',
            'supports' => array('title', 'editor', 'author', 'revisions', 'thumbnail', 'comments', 'page-attributes'),
            'menu_icon' => 'dashicons-welcome-learn-more',
            'capability_type'     => array('faq','faqs'),
            'capabilities' => array(
                'publish_posts' => 'publish_faqs',
                'edit_posts' => 'edit_faqs',
                'edit_others_posts' => 'edit_others_faqs',
                'delete_posts' => 'delete_faqs',
                'delete_others_posts' => 'delete_others_faqs',
                'delete_published_posts' => 'delete_published_faqs',
                'read_private_posts' => 'read_private_faqs',
                'edit_post' => 'edit_faq',
                'delete_post' => 'delete_faq',
                'read_post' => 'read_faq',
                'edit_published_posts' => 'edit_published_faqs'
            ),
            'map_meta_cap' => true,
            'taxonomies' => array('product_cat'),
        );

        register_post_type('faq', $args);
    }

    /**
     * Add custom ticket metaboxes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post_type [description]
     * @param   [type]                       $post      [description]
     */
    public function add_custom_metaboxes($post_type, $post)
    {
        add_meta_box('wordpress-helpdesk-faq-roles', __('User Role Access', 'wordpress-helpdesk'), array($this, 'show_user_roles'), 'faq', 'side', 'default');
    }

    public function show_user_roles()
    {
        global $wp_roles, $post;
        $rolesSettings = array();

        wp_nonce_field(basename(__FILE__), 'wordpress_helpdesk_meta_nonce');

        if(isset($wp_roles->roles) && !empty($wp_roles->roles)) {
            $roles = $wp_roles->roles;
            
            if(!empty($roles)) {
                
                $existing = get_post_meta($post->ID, 'user_roles', true);
                $options = '<option value="">' . esc_html__('Select User Roles', 'wordpress-helpdesk') . '</option>';
                $options = '<option value="guest">' . esc_html__('Guest (logged out)', 'wordpress-helpdesk') . '</option>';
                foreach ($roles as $roleKey => $roledData) {

                    $selected = '';
                    if(in_array($roleKey, $existing)) {
                        $selected = 'selected="selected"';
                    }

                    $options .= '<option value="' . $roleKey . '" ' . $selected . '>' . $roledData['name'] . '</option>';
                }

                ?>

                <label for="wordpress_helpdesk_faq_user_roles">
                    <?php esc_html_e('User Roles Access', 'wordpress-helpdesk') ?>
                    <select name="wordpress_helpdesk_faq_user_roles[]" multiple id="" class="wordpress-helpdesk-select2">
                        <?php echo $options ?>
                    </select>
                </label>

                <?php
            }
        }
    }

    /**
     * Save Custom Metaboxes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post_id [description]
     * @param   [type]                       $post    [description]
     * @return  [type]                                [description]
     */
    public function save_custom_metaboxes($post_id, $post)
    {
        global $post;
        
        if (!is_object($post)) {
            return;
        }

        if($post->post_type !== "faq") {
            return;
        }

        // Is the user allowed to edit the post or page?
        if (!current_user_can('edit_post', $post->ID)) {
            return $post->ID;
        }

        if (!isset($_POST['wordpress_helpdesk_meta_nonce']) || !wp_verify_nonce($_POST['wordpress_helpdesk_meta_nonce'], basename(__FILE__))) {
            return;
        }

        if(isset($_POST['wordpress_helpdesk_faq_user_roles']) && !empty($_POST['wordpress_helpdesk_faq_user_roles'])) {
            update_post_meta($post->ID, 'user_roles', $_POST['wordpress_helpdesk_faq_user_roles']);
        } else {
            delete_post_meta($post->ID, 'user_roles');
        }
    }

    /**
     * Register FAQ Categories and FAQ Filter Taxonomies.
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function register_faq_taxonomy()
    {
        $redirect_base = "";
        $FAQKnowledgeBasePage = $this->get_option('FAQKnowledgeBasePage');
        if (!empty($FAQKnowledgeBasePage)) {
            $redirect_base = get_post_field('post_name', $FAQKnowledgeBasePage) . '/';
        }

        // FAQ Category
        $singular = __('Topic', 'wordpress-helpdesk');
        $plural = __('Topics', 'wordpress-helpdesk');

        $labels = array(
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => sprintf(__('Search %s', 'wordpress-helpdesk'), $plural),
            'all_items' => sprintf(__('All %s', 'wordpress-helpdesk'), $plural),
            'parent_item' => sprintf(__('Parent %s', 'wordpress-helpdesk'), $singular),
            'parent_item_colon' => sprintf(__('Parent %s:', 'wordpress-helpdesk'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'wordpress-helpdesk'), $singular),
            'update_item' => sprintf(__('Update %s', 'wordpress-helpdesk'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'wordpress-helpdesk'), $singular),
            'new_item_name' => sprintf(__('New %s Name', 'wordpress-helpdesk'), $singular),
            'menu_name' => $plural,
        );

        $args = array(
                'labels' => $labels,
                'public' => true,
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'sort' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
                'rewrite' => array('slug' => $redirect_base . 'topics', 'hierarchical' => true, 'with_front' => false),
                'capabilities' => array(
                    'manage_terms' => 'manage_faq_topics',
                    'edit_terms' => 'edit_faq_topics',
                    'delete_terms' => 'delete_faq_topics',
                    'assign_terms' => 'assign_faq_topics',
                ),
        );

        register_taxonomy('faq_topics', 'faq', $args);
    }

    /**
     * Show Copy to FAQ Button on Tickets
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function show_copy_button()
    {
        global $post;

        if (!$this->get_option('enableFAQ')) {
            return false;
        }

        if (! is_object($post)) {
            return;
        }

        if ($post->post_type != 'ticket') {
            return;
        }

        if (isset($_GET['post'])) {
            $notifyUrl = wp_nonce_url(admin_url("edit.php?action=copy_ticket_to_faq&post=" . absint($_GET['post'])), 'wordpress_helpdesk_copy_' . $_GET['post']);
            ?>
           <a class="button button-primary button-large copy-to-faq" href="<?php echo esc_url($notifyUrl); ?>"><?php _e('Create FAQ from this Ticket', 'wordpress-helpdesk'); ?></a>
            <?php
        }
    }

    /**
     * Copy a ticket content to an FAQ
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function copy_ticket_to_faq()
    {
        if (empty($_REQUEST['post'])) {
            wp_die(__('No ticket to duplicate has been supplied!', 'wordpress-helpdesk'));
        }

        // Get the original page
        $id = isset($_REQUEST['post']) ? absint($_REQUEST['post']) : '';

        check_admin_referer('wordpress_helpdesk_copy_' . $id);

        $post = get_post($id);

        if (! empty($post)) {
            unset($post->ID);
            $post->post_type = 'faq';
            $post->post_author = wp_get_current_user()->ID;

            $new_post_id = wp_insert_post($post);

            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit();
        } else {
            wp_die(__('FAQ creation failed, could not find original ticket: ', 'wordpress-helpdesk') . ' ' . $id);
        }
    }


    /**
     * Add Custom Meta Field Icon to FAQ Topics
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     */
    public function add_custom_meta_fields()
    {
        $prefix = 'wordpress_helpdesk_';
        $custom_taxonomy_meta_config = array(
            'id' => 'faq_meta_box',
            'title' => 'FAQ Meta Box',
            'pages' => array('faq_topics'),
            'context' => 'side',
            'fields' => array(),
            'local_images' => false,
            'use_with_theme' => false,
        );

        $custom_taxonomy_meta_fields = new Tax_Meta_Class($custom_taxonomy_meta_config);
        $custom_taxonomy_meta_fields->addText($prefix.'icon', array('name'=> __('Font Awesome Icon.', 'wordpress-helpdesk'), 'std' => 'fa fa-file-alt fa-1x', 'desc' => 'Learn more here: http://fontawesome.io/icons/'));
        $custom_taxonomy_meta_fields->addImage($prefix.'image', array('name'=> __('Custom Image Icon.', 'wordpress-helpdesk'), 'std' => ''));

        global $wp_roles;
        if(isset($wp_roles->roles) && !empty($wp_roles->roles)) {
            $roles = $wp_roles->roles;
            
            if(!empty($roles)) {
                    
                $options = array();
                $options['guest'] = esc_html__('Guest (logged out)', 'wordpress-helpdesk');
                foreach ($roles as $roleKey => $roledData) {
                    $options[$roleKey] = $roledData['name'];    
                }
                $custom_taxonomy_meta_fields->addSelect($prefix.'user_roles', $options, array(
                    'name'=> __('User Role Access.', 'wordpress-helpdesk'), 
                    'std' => '', 
                    'desc' => 'What user roles should have access to this topic?',
                    'multiple' => true,
                ) );
            }
        }

        $custom_taxonomy_meta_fields->Finish();
    }

    /**
     * AJAX search FAQs
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function ajax_search_faqs()
    {
        $FAQSearchMaxResults = $this->get_option('FAQSearchMaxResults');

        $term = $_POST['term'];
        
        $term = filter_var($term, FILTER_SANITIZE_STRING);

        $words = array_count_values(str_word_count(strtolower($term), 1));
        $words = array_diff_key($words, array_flip($this->stop_words));
        $words = array_keys($words);

        $search_words = implode(' ', $words);
        $search_words_key = str_replace(' ', '-', $search_words);

        $args = array(
            'post_type' => 'faq',
            'post_status' => 'publish',
            's' => $search_words,
            'posts_per_page' => $FAQSearchMaxResults
        );
        $search = new WP_Query($args);

        $response = array(
            'count' => 0,
            'message' => '',
        );

        ob_start();
        $content = "";
        if ($search->have_posts()) {
            $response['count'] = count($search->posts);

            echo '<header class="wordpress-helpdesk-faq-live-search-header">';
                echo '<div class="wordpress-helpdesk-faq-live-search-header-title">' . sprintf(__('Search Results for: %s', 'wordpress-helpdesk'), $term) . '</div>';
            echo '</header>';

            while ($search->have_posts()) :
                $search->the_post();

                echo '<a href="' . get_the_permalink() . '" class="wordpress-helpdesk-faq-live-search-result">';
                    echo '<div class="wordpress-helpdesk-faq-live-search-result-title">' . get_the_title() . '</div>';
                    echo '<div class="wordpress-helpdesk-faq-live-search-result-content">' . $this->get_excerpt( get_the_content() ) . '</div>';
                echo '</a>';
            endwhile;

            if($response['count'] == $FAQSearchMaxResults) {
                echo '<footer class="wordpress-helpdesk-faq-live-search-footer">';
                    echo '<div class="wordpress-helpdesk-faq-live-search-footer-found-more">' . sprintf( __('We found more than %d results ...', 'wordpress-helpdesk'), $FAQSearchMaxResults) . '</div>';
                    echo '<a href="' . get_home_url() . '?post_type=faq&s=' . $term . '" class="wordpress-helpdesk-faq-live-search-footer-see-all">' . __('Click here to see all', 'wordpress-helpdesk') . '</a>';
                echo '</footer class="wordpress-helpdesk-faq-live-search-footer">';
            }

        } else {

            if($this->get_option('FAQSearchComments')) {
                $args = array(
                    'type' => 'comment',
                    'search' => $search_words,
                    'number' => $FAQSearchMaxResults,
                );
                $comments_query = new WP_Comment_Query;
                $comments = $comments_query->query( $args );
                
                if(!empty($comments)) {
                    
                    $commentsCount = count($comments);

                    echo '<header class="wordpress-helpdesk-faq-live-search-header">';
                        echo '<div class="wordpress-helpdesk-faq-live-search-header-title">' . sprintf(__('Search Results for: %s', 'wordpress-helpdesk'), $term) . '</div>';
                    echo '</header>';

                    foreach ($comments as $comment) {                   

                        if(!isset($comment->comment_post_ID) || empty($comment->comment_post_ID)) {
                            continue;
                        }

                        $faq = get_post($comment->comment_post_ID);
                        echo '<a href="' . get_permalink($comment->comment_post_ID) . '" class="wordpress-helpdesk-faq-live-search-result">';
                            echo '<div class="wordpress-helpdesk-faq-live-search-result-title">' . $faq->post_title . '</div>';
                            echo '<div class="wordpress-helpdesk-faq-live-search-result-content">' . $this->get_excerpt( $faq->post_content ) . '</div>';
                        echo '</a>';

                    } 

                    if($commentsCount == $FAQSearchMaxResults) {
                        echo '<footer class="wordpress-helpdesk-faq-live-search-footer">';
                            echo '<div class="wordpress-helpdesk-faq-live-search-footer-found-more">' . sprintf( __('We found more than %d results ...', 'wordpress-helpdesk'), $FAQSearchMaxResults) . '</div>';
                            echo '<a href="' . get_home_url() . '?post_type=faq&s=' . $term . '" class="wordpress-helpdesk-faq-live-search-footer-see-all">' . __('Click here to see all') . '</a>';
                        echo '</footer class="wordpress-helpdesk-faq-live-search-footer">';
                    }

                } else {
                    echo '<header class="wordpress-helpdesk-faq-live-search-header">';
                        echo '<div class="wordpress-helpdesk-faq-live-search-header-title">' . sprintf(__('Could not find anything for: %s', 'wordpress-helpdesk'), $term) . '</div>';
                    echo '</header>';
                }

            } else {

                echo '<header class="wordpress-helpdesk-faq-live-search-header">';
                    echo '<div class="wordpress-helpdesk-faq-live-search-header-title">' . sprintf(__('Could not find anything for: %s', 'wordpress-helpdesk'), $term) . '</div>';
                echo '</header>';

            }
        }
        
        $content = ob_get_clean();
        
        $search_words_options = get_option('helpdesk_faq_search_words');
        if(empty($search_words_options)) {
            $search_words_options[$search_words_key] = array(
                'term' => $search_words,
                'count' => 1,
                'found' => $response['count']
            );
            update_option('helpdesk_faq_search_words', $search_words_options);
        } else {
            if(isset($search_words_options[$search_words_key])) {
                $search_words_options[$search_words_key]['count'] = $search_words_options[$search_words_key]['count'] + 1;
                $search_words_options[$search_words_key]['found'] = $response['count'];
            } else {
                $search_words_options[$search_words_key] = array(
                    'term' => $search_words,
                    'count' => 1,
                    'found' => $response['count']
                );
            }
            update_option('helpdesk_faq_search_words', $search_words_options);
        }

        $response['message'] = $content;
        die(json_encode($response));
    }

    /**
     * Count FAQ views and save views into faq_popularity meta key
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function count_views()
    {
        global $post;

        if(empty($post)) {
            return false;
        }

        if ($post->post_type !== "faq") {
            return false;
        }

        if (!is_single()) {
            return false;
        }

        $count_key = 'faq_popularity';
        $count = get_post_meta($post->ID, $count_key, true);

        if (!empty($count) || ($count === "0")) {
            $count++;
        } else {
            $count = 0;
        }
        update_post_meta($post->ID, $count_key, $count);
    }

    /**
     * Load custom FAQ Topics Template
     * Override this via a file in your theme called archive-faq_topic.php
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $template [description]
     * @return  [type]                                 [description]
     */
    public function faq_templates( $template ) 
    {
        global $post;

        if($this->get_option('useThemesTemplate')) {
            return $template;
        }

        $queried_object = get_queried_object();
        if(is_archive()) {
            if(isset($queried_object->taxonomy) && $queried_object->taxonomy == "faq_topics") {
                $theme_files = array('archive-faq_topic.php', 'wordpress-helpdesk/archive-faq_topic.php');
                $exists_in_theme = locate_template($theme_files, false);
                if ( $exists_in_theme != '' ) {
                    return $exists_in_theme;
                } else {
                    return plugin_dir_path(__FILE__) . 'views/archive-faq_topic.php';
                }
            }
        }
        if(is_single()) {
            if($post->post_type == "faq") {
                $theme_files = array('single-faq.php', 'wordpress-helpdesk/single-faq.php');
                $exists_in_theme = locate_template($theme_files, false);
                if ( $exists_in_theme != '' ) {
                    return $exists_in_theme;
                } else {
                    return plugin_dir_path(__FILE__) . 'views/single-faq.php';
                }
            }
        }
        return $template;
    }

    /**
     * Get excerpt from string
     * 
     * @param String $str String to get an excerpt from
     * @param Integer $startPos Position int string to start excerpt from
     * @param Integer $maxLength Maximum length the excerpt may be
     * @return String excerpt
     */
    private function get_excerpt($str, $startPos=0, $maxLength=250) {

        $maxLength = $this->get_option('FAQExcerptMaxLength') ? $this->get_option('FAQExcerptMaxLength') : 250;

        $excerpt = strip_tags( do_shortcode($str) );
        
        if(strlen($excerpt) > $maxLength) {
            $excerpt   = substr($excerpt, $startPos, $maxLength-3);
            $lastSpace = strrpos($excerpt, ' ');
            $excerpt   = substr($excerpt, 0, $lastSpace);
            $excerpt  .= '...';
        } else {
            $excerpt = $str;
        }

        $excerpt = strip_shortcodes( preg_replace("/\[[^\]]+\]/", '', $excerpt) );

        return strip_tags( $excerpt );
    }

    /**
     * Count FAQ likes and save likes into faq_likes meta key
     * @author Daniel Barenkamp
     * @version 1.1.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function count_likes()
    {
        $post_id = $_POST['post_id'];
        $post_id = filter_var($post_id, FILTER_SANITIZE_NUMBER_INT);

        if(empty($post_id)) {
            return false;
        }

        $post = get_post($post_id);

        if ($post->post_type !== "faq") {
            return false;
        }

        $ips_key = 'faq_ips';
        $count_key = 'faq_likes';
        $count = get_post_meta($post->ID, $count_key, true);

        if (!empty($count) || ($count == 1)) {
            $count++;
        } else {
            $count = 1;
        }

        $users_ip = (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
        if(!empty($users_ip)) {
            $ips = get_post_meta($post->ID, 'faq_ips', true);
            if(empty($ips)) {
                $ips = array(
                    $users_ip
                );
            } else {
                if(!in_array($users_ip, $ips)) {
                    $ips[] = $users_ip;
                } else {
                    $count--;
                }
            }

            update_post_meta($post->ID, $ips_key, $ips);
        }

        update_post_meta($post->ID, $count_key, $count);

        die(json_encode($count));
    }

    /**
     * Count FAQ dislikes and save dislikes into faq_dislikes meta key
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.1.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function count_dislikes()
    {
        $post_id = $_POST['post_id'];
        $post_id = filter_var($post_id, FILTER_SANITIZE_NUMBER_INT);

        if(empty($post_id)) {
            return false;
        }

        $post = get_post($post_id);

        if ($post->post_type !== "faq") {
            return false;
        }

        $ips_key = 'faq_ips';
        $count_key = 'faq_dislikes';
        $count = get_post_meta($post->ID, $count_key, true);

        if (!empty($count) || ($count == 1)) {
            $count++;
        } else {
            $count = 1;
        }
        

        $users_ip = (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
        if(!empty($users_ip)) {
            $ips = get_post_meta($post->ID, 'faq_ips', true);
            if(empty($ips)) {
                $ips = array(
                    $users_ip
                );
            } else {
                if(!in_array($users_ip, $ips)) {
                    $ips[] = $users_ip;
                } else {
                    $count--;
                }
            }

            update_post_meta($post->ID, $ips_key, $ips);
        }

        update_post_meta($post->ID, $count_key, $count);

        die(json_encode($count));
    }

    /**
     * Only show FAQ content to logged in users
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.1.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function only_logged_in($content)
    {
        global $post;

        if($this->get_option('FAQSingleLoggedIn')) {
            if ( $post->post_type == 'faq' && !is_user_logged_in()) {
                $content = sprintf(__('Please <a href="%s" title="Login">login to view this faq.</a>', 'wordpress-helpdesk'), wp_login_url(get_permalink()));    
            }
        }

        $loggedInOnlyFAQs = $this->get_option('FAQLoggedInOnly');
        if(is_array($loggedInOnlyFAQs) && in_array($post->ID, $loggedInOnlyFAQs) && !is_user_logged_in()) {
            $content = sprintf(__('Please <a href="%s" title="Login">login to view this faq.</a>', 'wordpress-helpdesk'), wp_login_url(get_permalink()));    
        }

        $FAQByUserRole = $this->get_option('FAQByUserRole');
        if($FAQByUserRole) {
            $FAQUserRoles = get_post_meta($post->ID, 'user_roles', true);
            if(!empty($FAQUserRoles) && !in_array($this->user_role, $FAQUserRoles)) {
                $content = sprintf(__('You do not have sufficient access rights to see this FAQ.', 'wordpress-helpdesk'), wp_login_url(get_permalink()));    
            }
        }

        return $content;
    }  


    public function add_faq_term_page()
    {
        add_submenu_page(
            'edit.php?post_type=ticket',
            __('FAQ Terms', 'wordpress-helpdesk'),
            __('FAQ Terms', 'wordpress-helpdesk'),
            'manage_options',
            'helpdesk-faq-terms',
            array($this, 'get_faq_terms_table')
        );
    }

    public function get_faq_terms_table()
    {

        $search_words_options = get_option('helpdesk_faq_search_words');
        if(empty($search_words_options) || !is_array($search_words_options)) {
            echo __('No Search Terms found yet', 'wordpress-helpdesk');
            return;
        }

        usort($search_words_options, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        echo '<h2>' . __('FAQ Terms', 'wordpress-helpdesk') . '</h2>';

        echo 
        '<table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td>' . __('Term', 'wordpress-helpdesk') . '</td>
                    <td>' . __('Search Counts', 'wordpress-helpdesk') . '</td>
                    <td>' . __('Articles Found', 'wordpress-helpdesk') . '</td>
                </tr>
            </thead>
            <tbody>';
        foreach ($search_words_options as $search_word) {
            echo '<tr>' .
                '<td>' . $search_word['term'] . '</td>' .
                '<td>' . $search_word['count'] . '</td>' .
                '<td>' . $search_word['found'] . '</td>' .
            '</tr>';
        }   
        echo 
            '</tbody>
        </table>';
    }

    public function add_product_query($query) {
        $query->set('post_type', array('product')); 
    }
}