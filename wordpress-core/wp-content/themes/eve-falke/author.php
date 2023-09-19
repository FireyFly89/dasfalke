<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package eve
 */

$author_id = get_queried_object_id();
$author_role = null;

if (!empty($author_id)) {
    $author_role = eve_get_user_by_id($author_id)->roles[0];
}

redirect_to_if_not_eligible($author_role, null, 'employer');

get_header();

global $wp_query;
$query_posts_per_page = $wp_query->post_count;
$default_posts_per_page = get_option( 'posts_per_page' );
$featured_position = -1;

if( $query_posts_per_page >= $default_posts_per_page ) {
    $featured_position = $default_posts_per_page / 2;
}

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_author_page_top                          - 5
 * @hooked eve_page_sidebar_content_wrappers_start      - 10
 * @hooked eve_page_sidebar_content_main_start          - 20
 */

do_action('page_sidebar_before_content'); ?>

    <main>

        <div class="author-content__bg">
            <?php if ($whoweare = get_user_data( 'company_whoweare', $author_id )) : ?>
                <h2 class="author-content__title"><?php _e('Who we are', 'dasfalke-author'); ?></h2>
                <div class="author-content__wrp">
                    <div><?php echo $whoweare; ?></div>
                </div>
                <br>
            <?php endif; ?>

            <?php if ($whatweoffer = get_user_data( 'company_whatweoffer', $author_id )) : ?>
                <h2 class="author-content__title"><?php _e('What we offer', 'dasfalke-author'); ?></h2>
                <div class="author-content__wrp">
                    <div><?php echo $whatweoffer; ?></div>
                </div>
                <br>
            <?php endif; ?>

            <?php if ($ourexpectations = get_user_data( 'company_ourexpectation', $author_id )) : ?>
                <h2 class="author-content__title"><?php _e('Our expectations', 'dasfalke-author'); ?></h2>
                <div class="author-content__wrp">
                    <div><?php echo $ourexpectations; ?></div>
                </div>
            <?php endif; ?>
        </div>

        <?php

        if ( have_posts() ) :

            echo '<h2 class="author-top__title">'. __('Job openings at', 'dasfalke-author') .' '. get_user_name($author_id) .'</h2>';
            echo '<div class="job-list__items">';

            $i = 0;

            while ( have_posts() ) :
                the_post();

                /*
                 * Include the Post-Type-specific template for the content.
                 * If you want to override this in a child theme, then include a file
                 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
                 */
                get_template_part( 'template-parts/content', 'job-loop' );

                if( $i == $featured_position ){
                    eve_get_featured_slider( __('Featured','dasfalke-job') );
                }

                $i++;

            endwhile;

            echo '</div>';

            ?><div><?php eve_get_featured_slider( __('Featured','dasfalke-job') ); ?></div><?php

            the_posts_navigation();
            wp_reset_query();

        else :

            echo '<h2 class="author-top__title">'. __('No job openings at', 'dasfalke-author') .' '. get_user_name($author_id) .'</h2>';

        endif;
        ?>
    </main>

<?php 

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_page_sidebar_content_main_end      - 10
 * @hooked eve_page_sidebar_aside_wrp_start       - 20
 */

do_action('page_sidebar_between_content'); ?>

<aside>
  <?php

  /**
   * Before widget location hook
   */

  do_action('author_sidebar_before_widget');


  /**
   * Before widget location hook
   */

  get_sidebar('author');


  /**
   * After widget location hook
   */

  do_action('author_sidebar_after_widget'); ?>
</aside>

<?php 

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_page_sidebar_aside_wrp_end             - 10
 * @hooked eve_page_sidebar_content_wrappers_end      - 20
 */

do_action('page_sidebar_after_content'); ?>

<?php

get_footer();
