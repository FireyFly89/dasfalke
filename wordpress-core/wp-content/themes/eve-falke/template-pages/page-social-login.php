<?php

/* Template Name: Falke Account Type */

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

$textdomain = 'dasfalke-login';

?>

<div class="ctn narrow">
  <div class="content-area">
    <div class="content-main">
      <main><?php
        _e( 'Choose wisely, fucker.', $textdomain );
			?></main>
    </div>
  </div>
</div>

<?php

get_footer();
