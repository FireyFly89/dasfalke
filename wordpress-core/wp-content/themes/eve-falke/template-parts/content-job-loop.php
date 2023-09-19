<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package eve
 */

$job_id = get_the_ID();
$payment_extent = get_post_meta($job_id, 'employment_payment_extent', true);
$bundleManager = new DFPBManager();
$is_highlight = !empty($bundleManager->is_featured_for_job($job_id, ['feature-bundle-highlight', 'feature-highlight', 'feature-highlight-30']));
$extra_post_class = "";

if ($is_highlight && !is_front_page()) {
    $extra_post_class = ' highlighted';
}
?>

<article id="post-<?php echo $job_id; ?>" <?php post_class('job-list__item active-job-list-item' . $extra_post_class); ?>>
	<div class="job-list__header">
		<div class="job-list__header-meta">
			<h3 class="job-list__header-title"><a href="<?php echo get_permalink(); ?>" class="job-list__header-title-uri"><?php echo get_post_meta($job_id, 'job_title', true) ?></a></h3>
			<div class="job-list__header-subtitles">
				<span class="job-list__header-subtitle"><?php _e(get_location_string($job_id), 'dasfalke-profile') ?></span>
				<span class="job-list__header-divider"></span>
				<span class="job-list__header-subtitle"><?php echo (!empty($payment_extent) ? eve_get_formatted_price($payment_extent) . " " . get_salary_text_by_numtype(get_post_meta($job_id, 'employment_payment', true)) : "Not available"); ?></span>
			</div>
		</div>
		<div class="job-list__header-avatar"><img class="job-list__header-avatar-img" src="<?php echo get_user_avatar(get_the_author_meta('ID')) ?>" alt=""></div>
	</div>
	<div class="job-list__content">
		<p><?php 
		$job_content = strip_tags( get_post_meta($job_id, 'job_description', true) );
		$job_content = substr($job_content, 0, 160) .'...'; 
		
		echo $job_content; ?></p>
	</div>
	<div class="footer">
		<span><?php echo $bundleManager->time_ago(get_the_time('U')); ?></span>
		<a href="<?php echo get_permalink(); ?>"><?php _e( 'View', 'dasfalke-profile'); ?></a>
	</div>
</article>
