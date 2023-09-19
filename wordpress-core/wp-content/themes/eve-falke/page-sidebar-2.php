<?php

/* Template Name: Falke Sidebar Template 2 */

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

<?php 

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_page_sidebar_content_wrappers_start      - 10
 * @hooked eve_page_sidebar_content_main_start          - 20
 */

do_action('page_sidebar_before_content'); ?>

<main>
  <!-- Main: <?php echo eve_get_current_page_id(); ?> -->
  <div class="search-field absolute">
    <form action="/adasd">
      <div class="location">
        <span class="icon pin">
          <img src="<?php echo get_template_directory_uri(); ?>/img/pin.svg" class="svg">
        </span>
        <input type="text" placeholder="City" name="city">
        <span class="icon loc">
          <img src="<?php echo get_template_directory_uri(); ?>/img/location.svg" class="svg">
        </span>
      </div>
      <div class="text">
        <input type="text" placeholder="e.g. Design, Cleaner" name="keyword">
        <span class="icon search">
          <img src="<?php echo get_template_directory_uri(); ?>/img/search.svg" class="svg">
        </span>
      </div>
      <input type="submit" value="Keresés">
    </form>
  </div>
  <div class="form-row" style="margin-top: 200px">
    <div class="styled-radio">
      <input type="radio" name="test" id="test">
      <label for="test">Test</label>
    </div>
    <div class="styled-radio">
      <input type="radio" name="test" id="test2">
      <label for="test2">Test2</label>
    </div>
  </div>
  <div class="form-row">
    <input type="text" class="small" placeholder="Small input">
  </div>
  <div class="form-row">
    <textarea name="test_tinyMCE" id="" cols="30" rows="10" placeholder="Test TinyMCE"></textarea>
  </div>
  <div class="form-row">
    <input type="submit" class="df-btn secondary">
  </div>
  <div class="form-row">
    <input type="submit" class="df-btn alt">
  </div>
  <div class="form-row">
    <div class="size-input">
      <a href="#" class="minus">
        <img src="<?php echo get_template_directory_uri(); ?>/img/minus.svg" class="svg">
      </a>
      <input type="text" value="1">
      <button class="plus">
        <img src="<?php echo get_template_directory_uri(); ?>/img/plus.svg" class="svg">
      </button>
    </div>
  </div>
  <div class="form-row">
    <div class="styled-checkbox">
      <input type="checkbox" name="test" id="test_cb">
      <label for="test_cb">
        <span class="checker"></span>
      </label>
    </div>
  </div>
  <div class="alert-message success">
    <h2>
      <img src="<?php echo get_template_directory_uri(); ?>/img/checkmark.svg" class="svg">
      Success message
    </h2>
    <p>Lórum ipse mint pajlan rodorsos kemendő, elsősorban egy tarc faradás. Ezt úgy kell kóznia.</p>
  </div>
  <div class="alert-message error">
    <h2>
      <img src="<?php echo get_template_directory_uri(); ?>/img/cross.svg" class="svg">
      Error message
    </h2>
    <p>Lórum ipse mint pajlan rodorsos kemendő, elsősorban egy tarc faradás. Ezt úgy kell kóznia.</p>
  </div>
  <div class="alert-message warning">
    <h2>
      <img src="<?php echo get_template_directory_uri(); ?>/img/warning.svg" class="svg">
      Warning message
    </h2>
    <p>Lórum ipse mint pajlan rodorsos kemendő, elsősorban egy tarc faradás. Ezt úgy kell kóznia.</p>
  </div>
</main>

<?php 

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_page_sidebar_content_main_end      - 10
 */

do_action('page_sidebar_between_content'); ?>

<div class="content-aside">
  <div class="content-aside-inner">
    <aside>
      <div class="add-job-list">
        <a href="#">&larr; BACK TO PROFILE</a>
        <h4>JOB DETAILS</h4>
        <div class="form-row">
          <div class="styled-select">
            <label for="job_location">Job location</label>
            <select name="job_location" id="job_location">
              <option value="0">Please select</option>
            </select>
            <span class="arrow">
              <img src="<?php echo get_template_directory_uri(); ?>/img/arrow.svg" class="svg">
            </span>
          </div>
        </div>
      </div>
      <div class="reference-code">
        <h5>Reference code</h5>
        <div class="form-row">
          <input type="text" placeholder="Please enter job reference code">
        </div>
        <a href="#">GENERATE RANDOM NUMBER</a>
      </div>
    </aside>
  </div>
</div>

<?php 

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_page_sidebar_content_wrappers_end      - 10
 */

do_action('page_sidebar_after_content'); ?>

<?php

get_footer();
