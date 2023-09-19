<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package eve
 */

get_header();

$bundleManager = new DFPBManager();
$presorted_posts_ids = get_presorted_posts_ids(['feature-bundle-pre-sort', 'feature-pre-sort']);
$inactive_jobs = $bundleManager->get_all_inactive_job_ids();

/*if (!empty($presorted_posts_ids)) {
    $presorted_posts_query = handle_job_list_search([
        'post__in' => $presorted_posts_ids,
        'posts_per_page' => 3,
        'orderby' => 'rand',
    ]);
}*/

//$presorted_queried_posts = [];
$default_posts_per_page = get_option( 'posts_per_page' );

/*if (!empty($presorted_posts_query->post_count)) {
    $default_posts_per_page -= $presorted_posts_query->post_count;
    $presorted_queried_posts = wp_list_pluck( $presorted_posts_query->posts, 'ID' );
}*/

$negative_query = null;

/*if (is_array($presorted_queried_posts) && is_array($inactive_jobs)) {
    $negative_query = array_merge($presorted_queried_posts, $inactive_jobs);
} else if (is_array($presorted_queried_posts)) {
    $negative_query = $presorted_queried_posts;
} else */if (is_array($inactive_jobs)) {
    $negative_query = $inactive_jobs;
}

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$bundleUtils = new DFPBUtilities();

$job_list_query = $bundleUtils->handle_job_search($_GET, [
    'posts_per_page' => $default_posts_per_page,
    'paged' => $paged,
    'post__not_in' => array_filter($negative_query),
]);

$query_posts_per_page = $job_list_query->post_count;
$featured_position = -1;

if( $query_posts_per_page >= $default_posts_per_page ) {
    $featured_position = $default_posts_per_page / 2;
}

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_index_page_search_filter                 - 5
 * @hooked eve_page_sidebar_content_wrappers_start      - 10
 * @hooked eve_page_sidebar_content_main_start          - 20
 */

?>

<form class="search-field__form" role="search" method="get" action="<?php echo home_url( '/jobs/' ); ?>">

<?php do_action('page_sidebar_before_content'); ?>

<main>
    <?php
    if ( !$job_list_query->have_posts() /*&& !$presorted_posts_query->have_posts()*/ ) {
        if (falke_is_doing_search()) {
            get_template_part('template-parts/content', 'job-nomatch');
        } else {
            get_template_part('template-parts/content', 'job-none');
        }
    }
    ?>
	<div class="job-list__items featured">
        <div class="job-list__items">
        <?php
        
        /* if ( !empty($presorted_posts_query) && $presorted_posts_query->have_posts() ) :
            echo '<div class="job-list__items">';

            while ( $presorted_posts_query->have_posts() ) :
                $presorted_posts_query->the_post();
                get_template_part( 'template-parts/content', 'job-loop' );
            endwhile;
            wp_reset_query();
            echo '</div>';
        endif; */

        $presorteds_output = [];

        while ($job_list_query->have_posts()) {
            $job_list_query->the_post();

            if (count($presorteds_output) <= 3 && is_array($presorted_posts_ids) && in_array(get_the_ID(), $presorted_posts_ids)) {
                get_template_part('template-parts/content', 'job-loop');
                $presorteds_output[] = get_the_ID();
            }
        }

        wp_reset_query();
        ?>
        </div>
	</div>

    <div class="job-list__items">
    <?php
        $i = 0;

        while ( $job_list_query->have_posts() ) {
            $job_list_query->the_post();

            if (in_array(get_the_ID(), $presorteds_output)) {
                continue;
            }

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
        }

        eve_get_featured_slider(__('Featured','dasfalke-job'));
        wp_reset_query();
        $pagination_array = falke_pagination($paged, $job_list_query->max_num_pages, 'array', __('Previous page'), __('Next page'));

        if (!empty($pagination_array)) {
            $last_elem = $pagination_array[count($pagination_array) - 1];
            ?>
            <nav class="navigation posts-navigation" role="navigation">
                <div class="nav-links">
                    <?php if (!empty($pagination_array) && is_array($pagination_array)) : ?>
                        <?php if (strpos($pagination_array[0], 'prev') !== false) : ?>
                            <div class="nav-previous">
                                <?php echo $pagination_array[0]; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (strpos($last_elem, 'next') !== false) : ?>
                            <div class="nav-next">
                                <?php echo $last_elem; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </nav>
            <?php
        }
	?>
    </div>
</main>

<?php 

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_page_sidebar_content_main_end      - 10
 * @hooked eve_page_sidebar_aside_wrp_start       - 20
 */

do_action('page_sidebar_between_content'); ?>

<aside data-slideout-ignore>
  <?php 

  /**
   * Before widget location hook
   */

  do_action('single_sidebar_before_widget');


  /**
   * Before widget location hook
   */

  get_sidebar('single');


  /**
   * After widget location hook
   */

  do_action('single_sidebar_after_widget'); ?>
</aside>

<?php 

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_page_sidebar_aside_wrp_end             - 10
 * @hooked eve_page_sidebar_content_wrappers_end      - 20
 */

do_action('page_sidebar_after_content'); ?>

</form>

<?php

get_footer();
