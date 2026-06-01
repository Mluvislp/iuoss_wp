<?php
use Spatie\SchemaOrg\Schema;

global $post, $wordpress_helpdesk_options;

$sidebarClass = '';
$contentClass = '';
if($wordpress_helpdesk_options['supportSidebarPosition'] == "left") {
	$sidebarClass = 'wordpress-helpdesk-pull-left';
	$contentClass = 'wordpress-helpdesk-pull-right';
} elseif($wordpress_helpdesk_options['supportSidebarPosition'] == "right") {
	$sidebarClass = 'wordpress-helpdesk-pull-right';
	$contentClass = 'wordpress-helpdesk-pull-left';
}

$likes = get_post_meta($post->ID, 'faq_likes', true);
if(!$likes) {
	$likes = 0;
}

get_header();


$FAQContentBefore = $wordpress_helpdesk_options['FAQContentBefore'];
if(!empty($FAQContentBefore)) {
    echo '<div class="wordpress-helpdesk-faq-content-before">';
        echo do_shortcode($FAQContentBefore);
    echo '</div>';
}

if($wordpress_helpdesk_options['FAQSchemaSupport'] == "1") {
    $SchemaFAQPage = Schema::QAPage()->mainEntity(Schema::question()
                ->name($post->post_title)
                ->answerCount(1)
                ->acceptedAnswer(
                    Schema::answer()
                    	->url( get_permalink($post->ID))
                    	->upvoteCount($likes)
                        ->text( str_replace(array("\r", "\n", "\t"), "", strip_tags( preg_replace("/\[[^\]]+\]/", '', $post->post_content) ) ) )
                ));
    echo $SchemaFAQPage->toScript();
}

$topics = get_the_terms($post->ID, 'faq_topics');
$userRole = 'guest';
$userId = get_current_user_id();
if(!empty($userId)) {

	$user = get_userdata($userId);
	if(!empty($user) && isset($user->roles)) {

		$userRole = array_shift($user->roles);
	}
}

$canView = true;
if(!empty($topics)) {
	foreach ($topics as $topic) {

        $termUserRoles = get_term_meta($topic->term_id, 'wordpress_helpdesk_user_roles', true);
        if(!empty($termUserRoles) && !in_array($userRole, $termUserRoles)) {
            $canView = false;
            break;
		 	}
		
	}
}

?>
<div class="clearfix"></div>
<div class="wordpress-helpdesk">
	<div id="main-content" class="main-content">
		<div class="container">
			<div class="container_inner default_template_holder clearfix page_container_inner">
				<div class="wordpress-helpdesk-row">
					<?php
			        $checks = array('none', 'only_ticket');
			        if(in_array($wordpress_helpdesk_options['supportSidebarDisplay'], $checks)) {
			            echo '<div class="wordpress-helpdesk-col-sm-12">';
			        } else {
			            echo '<div class="wordpress-helpdesk-col-sm-8 ' . $contentClass . '">';
			        }
			        ?>
						<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

						    <div <?php post_class() ?> id="post-<?php the_ID(); ?>">

						    	<?php if($canView) { ?>
							    	<div class="wordpress-helpdesk-row">
							    		<div class="wordpress-helpdesk-col-sm-12">

							        		<h1 class="wordpress-helpdesk-single-title"><?php the_title(); ?></h1>
							        		<?php 
											if($wordpress_helpdesk_options['FAQShowBackToParentTopic']) {

												if(!empty($topics)) {

													foreach ($topics as $topic) {

														$children = get_term_children($topic->term_id, 'faq_topics');
														if( empty( $children ) ) {
														    $lastTopic = $topic;
														    break;
														}
													}

												    if($lastTopic->parent != 0) {

												        $topicParentTerm = $lastTopic;
												        $topicParentTermLink =  get_term_link($topicParentTerm->term_id);
												        $topicParentTermName = apply_filters('wordpress_helpdesk_topic_title', esc_html__('< Back to ', 'wordpress-helpdesk') . $topicParentTerm->name, $topicParentTerm);

												        $FAQShowTopicTitleAppendix = $wordpress_helpdesk_options['FAQShowTopicTitleAppendix'];
												        if(!empty($FAQShowTopicTitleAppendix)) {
												            $topicParentTermName .= $FAQShowTopicTitleAppendix;
												        }

												        echo '<div class="wordpress-helpdesk-faq-back-to-parent-topic-container">';
												            echo '<a href="' . $topicParentTermLink . '" class="wordpress-helpdesk-faq-back-to-parent-topic">' . $topicParentTermName . '</a>';
												        echo '</div>';
												    }
											    }
											}
											?>
											
								            <div class="wordpress-helpdesk-meta-information">
									        <?php

									        // Topics
									        if (!empty($topics)) {							        
										        // Topics
										        foreach ($topics as $topic) {
										            $topic_color = get_term_meta($topic->term_id, 'wordpress_helpdesk_color');
										            if (isset($topic_color) && !empty($topic_color)) {
										                $topic_color = $topic_color[0];
										            } else {
										                $topic_color = '#000000';
										            }
										            echo '<a href="' . get_term_link($topic->term_id) . '">'
										                    . '<span class="wordpress-helpdesk-topics label wordpress-helpdesk-topic-' . $topic->slug . '" style="background-color: ' . $topic_color . '">'
										                        . $topic->name .
										                    '</span>' .
										                '</a> ';
										        }
									        }

									        // Views
									        if($wordpress_helpdesk_options['FAQShowViews'] === "1") {
										        $count = get_post_meta($post->ID, 'faq_popularity', true);
										        echo ' <span class="wordpress-helpdesk-viewed label" style="background-color: #03A9F4">' . sprintf(__('Viewed: %s', 'wordpress-helpdesk'), $count) . '</span>';
									        }
									        
									        // Rating System
									        if($wordpress_helpdesk_options['FAQRatingEnable'] === "1") {

									        	echo '<div class="wordpress-helpdesk-faq-rating">';

										        		echo '<a class="wordpress-helpdesk-faq-rating-like" data-post_id="' . $post->ID . '" href="#">';
										        			echo '<i class="fa fa-thumbs-up"></i> <span id="wordpress-helpdesk-faq-rating-like-count">' . $likes . '</span>';
										        		echo '</a>';
													
										        	if($wordpress_helpdesk_options['FAQRatingDisableDislikeButton'] === "0") {

										        		$dislikes = get_post_meta($post->ID, 'faq_dislikes', true);
										        		if(!$dislikes) {
										        			$dislikes = 0;
										        		}
									        			echo '<a class="wordpress-helpdesk-faq-rating-dislike" data-post_id="' . $post->ID . '" href="#">';
									        				echo '<i class="fa fa-thumbs-down"></i> <span id="wordpress-helpdesk-faq-rating-dislike-count">' . $dislikes . '</span>';
									        			echo '</a>';
								        			}
							        			echo '</div>';
									        }
									        ?>
								            </div>
										</div>
									</div>
							        <div class="wordpress-helpdesk-row">
										<div class="wordpress-helpdesk-col-sm-12">
											<div class="entry">
							            		<?php the_content(); ?>
							            	</div>
						            	</div>
							        </div>

									<div class="wordpress-helpdesk-row">
										<div class="wordpress-helpdesk-col-sm-12">
											<div class="wordpress-helpdesk-comments">
						            			<?php comments_template(); ?>
						            		</div>
						            	</div>
							        </div>
					        	<?php } else {
					        		esc_html_e('You do not have sufficient access rights to see this faq.', 'wordpress-helpdesk');
					        	} ?>
						    </div>
					    <?php endwhile; endif; ?>
					</div>
					<?php
					$checks = array('both', 'only_faq');
					if(in_array($wordpress_helpdesk_options['supportSidebarDisplay'], $checks)) {
					?>
					<div class="wordpress-helpdesk-col-sm-4 wordpress-helpdesk-sidebar <?php echo $sidebarClass ?>">
						<?php dynamic_sidebar('helpdesk-sidebar'); ?>
					</div>
					<?php
					}
					?>
				</div>
			</div>
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