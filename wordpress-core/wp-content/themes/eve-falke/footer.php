<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package eve
 */

?>

</div>
<footer class="site-footer" data-slideout-ignore>
	<div class="ctn max">
		<div class="site-footer-top">
			<div class="brand">
				<a class="brand-uri fr" href="<?php echo apply_filters( 'wpml_home_url', get_option( 'home' ) ); ?>"><svg class="i i-logo brand-img" width="116.217" height="37.348" title="<?php get_bloginfo('name') ?>"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-logo" href="#i-logo"></use></svg></a>
			</div>
			<div class="footer__social-links">
				<?php eve_get_social_links(); ?>				
			</div>
		</div>
	</div>
	<div class="site-footer-content">
		<div class="ctn max">
			<!-- <div class="site-footer-links">
				<?php eve_get_social_links(); ?>
			</div>
			<br> -->
			<div class="site-footer-columns">
				<div class="site-footer-col nav">
					<!-- <h3>More about us</h3> -->
					<?php
					wp_nav_menu( array(
						'theme_location' => 'footer-menu-1',
						'menu_id'        => 'footer-menu-1',
						'container'      => false
					) );
					?>
				</div>
				<div class="site-footer-col info">
					<!-- TODO: Nyelv metodus link csere... -->
					<a class="df-btn primary block" href="/kontakt"><?php _e('Support and Contact', 'dasfalke-footer'); ?></a>
				</div>
			</div>
			<div class="site-footer-bottom">
				&copy; <?php echo date('Y'); ?> <?php _e( 'Das Falke Personal Limited, all rights reserved.', 'dasfalke-footer' ); ?>
			</div>
		</div>
	</div>
</footer>
</div>
</div>
<?php wp_footer(); ?>
</body>
</html>