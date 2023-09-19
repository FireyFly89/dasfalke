<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package eve
 */

get_header();
?>
<div class="ctn max">
	<div class="content-area">
		<main class="site-main">
		<?php if ( have_posts() ) { ?>

			<header class="page-header">
				<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
                <?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
			</header><!-- .page-header -->

			<?php
			while ( have_posts() ) {
				the_post();

				/*
				 * Include the Post-Type-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
				 */
				get_template_part( 'template-parts/content', get_post_type() );
            }

            wp_reset_query();
			the_posts_navigation();

        } else {
			get_template_part( 'template-parts/content', 'none' );
        }
		?>

		</main><!-- #main -->
	</div><!-- #primary -->
</div>
<?php
get_footer();
