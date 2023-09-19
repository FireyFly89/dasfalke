<?php

/* Template Name: Falke Sidebar Template */

/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package eve
 */

get_header();

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_page_sidebar_content_wrappers_start      - 10
 * @hooked eve_page_sidebar_content_main_start          - 20
 */

do_action('page_sidebar_before_content'); ?>

<main>
  <?php
    while ( have_posts() ) {
        the_post();
        get_template_part('template-parts/content', 'page');
    }

    wp_reset_query();
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

  do_action('page_sidebar_before_widget');


  /**
   * Before widget location hook
   */

  get_sidebar();


  /**
   * After widget location hook
   */

  do_action('page_sidebar_after_widget'); ?>
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
