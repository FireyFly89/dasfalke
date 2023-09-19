<?php

/* Template Name: Falke Profile Template */

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

get_header();

$current_user_id = $current_user->ID;

// BUY COMPANY FEATURED RELATED STUFF
$product_obj = "";
$full_product_obj = 0;
$company_featured = $bundleManager->get_slugs_by_typecategory('company_features');

if (!empty($company_featured[0])) {
    $product_obj = get_page_by_path($company_featured[0], OBJECT, 'product');

    if (is_object($product_obj) && property_exists($product_obj, 'ID')) {
        $full_product_obj = wc_get_product($product_obj->ID);
    }
}
?>

<?php

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_page_sidebar_content_wrappers_start      - 10
 * @hooked eve_page_sidebar_content_main_start          - 20
 */

do_action('page_sidebar_before_content'); ?>

    <main>
        <div>
            <?php
            while (have_posts()) {
                the_post();
                get_template_part('template-parts/content', 'page-simple');
            }

            wp_reset_query();
            ?>
        </div>
    </main>

<?php

/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_page_sidebar_content_main_end      - 10
 * @hooked eve_page_sidebar_aside_wrp_start       - 20
 */

do_action('page_sidebar_between_content'); ?>

    <aside>
        <div class="profile sidebar">
            <div class="profile__img-wrp">
                <div class="profile__img-psn company">
                    <img class="profile__img" src="<?php echo get_user_avatar(); ?>"
                         alt="<?php echo get_user_name($current_user_id); ?>">
                </div>
            </div>
            <div class="profile__name"><?php echo get_user_name($current_user_id); ?></div>
            <div class="profile__nav-list">
                <?php

                /**
                 * Functions hooked in to profile navigation
                 *
                 * @hooked woocommerce_account_navigation      - 100
                 */

                do_action('profile_sidebar_navlist'); ?>
            </div>
        </div>

        <?php if (eve_is_user_employer()): ?>
            <div class="aside-footer">
                <?php if (!is_add_job_page()) : ?>
                    <!-- Ügyfél kérésére    <a href="/my-account/add-job" class="df-btn primary"><?php _e('ADD NEW LISTING', 'dasfalke-profile'); ?></a> -->
                <?php endif; ?>
                <!-- Ügyfél kérésére
        <p><strong><?php _e('Need help?', 'dasfalke-profile'); ?></strong></p>
        <a href="<?php echo apply_filters( 'wpml_home_url', get_option( 'home' ) ); ?>/contact/" class="df-btn outline"><?php _e('CONTACT US', 'dasfalke-profile'); ?></a> -->
            </div>

            <!-- BUY COMPANY FEATURED -->
            <div class="job-page__advopts">
                <div class="job-page__advopts-row">
                    <div class="job-page__advopts-col">
                        <div class="job-page__advopt-title"><?php _e($product_obj->post_title, 'dasfalke-product'); ?></div>
                        <div class="job-page__advopt-desc"><?php _e($product_obj->post_content, 'dasfalke-product'); ?></div>
                        <div class="job-page__advopt-icon"><img src="<?php echo get_template_directory_uri(); ?>/img/service-company_features.png" alt="Company feature"></div>

                        <?php if (!empty($company_featured_data = is_company_has_featured($current_user_id))) : ?>
                            <div class="job-page__advopt-option">
                                <div class="job-list__item-feature active" title="Active" style="float:none;">
                                    <span><?php echo sprintf(__('%s days left', 'dasfalke-jobpage'), $bundleManager->seconds_to_days($company_featured_data['expires_in'])); ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <form>
                                <div class="job-page__advopt-option">
                                    <input type="hidden" name="add-to-cart" value="<?php echo $product_obj->ID; ?>"/>
                                    <div class="job-page__advopt-price"><?php echo eve_get_formatted_price($full_product_obj->get_price()); ?></div>
                                    <div class="job-page__advopt-timeframe">
                                        <span><?php echo $bundleManager->get_product_field_by_slug($company_featured[0], 'expires_in'); ?></span>&nbsp;<?php _e('days', 'dasfalke-jobpage'); ?>
                                    </div>
                                </div>
                                <div class="job-page__advopt-action"><input class="df-btn primary block small" type="submit" value="<?php _e('BUY NOW', 'dasfalke-jobpage') ?>">
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </aside>

<?php
/**
 * Functions hooked in to Sidebar Template before content
 *
 * @hooked eve_page_sidebar_aside_wrp_end             - 10
 * @hooked eve_page_sidebar_content_wrappers_end      - 20
 */

do_action('page_sidebar_after_content'); ?>
<?php
get_footer();
