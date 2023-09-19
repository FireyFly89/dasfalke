<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package eve
 */

?>
<div class="article__column">
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'article__col' ); ?>>
		<a href="<?php echo get_permalink(); ?>">
			<div class="entry-thumbnail"><?php 

				if( get_the_post_thumbnail_url() ): ?>
					<img src="<?php echo get_the_post_thumbnail_url( get_the_ID(), 'blog-thumbnail' ) ?>" alt="<?php the_title() ?>">
				<?php 
				endif;
				
			?></div>
		</a>
		<header class="entry-header">
			<a href="<?php echo get_permalink(); ?>">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</a>
		</header>
		<div class="entry-content"><?php echo get_the_excerpt(); ?></div>
		<div class="entry-action">
			<span class="entry-date"><?php echo get_the_date(); ?></span>
			<a class="entry-read-more" href="<?php echo get_permalink(); ?>"><?php _e( 'Read more', 'dasfalke-blog' ); ?></a>
		</div>
	</article>
</div>