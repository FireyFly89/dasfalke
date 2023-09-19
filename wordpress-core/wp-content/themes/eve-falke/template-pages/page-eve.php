<?php

/* Template Name: Falke Homepage */

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
?>

<div class="hhero">
  <div class="hhero__content">
    <div class="ctn max">
      <div><h1 class="hhero__title"><?php _e('We connect people and companies', 'dasfalke-home') ;?></h1></div>
      <div><div class="hhero__searchbar"><?php eve_main_search_bar( 'home-page' ); ?></div></div>
    </div>
  </div>
  <div class="hhero__bg"><?php eve_get_section_bg( get_the_ID() ); ?> </div>
</div>

<div class="hhero__content">
  <section class="section-hero">
    <div class="ctn max">
      <div class="hero-header right">
        <h2><?php _e('Find your perfect job! The simple and better way of looking for a job.', 'dasfalke-home') ;?></h2>
        <p><?php _e('Are you looking for a new job? The career portal shows you which jobs are currently advertised in Austria in your field of activity', 'dasfalke-home') ;?></p>
        <div class="btn-wrap">
            <a href="<?php echo get_page_url_by_slug('register'); ?>" class="df-btn secondary small"><?php _e('Join us now', 'dasfalke-home') ;?></a>
        </div>
      </div>
      <div class="hero-content">
        <div class="col-left">
          <div class="col-left__psn">
            <h2><?php _e('You want an employee as quickly as possible?', 'dasfalke-home') ;?></h2>
            <p><?php _e('Finding the adequate employee is the special challenge of the New Economy. Working is everywhere a key driver of success for companies. Whether it is to attract the best talent, to temporarily build project teams or to increase the work-life integration. With our job ads, there is a solution to make these points even easier.', 'dasfalke-home') ;?></p>
            <div class="btn-wrap">
                <a href="<?php echo get_page_url_by_slug('pricing'); ?>" class="df-btn primary small"><?php _e('Buy an advertisement package', 'dasfalke-home') ;?></a>
                <a href="<?php echo get_page_url_by_slug('register'); ?>" class="df-btn secondary small"><?php _e('Join us now', 'dasfalke-home') ;?></a>
            </div>
          </div>
        </div>
        <div class="col-right"><?php eve_get_featured_slider(__('Featured company', 'dasfalke-home')); ?></div>
      </div>
    </div>
  </section>
</div>

<?php

if( !empty($home_featured = eve_get_homepage_featured() )):
  
  ?><div class="hhero__featured">
    <div class="ctn max">
      <h3 class="hhero__featured-title"><?php _e('Featured positions', 'dasfalke-home'); ?></h3>
      <div class="row"><?php

      while ($home_featured->have_posts()) {
          $home_featured->the_post();

          echo '<div class="col-sm-6">';
          get_template_part('template-parts/content', 'job-loop');
          echo '</div>';

          if ((($home_featured->current_post + 1) % 2) === 0) {
              echo '</div><div class="row">';
          }
      }

      wp_reset_query();

  ?></div></div></div><?php
endif;


get_footer();
