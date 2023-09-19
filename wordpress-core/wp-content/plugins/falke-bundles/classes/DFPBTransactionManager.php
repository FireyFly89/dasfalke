<?php

/**
 * Class DFPBManager
 *
 * Das Falke Personal Bundle Transaction Manager
 * @package Das Falke Personal Bundle plugins
 */
class DFPBTransactionManager extends DFPBUtilities
{
    /**
     * @var string
     */
    private $bundle_user_table = "bundle_user_assoc";

    /**
     * @var string
     */
    private $bundle_job_table = "bundle_job_assoc";

    /**
     * @var string
     */
    private $bundle_feature_table = "bundle_featured_assoc";

    /**
     * @var
     */
    private $order_extras_featured;

    /**
     * DFPBTransactionManager constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fetches falke bundles from the database for the pricing page
     *
     * @param array $products
     * @return WP_Query
     */
    public function get_falke_products(Array $products)
    {
        $bundles = new WP_Query([
            'post_type' => 'product',
            'post_name__in' => $products,
        ]);
        wp_reset_query();
        return $bundles;
    }

    /**
     * Updates the order when the woocommerce event is fired for order_status_change and it has a completed status, so the order has been paid
     *
     * @param WC_Order $order
     * @return bool
     */
    public function update_order(WC_Order $order)
    {
        $order_id = $order->get_id();
        $company_featured = $this->get_slugs_by_typecategory('company_features')[0];
        $user_id = $order->get_user_id();

        foreach ($order->get_items() as $key => $item) {
            $product = $item->get_product();
            $current_slug = $product->get_slug();
            $bundle_data = $this->get_bundle_data($current_slug);

            if ($current_slug === $this->get_main_bundle_slug()) {
                $this->wpdb->update(
                    $this->get_plugin_table($this->bundle_user_table),
                    ['expires_at' => date("Y-m-d H:i:s", strtotime("+ 365 days"))],
                    ['order_id' => $order_id],
                    ['%s'],
                    ['%d']
                );
            } else if ($current_slug === $this->get_main_product_slug()) {
                $job_ids = $this->get_jobs_by('order_id', $order_id);

                foreach ($job_ids as $job_id) {
                    $this->wpdb->update(
                        $this->get_plugin_table($this->bundle_job_table),
                        ['expires_in' => $this->get_expire_in_seconds($bundle_data['expires_in'])],
                        ['id' => $job_id->id],
                        ['%d'],
                        ['%d']
                    );
                }
            } else if (strpos($current_slug, 'feature-bundle-') !== false && !empty($featured_ids = $this->get_featureds_by($current_slug, 'order_id', $order_id))) {
                foreach ($featured_ids as $featured_id) {
                    $this->wpdb->update(
                        $this->get_plugin_table($this->bundle_feature_table),
                        [
                            'expires_in' => $this->get_expire_in_seconds($bundle_data['expires_in']),
                            'status' => 0
                        ],
                        ['id' => $featured_id->id],
                        ['%d', '%d'],
                        ['%d']
                    );
                }
            } else if (strpos($current_slug, 'feature-') !== false && !empty($post_id = wc_get_order_item_meta($key, 'job_id', true))) {
                $job_id = $this->get_job_id_by_post_id($post_id);
                $job_status = $this->get_product_status($this->get_plugin_table($this->bundle_job_table), [
                    'post_id',
                ], [
                    $post_id,
                ]);

                if (!empty($job_id) && !empty($job_status) && property_exists($job_status[0], 'status')) {
                    $this->wpdb->update(
                        $this->get_plugin_table($this->bundle_feature_table),
                        [
                            'expires_in' => $this->get_expire_in_seconds($bundle_data['expires_in']),
                            'status' => $job_status[0]->status
                        ],
                        [
                            'job_id' => $job_id[0]->id,
                            'type' => $current_slug
                        ],
                        ['%d', '%d'],
                        ['%d', '%s']
                    );
                }
            } else if ($company_featured === $current_slug) {
                $user_meta_feature_data = get_user_meta($user_id, $company_featured, true);

                if (empty($user_meta_feature_data)) {
                    falke_add_notice(__('An unknown error occured when trying to complete the order!', $this->dfpbm_domain), 'error');
                    die('An unknown error occured when trying to complete the order!');
                }

                if (is_string($user_meta_feature_data)) {
                    if (is_array($featured_data = unserialize($user_meta_feature_data))) {
                        if ($featured_data['status'] === 1 || $featured_data['expires_in'] > 0) {
                            return false;
                        }

                        $featured_data['status'] = 1;
                        $featured_data['updated_at'] = date("Y-m-d H:i:s");
                        $featured_data['expires_in'] = $this->get_expire_in_seconds($this->get_product_field_by_slug($company_featured, 'expires_in'));
                        return update_user_meta($user_id, $company_featured, serialize($featured_data));
                    }
                }
            }
        }

        return true;
    }

    /**
     * Updates an unusued job slot with a newly created job post_id
     *
     * @param $post_id
     * @param $user_id
     */
    public function update_job_post_id($post_id, $user_id, $job_id)
    {
        if (empty($post_id) || empty($user_id) || empty($job_id)) {
            return false;
        }

        $this->wpdb->update(
            $this->get_plugin_table($this->bundle_job_table),
            ['post_id' => $post_id],
            ['id' => $job_id],
            ['%d'],
            ['%d']
        );

        return true;
    }

    public function inactivate_all_featureds_by_post_id($post_id)
    {
        if (empty($post_id) || empty($job_id = $this->get_job_id_by_post_id($post_id))) {
            return false;
        }

        if (!property_exists($job_id[0], 'id')) {
            return false;
        }

        $this->wpdb->update(
            $this->get_plugin_table($this->bundle_feature_table),
            ['status' => 0],
            ['job_id' => $job_id[0]->id],
            ['%d'],
            ['%d']
        );

        return true;
    }

    public function change_status($id, $status, $type = "job")
    {
        $table = $this->get_plugin_table($this->bundle_job_table);
        $conditional_column = 'post_id';

        if ($type === "feature") {
            $table = $this->get_plugin_table($this->bundle_feature_table);
            $conditional_column = 'id';
        }

        $this->wpdb->update(
            $table,
            ['status' => $status],
            [$conditional_column => $id],
            ['%d'],
            ['%d']
        );
    }

    public function change_status_by_id($id, $type, $status = 0)
    {
        $table = "";

        if ($type === 'feature') {
            $table = $this->get_plugin_table($this->bundle_feature_table);
        } else if ($type === 'job') {
            $table = $this->get_plugin_table($this->bundle_job_table);
        } else if ($type === 'user') {
            $table = $this->get_plugin_table($this->bundle_user_table);
        }

        $this->wpdb->update(
            $table,
            ['status' => $status],
            ['id' => $id],
            ['%d'],
            ['%d']
        );
    }

    public function change_expire_time_by_id($id, $type, $new_expire_time)
    {
        $table = "";

        if ($type === 'feature') {
            $table = $this->get_plugin_table($this->bundle_feature_table);
        } else if ($type === 'job') {
            $table = $this->get_plugin_table($this->bundle_job_table);
        }

        $this->wpdb->update(
            $table,
            ['expires_in' => $new_expire_time],
            ['id' => $id],
            ['%d'],
            ['%d']
        );
    }

    /**
     * Retrieves all available job slots for one user
     *
     * @param $user_id
     * @return array|object|null
     */
    public function get_available_job_slots($user_id)
    {
        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_job_table) . ".id from " . $this->get_plugin_table($this->bundle_job_table) . "
            join " . $this->get_plugin_table($this->bundle_user_table) . "
            on " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id = " . $this->get_plugin_table($this->bundle_user_table) . ".id
            where " . $this->get_plugin_table($this->bundle_job_table) . ".post_id is null
            and " . $this->get_plugin_table($this->bundle_user_table) . ".user_id = " . $user_id . ";", OBJECT);
    }

    /**
     * Retrieves all available jobs and the users for them
     *
     * @return array|object|null
     */
    public function get_all_available_jobs()
    {
        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_job_table) . ".post_id, " . $this->get_plugin_table($this->bundle_user_table) . ".user_id 
            from " . $this->get_plugin_table($this->bundle_job_table) . " join " . $this->get_plugin_table($this->bundle_user_table) . "
            on " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id = " . $this->get_plugin_table($this->bundle_user_table) . ".id
            where " . $this->get_plugin_table($this->bundle_job_table) . ".post_id is not null and
            " . $this->get_plugin_table($this->bundle_job_table) . ".status = 1;", OBJECT);
    }

    /**
     * Retrieves all available featured slots by it's type and job slots with user id
     *
     * @param $feature_type
     * @param bool $single_random
     * @return array|bool|object|null
     */
    public function get_all_available_and_featured_jobs($feature_types, $single_random = false)
    {
        if (empty($feature_types)) {
            return false;
        }

        if (is_array($feature_types)) {
            $feature_types = implode("','", $feature_types);
        }

        $query_string = "select distinct group_concat(" . $this->get_plugin_table($this->bundle_job_table) . ".post_id) as post_ids, " . $this->get_plugin_table($this->bundle_user_table) . ".user_id as user 
            from " . $this->get_plugin_table($this->bundle_job_table) . " join " . $this->get_plugin_table($this->bundle_user_table) . "
            on " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id = " . $this->get_plugin_table($this->bundle_user_table) . ".id
            join " . $this->get_plugin_table($this->bundle_feature_table) . " on
            " . $this->get_plugin_table($this->bundle_feature_table) . ".job_id = " . $this->get_plugin_table($this->bundle_job_table) . ".id
            where " . $this->get_plugin_table($this->bundle_job_table) . ".post_id is not null and
            " . $this->get_plugin_table($this->bundle_job_table) . ".status = 1 and
            " . $this->get_plugin_table($this->bundle_feature_table) . ".type in ('" . $feature_types . "')" . " and
            " . $this->get_plugin_table($this->bundle_feature_table) . ".status = 1
            group by " . $this->get_plugin_table($this->bundle_user_table) . ".user_id";

        if ($single_random === true) {
            $query_string .= ' ORDER BY RAND() LIMIT 1';
        }

        return $this->wpdb->get_results($query_string . ";", OBJECT);
    }

    public function is_job_active($job_id)
    {
        if (empty($job_id)) {
            return false;
        }

        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_job_table) . ".id from " . $this->get_plugin_table($this->bundle_job_table) . " where
            " . $this->get_plugin_table($this->bundle_job_table) . ".post_id = " . $job_id . " and " . $this->get_plugin_table($this->bundle_job_table) . ".status = 1
            and " . $this->get_plugin_table($this->bundle_job_table) . ".expires_in > 0;", OBJECT);
    }

    public function get_all_active_job_ids_for_users_by_user_ids($user_ids)
    {
        if (empty($user_ids) || !is_array($user_ids)) {
            return false;
        }

        return $this->wpdb->get_results("select group_concat(" . $this->get_plugin_table($this->bundle_job_table) . ".post_id) as post_ids,
            " . $this->get_plugin_table($this->bundle_user_table) . ".user_id
            from " . $this->get_plugin_table($this->bundle_job_table) . " join
            " . $this->get_plugin_table($this->bundle_user_table) . " on " . $this->get_plugin_table($this->bundle_user_table) . ".id = " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id
            where " . $this->get_plugin_table($this->bundle_job_table) . ".post_id is not null 
            and (" . $this->get_plugin_table($this->bundle_job_table) . ".status = 1 or " . $this->get_plugin_table($this->bundle_job_table) . ".expires_in > 0) and
            " . $this->get_plugin_table($this->bundle_user_table) . ".user_id in ('" . implode("','", $user_ids) . "') group by " . $this->get_plugin_table($this->bundle_user_table) . ".user_id;", OBJECT);
    }

    public function get_all_active_job_ids_for_users($user_ids)
    {
        if (empty($user_ids) || !is_array($user_ids)) {
            return false;
        }

        $user_ids = "'" . implode("','", $user_ids) . "'";

        $post_ids = $this->wpdb->get_results("select group_concat(" . $this->get_plugin_table($this->bundle_job_table) . ".post_id) as post_ids 
            from " . $this->get_plugin_table($this->bundle_job_table) . " join
            " . $this->get_plugin_table($this->bundle_user_table) . " on " . $this->get_plugin_table($this->bundle_user_table) . ".id = " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id
            where " . $this->get_plugin_table($this->bundle_job_table) . ".post_id is not null 
            and (" . $this->get_plugin_table($this->bundle_job_table) . ".status = 1 or " . $this->get_plugin_table($this->bundle_job_table) . ".expires_in > 0) and
            " . $this->get_plugin_table($this->bundle_user_table) . ".user_id in (" . $user_ids . ");", OBJECT);

        if (is_array($post_ids) && property_exists($post_ids[0], 'post_ids') && $post_ids[0]->post_ids !== null) {
            $post_ids = explode(',', $post_ids[0]->post_ids);
        } else {
            return null;
        }

        return $post_ids;
    }

    public function is_new_order($order_id)
    {
        return empty($this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_user_table) . ".id from " . $this->get_plugin_table($this->bundle_user_table) . " where " . $this->get_plugin_table($this->bundle_user_table) . ".order_id = " . $order_id . ";", OBJECT));
    }

    public function get_bundle_ids_by_user_id($user_id)
    {
        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_user_table) . ".id from " . $this->get_plugin_table($this->bundle_user_table) . " where " . $this->get_plugin_table($this->bundle_user_table) . ".user_id = " . $user_id . ";", OBJECT);
    }

    public function get_all_inactive_job_ids()
    {
        $post_ids = $this->wpdb->get_results("select group_concat(" . $this->get_plugin_table($this->bundle_job_table) . ".post_id) as post_ids
            from " . $this->get_plugin_table($this->bundle_job_table) . "
            where " . $this->get_plugin_table($this->bundle_job_table) . ".post_id is not null
            and (" . $this->get_plugin_table($this->bundle_job_table) . ".status = 0
            or " . $this->get_plugin_table($this->bundle_job_table) . ".expires_in <= 0);", OBJECT);

        if (is_array($post_ids) && property_exists($post_ids[0], 'post_ids')) {
            $post_ids = explode(',', $post_ids[0]->post_ids);
        }

        return $post_ids;
    }

    /**
     * Retrieves all available job slots by user id
     *
     * @param $user_id
     * @return array|bool|object|null
     */
    public function get_all_available_jobs_by_user($user_id)
    {
        if (empty($user_id)) {
            return false;
        }

        $query_string = "select distinct group_concat(" . $this->get_plugin_table($this->bundle_job_table) . ".post_id) as post_ids, " . $this->get_plugin_table($this->bundle_user_table) . ".user_id as user 
            from " . $this->get_plugin_table($this->bundle_job_table) . " join " . $this->get_plugin_table($this->bundle_user_table) . "
            on " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id = " . $this->get_plugin_table($this->bundle_user_table) . ".id
            where " . $this->get_plugin_table($this->bundle_job_table) . ".post_id is not null and
            " . $this->get_plugin_table($this->bundle_job_table) . ".status = 1 and
            " . $this->get_plugin_table($this->bundle_user_table) . ".user_id = " . $user_id . "
            group by " . $this->get_plugin_table($this->bundle_user_table) . ".user_id";

        return $this->wpdb->get_results($query_string . ";", OBJECT);
    }

    public function get_all_jobs_and_featureds_for_user($user_id)
    {
        $job_data = $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_user_table) . ".expires_at as bundle_is_paid, 
            group_concat(distinct " . $this->get_plugin_table($this->bundle_job_table) . ".id) as job_id, 
            group_concat(distinct " . $this->get_plugin_table($this->bundle_job_table) . ".status) as job_status, 
            " . $this->get_plugin_table($this->bundle_job_table) . ".post_id as post_id,
            group_concat(distinct " . $this->get_plugin_table($this->bundle_job_table) . ".expires_in) as job_expire_time
            from " . $this->get_plugin_table($this->bundle_job_table) . "
            join " . $this->get_plugin_table($this->bundle_user_table) . " on " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id = " . $this->get_plugin_table($this->bundle_user_table) . ".id
            where " . $this->get_plugin_table($this->bundle_user_table) . ".user_id = " . $user_id . "
            group by " . $this->get_plugin_table($this->bundle_job_table) . ".id;", OBJECT);

        $feature_data = $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_job_table) . ".id,
            group_concat(" . $this->get_plugin_table($this->bundle_feature_table) . ".id) as feature_ids,
            group_concat(" . $this->get_plugin_table($this->bundle_feature_table) . ".type) as feature_types,
            group_concat(" . $this->get_plugin_table($this->bundle_feature_table) . ".status) as feature_statuses,
            group_concat(" . $this->get_plugin_table($this->bundle_feature_table) . ".expires_in) as feature_expire_times
            from " . $this->get_plugin_table($this->bundle_feature_table) . "
            join " . $this->get_plugin_table($this->bundle_job_table) . " on " . $this->get_plugin_table($this->bundle_feature_table) . ".job_id = " . $this->get_plugin_table($this->bundle_job_table) . ".id
            join " . $this->get_plugin_table($this->bundle_user_table) . " on " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id = " . $this->get_plugin_table($this->bundle_user_table) . ".id
            where " . $this->get_plugin_table($this->bundle_user_table) . ".user_id = " . $user_id . "
            group by " . $this->get_plugin_table($this->bundle_job_table) . ".id;", OBJECT);

        $featured_products_json = $this->get_plugin_products_by_type('feature-product');
        $featured_products = [];

        foreach ($featured_products_json as $feature_product) {
            $product_info = get_page_by_path($feature_product['slug'], OBJECT, 'product');
            $featured_products[] = $product_info;
        }

        $job_data['job_count'] = 0;

        foreach ($job_data as $job_info) {
            if (!isset($job_info->job_id)) {
                continue;
            }

            $job_info->job_id = (int)$job_info->job_id;
            $job_info->job_status = (int)$job_info->job_status;
            $job_info->job_expire_time = (int)$job_info->job_expire_time;
            $job_info->unassigned_featured_category = [];
            $job_info->feature_typecats = [];
            $job_data['job_count']++;

            if ($job_info->post_id !== null) {
                $job_info->post_id = (int)$job_info->post_id;
                $job_data['post_ids'][] = $job_info->post_id;
            }

            foreach ($feature_data as $feature_info) {
                if ((int)$feature_info->id !== $job_info->job_id) {
                    continue;
                }

                $job_info->feature_types = explode(',', $feature_info->feature_types);
                $job_info->feature_ids = explode(',', $feature_info->feature_ids);
                $job_info->feature_statuses = explode(',', $feature_info->feature_statuses);
                $job_info->feature_expire_times = explode(',', $feature_info->feature_expire_times);

                foreach ($job_info->feature_ids as $key => $id) {
                    $job_info->feature_ids[$key] = (int)$id;
                }

                foreach ($job_info->feature_statuses as $key => $status) {
                    $job_info->feature_statuses[$key] = (int)$status;
                }

                foreach ($job_info->feature_types as $key => $type) {
                    foreach ($featured_products as $product) {
                        if ($product->post_name === $type) {
                            $job_info->feature_names[$key] = $product->post_title;
                            $job_info->feature_typecats[] = $this->get_typecategory_by_slug($product->post_name);
                        }
                    }
                }
            }

            foreach ($featured_products as $product) {
                if (!in_array($product->post_title, $job_info->unassigned_featured_category)) {
                    if (property_exists($job_info, 'feature_names')) {
                        $product_type_cat = $this->get_typecategory_by_slug($product->post_name);

                        if (!in_array($product_type_cat, $job_info->feature_typecats)) {
                            $job_info->unassigned_featured_category[] = $product->post_title;
                        }
                    } else {
                        $job_info->unassigned_featured_category[] = $product->post_title;
                    }
                }
            }
        }

        return $job_data;
    }

    /**
     * Retrieve feature data from the database by job id
     */
    public function get_featureds_by_job_id($job_id)
    {
        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_feature_table) . ".id from " . $this->get_plugin_table($this->bundle_feature_table) . "
            join " . $this->get_plugin_table($this->bundle_job_table) . " on
            " . $this->get_plugin_table($this->bundle_job_table) . ".id = " . $this->get_plugin_table($this->bundle_feature_table) . ".job_id
            where " . $this->get_plugin_table($this->bundle_job_table) . ".post_id = '" . $job_id . "';", OBJECT);
    }

    /**
     * Retrieve feature data from the database based on given parameters
     *
     * @param String $type
     * @param String $condition
     * @param Int $conditional_value
     * @param array $return_values
     * @return array|object|null
     */
    public function get_featureds_by(String $type, String $condition_column, Int $conditional_value, Array $return_values = ['id'], $condition_table = null)
    {
        $job_ids = $this->get_jobs_by($condition_column, $conditional_value, $return_values, $condition_table);
        $column_string = "";
        $i = 1;
        $ids_for_conditional = null;

        foreach ($job_ids as $job_id) {
            $ids_for_conditional .= $job_id->id . ($i < count($job_ids) ? ", " : "");
            $i++;
        }

        $i = 1;

        foreach ($return_values as $column) {
            $column_string .= $this->get_plugin_table($this->bundle_feature_table) . "." . $column . ($i < count($return_values) ? ", " : "");
            $i++;
        }

        $type_selector = "";

        if (!empty($type)) {
            $type_selector = "where " . $this->get_plugin_table($this->bundle_feature_table) . ".type = '" . $type . "' AND";
        }

        return $this->wpdb->get_results("select " . $column_string . " from " . $this->get_plugin_table($this->bundle_feature_table) . " 
            join " . $this->get_plugin_table($this->bundle_job_table) . "
            on " . $this->get_plugin_table($this->bundle_job_table) . ".id = " . $this->get_plugin_table($this->bundle_feature_table) . ".job_id
            " . $type_selector . "
            " . $this->get_plugin_table($this->bundle_job_table) . ".id in (" . $ids_for_conditional . ");", OBJECT);
    }

    public function get_jobs_by(String $condition_column, Int $conditional_value, Array $return_values = ['id'], $condition_table = null)
    {
        if (empty($return_values)) {
            return false;
        }

        if ($condition_table === null) {
            $condition_table = $this->bundle_user_table;
        }

        $column_string = "";
        $i = 1;

        foreach ($return_values as $column) {
            $column_string .= $this->get_plugin_table($this->bundle_job_table) . "." . $column . ($i < count($return_values) ? ", " : "");
            $i++;
        }

        return $this->wpdb->get_results("select " . $column_string . " from " . $this->get_plugin_table($this->bundle_job_table) . "
            join " . $this->get_plugin_table($this->bundle_user_table) . "
            on " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id = " . $this->get_plugin_table($this->bundle_user_table) . ".id
            where " . $this->get_plugin_table($condition_table) . "." . $condition_column . " = " . $conditional_value . ";", OBJECT);
    }

    public function is_featured_for_job($job_id, Array $featured_types = ['feature-bundle-highlight'])
    {
        if (empty($job_id)) {
            return false;
        }

        $featureds = "'" . implode("','", $featured_types) . "'";

        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_feature_table) . ".type from " . $this->get_plugin_table($this->bundle_job_table) . " 
            join " . $this->get_plugin_table($this->bundle_feature_table) . " 
            on " . $this->get_plugin_table($this->bundle_job_table) . ".id = " . $this->get_plugin_table($this->bundle_feature_table) . ".job_id
            where " . $this->get_plugin_table($this->bundle_feature_table) . ".type in (" . $featureds . ") and
            " . $this->get_plugin_table($this->bundle_job_table) . ".post_id = " . $job_id . " and
            " . $this->get_plugin_table($this->bundle_feature_table) . ".status = 1 and
            " . $this->get_plugin_table($this->bundle_job_table) . ".status = 1;", OBJECT);
    }

    public function get_all_active_post_ids_with_featureds(Array $featured_types, $is_random_posts = true, $limit = 3)
    {
        if (empty($featured_types)) {
            return false;
        }

        $featureds = "'" . implode("','", $featured_types) . "'";
        $extra_query_string = "";

        if ($is_random_posts === true) {
            $extra_query_string .= "ORDER BY RAND()";
        }

        if (!empty($limit)) {
            $extra_query_string .= "  LIMIT " . $limit;
        }

        $results = $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_job_table) . ".post_id from " . $this->get_plugin_table($this->bundle_job_table) . "
            join " . $this->get_plugin_table($this->bundle_user_table) . " on 
            " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id = " . $this->get_plugin_table($this->bundle_user_table) . ".id
            join " . $this->get_plugin_table($this->bundle_feature_table) . " on
            " . $this->get_plugin_table($this->bundle_feature_table) . ".job_id = " . $this->get_plugin_table($this->bundle_job_table) . ".id
            where " . $this->get_plugin_table($this->bundle_user_table) . ".expires_at > CURDATE() and
            " . $this->get_plugin_table($this->bundle_job_table) . ".status = 1 and
            " . $this->get_plugin_table($this->bundle_job_table) . ".expires_in > 0 and
            " . $this->get_plugin_table($this->bundle_feature_table) . ".status = 1 and
            " . $this->get_plugin_table($this->bundle_feature_table) . ".expires_in > 0 and
            " . $this->get_plugin_table($this->bundle_feature_table) . ".type in (" . $featureds . ") " . $extra_query_string . ";", OBJECT);

        $return_results = [];

        if (!empty($results)) {
            foreach ($results as $result) {
                $return_results[] = $result->post_id;
            }
        }

        return $return_results;
    }

    public function is_featured_available_for_job($job_id, Array $featured_types = ['feature-bundle-highlight'])
    {
        if (empty($job_id)) {
            return false;
        }

        $featureds = "'" . implode("','", $featured_types) . "'";

        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_feature_table) . ".type from " . $this->get_plugin_table($this->bundle_job_table) . " 
            join " . $this->get_plugin_table($this->bundle_feature_table) . " 
            on " . $this->get_plugin_table($this->bundle_job_table) . ".id = " . $this->get_plugin_table($this->bundle_feature_table) . ".job_id
            where " . $this->get_plugin_table($this->bundle_feature_table) . ".type in (" . $featureds . ") and
            " . $this->get_plugin_table($this->bundle_feature_table) . ".expires_in > 0 and
            " . $this->get_plugin_table($this->bundle_job_table) . ".post_id = " . $job_id . ";", ARRAY_N);
    }

    /**
     * Retrieves all available featured slots and job slots by user id
     *
     * @param string $feature_type
     * @return array|object|null
     */
    public function get_all_available_featureds_by_user($user_id)
    {
        $processed_data = $this->wpdb->get_results("select group_concat(distinct " . $this->get_plugin_table($this->bundle_job_table) . ".id) as job_id,
            group_concat(" . $this->get_plugin_table($this->bundle_feature_table) . ".type) as type,
            group_concat(" . $this->get_plugin_table($this->bundle_feature_table) . ".expires_in) as expire_time
            from " . $this->get_plugin_table($this->bundle_job_table) . " join " . $this->get_plugin_table($this->bundle_user_table) . "
            on " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id = " . $this->get_plugin_table($this->bundle_user_table) . ".id
            join " . $this->get_plugin_table($this->bundle_feature_table) . " on
            " . $this->get_plugin_table($this->bundle_feature_table) . ".job_id = " . $this->get_plugin_table($this->bundle_job_table) . ".id
            where " . $this->get_plugin_table($this->bundle_job_table) . ".post_id is null and
            " . $this->get_plugin_table($this->bundle_job_table) . ".status = 0 and
            " . $this->get_plugin_table($this->bundle_feature_table) . ".status = 0 and
            " . $this->get_plugin_table($this->bundle_feature_table) . ".expires_in > 0 and
            " . $this->get_plugin_table($this->bundle_user_table) . ".user_id = " . $user_id . "
            group by " . $this->get_plugin_table($this->bundle_feature_table) . ".job_id;", OBJECT);

        if (empty($processed_data)) {
            $processed_data = $this->wpdb->get_results("select group_concat(distinct " . $this->get_plugin_table($this->bundle_job_table) . ".id) as job_id 
                from " . $this->get_plugin_table($this->bundle_job_table) . " 
                join " . $this->get_plugin_table($this->bundle_user_table) . " on 
                " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id = " . $this->get_plugin_table($this->bundle_user_table) . ".id 
                where " . $this->get_plugin_table($this->bundle_job_table) . ".post_id is null and 
                " . $this->get_plugin_table($this->bundle_job_table) . ".status = 0 and 
                " . $this->get_plugin_table($this->bundle_job_table) . ".expires_in > 0 and 
                " . $this->get_plugin_table($this->bundle_user_table) . ".user_id = " . $user_id . "
                group by " . $this->get_plugin_table($this->bundle_job_table) . ".id;", OBJECT);
        }

        $featured_products = [];

        foreach ($this->get_plugin_products_by_type('feature-product') as $feature_product) {
            $featured_products[] = get_page_by_path($feature_product['slug'], OBJECT, 'product');
        }

        foreach ($processed_data as $job_info) {
            $job_info->job_id = (int)$job_info->job_id;

            if (!property_exists($job_info, 'type')) {
                continue;
            }

            $job_info->type = explode(',', $job_info->type);
            $job_info->expire_time = explode(',', $job_info->expire_time);

            foreach ($job_info->expire_time as $key => $time) {
                $job_info->expire_time[$key] = $this->seconds_to_time($time);
            }

            foreach ($job_info->type as $key => $type) {
                foreach ($featured_products as $product) {
                    if ($product->post_name === $type) {
                        $job_info->type[$key] = $product->post_title;
                    }
                }
            }
        }

        return $processed_data;
    }

    /**
     * Create the bundle record on a new order
     *
     * @param $order
     * @return obj WC_Order
     */
    public function new_bundle_record_user($order)
    {
        $order_id = $order->get_id();
        $user_id = $order->get_user_id();

        if (empty($order_id) || empty($user_id)) {
            return false;
        }

        $this->wpdb->insert(
            $this->get_plugin_table($this->bundle_user_table),
            ['user_id' => $order->get_user_id(), 'order_id' => $order->get_id(), 'created_at' => date("Y-m-d H:i:s")],
            ['%d', '%d', '%s']
        );

        return $this->wpdb->insert_id;
    }

    public function is_bundle_order_exists($order_id)
    {
        return !empty($this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_user_table) . ".id from " . $this->get_plugin_table($this->bundle_user_table) . "
            where " . $this->get_plugin_table($this->bundle_user_table) . ".order_id = " . $order_id . ";", OBJECT));
    }

    /**
     * Create the job records on a new order
     *
     * @param $order
     * @param $bundle_id
     * @return array|null
     */
    public function new_bundle_record_job($order, $bundle_id)
    {
        $bundle_data = $this->get_main_bundle();
        $job_ids = [];

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();

            if ($product->get_slug() !== $bundle_data['slug']) {
                continue;
            }

            $item_quantity = $item->get_quantity();

            for ($i = 1; $i <= $item_quantity; $i++) {
                $this->wpdb->insert(
                    $this->get_plugin_table($this->bundle_job_table),
                    ['bundle_id' => $bundle_id, 'created_at' => date("Y-m-d H:i:s")],
                    ['%d', '%s']
                );

                $job_ids[] = $this->wpdb->insert_id;
            }
        }

        if (empty($job_ids)) {
            return null;
        }

        return $job_ids;
    }

    public function instant_expire_all_related_product($bundle_id)
    {
        $jobs_to_expire = $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_job_table) . ".id as job_id from " . $this->get_plugin_table($this->bundle_job_table) . "
            where " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id = " . $bundle_id . ";", OBJECT);


        $this->wpdb->update(
            $this->get_plugin_table($this->bundle_job_table),
            [
                'expires_in' => 0,
                'status' => 0,
            ],
            ['bundle_id' => $bundle_id],
            [
                '%d',
                '%d'
            ],
            ['%d']
        );

        foreach ($jobs_to_expire as $job_to_expire) {
            if (!property_exists($job_to_expire, 'job_id')) {
                continue;
            }

            $this->wpdb->update(
                $this->get_plugin_table($this->bundle_feature_table),
                [
                    'expires_in' => 0,
                    'status' => 0,
                ],
                ['job_id' => $job_to_expire->job_id],
                [
                    '%d',
                    '%d'
                ],
                ['%d']
            );
        }
    }

    /**
     * Create the featured items on a new order
     *
     * @param $order
     * @param $job_ids
     */
    public function new_bundle_record_featured($order, $job_ids)
    {
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $bundle_data = $this->get_bundle_data($product->get_slug());

            if ($bundle_data === false || $bundle_data['type'] !== 'feature-product') {
                continue;
            }

            foreach ($job_ids as $job_id) {
                $this->wpdb->insert(
                    $this->get_plugin_table($this->bundle_feature_table),
                    ['type' => $bundle_data['slug'], 'job_id' => $job_id, 'created_at' => date("Y-m-d H:i:s")],
                    ['%s', '%d', '%s']
                );
            }
        }
    }

    public function new_record_company_feature($order, $key)
    {
        $user_id = $order->get_user_id();
        $existing_data = get_user_meta($user_id, $key, true);

        if (!empty($existing_data) && is_string($existing_data) && is_array($existing_data = unserialize($existing_data))) {
            if ($existing_data['expires_in'] > 0) {
                return false;
            }
        }

        $data = [
            'status' => 0,
            'expires_in' => 0,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ];

        return update_user_meta($user_id, $key, serialize($data));
    }

    public function new_record_featured($featured_order)
    {
        if (!array_key_exists('product_id', $featured_order) || !array_key_exists('post_id', $featured_order)) {
            falke_add_notice(__('An error has occured during the order process', $this->dfpbm_domain), 'error');
            return false;
        }

        $slug = get_post_field('post_name', $featured_order['product_id']);
        $post_id = $featured_order['post_id'];
        $job_id = $this->get_job_id_by_post_id($post_id);

        if (empty($job_id)) {
            return false;
        }

        if (!$this->is_job_has_featured($job_id[0]->id, $slug)) {
            $this->wpdb->insert(
                $this->get_plugin_table($this->bundle_feature_table),
                ['type' => $slug, 'job_id' => $job_id[0]->id, 'created_at' => date("Y-m-d H:i:s")],
                ['%s', '%d', '%s']
            );

            return true;
        }

        return false;
    }

    public function get_user_id_by_job_id($job_id)
    {
        if (empty($job_id)) {
            return false;
        }

        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_user_table) . ".user_id from " . $this->get_plugin_table($this->bundle_user_table) . " 
            join " . $this->get_plugin_table($this->bundle_job_table) . " on
            " . $this->get_plugin_table($this->bundle_user_table) . ".id = " . $this->get_plugin_table($this->bundle_job_table) . ".bundle_id
            where " . $this->get_plugin_table($this->bundle_job_table) . ".id = " . $job_id . ";", OBJECT);
    }

    public function get_all_active_featureds()
    {
        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_feature_table) . ".id as feature_id,
            " . $this->get_plugin_table($this->bundle_feature_table) . ".expires_in as expire_time,
            CONVERT_TZ(" . $this->get_plugin_table($this->bundle_feature_table) . ".updated_at,'+00:00','-02:00') as last_update_time
            from " . $this->get_plugin_table($this->bundle_feature_table) . " where
            " . $this->get_plugin_table($this->bundle_feature_table) . ".status = 1;", OBJECT);
    }

    public function get_all_active_jobs()
    {
        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_job_table) . ".id as job_id,
            " . $this->get_plugin_table($this->bundle_job_table) . ".post_id as post_id,
            " . $this->get_plugin_table($this->bundle_job_table) . ".expires_in as expire_time,
            CONVERT_TZ(" . $this->get_plugin_table($this->bundle_job_table) . ".updated_at,'+00:00','-02:00') as last_update_time
            from " . $this->get_plugin_table($this->bundle_job_table) . " where
            " . $this->get_plugin_table($this->bundle_job_table) . ".post_id is not null and
            " . $this->get_plugin_table($this->bundle_job_table) . ".status = 1;", OBJECT);
    }

    public function get_bundles_to_expire()
    {
        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_user_table) . ".id as bundle_id
            from " . $this->get_plugin_table($this->bundle_user_table) . " where 
            " . $this->get_plugin_table($this->bundle_user_table) . ".expires_at < curdate() and
            " . $this->get_plugin_table($this->bundle_user_table) . ".status = 1;", OBJECT);
    }

    public function get_jobs_to_expire()
    {
        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_job_table) . ".id as job_id,
            " . $this->get_plugin_table($this->bundle_job_table) . ".post_id
            from " . $this->get_plugin_table($this->bundle_job_table) . " where 
            " . $this->get_plugin_table($this->bundle_job_table) . ".expires_in <= 0 and
            " . $this->get_plugin_table($this->bundle_job_table) . ".status = 1;", OBJECT);
    }

    public function get_features_to_expire()
    {
        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_feature_table) . ".id as feature_id
            from " . $this->get_plugin_table($this->bundle_feature_table) . " where 
            " . $this->get_plugin_table($this->bundle_feature_table) . ".expires_in <= 0 and
            " . $this->get_plugin_table($this->bundle_feature_table) . ".status = 1;", OBJECT);
    }

    public function is_job_has_featured($job_id, $featured_slug)
    {
        if (empty($job_id) || empty($featured_slug)) {
            return false;
        }

        $result = $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_job_table) . ".id from " . $this->get_plugin_table($this->bundle_job_table) . "
            join " . $this->get_plugin_table($this->bundle_feature_table) . " on 
            " . $this->get_plugin_table($this->bundle_feature_table) . ".job_id = " . $this->get_plugin_table($this->bundle_job_table) . ".id
            where " . $this->get_plugin_table($this->bundle_job_table) . ".id = " . $job_id . " and
            " . $this->get_plugin_table($this->bundle_feature_table) . ".type = '" . $featured_slug . "';", OBJECT);

        return !empty($result);
    }

    public function get_product_status($table, $where_columns, $where_conditionals)
    {
        if (empty($table) || empty($where_columns) || empty($where_conditionals) || !is_array($where_columns) || !is_array($where_conditionals) || count($where_columns) !== count($where_conditionals)) {
            return false;
        }

        $conditional_string = "";

        foreach ($where_columns as $key => $where_column) {
            $conditional_string .= $where_column . " = '" . $where_conditionals[$key] . "'";

            if (count($where_columns) - 1 > $key) {
                $conditional_string .= " and ";
            }
        }

        return $this->wpdb->get_results("select status from " . $table . " where " . $conditional_string . ";", OBJECT);
    }

    public function get_job_id_by_post_id($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        return $this->wpdb->get_results("select " . $this->get_plugin_table($this->bundle_job_table) . ".id from " . $this->get_plugin_table($this->bundle_job_table) . " 
            where " . $this->get_plugin_table($this->bundle_job_table) . ".post_id = " . $post_id, OBJECT);
    }

    /**
     * Create the user assoc table
     *
     * @param String $collate
     * @return string
     */
    protected function create_user_assoc_table(String $collate)
    {
        return "CREATE TABLE " . $this->get_plugin_table($this->bundle_user_table) . " (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    user_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                    order_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                    status TINYINT(4) NOT NULL DEFAULT '1',
                    expires_at DATETIME NULL DEFAULT NULL,
                    created_at DATETIME NULL DEFAULT NULL,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY  (id)
                ) " . $collate . ";";
    }

    /**
     * Create the job assoc table
     *
     * @param String $collate
     * @return string
     */
    protected function create_job_assoc_table(String $collate)
    {
        return "CREATE TABLE " . $this->get_plugin_table($this->bundle_job_table) . " (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    bundle_id INT(11) NULL DEFAULT NULL,
                    status TINYINT(4) NOT NULL DEFAULT '0',
                    expires_in INT NULL DEFAULT '0',
                    post_id BIGINT(20) NULL DEFAULT NULL,
                    created_at DATETIME NULL DEFAULT NULL,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                ) " . $collate . ";";
    }

    /**
     * Create the featured assoc table
     *
     * @param String $collate
     * @return string
     */
    protected function create_featured_assoc_table(String $collate)
    {
        return "CREATE TABLE " . $this->get_plugin_table($this->bundle_feature_table) . " (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    type VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_520_ci',
                    job_id INT(11) NULL DEFAULT NULL,
                    status TINYINT(4) NOT NULL DEFAULT '0',
                    expires_in INT NULL DEFAULT '0',
                    created_at DATETIME NULL DEFAULT NULL,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                ) " . $collate . ";";
    }

    /**
     * Creates the default woocommerce products we work with from the json config file
     */
    public function create_woocommerce_products()
    {
        foreach ($this->bundle_config as $extra_args) {
            if (array_key_exists('slug', $extra_args) && !empty(get_page_by_path($extra_args['slug'], OBJECT, 'product'))) {
                continue;
            }

            $product_object = new WC_Product();
            $product_object->set_name($extra_args['name']);
            $product_object->set_slug($extra_args['slug']);
            $product_object->set_status("publish");
            $product_object->set_description($extra_args['description']);
            $product_object->set_price($extra_args['price']);
            $product_object->set_regular_price($extra_args['price']);
            $product_object->set_reviews_allowed(false);
            $product_object->save();
        }
    }
}
