<?php

/* Template Name: Falke Pricing */

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

$textdomain = 'dasfalke-pricing';

?>
<div class="pricing">
  <div class="ctn max">
    <div class="content-area">
      <div class="content-main">
        <div class="row">
          <div class="col-sm-6">
            <div class="price_ill">
              <div class="price_ill-img-wrp">
                <div class="price_ill-img-psn"><img class="price_ill-img"
                    src="<?php echo get_template_directory_uri(); ?>/img/pricing-full-pack.png"
                    data-full-pack-img="<?php echo get_template_directory_uri(); ?>/img/pricing-full-pack.png"
                    data-half-pack-img="<?php echo get_template_directory_uri(); ?>/img/pricing-half-pack.png"
                    data-standard-pack-img="<?php echo get_template_directory_uri(); ?>/img/pricing-standard-pack.png"
                    data-half-standard-pack-img="<?php echo get_template_directory_uri(); ?>/img/pricing-half-standard-pack.png"
                    alt="Das Falke Personal Job Postings"></div>
              </div>
              <h2 class="price_ill-title"><?php _e('5x More Applicants With Our Job Postings', $textdomain); ?></h2>
              <div class="price_ill-desc">
                <?php _e('75% of the job searches happen on mobile devices. Advertise on dasfalkepersonal.at so applicants could apply for your job postings from any mobile devices.', $textdomain); ?>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <main>
              <?php
                $featureds = [];
                $bundle = null;

                if (class_exists('DFPBManager')) : ?>
              <?php
                $bundleManager = new DFPBManager();
                $bundles = $bundleManager->get_falke_products($bundleManager->get_plugin_product_slugs());

                if (!empty($bundles)) {
                    while ($bundles->have_posts()) {
                        $bundles->the_post();
                    }
                    wp_reset_query();

                    foreach($bundles->posts as $product_post) {
                        if ($product_post->post_name === $bundleManager->get_main_product_slug()) {
                            $bundle = wc_get_product($product_post->ID);
                        } else if (strpos($product_post->post_name, 'feature-bundle-') !== false) {
                            $featureds[] = wc_get_product($product_post->ID);
                        }
                    }
                }
                ?>

              <div class="product-wrapper" data-product="<?php echo $bundle->get_id(); ?>">
                <h1><?php _e('Please choose the best combination for you', $textdomain); ?></h1>
                <h2 class="price js-price">€<span><?php echo $bundle->get_price(); ?></span></h2>
                <!-- <div class="price-posts"><?php _e('<span class="quantity-count">1</span> job posting(s) <grey>in the next</grey> 365 days', $textdomain); ?></div> -->
                <form>
                  <input type="hidden" name="add-to-cart" value="<?php echo $bundle->get_id(); ?>" />
                  <div class="price-size">
                    <label class="price-group-label"><?php _e('NUMBER OF JOB POSTINGS', $textdomain); ?></label>
                    <div class="price-input-wrap shd">
                      <input class="button-minus quantity-minus" type="button" value="-" />
                      <input class="product-quantity" type="number" name="quantity" value="1" />
                      <input class="button-plus quantity-plus" type="button" value="+" />
                    </div>
                  </div>
                  <div class="price-options">
                    <label class="price-group-label"><?php _e('OPTIONS', $textdomain); ?></label>
                    <div class="price-list">
                      <div class="price-list-item">
                        <label><?php _e('Your job posting is visible for 60 days. You can modify your posting anytime.', 'dasfalke-pricing') ;?>&nbsp;
                          <div class="price-list-item__info"><svg class="i i-info" width="16" height="14">
                              <use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-info" href="#i-info"></use>
                            </svg>
                            <div class="price-list-item__info-content shd">
                              <?php _e('One week and one day before the 60 days have expired, you will receive an e-mail from us reminding you that your ad is about to expire. You cannot extend the duration of your posting but its text will remain visible to you.', $textdomain); ?>
                            </div>
                          </div></label>
                        <svg class="i i-green-ok" width="32" height="32">
                          <use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-green-ok" href="#i-green-ok"></use>
                        </svg>
                      </div>
                      <?php 
                        foreach($featureds as $featured) :
                          $tooltip = "";

                          if ($featured->get_slug() === 'feature-bundle-homepage') {
                              echo '<input style="display: none;" type="checkbox" name="' . $featured->get_slug() . '" class="onoffswitch-checkbox" id="' . $featured->get_slug() . '" checked>';
                              continue;
                          } else if ($featured->get_slug() === 'feature-bundle-pre-sort') {
                              $tooltip = __('Your posting will rotate up the search results to attract even more applicants.', $textdomain);
                          } else if ($featured->get_slug() === 'feature-bundle-highlight') {
                              $tooltip = __('Your posting will be rotated on the front page and highlighted in color in the search results.', $textdomain);;
                          }
                        ?>
                      <div class="price-list-item">
                        <label><?php _e($featured->get_description(), $textdomain);?>&nbsp;<div
                            class="price-list-item__info"><svg class="i i-info" width="16" height="14">
                              <use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-info" href="#i-info"></use>
                            </svg>
                            <div class="price-list-item__info-content shd"><?php _e($tooltip, $textdomain); ?></div>
                          </div></label>
                        <div class="onoffswitch">
                          <input type="checkbox" name="<?php echo $featured->get_slug() ?>" class="onoffswitch-checkbox"
                            id="<?php echo $featured->get_slug() ?>" checked>
                          <label class="onoffswitch-label" for="<?php echo $featured->get_slug() ?>"></label>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <div class="submit-wrap">
                    <div><input class="df-btn primary submit-btn" type="submit"
                        value="<?php _e('BUY NOW', $textdomain); ?>" /></div><br><br>
                    <div>
                      <span class="submit-wrap__card-icon wc-credit-card-form-card-cvc visa"></span>
                      <span class="submit-wrap__card-icon wc-credit-card-form-card-cvc mastercard"></span>
                      <span class="submit-wrap__card-icon wc-credit-card-form-card-cvc maestro"></span>
                      <span class="submit-wrap__card-icon wc-credit-card-form-card-cvc paypal"></span>
                      <span class="submit-wrap__card-icon wc-credit-card-form-card-cvc sofort"></span>
                    </div>
                  </div>
                </form>
              </div>
              <?php else : ?>
              <?php _e('Missing package', $textdomain); ?>
              <?php endif; ?>
            </main>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php

get_footer();