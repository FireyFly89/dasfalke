<?php

  $textdomain = 'dasfalke-company-list';
  $author_page_url = get_author_posts_url( $author_id );
  $profile_image_src = get_user_avatar( $author_id );
  $company_name = get_user_data( 'billing_company', $author_id );
  $bundleManager = new DFPBManager();
  $active_jobs = $bundleManager->get_all_active_job_ids_for_users([$author_id]);
  $active_jobs_count = !empty($active_jobs) ? count($active_jobs) : 0;
?>
<article class="company-list__item">
  <div class="row">
    <div class="col-sm-6">
      <div class="company-list__item-details">
        <a class="company-list__item-title" href="<?php echo $author_page_url; ?>"><?php echo $company_name; ?></a>
        <div class="company-list__item-metas">
          <div class="company-list__item-meta"><?php _e( 'Headquarters', $textdomain ); ?>:&nbsp;<span><?php _e(get_user_data('billing_city', $author_id), $textdomain); ?></span></div>
          <div class="company-list__item-meta"><?php _e( 'Current jobs', $textdomain ); ?>:&nbsp;<span><?php echo $active_jobs_count; ?></span></div>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <a href="<?php echo $author_page_url; ?>">
        <div class="company-list__item-img-wrp">
          <img class="company-list__item-img" src="<?php echo $profile_image_src; ?>">
        </div>
      </a>
    </div>
  </div>
</article>
