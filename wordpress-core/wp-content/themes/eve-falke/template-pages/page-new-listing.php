<?php

/* Template Name: Falke New Listing */

/**
 * The template for displaying the new-listing page
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package eve
 */

get_header();
?>
<main>
    <div>
        <form>
            <?php get_template_part('template-parts/content', 'admin-meta-boxes'); ?>
        </form>
    </div>
</main>

<?php get_footer(); ?>
