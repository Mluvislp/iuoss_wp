<?php
global $wordpress_helpdesk_options;

$queried_object = get_queried_object();
get_header();

$FAQContentBefore = $wordpress_helpdesk_options['FAQContentBefore'];
if(!empty($FAQContentBefore)) {
    echo '<div class="wordpress-helpdesk-faq-content-before">';
        echo do_shortcode($FAQContentBefore);
    echo '</div>';
}

$FAQShowChildren = 'false';
if($wordpress_helpdesk_options['FAQShowChildren']) {
	$FAQShowChildren = 'true';
} else {
	$FAQShowChildren = 'false';
}

$FAQShowChildCategories = 'false';
if($wordpress_helpdesk_options['FAQShowChildCategories']) {
	$FAQShowChildCategories = 'true';
} else {
	$FAQShowChildCategories = 'false';
}

$FAQShowTopicTitle = 'false';
if($wordpress_helpdesk_options['FAQShowTopicTitle']) {
	$FAQShowTopicTitle = 'true';
} else {
	$FAQShowTopicTitle = 'false';
}

$FAQHideFAQsWhenSubcategoriesExists = 'true';
if($wordpress_helpdesk_options['FAQHideFAQsWhenSubcategoriesExists']) {
	$FAQHideFAQsWhenSubcategoriesExists = 'true';
} else {
	$FAQHideFAQsWhenSubcategoriesExists = 'false';
}

$FAQShowBackToParentTopic = 'true';
if($wordpress_helpdesk_options['FAQShowBackToParentTopic']) {
	$FAQShowBackToParentTopic = 'true';
} else {
	$FAQShowBackToParentTopic = 'false';
}

?>

<div class="container">
	<div class="container_inner default_template_holder clearfix page_container_inner">
		<div class="wordpress-helpdesk-row">
			<?php

			$sidebarClass = '';
			$contentClass = '';
			if($wordpress_helpdesk_options['supportSidebarPosition'] == "left") {
				$sidebarClass = 'wordpress-helpdesk-pull-left';
				$contentClass = 'wordpress-helpdesk-pull-right';
			} elseif($wordpress_helpdesk_options['supportSidebarPosition'] == "right") {
				$sidebarClass = 'wordpress-helpdesk-pull-right';
				$contentClass = 'wordpress-helpdesk-pull-left';
			}

	        $checks = array('none', 'only_ticket');
	        if(in_array($wordpress_helpdesk_options['supportSidebarDisplay'], $checks)) {
	            echo '<div class="wordpress-helpdesk-col-sm-12">';
	        } else {
	            echo '<div class="wordpress-helpdesk-col-sm-8 ' . $contentClass . '">';
	        }
	        ?>

				<?php echo do_shortcode('[faqs topic="' . $queried_object->term_id . '" show_topic_title="' . $FAQShowTopicTitle . '" 
				hide_faqs_when_subcategories_exists="' . $FAQHideFAQsWhenSubcategoriesExists . '" 
				show_children="' . $FAQShowChildren . '" show_child_categories="' . $FAQShowChildCategories . '" show_back_to_parent_topic="' . $FAQShowBackToParentTopic . '" max_faqs="-1"]'); ?>
			</div>
			<?php
			$checks = array('both', 'only_faq');
			if(in_array($wordpress_helpdesk_options['supportSidebarDisplay'], $checks)) {
			?>
			<div class="wordpress-helpdesk-col-sm-4 wordpress-helpdesk-pull-right wordpress-helpdesk-sidebar <?php echo $sidebarClass ?>">
				<?php dynamic_sidebar('helpdesk-sidebar'); ?>
			</div>
			<?php
			}
			?>
		</div>
	</div>
</div>

<?php

$FAQContentAfter = $wordpress_helpdesk_options['FAQContentAfter'];
if(!empty($FAQContentAfter)) {
    echo '<div class="wordpress-helpdesk-faq-content-after">';
        echo do_shortcode($FAQContentAfter);
    echo '</div>';
}

get_footer();