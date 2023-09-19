<?php

/**
 * Class DFPBManager
 *
 * Das Falke Personal Bundle Manager
 * @package Das Falke Personal Bundle plugins
 */
class DFPBManager extends DFPBTransactionManager
{
    private $feature_keys = [];
    private $main_product_quantity = 0;

    /**
     * DFPBManager constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (defined('WP_CLI') || is_admin()) {
            $this->install_admin_hooks();
        }

        if (!wp_doing_cron()) {
            add_filter('woocommerce_add_to_cart_validation', [$this, 'validate_before_checkout'], 10, 5);
            add_action('woocommerce_order_status_changed', [$this, 'catch_order_change'], 99, 3);
            add_action('woocommerce_before_calculate_totals', [$this, 'recalculate_prices'], 20);
            add_action('woocommerce_before_calculate_totals', [$this, 'equalize_feature_quantity'], 50);
            add_action('woocommerce_add_order_item_meta', [$this, 'add_order_item_meta'], 1, 2);
            add_action('wp_enqueue_scripts', [$this, 'bundle_manager_scripts']);
        } else {
            $this->product_expirer();

            if (array_key_exists('daily', $_GET)) {
                add_action('init', [$this, 'register_custom_taxonomies']);
                add_action('wp_mail_failed', [$this, 'action_wp_mail_failed']);
                add_action('init', [$this, 'daily_mails']);
            }
        }
    }

    function action_wp_mail_failed($wp_error)
    {
        error_log(print_r($wp_error, true));
    }

    /**
     * Enqueue and localize wp scripts
     */
    public function bundle_manager_scripts()
    {
        wp_enqueue_script('bundle-manager-scripts', plugins_url() . '/' . DAS_FALKE_BUNDLE_MANAGER_DIRNAME . '/js/bundle_manager_scripts.js', ['jquery'], time(), true);
        wp_localize_script('bundle-manager-scripts', 'bundle_manager_vars', [
            'falke_job_posts_max_quantity' => $this->max_job_post_quantity,
            'falke_job_posts_min_quantity' => $this->min_job_post_quantity,
            'falke_quantity_discounts' => json_decode(file_get_contents(DAS_FALKE_QUANTITY_DISCOUNTS))[0]->quantity_discounts,
            'feature_product_price' => $this->get_product_field_by_slug('feature-bundle-pre-sort', 'price')
        ]);
    }

    /**
     * Remembers job-product item quantity, for later modifications, and sets it's item price based on the quantity
     *
     * @param $cart
     * @return bool
     */
    public function recalculate_prices($cart)
    {
        if (is_admin() && !defined('DOING_AJAX') || did_action('woocommerce_before_calculate_totals') >= 2) {
            return false;
        }

        $featured_products = $this->get_plugin_products_by_type('feature-product');

        foreach ($cart->get_cart() as $hash => $cart_item) {
            $cart_data = $cart_item['data'];
            $current_item_slug = $cart_data->get_slug();

            if ($current_item_slug === $this->get_main_product_slug()) {
                if ($cart_item['quantity'] < $this->min_job_post_quantity) {
                    $cart->set_quantity($hash, $this->min_job_post_quantity);
                } else if ($cart_item['quantity'] > $this->max_job_post_quantity) {
                    $cart->set_quantity($hash, $this->max_job_post_quantity);
                }

                $this->main_product_quantity = $cart_item['quantity'];
                $discount_price = $this->get_single_item_price_when_discounted((int)$this->main_product_quantity);
                $cart_item['data']->set_price($discount_price);
            }

            foreach ($featured_products as $featured_product) {
                if ($featured_product['slug'] === $current_item_slug) {
                    $this->feature_keys[] = $hash;
                }
            }
        }

        return $cart;
    }

    public function add_order_item_meta($item_id, $values)
    {
        global $woocommerce;
        $items = $woocommerce->cart->get_cart();

        foreach ($items as $item) {
            if (!empty($item['job_id'])) {
                wc_add_order_item_meta($item_id, 'job_id', $item['job_id']);
            }
        }
    }

    /**
     * Sets the item quantity of featured products depending on what was the quantity of the job product item
     *
     * @param $cart
     * @return bool
     */
    public function equalize_feature_quantity($cart)
    {
        if (is_admin() && !defined('DOING_AJAX') || did_action('woocommerce_before_calculate_totals') >= 2) {
            return false;
        }

        if ($this->main_product_quantity > 0) {
            foreach ($this->feature_keys as $featured_key) {
                foreach ($cart->get_cart() as $hash => $cart_item) {
                    if ($hash === $featured_key) {
                        $cart->set_quantity($featured_key, $this->main_product_quantity);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Perform various tasks before getting redirected to the checkout page, like add products, and empty the cart
     *
     * @param $passed
     * @param $product_id
     * @param $quantity
     * @param string $variation_id
     * @param string $variations
     * @return bool
     */
    public function validate_before_checkout($passed, $product_id, $quantity, $variation_id = '', $variations = '')
    {
        // Empty the cart before adding anything to it, since we don't want the user to think we actually have a cart system, just send them straight to checkout
        WC()->cart->empty_cart();

        // Only if logged in
        if (!eve_is_user_logged_in()) {
            falke_add_notice(__('Almost there! Log in or register and get your job listing package', $this->dfpbm_domain), 'error');
            wp_safe_redirect(home_url("login"));
            exit;
        }

        // The only role allowed to buy anything, is the employer
        if (!eve_is_user_employer()) {
            falke_add_notice(__('You can only do that, if you are an ' . $this->employer_multilang, $this->dfpbm_domain), 'error');
            return false;
        }

        if (get_profile_completeness('complete_number') < 7) {
            falke_add_notice(__('Please fill the required fields in your company profile first!', $this->dfpbm_domain), 'error');
            return false;
        }

        // We always want to add the bundle product into the cart, when we are buying a bundle
        $this->addBundleToOrder($product_id);

        if ($this->addOrderExtras() === false) {
            falke_add_notice(__('An error has occured during the order process', $this->dfpbm_domain), 'error');
            return false;
        }

        return true;
    }

    /**
     * If there are any extras ordered (featured products mostly) add them to the cart
     *
     * @return bool
     */
    private function addOrderExtras()
    {
        if (empty($_GET)) {
            return false;
        }

        global $woocommerce;
        $featured_products = $this->get_plugin_products_by_type('feature-product');

        foreach ($featured_products as $featured) {
            if (!empty($_GET[$featured['slug']]) && $_GET[$featured['slug']] === 'on') {
                $product_obj = get_page_by_path($featured['slug'], OBJECT, 'product');
                $woocommerce->cart->add_to_cart($product_obj->ID);
            }
        }

        return true;
    }

    /**
     * Add the bundle to the order
     */
    private function addBundleToOrder($product_id)
    {
        global $woocommerce;
        $the_bundle = $this->get_plugin_products_by_type('bundle')[0];
        $bundle_object = get_page_by_path($the_bundle['slug'], OBJECT, 'product');
        $main_product = get_page_by_path($this->get_main_product_slug(), OBJECT, 'product');

        if ($product_id === $main_product->ID) {
            $woocommerce->cart->add_to_cart($bundle_object->ID);
        }
    }

    /**
     * Uses woocommerce hooks to catch if there was any change to an order's status
     *
     * @param $order_id
     * @param $old_status
     * @param $new_status
     */
    public function catch_order_change($order_id, $old_status, $new_status)
    {
        if (empty($order = wc_get_order($order_id))) {
            falke_add_notice(__('An error has occured during the order process', $this->dfpbm_domain), 'error');
            return false;
        }

        if (!empty($order)) {
            if ($new_status !== 'completed') {
                $featured_order = $this->get_featured_order($order->get_id());
                $order_data = wc_get_order($order_id);
                $product_slug = "";
                $company_featured = $this->get_slugs_by_typecategory('company_features')[0];

                foreach ($order_data->get_items() as $key => $item) {
                    $product_slug = get_post($item->get_product_id())->post_name;
                }

                if (!empty($featured_order['product_id']) && !empty($featured_order['post_id'])) {
                    $this->new_record_featured($featured_order);
                } else if ($product_slug === $company_featured) {
                    if ($this->new_record_company_feature($order_data, $company_featured) === false) {
                        falke_add_notice(__('An error has occured during the order process of the company homepage highlight feature', $this->dfpbm_domain), 'error');
                    }
                } else if ($this->is_new_order($order_id) === true && !$this->new_order($order)) {
                    falke_add_notice(__('An error has occured during the order process', $this->dfpbm_domain), 'error');
                }
            } else if (!$this->update_order($order)) {
                falke_add_notice(__('An error has occured during the order process', $this->dfpbm_domain), 'error');
            }
        }

        return $order_id;
    }

    private function get_featured_order($order_id)
    {
        $order_data = wc_get_order($order_id);

        foreach ($order_data->get_items() as $key => $item) {
            return [
                'product_id' => (int)$item->get_data()['product_id'],
                'post_id' => (int)wc_get_order_item_meta($key, 'job_id', true),
            ];
        }

        return false;
    }

    /**
     * Create a new order
     *
     * @param $order
     * @return bool
     */
    private function new_order($order)
    {
        if (empty($order)) {
            falke_add_notice(__('Something has went wrong during the order, please try again!', $this->dfpbm_domain), 'error');
            return false;
        }

        if (!$this->is_bundle_order_exists($order->get_id()) && empty($bundle_id = $this->new_bundle_record_user($order))) {
            falke_add_notice(__('Something has went wrong during the order, please try again!', $this->dfpbm_domain), 'error');
            return false;
        }

        if (empty($job_ids = $this->new_bundle_record_job($order, $bundle_id))) {
            falke_add_notice(__('Something has went wrong during the order, please try again!', $this->dfpbm_domain), 'error');
            return false;
        }

        $this->new_bundle_record_featured($order, $job_ids);
        return true;
    }

    /**
     * Run admin screen hooks.
     */
    protected function install_admin_hooks()
    {
        $plugin_file = DAS_FALKE_BUNDLE_MANAGER_PLUGIN_PATH . '/falke-bundles.php';
        register_activation_hook($plugin_file, [$this, 'activation_action']);
    }

    /**
     * The function that is called by wp plugin activation hook.
     */
    public function activation_action()
    {
        if (!class_exists('WooCommerce')) {
            die("Please install and activate WooCommerce first!");
        }

        $this->create_woocommerce_products();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $create_tables = ['create_user_assoc_table', 'create_job_assoc_table', 'create_featured_assoc_table'];
        $collate = $this->wpdb->get_charset_collate();

        foreach ($create_tables as $table) {
            $result = dbDelta(call_user_func([$this, $table], $collate)) === false;

            if ($result) {
                die("The method: " . $table . " failed to complete.");
            }
        }
    }

    public function product_expirer()
    {
        require_once(ABSPATH . 'wp-includes/pluggable.php');

        $all_active_features = $this->get_all_active_featureds();
        $all_active_jobs = $this->get_all_active_jobs();
        $current_time = time();
        $users = get_users();
        $meta_key = $this->get_slugs_by_typecategory('company_features')[0];

        foreach ( $users as $user ) {
            $user_role = "";

            if (is_array($user->roles)) {
                $user_role = reset($user->roles);

                if ($user_role !== strtolower($this->employer_multilang)) {
                    continue;
                }
            }

            $meta_data = get_user_meta( $user->ID, $meta_key, true );

            if(!empty($meta_data) && is_string($meta_data) && is_array($meta_data = unserialize($meta_data))) {
                $last_update_timestamp = strtotime($meta_data['updated_at']);
                $time_diff = $current_time - $last_update_timestamp;
                $new_expire_time = 0;

                if ($meta_data['expires_in'] > $time_diff) {
                    $new_expire_time = $meta_data['expires_in'] - $time_diff;
                }

                $meta_data['expires_in'] = $new_expire_time;
                $meta_data['updated_at'] = date('Y-m-d H:i:s');

                if ($new_expire_time <= 0) {
                    $meta_data['status'] = 0;
                }

                update_user_meta($user->ID, $meta_key, serialize($meta_data));
            }
        }

        if (!empty($all_active_features)) {
            foreach($all_active_features as $active_feature) {
                if (!property_exists($active_feature, 'feature_id') || !property_exists($active_feature, 'last_update_time') || !property_exists($active_feature, 'expire_time')) {
                    continue;
                }

                $last_update_timestamp = strtotime($active_feature->last_update_time);
                $time_diff = $current_time - $last_update_timestamp;
                $new_expire_time = 0;

                if ($active_feature->expire_time > $time_diff) {
                    $new_expire_time = $active_feature->expire_time - $time_diff;
                }

                $this->change_expire_time_by_id($active_feature->feature_id, 'feature', $new_expire_time);
            }
        }

        if (!empty($all_active_jobs)) {
            foreach($all_active_jobs as $active_job) {
                if (!property_exists($active_job, 'job_id') || !property_exists($active_job, 'last_update_time') || !property_exists($active_job, 'expire_time')) {
                    continue;
                }

                $last_update_timestamp = strtotime($active_job->last_update_time);
                $time_diff = $current_time - $last_update_timestamp;
                $new_expire_time = 0;

                if ($active_job->expire_time > $time_diff) {
                    $new_expire_time = $active_job->expire_time - $time_diff;
                }

                $this->change_expire_time_by_id($active_job->job_id, 'job', $new_expire_time);
            }
        }

        $bundles_to_expire = $this->get_bundles_to_expire();
        $jobs_to_expire = $this->get_jobs_to_expire();
        $features_to_expire = $this->get_features_to_expire();

        if (!empty($features_to_expire)) {
            foreach($features_to_expire as $feature) {
                if (!property_exists($feature, 'feature_id')) {
                    continue;
                }

                $this->change_status_by_id($feature->feature_id, 'feature');
            }
        }

        if (!empty($jobs_to_expire)) {
            foreach($jobs_to_expire as $job) {
                if (!property_exists($job, 'job_id')) {
                    continue;
                }

                $this->change_status_by_id($job->job_id, 'job');
                $user_id = $this->get_user_id_by_job_id($job->job_id);

                if (!empty($user_id[0]) && property_exists($user_id[0], 'user_id') && !empty($job_author = get_user_by('id', $user_id[0]->user_id))) {
                    // TEST: Mail template here (job post has expired)
                    $this->falke_send_mail([
                        'subject' => sprintf(__('Your job post has expired - dasfalkepesonal.at', $this->dfpbm_domain)),
                        'email' => $job_author->user_email,
                        'template' => '/mailtemplates/email-job-expired.php',
                    ]);
                }
            }
        }

        if (!empty($bundles_to_expire)) {
            foreach($bundles_to_expire as $bundle) {
                if (!property_exists($bundle, 'bundle_id')) {
                    continue;
                }

                $this->change_status_by_id($bundle->bundle_id, 'user');
                $this->instant_expire_all_related_product($bundle->bundle_id);
            }
        }

        $active_jobs = $this->get_all_active_jobs();
        $reminders = [
            'one' => [
                'meta_key' => 'one_day_reminder',
                'expire_time' => 86400,
            ],
            'seven' => [
                'meta_key' => 'one_week_reminder',
                'expire_time' => 604800,
            ],
            'expired' => [
                'meta_key' => 'expired_reminder',
                'expire_time' => 0,
            ],
        ];

        if (!empty($active_jobs)) {
            foreach ($active_jobs as $active_job) {
                if (!property_exists($active_job, 'job_id') || !property_exists($active_job, 'post_id')) {
                    continue;
                }

                $user_id = $this->get_user_id_by_job_id($active_job->job_id);

                if ($active_job->expire_time < $reminders['seven']['expire_time']) {
                    if (empty(get_post_meta($active_job->post_id, $reminders['seven']['meta_key'], true)) && !empty($user_id[0]) && property_exists($user_id[0], 'user_id') && !empty($job_author = get_user_by('id', $user_id[0]->user_id))) {
                        // TEST: Mail template here (7 days before expire reminder)
                        if ($this->falke_send_mail([
                            'subject' => sprintf(__('Your job post expires soon! - dasfalkepersonal.at', $this->dfpbm_domain)),
                            'email' => $job_author->user_email,
                            'template' => '/mailtemplates/email-job-expire-soon.php',
                        ])) {
                            update_post_meta($active_job->post_id, $reminders['seven']['meta_key'], true);
                        }
                    }
                } else if ($active_job->expire_time < $reminders['one']['expire_time']) {
                    if (empty(get_post_meta($active_job->post_id, $reminders['one']['meta_key'], true)) && !empty($user_id[0]) && property_exists($user_id[0], 'user_id') && !empty($job_author = get_user_by('id', $user_id[0]->user_id))) {
                        // TEST: Mail template here (1 day before expire reminder)
                        if ($this->falke_send_mail([
                            'subject' => sprintf(__('Your job post expires soon! - dasfalkepersonal.at', $this->dfpbm_domain)),
                            'email' => $job_author->user_email,
                            'template' => '/mailtemplates/email-job-expire-soon.php',
                        ])) {
                            update_post_meta($active_job->post_id, $reminders['one']['meta_key'], true);
                        }
                    }
                }
            }
        }
    }

    public function daily_mails()
    {
        $saved_alert_meta_key = 'sent_dailies';
        $alert_meta_key = 'job_alerts';

        $users_with_alerts = get_users([
            'role' => $this->jobseeker_multilang,
            'meta_key' => $alert_meta_key,
        ]);

        foreach($users_with_alerts as $users_with_alert) {
            if (property_exists($users_with_alert, 'ID')) {
                $user_id = $users_with_alert->ID;
                $job_alerts_raw = get_user_meta($user_id, $alert_meta_key, true);

                // Are there any job alerts saved for the user at all?
                if (!empty($job_alerts_raw) && !empty($job_alerts = unserialize($job_alerts_raw))) {
                    $new_posts = [];

                    foreach($job_alerts as $job_alert) {
                        // Extra check if the urldecoding succeeded without any problems
                        if (!empty($job_alert) && !empty($job_alert = urldecode($job_alert))) {
                            $job_alert = str_replace('?', '', $job_alert);
                            parse_str($job_alert, $parsed_job_alerts);

                            // Are there any job alerts for the user after parsing?
                            if (!empty($parsed_job_alerts)) {
                                $job_alerts_array = array_filter($parsed_job_alerts);
                                $jobs = $this->handle_job_search($job_alerts_array);

                                // Found jobs eligible for filter?
                                if (!empty($jobs) && $jobs->have_posts()) {
                                    $user_meta = get_user_meta($user_id, $saved_alert_meta_key, true);
                                    $post_ids = wp_list_pluck($jobs->posts, 'ID');

                                    if (empty($user_meta_unserialized = unserialize($user_meta))) {
                                        $user_meta_unserialized = [];
                                    }

                                    if (!empty($posts = array_diff($post_ids, $user_meta_unserialized))) {
                                        $new_posts = array_unique(array_merge($new_posts, $posts));

                                        foreach($new_posts as $key => $new_post) {
                                            if (empty($this->is_job_active($new_post))) {
                                                unset($new_posts[$key]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Got new posts to alert the user about?
                    if (!empty($new_posts) && is_array($new_posts)) {
                        $template = "";
                        $jobs_query = new WP_Query([
                            'post_type' => 'post',
                            'post__in' => $new_posts
                        ]);

                        // If for some reason the query did not find a post, even though it should exist, continue to the next user
                        if (empty($jobs_query)) {
                            continue;
                        }

                        ob_start();
                        include(locate_template('/mailtemplates/email-job-loop.php'));
                        $template .= ob_get_contents();
                        ob_end_clean();

                        // TODO: Daily mail for jobseekers about saved job alerts
                        $this->falke_send_mail([
                            'subject' => sprintf(__('The latest jobs for your Job Alert - dasfalkepersonal.at', 'dasfalke-email')),
                            'email' => $users_with_alert->get('user_email'),
                            'template' => [
                                'template_var' => $template,
                            ],
                        ]);

                        wp_reset_query();
                        update_user_meta($user_id, $saved_alert_meta_key, serialize(array_merge($new_posts, $user_meta_unserialized)));
                    }
                }
            }
        }
    }

    function register_custom_taxonomies()
    {
        register_taxonomy(
            'locations',
            ['locations'],
            [
                'label' => __('Locations'),
                'rewrite' => array('slug' => 'locations'),
            ]
        );

        register_taxonomy(
            'professions',
            ['professions'],
            [
                'label' => __('Professions'),
                'rewrite' => array('slug' => 'professions'),

            ]
        );
    }

    /**
     * Simplifies email sending for dasfalke, loads message body template, does minimal validations, defaults for utf8 head, etc...
     *
     * @param array $parameters
     * @param array $headers
     * @return bool
     */
    public function falke_send_mail($parameters = [], $headers = ['Content-Type: text/html; charset=UTF-8', 'From: Das Falke Personal <karrierportal@dasfalkepersonal.at>']) {

        if (!is_array($parameters)) {
            return false;
        }

        $required_parameters = ['subject', 'template', 'email'];
        $attachments = [];

        foreach($required_parameters as $required_parameter) {
            if (!array_key_exists($required_parameter, $parameters)) {
                return false;
            }
        }

        if (!empty($required_parameters['attachments'])) {
            $attachments = $required_parameters['attachments'];
        }

        if (!is_array($parameters['template'])) {
            ob_start();
            include(locate_template($parameters['template']));
            $mail_body = ob_get_contents();
            ob_end_clean();
        } else {
            $mail_body = $parameters['template']['template_var'];
        }

        wp_mail($parameters['email'], $parameters['subject'], $mail_body, $headers, $attachments);
    }
}
