<?php

/* Template Name: Coming soon */

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

?>
<style type="text/css">@media (min-width:1000px){#desktop_video{display:block}#mobile_video{display:none}}@media (max-width:999px){#desktop_video{display:none}#mobile_video{display:block}}</style>
<div class="ctn auto">
	<div class="content-area">
		<div class="content-main">
			<main>
				<div id="desktop_video">
			        <video autoplay loop id="splash-video" style="position:fixed;top:0;left:0;width:100%;height:56.25vw;">
                        <source src="/wp-content/uploads/2019/04/Swish-DE95D4C6-D5A0-4976-B8F0-BDEEDD335322.mov" type="video/mp4">
                    </video>
				</div>
				<div id="mobile_video">
			        <video autoplay loop id="splash-video" style="position:fixed;top:0;left:0;width:100%;height:177.78vw;">
                        <source src="/wp-content/uploads/2019/04/Swish-C48C0500-868E-4937-A3C7-929CEDB0E522.mov" type="video/mp4">
                    </video>
				</div>
			</main>
		</div>
	</div>
</div>
<?php
