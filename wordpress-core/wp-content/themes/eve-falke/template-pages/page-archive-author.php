<?php

/* Template Name: Falke Company List */

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

    $textdomain = 'dasfalke-company-list';

    $current_page = get_query_var('paged') ? (int) get_query_var('paged') : 1;
    $users_per_page = get_option( 'posts_per_page' );
    $user_args = array( 
      'role'    => EMPLOYER_ROLE,
      'number'  => $users_per_page,
      'paged'   => $current_page
    );

    $user_query = new WP_User_Query( $user_args );
    $total_users = (int) $user_query->get_total();
    $num_pages = ceil( $total_users / $users_per_page ); 

    if ( ! empty( $user_query->get_results() ) ) {

      ?>
      <h1 class="company-list__title"><?php _e( 'Companies', $textdomain ); ?></h1>
      <p class="company-list__desc"><?php echo sprintf( __('Displaying %s of %s users', $textdomain), $users_per_page, $total_users ); ?></p>
      
      <div class="company-list__items"><?php

      foreach ( $user_query->get_results() as $user ) {

        set_query_var( 'author_id', absint( $user->ID ) );

        get_template_part( 'template-parts/content', 'archive-author-loop' );
      }

      ?></div>
      <nav class="navigation posts-navigation" role="navigation">
        <div class="nav-links">
          <?php if ( $current_page < $num_pages ): ?>
            <div class="nav-next"><?php echo '<a href="'. add_query_arg(array('paged' => $current_page+1)) .'">'. __( 'Next Page', $textdomain ).'</a>'; ?></div>
          <?php endif; ?>

          <?php if ( $current_page > 1 ): ?>
            <div class="nav-previous"><?php echo '<a href="'. add_query_arg(array('paged' => $current_page-1)) .'">'. __( 'Previous Page', $textdomain ).'</a>'; ?></div>
          <?php endif; ?>
        </div>
      </nav><?php

    } else {

      ?><h1>No users found.</h1><?php  

    }

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

  do_action('company_sidebar_before_widget');


  /**
   * Before widget location hook
   */

  get_sidebar('company');


  /**
   * After widget location hook
   */

  do_action('company_sidebar_after_widget'); ?>
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
