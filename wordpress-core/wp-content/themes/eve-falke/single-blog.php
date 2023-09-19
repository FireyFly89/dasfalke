<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package eve
 */

get_header();

$has_post_thumbnail = has_post_thumbnail( get_the_ID() );
?>

<div class="page-hero<?php if( $has_post_thumbnail ){ echo ' has-post-thumbnail'; } ?>">
	<div class="page-hero__content">
		<div class="ctn blog">
			<header class="entry-header">
				<?php 
				$subtitle = eve_get_field( 'eve_page_subtitle', get_the_ID() );
				if( $subtitle ):
					printf( '<div class="page-hero__subtitle">%s</div>', $subtitle );
				endif; ?>
				<?php the_title( '<h1 class="page-hero__title">', '</h1>' ); ?>
				<div><?php the_breadcrumb(); ?></div>
			</header>
		</div>
	</div>
	<div class="page-hero__bg">
		<?php if( $has_post_thumbnail ): ?>
				<div class="page-hero__bg-vignette"></div>
				<?php if( wp_is_mobile() ): ?>
				<div class="page-hero__bg-img mobile loadlzly" style="background-image: url('<?php echo get_the_post_thumbnail_url( get_the_ID(), 'one-tree-page-banner-thumb' ); ?>');" data-src="<?php echo get_the_post_thumbnail_url( get_the_ID(), 'one-tree-page-banner-mobile-full' ); ?>"></div>
				<?php else: ?>
				<div class="page-hero__bg-img desktop loadlzly" style="background-image: url('<?php echo get_the_post_thumbnail_url( get_the_ID(), 'one-tree-page-banner-thumb' ); ?>');" data-src="<?php echo get_the_post_thumbnail_url( get_the_ID(), 'one-tree-page-banner-desktop-full' ); ?>"></div>
				<?php endif; ?>
		<?php else: ?>
			<div class="page-hero__bg-pattern"></div>
		<?php endif; ?>
	</div>
</div>
<div class="ctn blog">
	<div class="content-area">
		<div class="content-main">
			<main><?php
				while ( have_posts() ) { the_post();
          get_template_part('template-parts/content', 'page-simple');
       	}
				wp_reset_query();
			?></main>
		</div>
	</div>
</div>

<?php

get_footer();
