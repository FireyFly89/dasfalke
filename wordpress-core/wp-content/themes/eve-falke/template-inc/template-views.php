<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package eve
 */

if ( ! function_exists( 'eve_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time.
	 */
	function eve_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( DATE_W3C ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			/* translators: %s: post date. */
			esc_html_x( 'Posted on %s', 'post date', 'eve' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		echo '<span class="posted-on">' . $posted_on . '</span>'; // WPCS: XSS OK.

	}
endif;



if ( ! function_exists( 'eve_posted_by' ) ) :
	/**
	 * Prints HTML with meta information for the current author.
	 */
	function eve_posted_by() {
		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( 'by %s', 'post author', 'eve' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.

	}
endif;



if ( ! function_exists( 'eve_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function eve_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'eve' ) );
			if ( $categories_list ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'eve' ) . '</span>', $categories_list ); // WPCS: XSS OK.
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'eve' ) );
			if ( $tags_list ) {
				/* translators: 1: list of tags. */
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'eve' ) . '</span>', $tags_list ); // WPCS: XSS OK.
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'eve' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					get_the_title()
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'eve' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			),
			'<span class="edit-link">',
			'</span>'
		);
	}
endif;



if ( ! function_exists( 'eve_post_thumbnail' ) ) :
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	function eve_post_thumbnail() {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>

			<div class="post-thumbnail">
				<?php the_post_thumbnail(); ?>
			</div>

		<?php else : ?>

		<a class="post-thumbnail" href="<?php the_permalink(); ?>">
			<?php
			the_post_thumbnail( 'post-thumbnail', array(
				'alt' => the_title_attribute( array(
					'echo' => false,
				) ),
			) );
			?>
		</a>

		<?php
		endif; // End is_singular().
	}
endif;



if ( ! function_exists( 'the_breadcrumb' ) ) :
	/**
	 * The Breadcrumb
	 */
	function the_breadcrumb( $sep = '/' )
	{
		global $post;
		$itemprop_position = 1;

		echo '<ul class="breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList">';
		echo '<li itemprop="itemListElement" itemscope
		itemtype="http://schema.org/ListItem"><a itemprop="item" href="'. get_home_url() .'">';
		echo '<span itemprop="name">'. get_bloginfo( 'name' ) .'</span>';
		echo '</a><meta itemprop="position" content="'. $itemprop_position++ .'" /></li>';
		if( !is_home() ):
			echo '<li class="separator"> '. $sep .' </li>';

			if( is_single() ):

				echo '<li itemprop="itemListElement" itemscope
				itemtype="http://schema.org/ListItem"><a itemprop="item" href="'. get_post_type_archive_link( get_post_type() ) .'"><span itemprop="name">';
				echo ucfirst(get_post_type());
				echo '</span></a><meta itemprop="position" content="'. $itemprop_position++ .'" /></li>';

				echo '<li class="separator"> '. $sep .' </li>';

				echo '<li itemprop="itemListElement" itemscope
				itemtype="http://schema.org/ListItem"><a itemprop="item" href="'. get_permalink() .'"><span itemprop="name">';
				the_title();
				echo '</span></a><meta itemprop="position" content="'. $itemprop_position++ .'" /></li>';

			elseif( is_post_type_archive('blog') || is_singular('blog') ):

				echo '<li itemprop="itemListElement" itemscope
				itemtype="http://schema.org/ListItem"><a itemprop="item" href="'. get_post_type_archive_link( 'blog' ) .'"><span itemprop="name">';
				echo 'Blog';
				echo '</span></a><meta itemprop="position" content="'. $itemprop_position++ .'" /></li>';

			elseif( is_archive() && !is_category() && !is_tag() && !is_author() ):
				$archive = get_queried_object();
				echo '<li itemprop="itemListElement" itemscope
				itemtype="http://schema.org/ListItem"><a itemprop="item" href="'. get_post_type_archive_link( $archive->name ) .'"><span itemprop="name">';
				echo $archive->label;
				echo '</span></a><meta itemprop="position" content="'. $itemprop_position++ .'" /></li>';

			elseif( is_category() ):
				$category = get_queried_object();
				echo '<li itemprop="itemListElement" itemscope
				itemtype="http://schema.org/ListItem"><a itemprop="item" href="'. get_category_link( $category->term_id ) .'"><span itemprop="name">';
				echo get_the_category_by_ID( $category->term_id );
				echo '</span></a><meta itemprop="position" content="'. $itemprop_position++ .'" /></li>';

			elseif( is_page() ):
				if( !empty($post->post_parent) ):
					$anc = get_post_ancestors( $post->ID );
					$anc = array_reverse($anc);
					$title = get_the_title();
					foreach ( $anc as $ancestor ):
						echo '<li itemprop="itemListElement" itemscope
						itemtype="http://schema.org/ListItem"><a itemprop="item" href="'. get_permalink($ancestor) .'" title="'. get_the_title($ancestor) .'"><span itemprop="name">'. get_the_title($ancestor) .'</span></a><meta itemprop="position" content="'. $itemprop_position++ .'" /></li><li class="separator"> '. $sep .' </li>';
					endforeach;
					echo '<li itemprop="itemListElement" itemscope
					itemtype="http://schema.org/ListItem"><a itemprop="item" href="'. get_permalink( $post->ID ) .'" title="'. $title .'"><span itemprop="name">'. $title .'</span></a><meta itemprop="position" content="'. $itemprop_position++ .'" /></li>';
				else:
					echo '<li itemprop="itemListElement" itemscope
					itemtype="http://schema.org/ListItem"><a itemprop="item" href="'. get_permalink() .'"> <span itemprop="name">'. get_the_title() .'</span></a><meta itemprop="position" content="'. $itemprop_position++ .'" /></li>';
				endif;

			elseif( is_tag() ):
				$tag = get_queried_object();
				echo '<li itemprop="itemListElement" itemscope
				itemtype="http://schema.org/ListItem"><a itemprop="item" href="'. get_tag_link( $tag->term_id ) .'"><span itemprop="name">';
				echo single_tag_title();
				echo '</span></a><meta itemprop="position" content="'. $itemprop_position++ .'" /></li>';

			elseif( is_search() ):
				echo '<li>'. __( 'Search Results', 'eve' ) .'</li>';

			elseif ( is_day() ):
				echo '<li>'. __( 'Archive for ', 'eve' ); the_time('F jS, Y'); echo '</li>';

			elseif ( is_month() ): 
				echo '<li>'. __( 'Archive for ', 'eve' ); the_time('F, Y'); echo'</li>';

			elseif ( is_year() ): 
				echo '<li>'. __( 'Archive for ', 'eve' ); the_time('Y'); echo'</li>';

			elseif ( is_author() ):
				echo '<li>'. __( 'Author Archive', 'eve' ) .'</li>';

			elseif ( isset($_GET['paged']) && !empty($_GET['paged']) ):
				echo '<li>'. __( 'Blog Archives', 'eve' ) .'</li>';

			endif;

		elseif( is_home() ):
			echo '<li class="separator"> '. $sep .' </li>';
			echo '<li itemprop="itemListElement" itemscope
			itemtype="http://schema.org/ListItem"><a itemprop="item" href="'. get_permalink( get_option( 'page_for_posts' ) ) .'"><span itemprop="name">';
			single_post_title();
			echo '</span></a><meta itemprop="position" content="'. $itemprop_position++ .'" /></li>';

		endif;
		echo '</ul>';
	}
endif;




if ( !function_exists( 'progresseve_gtm_core' ) ) :
	/**
	 * GTM Code
	 */
	function progresseve_gtm_core() { 

    $gtm_ID = get_option('eve_gtm_code');
    if( empty($gtm_ID) ){ return; }

?><script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo $gtm_ID; ?>');</script>
<?php
  }
endif;



if ( !function_exists( 'progresseve_gtm_noscript' ) ) :
	/**
	 * GTM Code
	 */
	function progresseve_gtm_noscript() { 

    $gtm_ID = get_option('eve_gtm_code');
    if( empty($gtm_ID) ){ return; }

?><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $gtm_ID; ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php
  }
endif;



if ( ! function_exists( 'eve_page_sidebar_content_wrappers_start' ) ) :
	/**
	 * Page sidebar wrapper start
	 */
	function eve_page_sidebar_content_wrappers_start() {

		$classes = [];
		$classes[] = 'content-area';
		$classes[] = 'dark-left-sidebar';
		$classes[] = 'content-id-'. eve_get_current_page_id();

?><div class="ctn max"><div class="<?php echo implode( ' ', $classes ); ?>"><?php
	}
endif;



if ( ! function_exists( 'eve_page_sidebar_content_wrappers_end' ) ) :
	/**
	 * Page sidebar wrapper end
	 */
	function eve_page_sidebar_content_wrappers_end() {
?></div><!-- .content-area --></div><!-- .ctn --><?php
	}
endif;



if ( ! function_exists( 'eve_page_sidebar_content_main_start' ) ) :
	/**
	 * Page sidebar main start
	 */
	function eve_page_sidebar_content_main_start() {

		$classes = [];
		$classes[] = 'content-main';
		$classes[] = 'with-sidebar';

?><div class="<?php echo implode( ' ', $classes ); ?>"><?php
	}
endif;



if ( ! function_exists( 'eve_page_sidebar_content_main_end' ) ) :
	/**
	 * Page sidebar main end
	 */
	function eve_page_sidebar_content_main_end() {
?></div><!-- .content-main --><?php
	}
endif;



if ( ! function_exists( 'eve_page_sidebar_aside_wrp_start' ) ) :
	/**
	 * Page sidebar wrapper start
	 */
	function eve_page_sidebar_aside_wrp_start() {
?><div class="content-aside"><div class="content-aside-inner"><?php
	}
endif;



if ( ! function_exists( 'eve_page_sidebar_aside_wrp_end' ) ) :
	/**
	 * Page sidebar wrapper end
	 */
	function eve_page_sidebar_aside_wrp_end() {
?></div></div><!-- .content-aside --><?php
	}
endif;


if ( ! function_exists( 'eve_woo_profile_active_joblistings' ) ) :
	/**
	 * List of active job listings
	 */
	function eve_woo_profile_active_joblistings(){
	    define('ITEM_ACTIVATE', 'Activate');
	    define('ITEM_INACTIVATE', 'Inactivate');
	    define('ITEM_ACTIVE', 'Active');
	    define('ITEM_INACTIVE', 'Inactive');
	    define('ITEM_INVALID', 'Waiting for admin check');
	    define('ITEM_UNPAID', 'Unpaid' );
	    define('ITEM_EXPIRED', 'Expired');
	    define('ITEM_UNUSED', 'Unused');
	    define('FEATURE_ACTIVE', 'Active');
	    define('FEATURE_INACTIVE', 'Inactive');

        $bundleManager = new DFPBManager();
        $user_id = eve_get_user()->ID;
        $jobs_and_features = $bundleManager->get_all_jobs_and_featureds_for_user($user_id);
        $active_jobs_count = 0;

        foreach($jobs_and_features as $jobs) {
            if (is_object($jobs) && property_exists($jobs, 'post_id') && !empty($jobs->post_id) && $jobs->job_status === 1 && $jobs->job_expire_time > 1 ) {
                $active_jobs_count++;
            }
        }
?>
<div class="active-job-list">
	<h2><?php _e('Overview', 'dasfalke-profile'); ?></h2>
    <div class="profile-edit-wrapper">
		<div><p><?php _e('Share more about your business to increase the number of visitors and applicants.', 'dasfalke-profile'); ?></p></div>
		<div class="profile-edit__completeness">
			<svg class="i i-profile" width="50" height="50"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-profile" href="#i-profile"></use></svg>
			<div class="profile-edit__completeness-dsc"><?php if (get_profile_completeness() == '100%') { echo __('Your company profile is complete!', 'dasfalke-profile'); } else { echo sprintf(__('%s Your company profile is not complete yet!', 'dasfalke-profile'), get_profile_completeness());} ?></div>
			<div><a class="profile-edit__action" href="<?php echo eve_get_def_employe_page_uri(); ?>my-account/company-profile/"><?php _e('EDIT COMPANY PROFILE', 'dasfalke-profile'); ?></a></div>
		</div>
    </div>
	<br /><hr class="job-page__sep"><br />
    <div class="buy-job-package-wrapper">
        <div><a class="df-btn secondary block" href="<?php echo eve_get_def_employe_page_uri(); ?>pricing/"><?php _e('BUY JOB PACKAGE', 'dasfalke-profile'); ?></a></div>
    </div>
	<hr class="job-page__sep"><br />
    <?php if (!empty($jobs_and_features['post_ids'])) : ?>
        <div class="job-count-wrapper">
            <div class="job-count-title"><?php _e(ITEM_ACTIVE, 'dasfalke-profile'); ?>/<?php echo sprintf(__('%s jobs', 'dasfalke-profile'), __(ITEM_INACTIVE, 'dasfalke-profile')); ?></div>
            <div class="job-count"><?php echo sprintf(__('%d active jobs out of %d total jobs', 'dasfalke-profile'), $active_jobs_count, count($jobs_and_features['post_ids'])); ?></div>
        </div>
        <div class="job-count-wrapper">
            <div class="job-text"><?php _e('You can activate your postings at any time.', 'dasfalke-profile'); ?></div>
        </div>

        <div>
            <?php eve_woo_profile_list_jobs($jobs_and_features, $bundleManager); ?>
        </div>
    <?php else: ?>
        <?php eve_woo_profile_company_guide(); ?>
	<?php endif; ?>

    <div>
        <?php eve_woo_profile_list_jobs_notlive($jobs_and_features, $bundleManager); ?>
    </div>
</div>
<?php
	}
endif;

if (!function_exists('eve_woo_profile_list_jobs_notlive')) {
    function eve_woo_profile_list_jobs_notlive($jobs_and_features, $bundleManager)
    {
        $job_status_string = ITEM_INACTIVE;
        $iterrator = 0;

        foreach($jobs_and_features as $key => $data) {
            if (!is_object($data) || !empty($data->post_id)) {
                continue;
            }

            // This is the title: "unused/unpaid jobs"
            if ($iterrator <= 0) {
				echo '<br>';
				echo '<br>';
                echo "<div class='list-title'>" . __('Available jobs', 'dasfalke-profile') . "</div>";
            }

            if ($data->bundle_is_paid === null) {
                $job_status_string = ITEM_UNPAID;
            } else if ($data->job_status == 1) {
                $job_status_string = $data->job_expire_time <= 0 ? ITEM_EXPIRED : ITEM_INACTIVE;
            } else if ($data->job_expire_time > 0) {
                $job_status_string = ITEM_UNUSED;
            }
            ?>

			<article <?php post_class('job-list__item inactive-job-list-item'); ?>>
				<h4 class="job-list__item-title"><?php _e('Job slot', 'dasfalke-profile') ?> <?php echo ($key + 1); ?></h4>
				<div class="job-list__item-status <?php echo sanitize_title_with_dashes($job_status_string); ?>"><?php _e('Status', 'dasfalke-profile') ?>: <span><?php _e($job_status_string, 'dasfalke-profile'); ?></span></div>

				<?php if ($job_status_string === ITEM_UNUSED) : ?>
				<div class="job-list__item-status-wrp" data-job-id="<?php echo $data->post_id; ?>">
					<a class="df-btn outline small" href="<?php echo home_url("my-account/add-job/?job_id=" . $data->job_id); ?>"><?php _e('New job posting', 'dasfalke-profile') ?></a>
				</div>
				<?php endif; ?>

                <div class="job-list__item-info-wrapper">
                    <div class="job-list__item-info-title"><?php _e('Extras', 'dasfalke-profile'); ?>:</div>

                    <div class="job-list__item-info-features">

                    <?php if (!empty($data->feature_types)) : ?>

                            <?php foreach($data->feature_types as $feature_key => $feature_type) :
                                $feature_expire_time = "";
                                $feature_status_text = FEATURE_ACTIVE;
                                //$feature_status_text = FEATURE_INACTIVE;

                                /*if ($data->feature_statuses[$feature_key] === 1) {
                                    $feature_status_text = FEATURE_ACTIVE;
                                }*/

                                if ($data->feature_expire_times[$feature_key] === null) {
                                    $feature_status_text = ITEM_EXPIRED;
                                }

                                if ($job_status_string === ITEM_UNPAID) {
                                    $feature_status_text = ITEM_UNPAID;
                                }

                                if (property_exists($data, 'feature_expire_times') && $data->feature_expire_times[$feature_key] > 0) {
                                    $feature_expire_time = $bundleManager->seconds_to_days($data->feature_expire_times[$feature_key]);
                                }
                                ?>
                                <div class="job-list__item-feature <?php echo strtolower($feature_status_text); ?>" title="<?php echo $feature_status_text; ?>">
                                    <?php if (property_exists($data, 'feature_names')) : ?>
                                        <span><?php _e($data->feature_names[$feature_key], 'dasfalke-profile'); ?></span>
                                    <?php endif; ?>

                                    <?php if (property_exists($data, 'feature_expire_times') && $data->feature_expire_times[$feature_key] > 0) : ?>
                                        (<span><?php echo $feature_expire_time; ?></span>&nbsp;<?php _e('days', 'dasfalke-profile'); ?>)
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (property_exists($data, 'unassigned_featured_category')) : ?>
                        <?php foreach($data->unassigned_featured_category as $unassigned_type) : ?>
                            <div class="job-list__item-feature">
                                <span><?php _e($unassigned_type, 'dasfalke-profile'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php
            $iterrator++;
        }
    }
}

if (!function_exists('eve_woo_profile_list_jobs')) {
    function eve_woo_profile_list_jobs($jobs_and_features, $bundleManager) {
        $jobs_per_page = 12;
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $time_now = new DateTime(date("Y-m-d H:i:s"));

        $job_posts = new WP_Query([
            'post__in' => $jobs_and_features['post_ids'],
            'posts_per_page' => $jobs_per_page,
            'paged' => $paged,
            'post_status' => ['publish', 'draft'],
        ]);

        while ($job_posts->have_posts()) :
            $job_posts->the_post();
            $show_status_change_button = true;
            $job_status_string = ITEM_INACTIVE;
            $job_id = null;
            $post_id = get_the_ID();
            $status_change_text = ITEM_ACTIVATE;

            foreach($jobs_and_features as $data) :
                if (!isset($data->post_id) || $data->post_id !== $post_id) {
                    continue;
                }

                if ($data->bundle_is_paid === null) {
                    $job_status_string = ITEM_UNPAID;
                    $show_status_change_button = false;
                } else {
                    $bundle_expires_at = new DateTime($data->bundle_is_paid);

                    if ($bundle_expires_at <= $time_now) {
                        $job_status_string = ITEM_EXPIRED;
                        $show_status_change_button = false;
                    } else if ($data->job_status == 1) {
                        if ($data->job_expire_time <= 0) {
                            $job_status_string = ITEM_EXPIRED;
                            $show_status_change_button = false;
                        } else if ($data->job_expire_time > 0) {
                            $job_status_string = ITEM_ACTIVE;
                            $status_change_text = ITEM_INACTIVATE;
                        }
                    } else if ($data->job_expire_time > 0) {
                        if (get_post_status() === 'draft') {
                            $job_status_string = ITEM_INVALID;
                        } else {
                            $job_status_string = ITEM_INACTIVE;
                        }
                    }
                }

                if (empty($data->post_id) || $job_status_string === ITEM_INVALID) {
                    $show_status_change_button = false;
                }
                ?>

                <form method="post">
					<article <?php post_class('job-list__item active-job-list-item'); ?>>
						<h3 class="job-list__item-title"><a href="<?php echo get_permalink(); ?>" class="title"><?php echo get_the_title(); ?></a></h3>
						<div class="job-list__item-status <?php echo sanitize_title_with_dashes($job_status_string); ?>"><?php _e('Status', 'dasfalke-profile') ?>: <span><?php _e($job_status_string, 'dasfalke-profile'); ?></span></div>
						<div class="job-list__item-status-wrp" data-job-id="<?php echo $data->post_id; ?>">
							<?php if($show_status_change_button === true) : ?>
								<input type="hidden" name="change-job-status" value="<?php echo $data->post_id; ?>" />
								<button class="df-btn primary small" type="submit" name="change-job-status-button" value="<?php echo $status_change_text; ?>"><?php _e($status_change_text, 'dasfalke-profile'); ?></button>
								<a class="df-btn outline small" href="/my-account/edit-job/?job_id=<?php echo $data->post_id; ?>"><?php _e('Edit job', 'dasfalke-profile'); ?></a>
                                <a class="df-btn outline small" href="<?php echo get_permalink(); ?>"><?php _e('Preview', 'dasfalke-profile'); ?></a>
                                <a class="df-btn outline small" href="<?php echo get_permalink() . "?buy_extras"; ?>"><?php _e('Buy extras', 'dasfalke-profile'); ?></a>
							<?php endif; ?>
						</div>
                        <div class="job-list__item-info-wrapper">
							<div class="job-list__item-info-title"><?php _e('Extras', 'dasfalke-profile'); ?>:</div>
                            <div class="job-list__item-info-features">
                                <?php if(property_exists($data, 'feature_names')) : ?>
                                    <?php foreach($data->feature_names as $feature_key => $feature_type) :
                                        $feature_status_text = FEATURE_ACTIVE;
                                        //$feature_status_text = FEATURE_INACTIVE;
                                        $feature_expire_time = "";

                                        if ($data->feature_expire_times[$feature_key] > 0) {
                                            $feature_expire_time = $bundleManager->seconds_to_days($data->feature_expire_times[$feature_key]);
                                        }

                                        /*if ($data->feature_statuses[$feature_key] === 1) {
                                            $feature_status_text = FEATURE_ACTIVE;
                                        }*/

                                        if ($data->feature_expire_times[$feature_key] === null) {
                                            $feature_status_text = ITEM_EXPIRED;
                                        }

                                        if ($job_status_string === ITEM_UNPAID) {
                                            $feature_status_text = ITEM_UNPAID;
                                        }

                                        ?>

                                        <div class="job-list__item-feature <?php echo strtolower($feature_status_text); ?>" title="<?php echo $feature_status_text; ?>">
                                            <span><?php _e($feature_type, 'dasfalke-profile'); ?></span>&nbsp

                                            <?php if (property_exists($data, 'feature_expire_times') && $data->feature_expire_times[$feature_key] > 0) : ?>
                                                (<span><?php echo $feature_expire_time; ?></span>&nbsp;<?php _e('days', 'dasfalke-profile'); ?>)
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (property_exists($data, 'unassigned_featured_category')) : ?>
                                    <?php foreach($data->unassigned_featured_category as $unassigned_type) : ?>
                                        <div class="job-list__item-feature">
                                            <span><?php _e($unassigned_type, 'dasfalke-profile'); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
							</div>
						</div>

                    </article>
                </form>
            <?php endforeach; ?>
        <?php endwhile; ?>

        <?php wp_reset_query(); ?>
        <?php echo falke_pagination( $paged, $job_posts->max_num_pages);
    }
}


if ( ! function_exists( 'eve_woo_profile_company_guide' ) ) :
	/**
	 * Display a small guide how to add job postings
	 */
	function eve_woo_profile_company_guide() {
?>
<div class="faq-list">
	<div class="faq-list-item">
		<h3><?php _e( 'How to create a new listing?', 'dasfalke-faq' ); ?></h3>
		<p><?php _e( 'You can add as many jobs as your package can handle on your profile under Active new listings menu. If you need to add more please sub to a bigger package.', 'dasfalke-faq' ); ?></p>
	</div>
	<div class="faq-list-item">
		<h3><?php _e( 'How to edit my listing?', 'dasfalke-faq' ); ?></h3>
		<p><?php _e( 'You can edit your job postings under the Overview menu on your profile.', 'dasfalke-faq' ); ?></p>
	</div>
</div>
<?php
	}
endif;


if ( ! function_exists( 'eve_woo_profile_active_alerts' ) ) :
	/**
	 * List of active job listings
     */
    function eve_woo_profile_active_alerts()
    {
        $job_alerts = get_job_alerts();
        $domain = 'dasfalke-profile';
?>
<div class="job-alert-elems">
	<h2 class="profile__title"><?php _e('Your job alerts', $domain); ?></h2>
	<?php if( !empty($job_alerts) ) : ?>
		<?php foreach($job_alerts as $job_alert_num => $job_alert) : ?>
			<div class="job-alert-elem">
				<div class="job-alert-elem__num"><?php _e('Job alert', $domain); ?> #<?php echo ($job_alert_num + 1); ?></div>
				<div class="job-alert-elem__keys">
					<?php foreach($job_alert as $filter_key => $filter_value) : ?>
						<?php if (!empty($filter_value)) : ?>
							<div class="job-alert-elem__key">
								<?php _e(get_profile_key_name_pairs($filter_key), $domain); ?>:

                                <?php if ($filter_key !== 'selected_locations' && $filter_key !== 'selected_professions') : ?>
                                    <?php _e($filter_value, $domain); ?>
                                <?php else : ?>
                                    <?php if(!empty($the_term = get_term($filter_value))) : ?>
                                        <?php if(property_exists($the_term, 'name')) : ?>
                                            <?php _e($the_term->name, $domain); ?>
										<?php endif; ?>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<form method="post">
					<?php wp_nonce_field( 'delete-job-alert', 'delete_job_alert_nonce' ); ?>
					<input name="delete_job_alert_id" type="hidden" value="<?php echo ($job_alert_num + 1); ?>" />
					<button class="job-alert-elem__del df-btn secondary small block" name="delete_job_alert"><?php _e('Delete job alert', $domain); ?></button>
				</form>
			</div>
        <?php endforeach; ?>
	<?php else: ?>
        <div><a class="df-btn primary" href="/jobs"><?php _e('GO TO FILTER PAGE AND CREATE JOB ALERT', $domain); ?></a></div>
	    <?php eve_woo_profile_jobseeker_guide(); ?>
	<?php endif; ?>
</div>
<?php
	}
endif;


if ( ! function_exists( 'eve_woo_profile_jobseeker_guide' ) ) :
	/**
	 * Display a small guide how to add job postings
	 */
	function eve_woo_profile_jobseeker_guide() {
?>
<div class="faq-list">
	<div class="faq-list-item">
		<h3><?php _e( 'Get the latest job in Austria via email.', 'dasfalke-faq' ); ?></h3>
		<p><?php _e( 'To set up a Job alert please first run a search on our job page. When you are satisfied with the search results just click <strong>Save Filter Criteria</strong>. As a registered user, you can view and delete your job alerts in your profile.', 'dasfalke-faq' ); ?></p>
	</div>
</div>
<?php
	}
endif;



if ( ! function_exists( 'eve_woo_profile_company_edit_job' ) ) :
	/**
	 * Edit job page on dashboard
	 */
	function eve_woo_profile_company_edit_job() {
		$falke_meta_domain = "dasfalke-profile";
		$types = load_json_data('employment_type');
		$natures = load_json_data('employment_nature');
		$educations = load_json_data('required_education');
		$payments = load_json_data('required_payment');

		$emp_type = get_field_if_posted('employment_type');
		$payment_post = get_field_if_posted('employment_payment');
		$education_post = get_field_if_posted('employment_education');
		$nature_post = get_field_if_posted('employment_nature');
		$apply_type = get_field_if_posted('apply_type');
        $wpml_domain_submit_error = 'dasfalke-jobmessages';
?>

<?php if (!is_admin()) : ?>
    <form method="post">
<?php endif; ?>

<?php handle_falke_dropdown('locations', [__( 'Location', 'dasfalke-dropdowns' ), __( 'City', 'dasfalke-dropdowns' ), __( 'Address', 'dasfalke-dropdowns' )], "", __('All of Austria', 'dasfalke-dropdowns'), true, true); ?>
<?php handle_falke_dropdown('professions', [__( 'Main category', 'dasfalke-dropdowns' ), __( 'Category', 'dasfalke-dropdowns' ), __( 'Sub category', 'dasfalke-dropdowns' )], "", "", true, true); ?>

<div class="form-row text">
	<label class="form-row__label" for="job_title"><?php _e(get_profile_key_name_pairs('job_title'), $falke_meta_domain); ?> *</label>
	<div class="form-row__input">
		<input type='text' value="<?php echo get_field_if_posted('job_title', true); ?>" id="job_title" name="job_title" />
	</div>
</div>

<div class="form-row textarea">
    <label class="form-row__label" for="job_description"><?php _e(get_profile_key_name_pairs('job_description'), $falke_meta_domain); ?> *</label>

    <div class="form-row__input">
        <?php wp_editor(get_field_if_posted('job_description', true), "job_description", [
            'textarea_name' => 'job_description',
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

<div class="form-row textarea">
    <label class="form-row__label" for="employment_tasks"><?php _e(get_profile_key_name_pairs('employment_tasks'), $falke_meta_domain); ?></label>

    <div class="form-row__input">
        <?php wp_editor(get_field_if_posted('employment_tasks'), "employment_tasks", [
            'textarea_name' => 'employment_tasks',
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

<div class="form-row textarea">
    <label class="form-row__label" for="employment_requirements"><?php _e(get_profile_key_name_pairs('employment_requirements'), $falke_meta_domain); ?></label>

    <div class="form-row__input">
        <?php wp_editor(get_field_if_posted('employment_requirements'), "employment_requirements", [
            'textarea_name' => 'employment_requirements',
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

<div class="form-row textarea">
    <label class="form-row__label" for="employment_advantage"><?php _e(get_profile_key_name_pairs('employment_advantage'), $falke_meta_domain); ?></label>

    <div class="form-row__input">
        <?php wp_editor(get_field_if_posted('employment_advantage'), "employment_advantage", [
            'textarea_name' => 'employment_advantage',
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

<div class="form-row select">
	<label class="form-row__label" for="employment_type"><?php _e(get_profile_key_name_pairs('employment_type'), $falke_meta_domain); ?></label>
	<div class="form-row__input">
		<select id="employment_type" name="employment_type" >
			<option disabled selected value=""><?php _e('Please select...', $falke_meta_domain); ?></option>

			<?php foreach($types as $type) : ?>
				<option value="<?php echo $type;?>" <?php echo ($emp_type == $type ? "selected" : ""); ?>><?php _e($type, $falke_meta_domain); ?></option>
			<?php endforeach; ?>
		</select>
		<div class="select__arrow"><svg class="i i-arrow" width="16" height="14"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-arrow" href="#i-arrow"></use></svg></div>
	</div>
</div>

<div class="form-row select">
	<label class="form-row__label" for="employment_nature"><?php _e(get_profile_key_name_pairs('employment_nature'), $falke_meta_domain); ?></label>
	<div class="form-row__input">
		<select id="employment_nature" name="employment_nature" >
			<option disabled selected value=""><?php echo sprintf(__('Please select...', $falke_meta_domain), get_profile_key_name_pairs('employment_nature')); ?></option>

			<?php foreach($natures as $nature) : ?>
				<option  value="<?php echo $nature;?>" <?php echo ($nature_post == $nature ? "selected" : ""); ?>><?php _e($nature, $falke_meta_domain); ?></option>
			<?php endforeach; ?>
		</select>
		<div class="select__arrow"><svg class="i i-arrow" width="16" height="14"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-arrow" href="#i-arrow"></use></svg></div>
	</div>
</div>

<div class="form-row select">
	<label class="form-row__label" for="employment_education"><?php _e(get_profile_key_name_pairs('employment_education'), $falke_meta_domain); ?></label>
	<div class="form-row__input">
		<select id="employment_education" name="employment_education" >
			<option disabled selected value=""><?php echo sprintf(__('Please select...', $falke_meta_domain), get_profile_key_name_pairs('employment_education')); ?></option>

			<?php foreach($educations as $education) : ?>
				<option  value="<?php echo $education;?>" <?php echo ($education_post == $education ? "selected" : ""); ?>><?php _e($education, $falke_meta_domain); ?></option>
			<?php endforeach; ?>
		</select>
		<div class="select__arrow"><svg class="i i-arrow" width="16" height="14"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-arrow" href="#i-arrow"></use></svg></div>
	</div>
</div>

<div class="form-row select">
	<label class="form-row__label" for="employment_payment"><?php _e(get_profile_key_name_pairs('employment_payment'), $wpml_domain_submit_error); ?> *</label>
	<div class="form-row__input">
		<select id="employment_payment" name="employment_payment" >
			<option disabled selected value=""><?php echo sprintf(__('Please select...', $falke_meta_domain), get_profile_key_name_pairs('employment_payment')); ?></option>

			<?php foreach($payments as $key => $payment) : ?>
                <?php $upped_key = $key + 1; ?>
				<option <?php echo ($payment_post == $upped_key ? "selected" : ""); ?> value="<?php echo $upped_key; ?>"><?php _e($payment, $falke_meta_domain); ?></option>
			<?php endforeach; ?>
		</select>
		<div class="select__arrow"><svg class="i i-arrow" width="16" height="14"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-arrow" href="#i-arrow"></use></svg></div>
	</div>
</div>

<div class="row">
	<div class="col-sm-6">
		<div class="form-row text" style="display: none;">
			<label class="form-row__label" for="employment_payment_extent"><?php _e('Please enter an amount', $falke_meta_domain); ?> *</label>
			<div class="form-row__input">
				<input type="number" step="0.01" value="<?php echo get_field_if_posted('employment_payment_extent'); ?>" id="employment_payment_extent" name="employment_payment_extent" placeholder="<?php _e("Please define the payment extent", $falke_meta_domain); ?>">
			</div>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="form-row checkbox">
			<br>
			<div class="form-row__input">
				<label class="form-row__label-inline block" for="employment_payment_doe">
                    <input type="checkbox" <?php echo !empty(get_field_if_posted('employment_payment_doe')) ? "checked" : ""; ?> value="yes" id="employment_payment_doe" name="employment_payment_doe">&nbsp;<?php _e('Willingness to overpay depending on qualification and work experience.', $falke_meta_domain); ?>
                </label>
			</div>
		</div>
	</div>
</div>

<div class="form-row radio">
	<label class="form-row__label"><?php _e(get_profile_key_name_pairs('apply_type'), $falke_meta_domain); ?></label>
	<div class="form-row__input">
		<label class="form-row__label-inline" for="apply_type"><input type="radio" checked name="apply_type" <?php echo ($apply_type == "form" ? "checked" : ""); ?> id="apply_type" value="form" />&nbsp;<?php _e("With a form", $falke_meta_domain); ?></label>
		<label class="form-row__label-inline" for="apply_type_site"><input type="radio" name="apply_type" <?php echo ($apply_type == "site" ? "checked" : ""); ?> id="apply_type_site" value="site" />&nbsp;<?php _e("On employer site", $falke_meta_domain); ?></label>
	</div>
</div>

<div style="display:none;" class="form-row text">
	<label class="form-row__label" for="apply_type_site_field"><?php _e(get_profile_key_name_pairs('apply_type_site_field'), $falke_meta_domain); ?></label>
	<div class="form-row__input">
		<input id="apply_type_site_field" type="text" value="<?php echo get_field_if_posted('apply_type_site_field'); ?>" name="apply_type_site_field" placeholder="<?php _e("Please fill in the employer site URL", $falke_meta_domain); ?>">
	</div>
</div>

<div class="form-row text">
	<label class="form-row__label" for="employment_email"><?php _e(get_profile_key_name_pairs('employment_email'), $falke_meta_domain); ?></label>
	<div class="form-row__input">
		<input type="text" value="<?php echo get_field_if_posted('employment_email'); ?>" name="employment_email" id="employment_email" placeholder="<?php _e("E-mail address", $falke_meta_domain); ?>">
	</div>
</div>

<div class="form-row text">
	<label class="form-row__label" for="employment_refcode"><?php _e(get_profile_key_name_pairs('employment_refcode'), $falke_meta_domain); ?></label>
	<div class="form-row__input">
		<input type="text" value="<?php echo get_field_if_posted('employment_refcode'); ?>" name="employment_refcode" id="employment_refcode" placeholder="<?php _e("Reference code", $falke_meta_domain); ?>">
	</div>
</div>

<div class="form-row text">
	<label class="form-row__label" for="employment_docupload"><?php _e(get_profile_key_name_pairs('employment_docupload'), $falke_meta_domain); ?></label>
	<div class="form-row__input">
		<input type="text" value="<?php echo get_field_if_posted('employment_docupload'); ?>" name="employment_docupload" id="employment_docupload" placeholder="<?php _e("Documents to upload by employee", $falke_meta_domain); ?>">
	</div>
</div>

<div class="form-row textarea">
	<label class="form-row__label" for="employment_tags"><?php _e(get_profile_key_name_pairs('employment_tags'), $falke_meta_domain); ?></label>
	<div class="form-row__input">
		<textarea name="employment_tags" id="employment_tags" placeholder="<?php _e("Tags", $falke_meta_domain); ?>"><?php echo get_field_if_posted('employment_tags'); ?></textarea>
	</div>
</div>

<?php if (!is_admin()) : ?>
    <?php $button_text = is_edit_job_page() ? "Save job" : "Add job"; ?>

	<?php wp_nonce_field( 'submit-job-metas-nonce', 'submit-job-metas-noncename' ); ?>
	<input class="df-btn primary block" type="submit" name="submit-job-metas" value="<?php _e($button_text, $falke_meta_domain) ?>" />
	<br>
	<?php endif; ?>

</form>
<?php
	}
endif;

if ( ! function_exists( 'eve_author_page_top' ) ) :
	/**
	 * Display the index page filters
	 */
	function eve_author_page_top() {
?>
<div class="author-top">
	<div class="ctn max">
		<div class="author-top__push"></div>
	</div>
</div>
<?php
	}
endif;



if ( ! function_exists( 'eve_index_page_search_filter' ) ) :
	/**
	 * Display the index page filters
	 */
	function eve_index_page_search_filter() {
?>
<div class="filter__search">
	<div class="ctn max">
		<div class="filter__search-push"><h1 class="filter__search-title"><?php single_post_title(); ?></h1></div>
		<div><?php eve_main_search_bar( 'filter-page' ); ?></div>
		<div class="filter__search-detailed">
			<a class="df-btn block small" href="#filters"><?php _e('Detailed search', 'dasfalke-index'); ?></a>
		</div>
	</div>
</div>
<?php
	}
endif;



if ( ! function_exists( 'eve_main_search_bar' ) ) :
	/**
	 * Display the site main searchbar
	 * types: default, homepage
	 */
	function eve_main_search_bar( $type = 'default' ) {
?>
<div class="search-field<?php echo ' '. $type; ?>">
	<div class="search-field__psn">
		<?php if( $type != 'filter-page' ): ?>
        <form class="search-field__form" role="search" method="get" action="<?php echo home_url( '/jobs/' ); ?>">
		<?php endif; ?>
            <div class="search-field__query">
                <input tabindex=2 class="search-field__query-input" type="search"
                    placeholder="<?php echo esc_attr_x( 'e.g. Design, Cleaner', 'dasfalke-search' ); ?>"
                    value="<?php echo get_field_if_in_get('search'); ?>" name="search"
                    title="<?php echo esc_attr_x( 'Search for:', 'dasfalke-search' ); ?>" />
                <button class="search-field__btn submit" type="submit"><svg class="i i-search" width="16" height="16"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-search" href="#i-search"></use></svg><span class="t"><?php _e('Search', 'dasfalke-search'); ?></span></button>
            </div>
            <div class="search-field__location">
                <div class="search-field__icon location"><svg class="i i-pin" width="16" height="16"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-pin" href="#i-pin"></use></svg></div>
                <input tabindex=1 class="search-field__location-input" type="text" placeholder="<?php _e( 'Location', 'dasfalke-dropdowns' ); ?>" value="<?php echo get_field_if_in_get('loc'); ?>" name="loc">
                <!-- <button class="search-field__btn locate"><svg class="i i-location" width="16" height="16"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-location" href="#i-location"></use></svg></button> -->
            </div>
		<?php if( $type != 'filter-page' ): ?>
        </form>
		<?php endif; ?>
	</div>
</div>
<?php
	}
endif;



if ( ! function_exists( 'eve_get_social_links' ) ) :
	/**
	 * Get social links
	 */
	function eve_get_social_links() {

		$socials = array(
			array(
				'name' => 'LinkedIn',
				'slug' => 'linkedin',
				'id' => 'eve_social_linkedin_uri',
			),
			array(
				'name' => 'Instagram',
				'slug' => 'instagram',
				'id' => 'eve_social_instagram_uri',
			),
			array(
				'name' => 'Facebook',
				'slug' => 'facebook',
				'id' => 'eve_social_facebook_uri',
			)
		);

		get_option('eve_employe_page_uri');

		echo '<div class="social-links">';

		foreach( $socials as $social ):

			$url = get_option($social['id']);

			if( empty($url) )
				continue;

?><a href="<?php echo $url; ?>"><svg class="i i-<?php echo $social['slug']; ?>" width="22" height="22" title="<?php echo $social['name']; ?>"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-<?php echo $social['slug']; ?>" href="#i-<?php echo $social['slug']; ?>"></use></svg></a><?php

		endforeach;

		echo '</div>';
	}
endif;


if ( ! function_exists( 'eve_get_featured_slider' ) ) :
	/**
	 * Display featured slider
	 */
	function eve_get_featured_slider( $heading = false, $tag = 'h3' ) {
        $featured_company_data = get_featured_company_data();

        if ($featured_company_data === false) {
            return false;
        }

        $user_id = $featured_company_data['user_id'];
        $slider_company = new WP_Query($featured_company_data['query_args']);
        wp_reset_query();
        $post_count = $slider_company->post_count;

        if ($post_count <= 0) {
            return false;
        }
?>
<div class="featured-jobs" data-slideout-ignore>
	<?php if( $heading ):
		printf('<%s class="featured-jobs__heading">%s</%s>', $tag, $heading, $tag );
	endif; ?>

	<div class="featured-jobs-content">
		<div class="featured-jobs-content-header">
			<img style="width:46px;height:auto;" src="<?php echo get_user_avatar($user_id); ?>" alt="user_avatar">
			<div class="title">
				<h4><?php echo get_user_meta($user_id, 'billing_company', true); ?></h4>
				<p><?php echo $post_count; ?> <?php _e('job opening(s)', 'dasfalke-sidebar'); ?></p>
			</div>
			<a href="<?php echo get_author_posts_url( $user_id ); ?>"><?php _e('show all', 'dasfalke-sidebar'); ?></a>
		</div>
		<div class="featured-jobs-slider featured-slider">
            <?php while($slider_company->have_posts()) : ?>
                <?php $slider_company->the_post(); ?>
                <?php $post_id = get_the_ID(); ?>

                <div class="featured-jobs-slider-item">
                    <a href="<?php echo get_the_permalink(); ?>">
                        <h5><?php echo get_post_meta($post_id, 'job_title', true); ?></h5>
                        <h6><?php echo get_location_string($post_id); ?></h6>
                    </a>
                </div>
            <?php endwhile; ?>
            <?php wp_reset_query(); ?>
		</div>
	</div>
</div>
<?php
	}
endif;



if ( ! function_exists( 'eve_get_featured_list' ) ) :
	/**
	 * Display featured list
	 */
	function eve_get_featured_list() {
        $user_data = eve_get_user();

        if (!empty($user_data) && property_exists($user_data, 'ID')) {
            $author_page_id = get_the_author_meta('ID');

            if ($user_data->ID === $author_page_id) {
                return false;
            }
        }

        $featured_company_data = get_featured_data('feature-homepage');
        $user_id = $featured_company_data['author'];
        $slider_company = new WP_Query($featured_company_data);
        wp_reset_query();
        $post_count = $slider_company->post_count;

        if ($post_count <= 0) {
            return "";
        }
?>
<div class="featured-block">
	<div class="featured-block__header">
        <img src="<?php echo get_user_avatar($user_id); ?>" alt="user_avatar">
		<div class="featured-block__title">
            <h3><?php echo get_user_meta($user_id, 'billing_company', true); ?></h3>
            <p><?php echo $post_count; ?> <?php _e('job opening(s)', 'dasfalke-sidebar'); ?></p>
		</div>
		<a href="<?php echo get_author_posts_url( $user_id ); ?>"><?php _e('show all', 'dasfalke-sidebar'); ?></a>
	</div>
	<div class="featured-block__content">
        <?php while($slider_company->have_posts()) : ?>
            <?php $slider_company->the_post(); ?>
            <?php $post_id = get_the_ID(); ?>

            <div class="featured-block__content-item">
                <a href="<?php echo get_the_permalink(); ?>">
                    <h4><?php echo get_post_meta($post_id, 'job_title', true); ?></h4>
                    <span><?php echo get_location_string($post_id); ?></span>
                </a>
            </div>
        <?php endwhile; ?>
        <?php wp_reset_query(); ?>
	</div>
	<span class="sponsored"><?php _e('Sponsored', 'dasfalke-sidebar'); ?></span>
</div>
<?php
	}
endif;



if ( ! function_exists( 'eve_single_sidebar' ) ) :
	/**
	 * Returns single sidebar
	 */
	function eve_single_sidebar() {
	    if( ! is_single() ) {
            return;
        }

	    $user_id = get_the_author_meta('ID');
        $bundleManager = new DFPBManager();
        $sidebar_jobs = $bundleManager->get_all_available_jobs_by_user($user_id);
        $post_count = 0;

        if (!empty($sidebar_jobs)) {
            $sidebar_jobs = new WP_Query(['author' => $user_id, 'post__in' => explode(",", $sidebar_jobs[0]->post_ids)]);
            wp_reset_query();
            $post_count = $sidebar_jobs->post_count;
        }
?>
<div class="profile sidebar">
	<div class="profile__img-wrp">
		<div class="profile__img-psn company">
			<img class="profile__img" src="<?php echo get_user_avatar($user_id); ?>" alt="<?php echo get_user_name($user_id); ?>">
		</div>
	</div>
	<div class="profile__name"><?php echo get_user_name($user_id); ?></div>
	<div class="profile__subname"><?php echo $post_count; ?> <?php _e('job opening(s)', 'dasfalke-sidebar'); ?></div>
	<div class="profile__desc"><?php 
	$profile_content = strip_tags( get_user_data('company_whoweare', $user_id) );
	$profile_content = substr($profile_content, 0, 160) .'...'; 
	
	echo $profile_content; ?></div>
	<div class="profile__prof"><a class="df-link" href="<?php echo get_author_posts_url( get_the_author_meta('ID')  ); ?>"><?php _e('COMPANY PROFILE', 'dasfalke-profile') ?></a></div>
</div>
<?php eve_get_featured_list(); ?>
<?php
	}
endif;



if ( ! function_exists( 'eve_author_sidebar' ) ) :
	/**
	 * Returns author sidebar
	 */
	function eve_author_sidebar() {
		if( ! is_author() ) {
            return;
        }

        $user_id = get_the_author_meta('ID');
        $bundleManager = new DFPBManager();
        $sidebar_jobs = $bundleManager->get_all_available_jobs_by_user($user_id);
        $post_count = 0;

        if (!empty($sidebar_jobs)) {
            $sidebar_jobs = new WP_Query(['author' => $user_id, 'post__in' => explode(",", $sidebar_jobs[0]->post_ids)]);
            wp_reset_query();
            $post_count = $sidebar_jobs->post_count;
        }
        ?>
        <div class="profile sidebar">
            <div class="profile__img-wrp">
                <div class="profile__img-psn company">
                    <img class="profile__img" src="<?php echo get_user_avatar($user_id); ?>" alt="<?php echo get_user_name($user_id); ?>">
                </div>
            </div>
            <h1 class="profile__name"><?php echo get_user_name($user_id); ?></h1>
			<div class="profile__subname"><?php echo $post_count; ?> <?php _e('job opening(s)', 'dasfalke-sidebar'); ?></div>
			
			<br>

			<?php $company_industry = get_user_data( 'company_industry', $user_id );
			if( $company_industry ): ?><div class="profile__meta">
				<div class="profile__meta-label"><?php _e('Industry','dasfalke-author'); ?></div>
				<div class="profile__meta-val"><?php echo $company_industry; ?></div>
			</div><?php endif; ?>
			
			<?php $company_website = get_user_data( 'company_website', $user_id );
			if( $company_website ): ?><div class="profile__meta">
				<div class="profile__meta-label"><?php _e('Website','dasfalke-author'); ?></div>
				<div class="profile__meta-val"><a class="df-link" href="http://<?php echo $company_website; ?>" target="_blank"><?php echo $company_website; ?></a></div>
			</div><?php endif; ?>
			
			<?php $company_founding_year = get_user_data( 'company_founding_year', $user_id );
			if( $company_founding_year ): ?><div class="profile__meta">
				<div class="profile__meta-label"><?php _e('Founded','dasfalke-author'); ?></div>
				<div class="profile__meta-val"><?php echo $company_founding_year; ?></div>
			</div><?php endif; ?>
			
			<?php $company_number_of_employees = get_user_data( 'company_number_of_employees', $user_id );
			if( $company_number_of_employees ): ?><div class="profile__meta">
				<div class="profile__meta-label"><?php _e('Number of employees','dasfalke-author'); ?></div>
				<div class="profile__meta-val"><?php echo $company_number_of_employees; ?></div>
			</div><?php endif; ?>
			
			<?php $billing_city = get_user_data( 'billing_city', $user_id );
			if( $billing_city ): ?><div class="profile__meta">
				<div class="profile__meta-label"><?php _e('City','dasfalke-author'); ?></div>
				<div class="profile__meta-val"><?php echo $billing_city; ?></div>
			</div><?php endif; ?>

        </div>
	<?php eve_get_featured_list(); ?><br>
<?php
	}
endif;


if ( ! function_exists( 'eve_index_page_filters' ) ) :
	/**
	 * Displays search page filters
	 */
	function eve_index_page_filters() {
        $falke_meta_domain = "dasfalke-profile";

        $types = load_json_data('employment_type');
        $natures = load_json_data('employment_nature');
        $educations = load_json_data('required_education');

        $emp_type = get_field_if_in_get('employment_type');
        $education_post = get_field_if_in_get('employment_education');
        $nature_post = get_field_if_in_get('employment_nature');
?>
<div id="filters" class="filter__options">
	<div class="filter__options-head">
		<div class="filter__options-left"><h4 class="filter__options-name"><?php _e('Filters', 'dasfalke-index'); ?></h4></div>
		<div class="filter__options-right"><a class="filter__options-reset" href="<?php echo '/jobs'; ?>"><?php _e('CLEAr', 'dasfalke-index'); ?></a></div>
	</div>
	<div class="filter__options-block">
        <?php handle_falke_dropdown('professions', [__( 'Main category', 'dasfalke-dropdowns' ), __( 'Category', 'dasfalke-dropdowns' ), __( 'Sub category', 'dasfalke-dropdowns' )], 'in-sidebar'); ?>

        <div class="form-row select in-sidebar">
            <label class="form-row__label" for=""><?php _e('Employment type', $falke_meta_domain); ?></label>
            <div class="form-row__input">
                <select class="in-sidebar" name="employment_type" id="employment_type">
                    <option disabled selected value=""><?php _e('Please select...', $falke_meta_domain); ?></option>
                    <?php foreach($types as $type) : ?>
                        <option value="<?php echo $type;?>" <?php echo ($emp_type == $type ? "selected" : ""); ?>><?php _e($type, $falke_meta_domain); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="select__arrow"><svg class="i i-arrow" width="16" height="14"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-arrow" href="#i-arrow"></use></svg></div>
            </div>
        </div>

        <div class="form-row select">
            <label class="form-row__label" for=""><?php _e('Employment nature', $falke_meta_domain); ?></label>
            <div class="form-row__input">
                <select class="in-sidebar" name="employment_nature" id="employment_nature">
                    <option disabled selected value=""><?php _e('Please select...', $falke_meta_domain); ?></option>

                    <?php foreach($natures as $nature) : ?>
                        <option value="<?php echo $nature;?>" <?php echo ($nature_post == $nature ? "selected" : ""); ?>><?php _e($nature, $falke_meta_domain); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="select__arrow"><svg class="i i-arrow" width="16" height="14"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-arrow" href="#i-arrow"></use></svg></div>
            </div>
        </div>

        <?php handle_falke_dropdown('locations', [__( 'Location', 'dasfalke-dropdowns' ), __( 'City', 'dasfalke-dropdowns' ), __( 'Address', 'dasfalke-dropdowns' )], 'in-sidebar', __('All of Austria', 'dasfalke-dropdowns'), true); ?>

        <div class="form-row select">
            <label class="form-row__label" for=""><?php _e('Required education', $falke_meta_domain); ?></label>
            <div class="form-row__input">
                <select class="in-sidebar" name="employment_education" id="employment_education">
                    <option disabled selected value=""><?php _e('Please select...', $falke_meta_domain); ?></option>

                    <?php foreach($educations as $education) : ?>
                        <option value="<?php echo $education;?>" <?php echo ($education_post == $education ? "selected" : ""); ?>><?php _e($education, $falke_meta_domain); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="select__arrow"><svg class="i i-arrow" width="16" height="14"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-arrow" href="#i-arrow"></use></svg></div>
            </div>
        </div>

        <div class="form-row submit">
            <input class="df-btn primary block small" type="submit" value="<?php _e('Submit','dasfalke-index'); ?>">
        </div>
	</div>

	<br>
	<div class="filter__options-block">
		<div class="alerts">
			<div class="alerts__form">
				<div class="alerts__note"><?php _e('Start your search, save the filter criteria and get the latest jobs via e-mail for free.', 'dasfalke-index'); ?></div>
				<div class="alerts__trigger"><a id="create_job_alert" class="df-btn secondary block small" href="#"><?php _e('SAVE FILTER CRITERIA', 'dasfalke-index'); ?></a></div>
			</div>
		</div>
	</div>
	<div class="filter__options-head">
	<!--	<div class="filter__options-left"><h4 class="filter__options-name"><?php _e('Job alerts', 'dasfalke-index'); ?></h4></div>  -->
		<div class="filter__options-right"><a class="filter__options-reset" href="<?php echo eve_get_user_profile_uri(); ?>"><?php _e('MANAGE', 'dasfalke-index'); ?></a></div>
	</div>

	<div class="filter__options-block"><?php eve_get_featured_list(); ?></div>
</div>
<?php
}
endif;


if ( ! function_exists( 'eve_company_sidebar' ) ) :
	/**
	 * Returns single sidebar
	 */
	function eve_company_sidebar() {
		eve_get_featured_list(); 
	}
endif;


if ( !function_exists( 'eve_get_section_bg' ) ) :
	/**
	 * Generate section bg image
	 */
  function eve_get_section_bg($post_ID)
  {
    if( !has_post_thumbnail($post_ID) ){ 
      ?><div class="hhero__bg-plain"></div><?php
      return;
    }

    if( wp_is_mobile() ):
      ?><div class="hhero__bg-vignette"></div><div class="hhero__bg-img loadlzly mobile" data-bg="url('<?php echo get_the_post_thumbnail_url($post_ID,'one-tree-page-banner-mobile-full') ?>')"></div><?php
    else:
      ?><div class="hhero__bg-vignette"></div><div class="hhero__bg-img loadlzly desktop" data-bg="url('<?php echo get_the_post_thumbnail_url($post_ID,'one-tree-page-banner-desktop-full') ?>')"></div><?php
    endif;

  }
endif;


if ( ! function_exists( 'eve_woo_profile_company_company_profile' ) ) :
	/**
	 * Edit details page for company
	 */
	function eve_woo_profile_company_company_profile() {

		$falke_meta_domain = 'dasfalke-profile';
        $company_image = get_file_if_posted('user_meta', 'profile_image');
        $company_image_src = "";
        $employee_numbers = get_employee_numbers();
        $industries = get_industries();

        if (is_array($company_image) && array_key_exists('sizes', $company_image)) {
            $company_image_src = $company_image['sizes']['profile-image']['src'];
        }

        $countries_obj = new WC_Countries();
        $countries = $countries_obj->__get('countries');
?>
<div>
<h1 class="profile__title"><?php _e('Company profile', $falke_meta_domain); ?></h1>
<form class="filter__options-form" method="post" enctype="multipart/form-data">

<br>

<h2 class="profile__subtitle"><?php _e('Company details', $falke_meta_domain); ?></h2>

<?php echo make_text_field('billing_company', true); ?>

<div class="form-row file">
	<label class="form-row__label" for="profile_image"><?php _e('Company logo', $falke_meta_domain); ?> *</label>
	<div class="form-row__input">
        <input type="file" name="profile_image" id="profile_image" value="">

        <?php if (!empty($company_image)) : ?>
			<div class="profile__image-wrp">
				<div class="profile__image-psn"><img class="profile__image-img" src="<?php echo $company_image_src; ?>" alt="<?php echo $company_image['file_name']; ?>"></div>
				<div class="profile__image-name"><?php echo $company_image['file_name']; ?></div>
			</div>
        <?php endif; ?>
	</div>
	<div class="form-row__note">
		<?php _e('For best result please upload a square aspect ratio image with a minimum resolution of 400 x 400px.', $falke_meta_domain); ?>
	</div>
</div>

<?php echo make_text_field('billing_address_1', true); ?>

<div class="row">
	<div class="col-sm-6">
        <?php echo make_text_field('billing_city', true); ?>
	</div>
	<div class="col-sm-6">
        <?php echo make_text_field('billing_postcode', true); ?>
	</div>
</div>

<?php echo make_simple_dropdown('billing_country', 'Country', $countries, get_field_if_posted('billing_country', true), 'Please select...', true, true); ?>
<?php echo make_text_field('billing_tax_number'); ?>
<?php echo make_text_field('company_website'); ?>

<div class="form-row textarea">
	<label class="form-row__label" for="company_whoweare"><?php _e('Who We Are', $falke_meta_domain); ?> *</label>
	<div class="form-row__input">
			<?php wp_editor(get_field_if_posted('company_whoweare', true), "company_whoweare", [
					'textarea_name' => 'company_whoweare',
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

<div class="form-row textarea">
	<label class="form-row__label" for="company_whatweoffer"><?php _e('What We Offer', $falke_meta_domain); ?></label>
	<div class="form-row__input">
			<?php wp_editor(get_field_if_posted('company_whatweoffer', true), "company_whatweoffer", [
					'textarea_name' => 'company_whatweoffer',
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

    <div class="form-row textarea">
        <label class="form-row__label" for="company_ourexpectation"><?php _e('Our expectations', $falke_meta_domain); ?></label>
        <div class="form-row__input">
            <?php wp_editor(get_field_if_posted('company_ourexpectation', true), "company_ourexpectation", [
                'textarea_name' => 'company_ourexpectation',
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

<div class="row">
	<div class="col-sm-6">
        <?php echo make_text_field('company_founding_year', false, 'number'); ?>
	</div>
	<div class="col-sm-6">
        <?php echo make_simple_dropdown('company_number_of_employees', 'Number of employees', $employee_numbers, get_field_if_posted('company_number_of_employees', true)); ?>
	</div>
</div>

<?php echo make_simple_dropdown('company_industry', 'Industry', $industries, get_field_if_posted('company_industry', true)); ?>

<br><br>

<h2 class="profile__subtitle"><?php _e('Contact person', $falke_meta_domain); ?></h2>

<div class="row">
	<div class="col-sm-4">
        <?php echo make_simple_dropdown('contactperson_honorific', 'Title', ['Mrs.', 'Mr.'], get_field_if_posted('contactperson_honorific', true), 'Not Specified'); ?>
	</div>
	<div class="col-sm-4">
        <?php echo make_text_field('contactperson_first_name'); ?>
	</div>
	<div class="col-sm-4">
        <?php echo make_text_field('contactperson_last_name'); ?>
	</div>
</div>

<?php echo make_text_field('contactperson_position', false, 'text', "eg. Managing Director, CEO, CMO..."); ?>

<div class="row">
	<div class="col-sm-6">
        <?php echo make_text_field('contactperson_email', false, 'email', "Your email"); ?>
	</div>
	<div class="col-sm-6">
        <?php echo make_text_field('contactperson_phone', false, 'text', "Number"); ?>
	</div>
</div>

<br>
<div class="form-row submit">
	<input class="df-btn primary block" type="submit" name="company_profile_save" value="<?php _e("Save account details", 'dasfalke-profile'); ?>" />
</div>

</form>
</div>
<?php
	}
endif;


if ( ! function_exists( 'eve_checkout_payment_heading' ) ) :
	/**
	 * Add heading for payment type box
	 */
	function eve_checkout_payment_heading() {
?><h3><?php _e('Payment methods', 'dasfalke-checkout'); ?></h3><?php
	}
endif;


if ( ! function_exists( 'eve_foo' ) ) :
	/**
	 * Comment
	 */
	function eve_foo() {

	}
endif;


if (!function_exists('progresseve_scripts')) :
	/**
	 * Template scripts and style loader
	 */
	function progresseve_scripts()
	{

			/**
			 *
			 * Load JS files
			 * (function() {
			 * var wf = document.createElement('script');
			 * wf.src = '<?php echo get_template_directory_uri(); ?>/js/eve.js?v=<?php echo progresseve_verison_control(); ?>';
			 * wf.type = 'text/javascript';
			 * document.body.appendChild(wf);
			 * })();
			 **/

			/**
			 *
			 * Load CSS files
			 *
			 * (function() {
			 *   var css = document.createElement('link');
			 *   css.rel = 'stylesheet';
			 *   css.href = '<?php echo get_template_directory_uri(); ?>/css/swiper.css?v=<?php echo progresseve_verison_control(); ?>';
			 *   css.type = 'text/css';
			 *   var godefer = document.getElementsByTagName('link')[0];
			 *   godefer.parentNode.insertBefore(css, godefer);
			 * })();
			 */

			/**
			 *
			 * Load CSS background images (OLD METHOD, USE THE LAZY LOAD OPTION FOR THIS)
			 *
			 * (function() {
			 *   var container = document.getElementById( 'section__bg' );
			 *   if ( !container ) { return; }
			 *   var image = new Image();
			 *   image.src = container.dataset.bg;
			 *   image.onload = function() {
			 *     container.style.backgroundImage = "url('"+ image.src +"')";
			 *     container.classList.add( 'active' );
			 *   }
			 * })();
			 */

?><script type='text/javascript'>
var _extends=Object.assign||function(t){for(var e=1;e<arguments.length;e++){var n=arguments[e];for(var o in n)Object.prototype.hasOwnProperty.call(n,o)&&(t[o]=n[o])}return t},_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};!function(t,e){"object"===("undefined"==typeof exports?"undefined":_typeof(exports))&&"undefined"!=typeof module?module.exports=e():"function"==typeof define&&define.amd?define(e):t.LazyLoad=e()}(this,function(){"use strict";function o(t,e,n){var o,a,s,r,i,c,l,u=e._settings;!n&&_(t)||(y(u.callback_enter,t),-1<L.indexOf(t.tagName)&&(a=e,s=function t(e){I(e,!0,a),E(o,t,r)},l=r=function t(e){I(e,!1,a),E(o,s,t)},w(i=o=t,"load",c=s),w(i,"loadeddata",c),w(i,"error",l),p(t,u.class_loading)),function(t,e){var n=e._settings,o=t.tagName,a=m[o];if(a)return a(t,n),e._updateLoadingCount(1),e._elements=(s=e._elements,r=t,s.filter(function(t){return t!==r}));var s,r;!function(t,e){var n=v&&e.to_webp,o=d(t,e.data_src),a=d(t,e.data_bg);if(o){var s=g(o,n);t.style.backgroundImage='url("'+s+'")'}if(a){var r=g(a,n);t.style.backgroundImage=r}}(t,n)}(t,e),f(t,"was-processed","true"),y(u.callback_set,t))}var t,n={elements_selector:"img",container:document,threshold:300,thresholds:null,data_src:"src",data_srcset:"srcset",data_sizes:"sizes",data_bg:"bg",class_loading:"loading",class_loaded:"loaded",class_error:"error",load_delay:0,callback_load:null,callback_error:null,callback_set:null,callback_enter:null,callback_finish:null,to_webp:!1},d=function(t,e){return t.getAttribute("data-"+e)},f=function(t,e,n){var o="data-"+e;null!==n?t.setAttribute(o,n):t.removeAttribute(o)},_=function(t){return"true"===d(t,"was-processed")},l=function(t,e){return f(t,"ll-timeout",e)},u=function(t){return d(t,"ll-timeout")},a=function(t,e){var n,o=new t(e);try{n=new CustomEvent("LazyLoad::Initialized",{detail:{instance:o}})}catch(t){(n=document.createEvent("CustomEvent")).initCustomEvent("LazyLoad::Initialized",!1,!1,{instance:o})}window.dispatchEvent(n)},g=function(t,e){return e?t.replace(/\.(jpe?g|png)/gi,".webp"):t},e="undefined"!=typeof window,s=e&&!("onscroll"in window)||/(gle|ing|ro)bot|crawl|spider/i.test(navigator.userAgent),r=e&&"IntersectionObserver"in window,h=e&&"classList"in document.createElement("p"),v=e&&(!(!(t=document.createElement("canvas")).getContext||!t.getContext("2d"))&&0===t.toDataURL("image/webp").indexOf("data:image/webp")),c=function(t,e,n,o){for(var a,s=0;a=t.children[s];s+=1)if("SOURCE"===a.tagName){var r=d(a,n);b(a,e,r,o)}},b=function(t,e,n,o){n&&t.setAttribute(e,g(n,o))},m={IMG:function(t,e){var n=v&&e.to_webp,o=e.data_srcset,a=t.parentNode;a&&"PICTURE"===a.tagName&&c(a,"srcset",o,n);var s=d(t,e.data_sizes);b(t,"sizes",s);var r=d(t,o);b(t,"srcset",r,n);var i=d(t,e.data_src);b(t,"src",i,n)},IFRAME:function(t,e){var n=d(t,e.data_src);b(t,"src",n)},VIDEO:function(t,e){var n=e.data_src,o=d(t,n);c(t,"src",n),b(t,"src",o),t.load()}},p=function(t,e){h?t.classList.add(e):t.className+=(t.className?" ":"")+e},y=function(t,e){t&&t(e)},w=function(t,e,n){t.addEventListener(e,n)},i=function(t,e,n){t.removeEventListener(e,n)},E=function(t,e,n){i(t,"load",e),i(t,"loadeddata",e),i(t,"error",n)},I=function(t,e,n){var o,a,s=n._settings,r=e?s.class_loaded:s.class_error,i=e?s.callback_load:s.callback_error,c=t.target;o=c,a=s.class_loading,h?o.classList.remove(a):o.className=o.className.replace(new RegExp("(^|\\s+)"+a+"(\\s+|$)")," ").replace(/^\s+/,"").replace(/\s+$/,""),p(c,r),y(i,c),n._updateLoadingCount(-1)},L=["IMG","IFRAME","VIDEO"],C=function(t,e,n){o(t,n),e.unobserve(t)},O=function(t){var e=u(t);e&&(clearTimeout(e),l(t,null))},k=function(t){return t.isIntersecting||0<t.intersectionRatio},x=function(t,e){this._settings=_extends({},n,t),this._setObserver(),this._loadingCount=0,this.update(e)};return x.prototype={_manageIntersection:function(t){var e,n,o,a,s,r=this._observer,i=this._settings.load_delay,c=t.target;i?k(t)?(e=c,n=r,a=(o=this)._settings.load_delay,(s=u(e))||(s=setTimeout(function(){C(e,n,o),O(e)},a),l(e,s))):O(c):k(t)&&C(c,r,this)},_onIntersection:function(t){t.forEach(this._manageIntersection.bind(this))},_setObserver:function(){var t;r&&(this._observer=new IntersectionObserver(this._onIntersection.bind(this),{root:(t=this._settings).container===document?null:t.container,rootMargin:t.thresholds||t.threshold+"px"}))},_updateLoadingCount:function(t){this._loadingCount+=t,0===this._elements.length&&0===this._loadingCount&&y(this._settings.callback_finish)},update:function(t){var e=this,n=this._settings,o=t||n.container.querySelectorAll(n.elements_selector);this._elements=Array.prototype.slice.call(o).filter(function(t){return!_(t)}),!s&&this._observer?this._elements.forEach(function(t){e._observer.observe(t)}):this.loadAll()},destroy:function(){var e=this;this._observer&&(this._elements.forEach(function(t){e._observer.unobserve(t)}),this._observer=null),this._elements=null,this._settings=null},load:function(t,e){o(t,this,e)},loadAll:function(){var e=this;this._elements.forEach(function(t){e.load(t)})}},e&&function(t,e){if(e)if(e.length)for(var n,o=0;n=e[o];o+=1)a(t,n);else a(t,e)}(x,window.lazyLoadOptions),x});
var eveLazyLoad = new LazyLoad({
	elements_selector: '.loadlzly', callback_set: function (el) {
			el.classList.add('active');
	}
});

<?php if( wp_is_mobile() ): ?>
(function () {
!function(t){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=t();else if("function"==typeof define&&define.amd)define([],t);else{var e;"undefined"!=typeof window?e=window:"undefined"!=typeof global?e=global:"undefined"!=typeof self&&(e=self),e.Slideout=t()}}(function(){var t,e,n;return function i(t,e,n){function o(r,a){if(!e[r]){if(!t[r]){var u=typeof require=="function"&&require;if(!a&&u)return u(r,!0);if(s)return s(r,!0);var l=new Error("Cannot find module '"+r+"'");throw l.code="MODULE_NOT_FOUND",l}var h=e[r]={exports:{}};t[r][0].call(h.exports,function(e){var n=t[r][1][e];return o(n?n:e)},h,h.exports,i,t,e,n)}return e[r].exports}var s=typeof require=="function"&&require;for(var r=0;r<n.length;r++)o(n[r]);return o}({1:[function(t,e,n){"use strict";var i=t("decouple");var o=t("emitter");var s;var r=false;var a=window.document;var u=a.documentElement;var l=window.navigator.msPointerEnabled;var h={start:l?"MSPointerDown":"touchstart",move:l?"MSPointerMove":"touchmove",end:l?"MSPointerUp":"touchend"};var f=function v(){var t=/^(Webkit|Khtml|Moz|ms|O)(?=[A-Z])/;var e=a.getElementsByTagName("script")[0].style;for(var n in e){if(t.test(n)){return"-"+n.match(t)[0].toLowerCase()+"-"}}if("WebkitOpacity"in e){return"-webkit-"}if("KhtmlOpacity"in e){return"-khtml-"}return""}();function c(t,e){for(var n in e){if(e[n]){t[n]=e[n]}}return t}function p(t,e){t.prototype=c(t.prototype||{},e.prototype)}function d(t){while(t.parentNode){if(t.getAttribute("data-slideout-ignore")!==null){return t}t=t.parentNode}return null}function _(t){t=t||{};this._startOffsetX=0;this._currentOffsetX=0;this._opening=false;this._moved=false;this._opened=false;this._preventOpen=false;this.panel=t.panel;this.menu=t.menu;this._touch=t.touch===undefined?true:t.touch&&true;this._side=t.side||"left";this._easing=t.fx||t.easing||"ease";this._duration=parseInt(t.duration,10)||300;this._tolerance=parseInt(t.tolerance,10)||70;this._padding=this._translateTo=parseInt(t.padding,10)||256;this._orientation=this._side==="right"?-1:1;this._translateTo*=this._orientation;if(!this.panel.classList.contains("slideout-panel")){this.panel.classList.add("slideout-panel")}if(!this.panel.classList.contains("slideout-panel-"+this._side)){this.panel.classList.add("slideout-panel-"+this._side)}if(!this.menu.classList.contains("slideout-menu")){this.menu.classList.add("slideout-menu")}if(!this.menu.classList.contains("slideout-menu-"+this._side)){this.menu.classList.add("slideout-menu-"+this._side)}if(this._touch){this._initTouchEvents()}}p(_,o);_.prototype.open=function(){var t=this;this.emit("beforeopen");if(!u.classList.contains("slideout-open")){u.classList.add("slideout-open")}this._setTransition();this._translateXTo(this._translateTo);this._opened=true;setTimeout(function(){t.panel.style.transition=t.panel.style["-webkit-transition"]="";t.emit("open")},this._duration+50);return this};_.prototype.close=function(){var t=this;if(!this.isOpen()&&!this._opening){return this}this.emit("beforeclose");this._setTransition();this._translateXTo(0);this._opened=false;setTimeout(function(){u.classList.remove("slideout-open");t.panel.style.transition=t.panel.style["-webkit-transition"]=t.panel.style[f+"transform"]=t.panel.style.transform="";t.emit("close")},this._duration+50);return this};_.prototype.toggle=function(){return this.isOpen()?this.close():this.open()};_.prototype.isOpen=function(){return this._opened};_.prototype._translateXTo=function(t){this._currentOffsetX=t;this.panel.style[f+"transform"]=this.panel.style.transform="translateX("+t+"px)";return this};_.prototype._setTransition=function(){this.panel.style[f+"transition"]=this.panel.style.transition=f+"transform "+this._duration+"ms "+this._easing;return this};_.prototype._initTouchEvents=function(){var t=this;this._onScrollFn=i(a,"scroll",function(){if(!t._moved){clearTimeout(s);r=true;s=setTimeout(function(){r=false},250)}});this._preventMove=function(e){if(t._moved){e.preventDefault()}};a.addEventListener(h.move,this._preventMove);this._resetTouchFn=function(e){if(typeof e.touches==="undefined"){return}t._moved=false;t._opening=false;t._startOffsetX=e.touches[0].pageX;t._preventOpen=!t._touch||!t.isOpen()&&t.menu.clientWidth!==0};this.panel.addEventListener(h.start,this._resetTouchFn);this._onTouchCancelFn=function(){t._moved=false;t._opening=false};this.panel.addEventListener("touchcancel",this._onTouchCancelFn);this._onTouchEndFn=function(){if(t._moved){t.emit("translateend");t._opening&&Math.abs(t._currentOffsetX)>t._tolerance?t.open():t.close()}t._moved=false};this.panel.addEventListener(h.end,this._onTouchEndFn);this._onTouchMoveFn=function(e){if(r||t._preventOpen||typeof e.touches==="undefined"||d(e.target)){return}var n=e.touches[0].clientX-t._startOffsetX;var i=t._currentOffsetX=n;if(Math.abs(i)>t._padding){return}if(Math.abs(n)>20){t._opening=true;var o=n*t._orientation;if(t._opened&&o>0||!t._opened&&o<0){return}if(!t._moved){t.emit("translatestart")}if(o<=0){i=n+t._padding*t._orientation;t._opening=false}if(!(t._moved&&u.classList.contains("slideout-open"))){u.classList.add("slideout-open")}t.panel.style[f+"transform"]=t.panel.style.transform="translateX("+i+"px)";t.emit("translate",i);t._moved=true}};this.panel.addEventListener(h.move,this._onTouchMoveFn);return this};_.prototype.enableTouch=function(){this._touch=true;return this};_.prototype.disableTouch=function(){this._touch=false;return this};_.prototype.destroy=function(){this.close();a.removeEventListener(h.move,this._preventMove);this.panel.removeEventListener(h.start,this._resetTouchFn);this.panel.removeEventListener("touchcancel",this._onTouchCancelFn);this.panel.removeEventListener(h.end,this._onTouchEndFn);this.panel.removeEventListener(h.move,this._onTouchMoveFn);a.removeEventListener("scroll",this._onScrollFn);this.open=this.close=function(){};return this};e.exports=_},{decouple:2,emitter:3}],2:[function(t,e,n){"use strict";var i=function(){return window.requestAnimationFrame||window.webkitRequestAnimationFrame||function(t){window.setTimeout(t,1e3/60)}}();function o(t,e,n){var o,s=false;function r(t){o=t;a()}function a(){if(!s){i(u);s=true}}function u(){n.call(t,o);s=false}t.addEventListener(e,r,false);return r}e.exports=o},{}],3:[function(t,e,n){"use strict";var i=function(t,e){if(!(t instanceof e)){throw new TypeError("Cannot call a class as a function")}};n.__esModule=true;var o=function(){function t(){i(this,t)}t.prototype.on=function e(t,n){this._eventCollection=this._eventCollection||{};this._eventCollection[t]=this._eventCollection[t]||[];this._eventCollection[t].push(n);return this};t.prototype.once=function n(t,e){var n=this;function i(){n.off(t,i);e.apply(this,arguments)}i.listener=e;this.on(t,i);return this};t.prototype.off=function o(t,e){var n=undefined;if(!this._eventCollection||!(n=this._eventCollection[t])){return this}n.forEach(function(t,i){if(t===e||t.listener===e){n.splice(i,1)}});if(n.length===0){delete this._eventCollection[t]}return this};t.prototype.emit=function s(t){var e=this;for(var n=arguments.length,i=Array(n>1?n-1:0),o=1;o<n;o++){i[o-1]=arguments[o]}var s=undefined;if(!this._eventCollection||!(s=this._eventCollection[t])){return this}s=s.slice(0);s.forEach(function(t){return t.apply(e,i)});return this};return t}();n["default"]=o;e.exports=n["default"]},{}]},{},[1])(1)});
})();
var slideout = new Slideout({
	'panel': document.getElementById('slideout-panel'),
	'menu': document.getElementById('slideout-menu'),
	'side': 'right',
	'padding': 300,
	'tolerance': 100,
	'easing': 'ease-in-out',
	'touch': false
});
document.querySelector('.slideout-toggle-button').addEventListener('click', function() {
	slideout.toggle();
});
function close(eve) {
	eve.preventDefault();
	slideout.close();
}
slideout
.on('beforeopen', function() {
	this.panel.classList.add('panel-open');
})
.on('open', function() {
	this.panel.addEventListener('click', close);
})
.on('beforeclose', function() {
	this.panel.classList.remove('panel-open');
	this.panel.removeEventListener('click', close);
});
<?php endif; ?>

function renderImgToSvg() {
jQuery('.svg').each(function() {
	var $img = jQuery(this);
	var imgURL = $img.attr('src');
	var attributes = $img.prop("attributes");

	jQuery.get(imgURL, function(data) {
		// Get the SVG tag, ignore the rest
		var $svg = jQuery(data).find('svg');

		// Remove any invalid XML tags
		$svg = $svg.removeAttr('xmlns:a');

		// Loop through IMG attributes and apply on SVG
		jQuery.each(attributes, function() {
			$svg.attr(this.name, this.value);
		});

		// Replace IMG with SVG
		$img.replaceWith($svg);
	}, 'xml');
});
}

(function () {
	var wf = document.createElement('script');
	wf.src = '<?php echo get_template_directory_uri(); ?>/js/slick.min.js?v=<?php echo progresseve_verison_control(); ?>';
	wf.type = 'text/javascript';
	document.body.appendChild(wf);
})();

jQuery(document).ready(function ($) {
	(function (){console.log('<?php echo esc_html(get_bloginfo('name')); ?>');})();

	renderImgToSvg();

	$("a[href*='#']:not([href='#'])").click(function () {
			if (location.pathname.replace(/^\//, "") == this.pathname.replace(/^\//, "") && location.hostname == this.hostname) {
					var t = $(this.hash);
					if ((t = t.length ? t : $("[name=" + this.hash.slice(1) + "]")).length) return $("html,body").animate({scrollTop: t.offset().top}, 1e3), !1
			}
	});
});
</script><?php

	}
endif;


if ( !function_exists( 'eve_theme_icons' ) ) :
	/**
	 * Theme svg icons
	 */
  function eve_theme_icons()
  {
?><svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" display="none">
<symbol id="i-logo" viewBox="0 0 119.971 37.627"><g transform="translate(-54.562 -307.861)"><g transform="translate(95.081 318.372)"><path d="M217.507,359.375v-7.932h3.211a4.919,4.919,0,0,1,1.823.313,3.782,3.782,0,0,1,1.317.849,3.534,3.534,0,0,1,.8,1.257,4.338,4.338,0,0,1,.27,1.536,4.278,4.278,0,0,1-.3,1.637,3.531,3.531,0,0,1-.852,1.251,3.853,3.853,0,0,1-1.329.8,4.986,4.986,0,0,1-1.729.285ZM222.6,355.4a2.6,2.6,0,0,0-.129-.838,1.861,1.861,0,0,0-.371-.648,1.661,1.661,0,0,0-.594-.419,1.983,1.983,0,0,0-.788-.151H219.8v4.134h.917a1.955,1.955,0,0,0,.8-.156,1.664,1.664,0,0,0,.594-.436,1.883,1.883,0,0,0,.365-.659A2.66,2.66,0,0,0,222.6,355.4Z" transform="translate(-217.475 -351.376)"/><path d="M260.337,351.443h2.093l2.893,7.932h-2.329l-.506-1.553h-2.223l-.494,1.553h-2.34Zm1.788,4.927-.741-2.458-.776,2.458Z" transform="translate(-247.709 -351.376)"/><path d="M305.4,353.825a6.628,6.628,0,0,0-.729-.358,6.882,6.882,0,0,0-.711-.251,2.687,2.687,0,0,0-.771-.117,1.12,1.12,0,0,0-.488.09.31.31,0,0,0-.182.3.339.339,0,0,0,.106.257,1.007,1.007,0,0,0,.306.184,3.986,3.986,0,0,0,.488.162q.288.078.653.19a8.878,8.878,0,0,1,1.041.369,3.253,3.253,0,0,1,.794.475,1.838,1.838,0,0,1,.506.665,2.3,2.3,0,0,1,.176.949,2.4,2.4,0,0,1-.276,1.2,2.2,2.2,0,0,1-.729.777,3.17,3.17,0,0,1-1.023.419,5.2,5.2,0,0,1-1.147.129,6.833,6.833,0,0,1-.941-.067,8.56,8.56,0,0,1-.958-.19,8.762,8.762,0,0,1-.918-.29,6.008,6.008,0,0,1-.818-.38l.988-1.91a7.042,7.042,0,0,0,.858.436,6.3,6.3,0,0,0,.853.3,3.567,3.567,0,0,0,.97.134,1.029,1.029,0,0,0,.523-.1.288.288,0,0,0,.147-.251.352.352,0,0,0-.147-.285,1.366,1.366,0,0,0-.406-.207q-.259-.09-.594-.179t-.712-.212a6.313,6.313,0,0,1-.952-.385,2.573,2.573,0,0,1-.659-.469,1.669,1.669,0,0,1-.382-.6,2.225,2.225,0,0,1-.124-.771,2.553,2.553,0,0,1,.253-1.162,2.409,2.409,0,0,1,.688-.838,3.133,3.133,0,0,1,.994-.508,3.968,3.968,0,0,1,1.182-.173,4.7,4.7,0,0,1,.894.084,7.449,7.449,0,0,1,.841.212q.406.129.759.279t.635.285Z" transform="translate(-279.778 -351.166)"/><path d="M362.285,359.375v-7.932h5.728v1.9h-3.434v1.318h2.8v1.765h-2.8v2.95Z" transform="translate(-327.112 -351.376)"/><path d="M396.878,351.443h2.093l2.893,7.932h-2.329l-.506-1.553h-2.223l-.494,1.553h-2.341Zm1.788,4.927-.741-2.458-.776,2.458Z" transform="translate(-351.109 -351.376)"/><path d="M439.035,359.375v-7.932h2.294v6.033h3.787v1.9Z" transform="translate(-385.233 -351.376)"/><path d="M477.071,359.375v-7.932h2.293v3.017l2.54-3.017h2.587L481.458,355l3.246,4.38h-2.635L480,356.46l-.635.625v2.291Z" transform="translate(-414.037 -351.376)"/><path d="M526.145,357.476v1.9h-5.951v-7.932h5.845v1.9h-3.552v1.117h3.034v1.765h-3.034v1.251Z" transform="translate(-446.693 -351.376)"/><path d="M217.375,403.4v-4.456H219.4a1.485,1.485,0,0,1,.634.135,1.617,1.617,0,0,1,.826.847,1.422,1.422,0,0,1,.119.562,1.53,1.53,0,0,1-.112.575,1.6,1.6,0,0,1-.314.5,1.494,1.494,0,0,1-.489.345,1.525,1.525,0,0,1-.631.129h-.766V403.4Zm1.288-2.435h.681a.308.308,0,0,0,.221-.1.53.53,0,0,0,.1-.374.477.477,0,0,0-.119-.376.37.37,0,0,0-.238-.1h-.647Z" transform="translate(-217.375 -387.346)"/><path d="M263.965,402.332V403.4h-3.343v-4.456h3.283v1.067H261.91v.627h1.7v.992h-1.7v.7Z" transform="translate(-250.125 -387.346)"/><path d="M303.163,403.4v-4.456h2.154a1.487,1.487,0,0,1,.634.135,1.62,1.62,0,0,1,.826.847,1.421,1.421,0,0,1,.119.562,1.524,1.524,0,0,1-.178.725,1.508,1.508,0,0,1-.5.549l.991,1.638h-1.454l-.826-1.368h-.483V403.4Zm1.288-2.435h.813a.288.288,0,0,0,.221-.126.542.542,0,0,0,.1-.351.489.489,0,0,0-.119-.355.339.339,0,0,0-.238-.122h-.78Z" transform="translate(-282.34 -387.346)"/><path d="M349.676,400.283a3.758,3.758,0,0,0-.41-.2,3.873,3.873,0,0,0-.4-.141,1.5,1.5,0,0,0-.433-.066.627.627,0,0,0-.274.05.175.175,0,0,0-.1.169.191.191,0,0,0,.059.144.565.565,0,0,0,.172.1,2.237,2.237,0,0,0,.274.091q.162.044.367.107a4.927,4.927,0,0,1,.585.207,1.821,1.821,0,0,1,.446.267,1.033,1.033,0,0,1,.284.373,1.293,1.293,0,0,1,.1.533,1.35,1.35,0,0,1-.155.675,1.235,1.235,0,0,1-.41.436,1.787,1.787,0,0,1-.575.235,2.926,2.926,0,0,1-.644.072,3.818,3.818,0,0,1-.529-.038,4.738,4.738,0,0,1-.538-.106,4.979,4.979,0,0,1-.515-.163,3.4,3.4,0,0,1-.459-.214l.555-1.073a3.88,3.88,0,0,0,.482.245,3.469,3.469,0,0,0,.479.169,2,2,0,0,0,.545.075.573.573,0,0,0,.294-.054.161.161,0,0,0,.083-.141.2.2,0,0,0-.083-.16.765.765,0,0,0-.228-.116q-.146-.05-.334-.1t-.4-.119a3.557,3.557,0,0,1-.535-.217,1.434,1.434,0,0,1-.37-.264.928.928,0,0,1-.215-.336,1.249,1.249,0,0,1-.069-.433,1.432,1.432,0,0,1,.142-.652,1.349,1.349,0,0,1,.387-.471,1.755,1.755,0,0,1,.558-.285,2.23,2.23,0,0,1,.664-.1,2.653,2.653,0,0,1,.5.047,4.245,4.245,0,0,1,.472.119q.228.072.426.157t.357.16Z" transform="translate(-315.172 -387.23)"/><path d="M392.218,403.359a2.359,2.359,0,0,1-.971-.194,2.41,2.41,0,0,1-.746-.512,2.307,2.307,0,0,1-.482-.725,2.146,2.146,0,0,1-.172-.841,2.093,2.093,0,0,1,.178-.847,2.234,2.234,0,0,1,.5-.719,2.446,2.446,0,0,1,1.721-.681,2.355,2.355,0,0,1,1.718.709,2.293,2.293,0,0,1,.479.728,2.164,2.164,0,0,1,.169.834,2.074,2.074,0,0,1-.178.844,2.277,2.277,0,0,1-.5.716,2.449,2.449,0,0,1-.756.5A2.417,2.417,0,0,1,392.218,403.359Zm-1.063-2.259a1.45,1.45,0,0,0,.066.436,1.13,1.13,0,0,0,.2.373.989.989,0,0,0,.333.264,1.183,1.183,0,0,0,.961,0,.965.965,0,0,0,.333-.27,1.137,1.137,0,0,0,.192-.38,1.521,1.521,0,0,0,.063-.433,1.453,1.453,0,0,0-.066-.436,1.051,1.051,0,0,0-.2-.37,1.008,1.008,0,0,0-.337-.258,1.077,1.077,0,0,0-.473-.1,1.058,1.058,0,0,0-.479.1.972.972,0,0,0-.334.266,1.114,1.114,0,0,0-.195.377A1.494,1.494,0,0,0,391.155,401.1Z" transform="translate(-347.984 -387.268)"/><path d="M439.308,401.209v2.19H438.02v-4.456h1l1.883,2.266v-2.266H442.2V403.4h-1.024Z" transform="translate(-384.464 -387.346)"/><path d="M484.751,398.943h1.176l1.625,4.456h-1.308l-.284-.872h-1.248l-.277.872h-1.315Zm1,2.768-.416-1.381-.436,1.381Z" transform="translate(-418.617 -387.346)"/><path d="M529.277,403.4v-4.456h1.288v3.389h2.127V403.4Z" transform="translate(-453.571 -387.346)"/></g><path d="M80.718,337.678A25.64,25.64,0,0,1,74.8,342.9l-.013-.03h0l0-.006c-.079-.176-.151-.349-.225-.522a26.056,26.056,0,0,0-6.934-8.3,51.01,51.01,0,0,1,5.371,3.576c-1.24-5.75.952-8.827,4.478-10.391,4.078-1.638,5.74-1.332,6.433-1.285a1.948,1.948,0,0,1,1.667,2.6c-.039.106-.079.212-.133.355a3.916,3.916,0,0,0,1.126-.963,3.471,3.471,0,0,0,.764-2.143,3.368,3.368,0,0,0-.653-2.189,4.819,4.819,0,0,0-1.284-1.1,6.721,6.721,0,0,0-3.46-1.089,5.04,5.04,0,0,0-2.289.417c-.151.068-.276.211-.411.282a.279.279,0,0,1-.112.034h0l-2.466.3a.239.239,0,0,1-.04,0,.263.263,0,0,1-.124-.492l1.165-.652a.211.211,0,0,1,.123-.034,1.745,1.745,0,0,0,1.512.076,8.7,8.7,0,0,1,1.921-.335.48.48,0,0,0,.169-.028,2.635,2.635,0,0,1-.655-.334c-.817-.459-1.465-1.442-2.392-1.6a10.155,10.155,0,0,0-2.454-.065c-4.728.4-11.331,2.1-13.319,5.779a2.258,2.258,0,0,0-.169.345l-.015.031,0,0a2.284,2.284,0,0,0,.591,2.513c.1.089.2.176.332.3a4.587,4.587,0,0,1-1.693-.379,4.066,4.066,0,0,1-1.966-1.8,3.946,3.946,0,0,1-.524-2.624,5.643,5.643,0,0,1,.725-1.846,7.383,7.383,0,0,1,2.984-3.025,22.553,22.553,0,0,1,5.492-1.938c.313-.088.226-.6-.089-.682l-1.729-.138c-.289-.045-.405.385-.405.385a1.224,1.224,0,0,1-.6.692c-.682.357-1.642.65-1.956.8-.044.02-.394.217-.394.217a2.184,2.184,0,0,0,.486-.739,8.527,8.527,0,0,1,.953-2.971,4.153,4.153,0,0,1,1.429-1.508c2.057-1.079,7.146-2.224,12.082-1.675a20.314,20.314,0,0,1,5.709,1.438,23.06,23.06,0,0,0-6.428-3.477,31.456,31.456,0,0,0-22.6,3.442,29.631,29.631,0,0,0,1.274,12.735c2.422,7.312,9.307,16.729,18.457,20.646A12.566,12.566,0,0,0,80.718,337.678Z"/></g></symbol>
<symbol id="i-search" viewBox="0 0 16.13 16.07"><path d="M776.56,1031.56a1.49,1.49,0,0,1-2.12,0h0l-2.66-2.65a7,7,0,1,1,2.13-2.13l2.65,2.66a1.46,1.46,0,0,1,.12,2.08A.15.15,0,0,1,776.56,1031.56ZM768,1018a5,5,0,1,0,5,5,5,5,0,0,0-5-5Z" transform="translate(-760.93 -1015.93)"/></symbol>
<symbol id="i-green-ok" viewBox="0 0 31.881 31.881"><path d="M47.941,32A15.941,15.941,0,1,0,63.881,47.941,15.942,15.942,0,0,0,47.941,32Zm8.177,10.61-9.9,12.7c-.078.078-.206.249-.363.249a.524.524,0,0,1-.363-.206c-.093-.093-5.615-5.4-5.615-5.4l-.107-.107a.37.37,0,0,1,0-.455.931.931,0,0,0,.078-.085c.548-.576,1.658-1.743,1.729-1.815.093-.093.171-.213.342-.213s.292.149.377.235,3.2,3.081,3.2,3.081l7.92-10.176a.406.406,0,0,1,.249-.1.4.4,0,0,1,.249.093L56.1,42.127a.4.4,0,0,1,.093.249A.369.369,0,0,1,56.117,42.61Z" transform="translate(-32 -32)"/></symbol>
<symbol id="i-facebook" viewBox="0 0 22.48 22.48"><path d="M769,1035.48H758.41a1.41,1.41,0,0,1-1.41-1.4V1014.4a1.41,1.41,0,0,1,1.41-1.4h19.67a1.4,1.4,0,0,1,1.4,1.4v19.68a1.4,1.4,0,0,1-1.4,1.4h-5.62v-8.43h2.81l.7-3.51h-3.51v-1.4a1.87,1.87,0,0,1,1.59-2.11,2.26,2.26,0,0,1,.52,0H776v-3.51h-2.81a3.89,3.89,0,0,0-3.08,1.37,5.45,5.45,0,0,0-1.14,3.54v2.11h-2.81v3.51H769Z" transform="translate(-757 -1013)"/></symbol>
<symbol id="i-instagram" viewBox="0 0 22.49 22.5"><g><path d="M768.24,1018.39a5.8,5.8,0,1,0,5.8,5.8h0A5.82,5.82,0,0,0,768.24,1018.39Zm0,9.52a3.72,3.72,0,1,1,3.72-3.72A3.72,3.72,0,0,1,768.24,1027.91Z" transform="translate(-756.99 -1012.99)"/><circle cx="17.28" cy="5.27" r="1.31"/><path d="M777.67,1014.86a6.46,6.46,0,0,0-4.76-1.86h-9.34a6.21,6.21,0,0,0-6.57,5.83c0,.24,0,.49,0,.74v9.29a6.51,6.51,0,0,0,1.9,4.85,6.59,6.59,0,0,0,4.71,1.77h9.25a6.64,6.64,0,0,0,4.76-1.77,6.49,6.49,0,0,0,1.86-4.8v-9.34A6.53,6.53,0,0,0,777.67,1014.86Zm-.18,14.05a4.42,4.42,0,0,1-1.32,3.31,4.66,4.66,0,0,1-3.31,1.18h-9.24a4.67,4.67,0,0,1-3.31-1.18,4.57,4.57,0,0,1-1.23-3.35v-9.3a4.15,4.15,0,0,1,4.54-4.48H773a4.49,4.49,0,0,1,3.31,1.22,4.64,4.64,0,0,1,1.23,3.26Z" transform="translate(-756.99 -1012.99)"/></g></symbol>
<symbol id="i-linkedin" viewBox="0 0 22.48 22.48"><path d="M778.08,1035.48H758.4a1.4,1.4,0,0,1-1.4-1.4V1014.4a1.4,1.4,0,0,1,1.4-1.4h19.68a1.4,1.4,0,0,1,1.4,1.4v19.68A1.4,1.4,0,0,1,778.08,1035.48Zm-7.73-11.94a1.27,1.27,0,0,1,1.4,1.11.76.76,0,0,1,0,.3v5.62h2.81v-4.92a8.28,8.28,0,0,0-.53-3.52,2.64,2.64,0,0,0-2.63-1.4c-2,0-2.3.72-2.46,1.41v-1.41h-2.8v9.84h2.8V1025a1.27,1.27,0,0,1,1.12-1.41Zm-8.43-2.81v9.84h2.81v-9.84Zm1.4-3.51a1.41,1.41,0,1,0,1.41,1.4A1.4,1.4,0,0,0,763.32,1017.22Z" transform="translate(-757 -1013)"/></symbol>
<symbol id="i-arrow" viewBox="0 0 8.08 13.29"><path d="M766.41,1030.22a1.51,1.51,0,0,1-1-.4,1.41,1.41,0,0,1,0-2l4.22-4.23-4.23-4.23a1.4,1.4,0,0,1,1.83-2.11l.14.14,5.22,5.21a1.4,1.4,0,0,1,.4,1,1.46,1.46,0,0,1-.4,1l-5.2,5.22A1.51,1.51,0,0,1,766.41,1030.22Z" transform="translate(-764.92 -1016.92)"/></symbol>
<symbol id="i-search" viewBox="0 0 16.13 16.07"><path d="M776.56,1031.56a1.49,1.49,0,0,1-2.12,0h0l-2.66-2.65a7,7,0,1,1,2.13-2.13l2.65,2.66a1.46,1.46,0,0,1,.12,2.08A.15.15,0,0,1,776.56,1031.56ZM768,1018a5,5,0,1,0,5,5,5,5,0,0,0-5-5Z" transform="translate(-760.93 -1015.93)"/></symbol>
<symbol id="i-pin" viewBox="0 0 12 16"><path d="M762,1022a6,6,0,0,1,12,0c0,4-6,10-6,10s-6-6-6-10m3.5,0a2.5,2.5,0,1,0,2.5-2.5A2.5,2.5,0,0,0,765.5,1022Z" transform="translate(-762 -1016)"/></symbol>
<symbol id="i-location" viewBox="0 0 16 16"><path d="M769,1029.92a6,6,0,0,0,4.92-4.92H771v-2h2.92a6,6,0,0,0-4.92-4.92V1021h-2v-2.92a6,6,0,0,0-4.92,4.92H765v2h-2.92a6,6,0,0,0,4.92,4.92V1027h2Zm-1,2.08a8,8,0,1,1,8-8A8,8,0,0,1,768,1032Z" transform="translate(-760 -1016)"/></symbol>
<symbol id="i-info" viewBox="0 0 15.794 15.794"><path d="M7.9,0a7.9,7.9,0,1,0,7.9,7.9A7.9,7.9,0,0,0,7.9,0ZM7.858,12.316a1.491,1.491,0,0,1-.617.242l-.035.016v-.016a.8.8,0,0,1-.159-.023l-.079-.02a.785.785,0,0,1-.56-.979l.609-2.455.275-1.109c.256-1.029-.811.219-1.029-.255C6.118,7.4,7.091,6.746,7.8,6.251a1.492,1.492,0,0,1,.617-.242l.036-.016v.016a.815.815,0,0,1,.159.023l.079.02a.816.816,0,0,1,.6.988L8.679,9.494,8.4,10.6c-.255,1.029.793-.224,1.012.251C9.56,11.167,8.569,11.82,7.858,12.316ZM9.4,4.643A1.142,1.142,0,1,1,8.567,3.26,1.142,1.142,0,0,1,9.4,4.643Z" transform="translate(0 -0.003)"/></symbol>
<symbol id="i-profile" viewBox="0 0 48 48"><path d="M35,47H13c-2.209,0-4-1.791-4-4V13c0-2.209,1.791-4,4-4h7V5c0-2.209,1.791-4,4-4  s4,1.791,4,4v4h7c2.209,0,4,1.791,4,4v30C39,45.209,37.209,47,35,47z M26,5c0-1.104-0.896-2-2-2s-2,0.896-2,2v6c0,1.104,0.896,2,2,2  s2-0.896,2-2V5z M37,13c0-1.104-0.896-2-2-2h-7c0,2.209-1.791,4-4,4s-4-1.791-4-4h-7c-1.104,0-2,0.896-2,2v24h3.797  c0.231-0.589,0.656-1.549,1.16-2.24c0.025-0.014,0.848-1.739,4.998-1.79c0.006-0.021,0.01-1.042,0.022-1.027  c-0.32-0.202-0.737-0.516-1.051-0.816c-0.255-0.156-1.161-1.029-1.452-2.583c-0.087-0.542-0.488-3.099-0.488-4.166  c0-3.171,1.265-6.381,5.953-6.381c0.021,0,0.1,0,0.121,0c4.688,0,5.953,3.21,5.953,6.381c0,1.067-0.401,3.624-0.488,4.166  c-0.291,1.554-1.196,2.427-1.452,2.583c-0.313,0.301-0.73,0.614-1.051,0.816c0.013-0.015,0.018,1.007,0.022,1.027  c4.151,0.051,4.974,1.776,4.998,1.79c0.504,0.691,0.929,1.651,1.16,2.24H37V13z M25.014,31.488  c-0.001,0.003-0.001,0.004-0.001,0.004c-0.003,0-0.017-0.781-0.017-0.781s1.166-0.601,2.031-1.378  c0.507-0.417,0.741-1.362,0.741-1.362c0.137-0.828,0.238-2.877,0.238-3.703c0-2.062-1.033-4.28-4.007-4.28c0,0,0-0.006,0-0.007  c0,0,0,0.007,0,0.007c-2.974,0-4.007,2.219-4.007,4.28c0,0.826,0.103,2.875,0.238,3.703c0,0,0.234,0.945,0.741,1.362  c0.865,0.777,2.031,1.378,2.031,1.378s-0.014,0.781-0.018,0.781c0,0,0-0.001,0-0.004c0,0,0.029,1.146,0.029,1.486  c0,1.363-1.365,2.019-2.223,2.019c-0.002,0-0.002,0-0.003,0c-2.593,0.114-3.205,0.976-3.21,0.984  C17.419,36.23,17.2,36.482,16.998,37h14.005c-0.203-0.518-0.422-0.77-0.582-1.022c-0.006-0.009-0.619-0.87-3.211-0.984  c-0.001,0-0.001,0-0.002,0c-0.858,0-2.224-0.655-2.224-2.019C24.984,32.634,25.014,31.488,25.014,31.488z M37,39H11v4  c0,1.104,0.896,2,2,2h22c1.104,0,2-0.896,2-2V39z M31,43h-3c-0.553,0-1-0.447-1-1s0.447-1,1-1h3c0.553,0,1,0.447,1,1  S31.553,43,31,43z M23,43h-6c-0.553,0-1-0.447-1-1s0.447-1,1-1h6c0.553,0,1,0.447,1,1S23.553,43,23,43z M23,5h2v2h-2V5z"/></symbol>
<symbol id="i-foo" viewBox="0 0 10 10"></symbol>
</svg>
<?php 
  }
endif;