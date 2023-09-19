<?php

/* Template Name: Falke Full Width */

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
<div class="ctn auto">
	<div class="content-area">
		<div class="content-main">
			<main><?php
				while ( have_posts() ) {
                    the_post();
                    get_template_part('template-parts/content', 'page');
                }
                wp_reset_query();
			?></main>
		</div>
	</div>
</div>
<?php

get_footer();
