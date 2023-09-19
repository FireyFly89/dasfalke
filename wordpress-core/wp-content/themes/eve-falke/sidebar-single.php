<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package eve
 */

if ( !is_active_sidebar( 'sidebar-single' ) ) {
	return;
}
?>
<aside class="widget-area">
	<?php dynamic_sidebar( 'sidebar-single' ); ?>
</aside>