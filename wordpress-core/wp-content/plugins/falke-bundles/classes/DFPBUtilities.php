<?php

/**
 * Class DFPBUtilities
 *
 * Das Falke Personal Bundle Utilities
 * @package Das Falke Personal Bundle plugins
 */

class DFPBUtilities
{
    /**
     * @var string
     */
    private $plugin_prefix = "das_falke";

    /**
     * Stores json config file data decoded into objects
     *
     * @var array|mixed|object
     */
    public $bundle_config;

    /**
     * @var
     */
    public $jobseeker_multilang;

    /**
     * @var
     */
    public $employer_multilang;

    /**
     * @var int
     */
    public $max_job_post_quantity = 100;

    /**
     * @var int
     */
    public $min_job_post_quantity = 1;

    /**
     * @var string
     */
    public $dfpbm_domain = 'dasfalke-bundlemanager';

    /**
     * @var wpdb
     */
    public $wpdb;

    /**
     * DFPBUtilities constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->jobseeker_multilang = __('Jobseeker', 'dasfalke-role');
        $this->employer_multilang = __('Employer', 'dasfalke-role');
        $this->bundle_config = $this->get_bundle_info();
    }

    /**
     * Returns the price of a single item minus the discount added on bundle package depending on overall item quantity
     *
     * @param Int $quantity
     * @return bool|float
     */
    public function get_single_item_price_when_discounted(Int $quantity = 0)
    {
        if ($quantity <= 0) {
            return false;
        }

        $quantity_discounts = json_decode(file_get_contents(DAS_FALKE_QUANTITY_DISCOUNTS));

        if (is_array($quantity_discounts)) {
            $quantity_discounts = array_shift($quantity_discounts);
        }

        foreach ($quantity_discounts->quantity_discounts as $discount_quantity => $discount) {
            if ($quantity === (int)$discount_quantity) {
                return (float)($discount / $quantity);
            }
        }

        return false;
    }

    /**
     * @param String $table_name
     * @return string
     */
    public function get_eve_table(String $table_name)
    {
        return $this->wpdb->prefix . $table_name;
    }

    /**
     * @param String $table_name
     * @return string
     */
    public function get_plugin_table(String $table_name)
    {
        $the_table = $this->plugin_prefix . "_" . $table_name;
        return $the_table;
    }

    /**
     * Retrieves the whole bundle info from the json config file
     *
     * @return array|mixed|object
     */
    private function get_bundle_info()
    {
        return json_decode(file_get_contents(DAS_FALKE_BUNDLE_CONFIG), true);
    }

    public function get_all_non_bundle_feature_product_slugs()
    {
        $slugs = [];

        foreach ($this->bundle_config as $bundle) {
            if (!empty($bundle['slug']) && $bundle['type'] === 'feature-product' && strpos($bundle['slug'], 'bundle') === false) {
                $slugs[] = $bundle['slug'];
            }
        }

        if (empty($slugs)) {
            return null;
        }

        return $slugs;
    }

    /**
     * Retrieves the full data of one bundle by slug
     *
     * @param String $slug
     * @return bool
     */
    public function get_bundle_data(String $slug = 'job-post')
    {
        foreach ($this->bundle_config as $bundle) {
            if (array_key_exists('slug', $bundle) && $bundle['slug'] === $slug) {
                return $bundle;
            }
        }

        return false;
    }

    /**
     * Compares given slug to plugin pre-defined products, if exists returns true
     *
     * @param String|null $slug
     * @return bool
     */
    public function is_product_exists(String $slug = null)
    {
        foreach ($this->bundle_config as $bundle) {
            if ($bundle['slug'] === $slug) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves all available slugs of the pre-defined products
     *
     * @return array|null
     */
    public function get_plugin_product_slugs()
    {
        $slugs = [];

        foreach ($this->bundle_config as $bundle) {
            if (array_key_exists('slug', $bundle) && !empty($bundle['slug'])) {
                $slugs[] = $bundle['slug'];
            }
        }

        if (empty($slugs)) {
            return null;
        }

        return $slugs;
    }

    /**
     * Retrieves all products that match the given type
     *
     * @param String $type
     * @return array|null
     */
    public function get_plugin_products_by_type(String $type)
    {
        $bundles = [];

        foreach ($this->bundle_config as $bundle) {
            if (array_key_exists('type', $bundle) && !empty($bundle['type']) && $bundle['type'] === $type) {
                $bundles[] = $bundle;
            }
        }

        if (empty($bundles)) {
            return null;
        }

        return $bundles;
    }

    /**
     * Retrieves a single field of all products that matches type
     *
     * @param String $type
     * @param String $field
     * @return array|null
     */
    public function get_field_by_type(String $type, String $field)
    {
        $bundles = [];

        foreach ($this->bundle_config as $bundle) {
            if (array_key_exists('type', $bundle) && !empty($bundle['type']) && $bundle['type'] === $type && array_key_exists($field, $bundle) && !empty($bundle[$field])) {
                $bundles[] = $bundle[$field];
            }
        }

        if (empty($bundles)) {
            return null;
        }

        return $bundles;
    }

    /**
     * Retrieves type_category by product slug
     *
     * @param String $slug
     * @return bool
     */
    public function get_typecategory_by_slug(String $slug)
    {
        foreach ($this->bundle_config as $bundle) {
            if (array_key_exists('slug', $bundle) && !empty($bundle['slug']) && $bundle['slug'] === $slug && array_key_exists('type_category', $bundle) && !empty($bundle['type_category'])) {
                return $bundle['type_category'];
            }
        }

        return false;
    }

    /**
     * Retrieves slugs by product type category
     *
     * @param String $type_cat
     * @return array|null
     */
    public function get_slugs_by_typecategory(String $type_cat)
    {
        $slugs = [];

        foreach ($this->bundle_config as $bundle) {
            if (array_key_exists('type_category', $bundle) && !empty($bundle['type_category']) && $bundle['type_category'] === $type_cat && array_key_exists('slug', $bundle) && !empty($bundle['slug'])) {
                $slugs[] = $bundle['slug'];
            }
        }

        if (empty($slugs)) {
            return null;
        }

        return $slugs;
    }

    /**
     * Retrieves all products that match the given type category
     *
     * @param String $type
     * @return array|null
     */
    public function get_plugin_products_by_typecategory(String $type_category)
    {
        $bundles = [];

        foreach ($this->bundle_config as $bundle) {
            if (array_key_exists('type_category', $bundle) && !empty($bundle['type_category']) && $bundle['type_category'] === $type_category) {
                $bundles[] = $bundle;
            }
        }

        if (empty($bundles)) {
            return null;
        }

        return $bundles;
    }

    /**
     * Retrieves all products that match the given type
     *
     * @param String $type
     * @return array|null
     */
    public function get_plugin_products_by_slug(String $slug)
    {
        $bundles = [];

        foreach ($this->bundle_config as $bundle) {
            if (array_key_exists('slug', $bundle) && !empty($bundle['slug']) && $bundle['slug'] === $slug) {
                $bundles[] = $bundle;
            }
        }

        if (empty($bundles)) {
            return null;
        }

        return $bundles;
    }

    /**
     * Retrieves the slug of the main product by plugin given unique type
     *
     * @return bool
     */
    public function get_main_product_slug()
    {
        $main_bundle_type = 'main-product';

        foreach ($this->bundle_config as $bundle) {
            if ($bundle['type'] === $main_bundle_type) {
                return $bundle['slug'];
            }
        }

        return false;
    }

    /**
     * Retrieves the slug of the main bundle by plugin given unique type
     *
     * @return bool
     */
    public function get_main_bundle_slug()
    {
        $main_bundle_type = 'bundle';

        foreach ($this->bundle_config as $bundle) {
            if ($bundle['type'] === $main_bundle_type) {
                return $bundle['slug'];
            }
        }

        return false;
    }

    /**
     * Retrieves the main bundle by plugin given unique type
     *
     * @return bool
     */
    public function get_main_bundle()
    {
        $main_bundle_type = 'main-product';

        foreach ($this->bundle_config as $bundle) {
            if ($bundle['type'] === $main_bundle_type) {
                return $bundle;
            }
        }

        return false;
    }

    /**
     * Retrieves a single field of a single bundle by slug
     *
     * @param $slug
     * @param $field
     * @return bool
     */
    public function get_product_field_by_slug($slug, $field)
    {
        $products = $this->get_plugin_products_by_slug($slug);

        foreach ($products as $product) {
            if (array_key_exists($field, $product)) {
                return $product[$field];
            }
        }

        return false;
    }

    /**
     * Converts days, hours or minutes into seconds
     *
     * @param Int|null $days
     * @param Int|null $hours
     * @param Int|null $minutes
     * @return float|int
     */
    public function get_expire_in_seconds(Int $days = null, Int $hours = null, Int $minutes = null)
    {
        $timestamp = 0;

        if ($days !== null) {
            $timestamp += $days * 24 * 60 * 60;
        }

        if ($hours !== null) {
            $timestamp += $hours * 60 * 60;
        }

        if ($minutes !== null) {
            $timestamp += $minutes * 60;
        }

        return $timestamp;
    }

    /**
     * Converts seconds to humanly readable time
     *
     * @param $seconds
     * @return string
     * @throws Exception
     */
    function seconds_to_time($seconds)
    {
        if (empty($seconds)) {
            return null;
        }

        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");

        return $dtF->diff($dtT)->format('%a days, %h hours, and %i minutes');
    }

    /**
     * Converts seconds to humanly readable time (days only)
     *
     * @param $seconds
     * @return string
     * @throws Exception
     */
    function seconds_to_days($seconds)
    {
        if (empty($seconds)) {
            return null;
        }

        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");

        return $dtF->diff($dtT)->format('%a');
    }

    /**
     * Returns easily readable and flexible time format from timestamp
     *
     * @param $time
     * @return bool|string
     */
    public function time_ago($time)
    {
        if (empty($time)) {
            return false;
        }
        
        $time_difference = time() - $time;

        if ($time_difference < 60) {
            return __('now', $this->dfpbm_domain);
        } elseif ($time_difference < 3600) {
            $time = round($time_difference / 60);
            return sprintf(__('%d %s ago', $this->dfpbm_domain), $time, $time > 1 ? __('minutes', $this->dfpbm_domain) : __('minute', $this->dfpbm_domain));
        } elseif ($time_difference < 3600 * 24) {
            $time = round($time_difference / 3600);
            return sprintf(__('%d %s ago', $this->dfpbm_domain), $time, $time > 1 ? __('hours', $this->dfpbm_domain) : __('hour', $this->dfpbm_domain));
        } elseif ($time_difference < 3600 * 24 * 2) {
            return __('yesterday', $this->dfpbm_domain);
        } else {
            $before_format = '%e %b';
            $before_format_year = '%e %b, %Y';

            return strftime(date('Y', $time) == date('Y') ? $before_format : $before_format_year, $time);
        }
    }

    public function handle_job_search($query_data, $extra_args = null)
    {
        $query_args = [
            'post_type' => 'post',
        ];

        if (is_array($extra_args)) {
            $query_args = array_merge($query_args, $extra_args);
        }

        $meta_query = ['meta_query' => ['relation' => 'AND',]];

        if (!empty($query_data['search'])) {
            $freeword_search_keys = [
                'job_title',
                'job_description',
                'employment_type',
                'employment_nature',
                'employment_education',
                'employment_tasks',
                'employment_requirements',
                'employment_advantage',
            ];

            $assembled_freeword_meta_query = ['relation' => 'OR'];

            foreach ($freeword_search_keys as $key) {
                $assembled_freeword_meta_query[] = [
                    'key' => $key,
                    'value' => $query_data['search'],
                    'compare' => 'LIKE'
                ];
            }

            $query_args = array_merge($query_args, ['meta_query' => $assembled_freeword_meta_query]);
        }

        if (!empty($query_data['selected_locations']) && empty($query_data['selected_professions'])) {
            $location_post_ids = $this->get_post_ids_by_meta_terms([$query_data['selected_locations']], 'locations', 'selected_locations');

            if (!empty($location_post_ids) && is_array($location_post_ids)) {
                $query_args = array_merge($query_args, ['post__in' => $location_post_ids]);
            }
        }

        if (!empty($query_data['selected_professions'])) {
            if (!empty($query_data['selected_locations'])) {
                $term_query_data = [
                    0=> [
                        'meta_name' => 'professions',
                        'meta_key' => 'selected_professions',
                        'term_id' => [$query_data['selected_professions']],
                    ],
                    1 => [
                        'meta_name' => 'locations',
                        'meta_key' => 'selected_locations',
                        'term_id' => [$query_data['selected_locations']],
                    ],
                ];

                $profession_post_ids = $this->get_post_ids_by_multiple_meta_terms($term_query_data);
            } else {
                $profession_post_ids = $this->get_post_ids_by_meta_terms([$query_data['selected_professions']], 'professions', 'selected_professions');
            }

            if (!empty($profession_post_ids) && is_array($profession_post_ids)) {
                if (array_key_exists('post__in', $query_args)) {
                    $multiple_args = array_merge($query_args['post__in'], $profession_post_ids);
                    $query_args = array_merge($query_args, ['post__in' => $multiple_args]);
                } else {
                    $query_args = array_merge($query_args, ['post__in' => $profession_post_ids]);
                }
            }
        }

        if (!empty($query_data['loc'])) {
            $term_ids = get_terms([
                'taxonomy' => 'locations',
                'hide_empty' => false,
                'name__like' => $query_data['loc'],
                'fields' => 'ids'
            ]);

            $location_post_ids = $this->get_post_ids_by_meta_terms($term_ids, 'locations', 'selected_locations');

            if (!empty($location_post_ids) && is_array($location_post_ids)) {
                if (array_key_exists('post__in', $query_args)) {
                    $multiple_args = array_unique(array_merge($query_args['post__in'], $location_post_ids));
                    $query_args = array_merge($query_args, ['post__in' => $multiple_args]);
                } else {
                    $query_args = array_merge($query_args, ['post__in' => $location_post_ids]);
                }
            }
        }

        $sidebar_filters = ['employment_type', 'employment_nature', 'employment_education'];

        foreach ($sidebar_filters as $filter) {
            if (!empty($query_data[$filter])) {
                $meta_query['meta_query'][] = [
                    'key' => $filter,
                    'value' => $query_data[$filter],
                ];

                $query_args = array_merge($query_args, $meta_query);
            }
        }

        $job_list_query = new WP_Query($query_args);
        wp_reset_query();
        return $job_list_query;
    }

    public function get_post_ids_by_meta_terms(Array $term_ids, $meta_name, $meta_key, $without_children = false)
    {
        if (empty($term_ids) || !is_array($term_ids)) {
            return [0];
        }

        global $wpdb;
        $meta_ids = [];

        if ($without_children === false) {
            $meta_ids = $this->get_falke_term_children($term_ids, $meta_name);

            if (empty($meta_ids) || !is_array($meta_ids)) {
                return [0];
            }
        }

        $meta_ids = array_merge($term_ids, $meta_ids);

        $results = $wpdb->get_results("select " . $wpdb->prefix . "postmeta.post_id from " . $wpdb->prefix . "postmeta join " . $wpdb->prefix . "posts on " . $wpdb->prefix . "posts.id = " . $wpdb->prefix . "postmeta.post_id 
            where " . $wpdb->prefix . "posts.post_status = 'publish' and
            meta_key = '" . $meta_key . "' and 
            meta_value in (" . implode(',', $meta_ids) . ");", OBJECT);

        if (!empty($results)) {
            return array_column($results, 'post_id');
        }

        return [0];
    }

    public function get_post_ids_by_multiple_meta_terms(Array $term_data, $without_children = false)
    {
        if (empty($term_data) || !is_array($term_data)) {
            return [0];
        }

        global $wpdb;
        $matches = 0;
        $conditional_query_string = "";

        foreach($term_data as $key => $data) {
            if (!empty($meta_ids = $this->get_falke_term_children($data['term_id'], $data['meta_name']))) {
                if (!empty($conditional_query_string)) {
                    $conditional_query_string .= " and flk_postmeta.post_id in (select flk_postmeta.post_id from flk_postmeta
                join flk_posts on flk_posts.id = flk_postmeta.post_id
                where flk_posts.post_status = 'publish' and meta_key = '" . $data['meta_key'] . "' and 
                meta_value in (" . implode(',', array_merge($data['term_id'], $meta_ids)) . ")) ";
                } else {
                    $conditional_query_string .= "meta_key = '" . $data['meta_key'] . "' and 
                meta_value in (" . implode(',', array_merge($data['term_id'], $meta_ids)) . ")";
                }
            }
        }

        if (empty($conditional_query_string)) {
            return [0];
        }

        $results = $wpdb->get_results("select " . $wpdb->prefix . "postmeta.post_id from " . $wpdb->prefix . "postmeta join " . $wpdb->prefix . "posts on " . $wpdb->prefix . "posts.id = " . $wpdb->prefix . "postmeta.post_id
            where " . $wpdb->prefix . "posts.post_status = 'publish' and " . $conditional_query_string . ";", OBJECT);

        if (!empty($results)) {
            return array_column($results, 'post_id');
        }

        return [0];
    }

    public function get_falke_term_children(Array $term_ids, $meta_name, $hierarchically = true)
    {

        global $wpdb;
        $final_results = [];

        // Because right now, we have a maximum level depth of 3 dropdowns, and we dont want to include the last one for sure, because it cannot have children, so we do a maximum of 2 iterations
        $iterrator_max = 1;

        if ($hierarchically === false) {
            $iterrator_max = 0;
        }

        for ($i = 0; $i <= $iterrator_max; $i++) {
            $results = $wpdb->get_results("select " . $wpdb->prefix . "term_taxonomy.term_id from " . $wpdb->prefix . "term_taxonomy 
                  where parent in (" . implode(',', $term_ids) . ") and taxonomy = '" . $meta_name . "';", OBJECT);

            if (empty($results) || !is_array($results)) {
                continue;
            }

            $results = $term_ids = array_column($results, 'term_id');
            $final_results = array_merge($final_results, $results);
        }

        return $final_results;
    }
}
