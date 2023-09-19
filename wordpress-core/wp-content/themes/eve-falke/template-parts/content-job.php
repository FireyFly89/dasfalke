<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package eve
 */
$permalink = apply_filters( 'wpml_permalink', get_the_permalink());
$post_id = get_the_ID();
$bundleManager = new DFPBManager();
$profession_name = "";
$apply_type = get_post_meta($post_id, 'apply_type', true);
$show_apply_button = null;
$domain_name = 'dasfalke-jobpage';

if (eve_is_user_employer() === false) {
    $show_apply_button = true;

    if (eve_is_user_jobseeker()) {
        $user = eve_get_user();
        $show_apply_button = get_job_application_eligibility($user->ID, $post_id);
    }

    if (isset($_POST['send_job_application'])) {
        apply_to_job();
    }
}

if (!empty($profession = get_post_meta($post_id, 'selected_professions', true))) {
    $profession_term = get_term($profession);

    if (is_object($profession_term) && property_exists($profession_term, 'name')) {
        $profession_name = get_term($profession)->name;
    }
}

$apply_site = "";

if ($apply_type === 'site') {
    $apply_site = get_post_meta($post_id, 'apply_type_site_field', true);

    if (strpos($apply_site, 'http') === false) {
        $apply_site = "http://" . $apply_site;
    }
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('job-page'); ?>>
	<div class="job-page__header">
		<div class="row">
			<div class="col-sm-8"><h1 class="job-page__title"><?php the_title(); ?></h1></div>
            <?php if (!empty($show_apply_button)) : ?>
                <div class="col-sm-4 only-desktop">
                    <?php if ($apply_type === 'site') : ?>
                        <div><a data-post-id="<?php echo $post_id; ?>" class="df-btn yellow small" href="<?php echo $apply_site; ?>"><?php _e( 'Apply now', $domain_name ); ?></a></div>
                    <?php else : ?>
                        <div><a data-post-id="<?php echo $post_id; ?>" class="df-btn yellow small start-job-apply" href="/jobapply/<?php echo $post_id; ?>"><?php _e( 'Apply now', $domain_name ); ?></a></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
		</div>
	</div>
	<div class="job-page__metas">
		<div class="job-page__meta"><?php echo get_location_string($post_id); ?></div>
		<div class="job-page__meta-sep">·</div>
		<div class="job-page__meta"><?php echo $bundleManager->time_ago(get_post_time('U')); ?></div>
	</div>

    <div class="job-page__salary">
        <div><?php echo eve_get_formatted_price(get_post_meta($post_id, 'employment_payment_extent', true)); ?> <?php echo get_salary_text_by_numtype(get_post_meta($post_id, 'employment_payment', true)); ?></div>
        <?php if ( !empty( get_post_meta($post_id, 'employment_payment_doe', true) ) ) {
            ?><div class="job-page__salary-doe"><?php _e('Willingness to overpay depending on qualification and work experience.', $domain_name); ?></div>
        <?php } ?>
    </div>
	<hr class="job-page__sep">
	<div class="job-page__content-psn">
        <div><strong><?php _e('Job description', $domain_name); ?></strong></div>
		<div class="job-page__content"><?php the_content(); ?></div>
	</div>
	<?php if ( !empty( $emptasks = get_post_meta($post_id, 'employment_tasks', true))) { ?>
		<div class="job-page__content-psn">
			<div><strong><?php _e('Your tasks', $domain_name); ?></strong></div>
			<div class="job-page__content"><?php echo $emptasks; ?></div>
		</div>
	<?php } ?>
		<?php if ( !empty( $requirements = get_post_meta($post_id, 'employment_requirements', true))) { ?>
		<div class="job-page__content-psn">
			<div><strong><?php _e('Requirements', $domain_name); ?></strong></div>
			<div class="job-page__content"><?php echo $requirements; ?></div>
		</div>
	<?php } ?>
	<?php if ( !empty( $empadvantage = get_post_meta($post_id, 'employment_advantage', true))) { ?>
		<div class="job-page__content-psn">
			<div><strong><?php _e('Advantage to have', $domain_name); ?></strong></div>
			<div class="job-page__content"><?php echo $empadvantage; ?></div>
		</div>
	<?php } ?>
	<hr class="job-page__sep">
	<div class="job-page__tags">

<?php $employment_type = get_post_meta($post_id, 'employment_type', true);
if( $employment_type ): ?>
    <div class="job-page__tag"><div class="page__tag-label"><?php _e('Employment type', $domain_name); ?>: </div><?php _e($employment_type, $domain_name); ?></div>
<?php endif; ?>

<?php 
$employment_nature = get_post_meta($post_id, 'employment_nature', true);
if( $employment_nature ): ?>
    <div class="job-page__tag"><div class="page__tag-label"><?php _e('Employment nature', $domain_name); ?>: </div><?php _e($employment_nature, $domain_name); ?></div>
<?php endif; ?>

<?php 
$employment_education = get_post_meta($post_id, 'employment_education', true);
if( $employment_education ): ?>
    <div class="job-page__tag"><div class="page__tag-label"><?php _e('Education', $domain_name); ?>: </div><?php _e($employment_education, $domain_name); ?></div>
<?php endif; ?>

<div class="job-page__tag"><div class="page__tag-label"><?php _e('Category', $domain_name); ?>: </div><?php echo $profession_name; ?></div>

	</div>
	<hr class="job-page__sep">
	<div class="job-page__apply">
        <?php if (!empty($show_apply_button)) : ?>
            <?php if ($apply_type === 'site') : ?>
                <div><a data-post-id="<?php echo $post_id; ?>" class="df-btn yellow small" href="<?php echo $apply_site; ?>"><?php _e( 'Apply now', $domain_name ); ?></a></div>
            <?php else : ?>
                <div><a data-post-id="<?php echo $post_id; ?>" class="df-btn yellow small start-job-apply" href="/jobapply/<?php echo $post_id; ?>"><?php _e( 'Apply now', $domain_name ); ?></a></div>
            <?php endif; ?>
        <?php elseif ($show_apply_button !== null) : ?>
            <div><?php _e("You've already applied for this job", $domain_name); ?></div>
        <?php endif; ?>

        <?php if ( ! empty($show_apply_button)) : ?>
            <?php if ($apply_type != 'site') : ?>
                <div class="job-page__apply-form-wrp">
                    <form method="post" class="job-page__apply-form job-application-form" style="display: none;" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-sm-6"><?php echo make_simple_text_field('first_name', __('First name', $domain_name), true); ?></div>
                            <div class="col-sm-6"><?php echo make_simple_text_field('last_name', __('Last name', $domain_name), true); ?></div>
                        </div>
                        <?php echo make_simple_text_field('account_email', __('Email address', $domain_name), true, 'email', (!empty($user->user_email) ? $user->user_email : '') ); ?>
                        <div class="form-row textarea">
                            <label class="form-row__label" for="job_description"><?php _e('Message', $domain_name); ?></label>
                            <div class="form-row__input">
                                <?php wp_editor('', "job_application_freetext", [
                                    'textarea_name' => 'job_application_freetext',
                                    'media_buttons' => false,
                                    'textarea_rows' => 8,
                                    'tabindex' => 4,
                                    'quicktags' => false,
                                    'tinymce' => [
                                        'toolbar1' => 'parapgraph,bold,italic,underline,alignleft,aligncenter,alignright,undo,redo,ul,ol,numlist,bullist,outdent,indent,removeformat',
                                        'toolbar2' => '',
                                        'toolbar3' => '',
                                    ],
                                ]); ?>
                            </div>
                        </div>
                        <div>
                            <div class="form-row file">
                                <label class="form-row__label"><?php _e('Attachments', $domain_name); ?></label>
                                <div class="form-row__input">
                                    <input type="file" name="job_application_attachments[]" value="" multiple />
                                </div>
                            </div>
                        </div>
                        <div><button class="df-btn secondary" name="send_job_application"><?php _e("Send application", $domain_name); ?></button></div>
                        <br>
                        <?php wp_nonce_field( 'job-application', 'job_application_nonce' ); ?>
                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
                    </form>
                </div>
            <?php endif; ?>
        <?php else: ?>
        <?php do_action('feature_buy_panel'); ?>
        <?php endif; ?>
	</div>
</article>
