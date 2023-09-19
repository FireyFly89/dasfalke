<?php
/**
 * DAS FALKE PERSONAL EXCLUSIVE STUFF ONLY
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package eve
 */

$create_dropdown_action = 'create_dropdown';

// Initializations
add_action('wp_ajax_nopriv_falke_user_request', 'validate_user_request');
add_action('wp_ajax_falke_user_request_logged', 'falke_logout_user');
add_action('wp_ajax_falke_jobalert_create', 'create_job_alert');
add_action('wp_ajax_' . $create_dropdown_action, 'handle_falke_dropdown');
add_action('wp_ajax_nopriv_' . $create_dropdown_action, 'handle_falke_dropdown');
add_action('init', 'change_post_object_label');
add_action('wp_enqueue_scripts', 'falke_scripts');
add_action('admin_enqueue_scripts', 'falke_scripts_admin');
define('POSTS_RENAMED', __('Jobs', 'dasfalke-admin'));

if (!wp_doing_cron() && !session_id()) {
    session_start();
}

// Roles
define('EMPLOYER_ROLE', 'employer');
define('JOBSEEKER_ROLE', 'jobseeker');

// Page URL-s
define('REGISTER_PAGE_URL', 'register');
define('LOGIN_PAGE_URL', 'login');
define('PRICING_PAGE_URL', 'pricing');
define('SUCCESS_NOTICE', 'success_notice');
define('ERROR_NOTICE', 'error_notice');
define('WARNING_NOTICE', 'warning_notice');
define('FALKE_NOTICES', 'falke_notices');

function falke_theme_setup_after()
{
    add_image_size('profile-image', 100, 100, true);
}
add_action('after_setup_theme', 'falke_theme_setup_after');

// Allow BR tags in wp_editor
add_action( 'init', function () {
    global $allowedtags;
    $allowedtags['br'] = [];
});

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
add_action('init', 'register_custom_taxonomies');

/**
 * Returns current URL
 *
 * @return string|void
 */
function get_current_url()
{
    global $wp;
    return home_url($wp->request);
}

/**
 * Overwrite cart page redirect to checkout page
 *
 * @return string
 */
function themeprefix_add_to_cart_redirect()
{
    global $woocommerce;
    return wc_get_checkout_url();
}
add_filter('woocommerce_add_to_cart_redirect', 'themeprefix_add_to_cart_redirect');

/**
 * Rewrite woocommerce cart button text
 *
 * @return string
 */
function themeprefix_cart_button_text()
{
    return __('Purchase', 'woocommerce');
}

//Add New Pay Button Text
add_filter('woocommerce_product_single_add_to_cart_text', 'themeprefix_cart_button_text');
add_filter('woocommerce_product_add_to_cart_text', 'themeprefix_cart_button_text');

function no_woo_messages()
{
    return "";
}
add_filter('woocommerce_add_message', 'no_woo_messages');
// add_filter('woocommerce_checkout_coupon_message', 'no_woo_messages');

// Change "Posts" to "Jobs" in the admin menu
add_action('admin_menu', function () {
    global $menu;
    $menu[5][0] = POSTS_RENAMED;
});

// So we can use wordpress's usertable to register full names with accents
//function sanitize_user_utf8($username, $raw_username, $strict)
//{
//    return sanitize_text_field($raw_username);
//}
//add_filter('sanitize_user', 'sanitize_user_utf8', 10, 3);

/**
 * Enqueue and localize falke scripts and styles
 */
function falke_scripts()
{
    global $create_dropdown_action;

    wp_enqueue_script('falke-scripts', get_template_directory_uri() . '/js/falke.js', array(), progresseve_verison_control(), true);
    wp_enqueue_script('falke-scripts-meta', get_template_directory_uri() . '/js/metaboxes.js', array(), progresseve_verison_control(), true);
    wp_localize_script('falke-scripts', 'localized_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'ajax_nonce' => wp_create_nonce('ajax_nonce'),
        'home_url' => home_url(),
        'dropdown_action' => $create_dropdown_action,
    ]);
}

/**
 * Enqueue and localize falke admin scripts and styles
 */
function falke_scripts_admin()
{
    global $create_dropdown_action;

    wp_enqueue_script('falke-scripts', get_template_directory_uri() . '/js/falke.js', array(), progresseve_verison_control(), true);
    wp_enqueue_script('falke-scripts-meta', get_template_directory_uri() . '/js/metaboxes.js', array(), progresseve_verison_control(), true);
    wp_localize_script('falke-scripts', 'localized_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'ajax_nonce' => wp_create_nonce('ajax_nonce'),
        'home_url' => home_url(),
        'dropdown_action' => $create_dropdown_action
    ]);
}

// "wp_roles()->is_role" is probably more future proof than get_role(), thus using that
if (!wp_roles()->is_role(EMPLOYER_ROLE)) {
    add_role(EMPLOYER_ROLE, __('Employer', 'dasfalke-role'));
}

if (!wp_roles()->is_role(JOBSEEKER_ROLE)) {
    add_role(JOBSEEKER_ROLE, __('Jobseeker', 'dasfalke-role'));
}

// Function to change post object labels to "jobs"
function change_post_object_label()
{
    global $wp_post_types;
    $labels = &$wp_post_types['post']->labels;
    $labels->name = POSTS_RENAMED;
    $labels->all_items = 'All ' . POSTS_RENAMED;
    $labels->singular_name = POSTS_RENAMED;
    $labels->add_new = 'Add ' . POSTS_RENAMED;
    $labels->add_new_item = 'Add ' . POSTS_RENAMED;
    $labels->edit_item = 'Edit ' . POSTS_RENAMED;
    $labels->new_item = POSTS_RENAMED;
    $labels->view_item = 'View ' . POSTS_RENAMED;
    $labels->search_items = 'Search ' . POSTS_RENAMED;
    $labels->not_found = 'No ' . POSTS_RENAMED . ' found';
    $labels->not_found_in_trash = 'No ' . POSTS_RENAMED . ' found in Trash';
}

/**
 * Assemble WC notice type error message for ajax calls
 *
 * @param String $message
 * @param String $type
 * @param String|null $redirect
 * @return array
 */
function send_json_wc_notice(String $message, String $type = 'error', String $redirect = null)
{
    $notice = "<ul class='woocommerce-" . $type . "' role='alert'>";
    $notice .= "<li>";
    $notice .= $message;
    $notice .= "</li>";
    $notice .= "</ul>";

    $return_json = [
        $type => $notice,
    ];

    if (!empty($redirect)) {
        $return_json['redirect'] = $redirect;
    }

    return $return_json;
}

/**
 * Validates all ajax user requests through specific forms
 *
 * @return bool
 */
function validate_user_request()
{
    if (empty($_REQUEST['data'])) {
        return false;
    }

    // Validate the nonce, so there can be less hijackers
    if (!check_ajax_referer('ajax_nonce', 'nonce')) {
        wp_nonce_ays('ajax_nonce');
    }

    $form_data = $_REQUEST['data'];
    $fieldsRequired = ['email', 'password'];
    $error_response = "";

    if (!empty($form_data['request']) && $form_data['request'] === 'register') {
        $fieldsRequired[] = 'role';

        if ($form_data['role'] === EMPLOYER_ROLE) {
            if (!array_key_exists('company_name', $form_data) || empty($form_data['company_name'])) {
                $error_response = __('All fields are required!', 'dasfalke-register');
            }
        }
    }

    // Check if all fields are given, and are not empty, since they are required for the registration process
    foreach ($fieldsRequired as $requiredField) {
        if (!array_key_exists($requiredField, $form_data) || empty($form_data[$requiredField])) {
            $error_response = __('All fields are required!', 'dasfalke-register');
        }
    }

    if (!empty($error_response)) {
        wp_send_json(send_json_wc_notice($error_response));
    }

    call_user_func_array('falke_' . $form_data['request'] . '_user', [$form_data, $error_response]);
}

function generate_unique_username($username) {
    $iterrator = 1;

    while(username_exists($username) !== false) {
        $username .= "-" . $iterrator;
    }

    return $username;
}

/**
 * Registers user by 3 basic, but required information given
 *
 * @param $form_data
 * @param $error_response
 */
function falke_register_user($form_data, $error_response)
{
    // Simple RFC php standard email validation and make the password at least 3 characters long for now
    if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error_response = __('Invalid E-mail address!', 'dasfalke-register');
    }

    if (strlen($form_data['password']) <= 3) {
        $error_response = __('The password is too short!', 'dasfalke-register');
    }

    if (empty($error_response)) {
        $username = $form_data['email'];

//        if ($form_data['role'] === EMPLOYER_ROLE) {
//            $username = generate_unique_username($form_data['company_name']);
//        }

        $user_id = wp_create_user(sanitize_user($username), $form_data['password'], $form_data['email']);

        if (is_wp_error($user_id)) {
            $error_response = $user_id->get_error_message();
        } else {
            send_mail_to_new_users($form_data['role'], $form_data['email']);
        }
    }

    if (!empty($error_response)) {
        wp_send_json(send_json_wc_notice($error_response));
    }

    $user_id_role = new WP_User($user_id);
    $user_id_role->set_role($form_data['role']);
    update_user_meta($user_id, 'billing_company', $form_data['company_name']);

    $loginResult = wp_signon([
        'user_login' => $form_data['email'],
        'user_password' => $form_data['password'],
        'remember' => true,
    ], false);

    if (is_wp_error($loginResult)) {
        wp_send_json(send_json_wc_notice(__('Failed to log-in, please log in manually!', 'dasfalke-login', eve_get_login_page_uri())));
    }

    wp_send_json(send_json_wc_notice(__('Successful registration!', 'dasfalke-register'), 'success', eve_get_user_profile_uri()));
}

function send_mail_to_new_users($role, $email)
{
    if ($role === EMPLOYER_ROLE) {
        // TODO: Employer email when registered a new account!
        falke_send_mail([
            'subject' => sprintf(__('Now you are on dasfalkepersonal.at', 'dasfalke-email')),
            'email' => $email,
            'template' => '/mailtemplates/email-new-employer-reg.php',
        ]);
    } else if ($role === JOBSEEKER_ROLE) {
        // TODO: Jobseeker email when registered a new account!
        falke_send_mail([
            'subject' => sprintf(__('Now you are on dasfalkepersonal.at', 'dasfalke-email')),
            'email' => $email,
            'template' => '/mailtemplates/email-new-jobseeker-reg.php',
        ]);
    }
}

/**
 * Logs in user through an ajax call if user exists in database, using wp_signon() method
 *
 * @param $form_data
 */
function falke_login_user($form_data)
{
    if (abort_login_if_deleted($form_data['email']) === true) {

        wp_send_json(send_json_wc_notice(__('Login has failed, account has been deleted from our records.', 'dasfalke-login')));
    }

    $loginResult = wp_signon([
        'user_login' => $form_data['email'],
        'user_password' => $form_data['password'],
        'remember' => true,
    ], false);

    if (is_wp_error($loginResult)) {
        wp_send_json(send_json_wc_notice(__('Login has failed, wrong email or password?', 'dasfalke-login')));
    }

    wp_send_json(send_json_wc_notice(__('Successfully logged in!', 'dasfalke-login'), 'success', eve_get_user_profile_uri()));
}

function abort_login_if_deleted($email) {
    if (empty($email)) {
        return false;
    }

    $user = get_user_by('email', $email);

    if (!property_exists($user, 'ID')) {
        return false;
    }

    $user_id = $user->ID;

    if (!empty(get_user_meta($user_id, 'account_deleted', true))) {
        return true;
    }

    return false;
}

/**
 * Logs out user through an ajax call, if user was logged in using wp_logout() method
 *
 * @return bool
 */
function falke_logout_user()
{
    if (empty($_REQUEST['data'])) {
        return false;
    }

    // Validate the nonce, so there can be less hijackers
    if (!check_ajax_referer('ajax_nonce', 'nonce')) {
        wp_nonce_ays('ajax_nonce');
    }

    clear_facebook_avatar_session();
    wp_logout();
    wp_send_json(send_json_wc_notice(__('Successfully logged out!', 'dasfalke-logout'), 'success', home_url()));
}

function falke_logout_user_wp() {
    clear_facebook_avatar_session();
}
add_action('wp_logout', 'falke_logout_user_wp');

/**
 * Checks if user is logged in with either employer or jobseker role
 *
 * @return bool
 */
function eve_is_user_logged_in()
{
    if (is_user_logged_in()) {
        if (empty($role = wp_get_current_user()->roles)) {
            return false;
        }

        $role = reset($role);

        if ($role !== JOBSEEKER_ROLE && $role !== EMPLOYER_ROLE && $role !== 'administrator') {
            return false;
        }

        return true;
    }

    return false;
}

// Hides admin bar for das falke users (by role)
add_action('init', function () {
    $user_data = eve_get_user();

    if (isset($user_data->roles) && is_array($user_data->roles)) {
        $current_role = reset($user_data->roles);
        $show = false;

        if ($current_role !== false && $current_role === 'administrator') {
            $show = true;
        }

        show_admin_bar($show);
    }
});
/**
 * Returns user role, or false if user is not logged in
 *
 * @return bool|mixed
 */
function eve_get_logged_in_user_role()
{
    if (!empty(eve_is_user_logged_in())) {
        if (empty($role = wp_get_current_user()->roles)) {
            return false;
        }

        return reset($role);
    }

    return false;
}

/**
 * Checks if logged in user has the role of jobseeker
 *
 * @return bool
 */
function eve_is_user_jobseeker($user_id = null)
{
    if (empty($user_id) && !empty(eve_is_user_logged_in())) {
        $role = reset(wp_get_current_user()->roles);

        if ($role === JOBSEEKER_ROLE) {
            return true;
        }
    } else if (!empty($user_id)) {
        $user = eve_get_user_by_id($user_id);

        if (property_exists($user, 'roles') && $user->roles[0] === JOBSEEKER_ROLE) {
            return true;
        }
    }

    return false;
}

/**
 * Checks if logged in user has the role of employer
 *
 * @return bool
 */
function eve_is_user_employer($user_id = null)
{
    if (empty($user_id) && !empty(eve_is_user_logged_in())) {
        if (empty($role = wp_get_current_user()->roles)) {
            return false;
        }

        $role = reset($role);

        if ($role === EMPLOYER_ROLE || $role === 'administrator') {
            return true;
        }
    } else if (!empty($user_id)) {
        $user = eve_get_user_by_id($user_id);

        if (property_exists($user, 'roles') && $user->roles[0] === EMPLOYER_ROLE) {
            return true;
        }
    }

    return false;
}

/**
 * Retrieves the "Login" page url
 *
 * @return string
 */
function eve_get_login_page_uri()
{
    return get_page_url_by_slug(LOGIN_PAGE_URL);
}

/**
 * Retrieves the "Register" page url
 *
 * @return string
 */
function eve_get_register_page_uri()
{
    return get_page_url_by_slug(REGISTER_PAGE_URL);
}

/**
 * Retrieves the "Pricing" page url
 *
 * @return string
 */
function eve_get_pricing_page_uri()
{
    return get_page_url_by_slug(PRICING_PAGE_URL);
}

/**
 * Return current logged in user
 *
 * @return bool|WP_User
 */
function eve_get_user()
{
    if (is_user_logged_in()) {
        return wp_get_current_user();
    }

    return false;
}

function eve_get_user_by_id($user_id)
{
    return get_user_by('id', $user_id);
}

/**
 * Retrieves the woocommerce user profile page url
 *
 * @return bool|false|string
 */
function eve_get_user_profile_uri()
{
    return get_permalink(get_option('woocommerce_myaccount_page_id'));
}

function register_job_meta_boxes()
{
    add_meta_box('job_desc', __('Job description', 'job_metafields'), 'eve_woo_profile_company_edit_job', 'post');
}

add_action('add_meta_boxes', 'register_job_meta_boxes');

/**
 * @param $file
 * @return array|mixed|object
 */
function load_json_data($file)
{
    return json_decode(file_get_contents(get_template_directory() . "/jsons/" . $file . ".json"))->fields;
}

/**
 * Retrieves user name or company name if available. Defaults to email address
 *
 * @return bool|mixed
 */
function get_user_name($user_id = null)
{
    $return_value = false;

    if (empty($user_id)) {
        $user_id = eve_get_user()->ID;
    }

    if (eve_is_user_employer($user_id) && !empty($company_name = get_user_meta($user_id, 'billing_company', true))) {
        $return_value = $company_name;
    } else if (eve_is_user_jobseeker($user_id) && !empty($first_name = get_user_meta($user_id, 'first_name', true))) {
        $return_value = $first_name;
    }

    if (empty($return_value) && !empty($display_name = get_userdata($user_id)->data->display_name)) {
        $return_value = $display_name;
    }

    return $return_value;
}

/**
 * Retrieves user avatar if available. Defaults to default.jpg
 *
 * @return string
 */
function get_user_avatar($user_id = null)
{
    if (empty($user_id) && !empty($user = eve_get_user())) {
        if (!property_exists($user, 'ID')) {
            return false;
        }

        $user_id = $user->ID;
    }

    if (!wp_doing_cron() && array_key_exists('facebook_avatar', $_SESSION) && !empty($fb_avatar_src = $_SESSION['facebook_avatar'])) {
        foreach($_SESSION['facebook_avatar'] as $key => $facebook_avatar) {
            if (array_key_exists('user_id', $facebook_avatar) && $facebook_avatar['user_id'] === $user_id) {
                return $facebook_avatar['src'];
            }
        }
    }

    $image_url = get_template_directory_uri() . '/img/default.jpg';
    $attachment_id = get_user_meta($user_id, 'profile_image', true);

    if (!empty($attachment_id)) {
        $image_url = wp_get_attachment_image_src($attachment_id, 'profile-image');

        if (!empty($image_url) && array_key_exists(0, $image_url)) {
            $image_url = $image_url[0];
        }
    }

    return $image_url;
}

function get_user_data($meta_key, $user_id = null)
{
    if (empty($user_id) && !empty($user = eve_get_user())) {
        $user_id = $user->ID;
    }

    return get_user_meta($user_id, $meta_key, true);
}

function is_add_job_page()
{
    global $wp;

    if (strpos(home_url($wp->request), "my-account/add-job") !== false) {
        return true;
    }

    return false;
}

function is_edit_job_page()
{
    global $wp;

    if (strpos(home_url($wp->request), "my-account/edit-job") !== false) {
        return true;
    }

    return false;
}

function get_salary_text_by_numtype($num)
{
    $num = (int)$num;
    $result = "";

    if ($num === 1) {
        $result = __("gross per hour", 'dasfalke-paymenttype');
    } else if ($num === 2) {
        $result = __("gross per month", 'dasfalke-paymenttype');
    } else if ($num === 3) {
        $result = __("gross per year", 'dasfalke-paymenttype');
    }

    return $result;
}

function get_job_field_names()
{
    return [
        "job_title",
        "job_description",
        "selected_locations",
        "selected_professions",
        "employment_type",
        "employment_nature",
        "employment_education",
        "employment_payment",
        "apply_type",
        "employment_email",
        "employment_tasks",
        "employment_requirements",
        "employment_tags",
        "employment_advantage",
        "employment_refcode",
        "employment_docupload",
        "apply_type_site_field",
        "employment_payment_extent",
        "employment_payment_doe",
    ];
}

if (!function_exists('edit_or_record_new_job')) {
    /**
     * Validates, and records creating new job listings
     *
     * @return bool
     */
    function edit_or_record_new_job()
    {
        if (empty($_GET['job_id'])) {
            falke_add_notice(__('No available job id, or something went wrong, please try again', 'dasfalke-jobmessages'), 'error');
            return false;
        }

        $bundleManager = new DFPBManager();

        if (!is_edit_job_page()) {
            $available_job_slots = $bundleManager->get_available_job_slots(eve_get_user()->ID);

            if (empty($available_job_slots)) {
                falke_add_notice(__('You have no more free job slots left, in order to continue, please purchase more slots', 'dasfalke-jobmessages'), 'error');
                return false;
            }
        }

        if (empty($_POST['submit-job-metas-noncename']) || !wp_verify_nonce($_POST['submit-job-metas-noncename'], 'submit-job-metas-nonce')) {
            falke_add_notice(__('Failed to validate the new job request, please try again', 'dasfalke-jobmessages'), 'error');
            return false;
        }

        $errors = 0;
        $fields = [
            "required" => [
                "job_title",
                "job_description",
                "selected_locations",
                "selected_professions",
                "employment_payment",
                "employment_payment_extent",
                "apply_type",

            ],
            "not_required" => [
                "employment_type",
                "employment_nature",
                "employment_education",
                "employment_requirements",
                "employment_tasks",
                "employment_advantage",
                "employment_refcode",
                "employment_docupload",
                "apply_type_site_field",
                "employment_tags",
                "employment_payment_doe"
            ],
            "dependencies" => [
                "apply_type_site_field" => ["apply_type" => "site"],
                "employment_email" => ["apply_type" => "form"],
                "employment_payment_extent" => ["employment_payment" => [1, 2, 3]],
            ],
            "key_name_pairs" => get_profile_key_name_pairs()
        ];

        foreach ($fields as $type => $field_names) {
            foreach ($field_names as $field_key => $field_name) {
                if ($type === "required") {
                    if (empty($_POST[$field_name])) {
                        falke_add_notice(__('The ' . $fields["key_name_pairs"][$field_name] . ' field is required', 'dasfalke-jobmessages'), 'error');
                        $errors++;
                    }
                } else if ($type === "dependencies") {
                    foreach ($field_name as $dependency_name => $dependencies) {
                        if (!empty($_POST[$dependency_name]) || (isset($_POST[$dependency_name]) && $_POST[$dependency_name] == 0)) {
                            if (is_string($dependencies) && $_POST[$dependency_name] === $dependencies && empty($_POST[$field_key])) {
                                falke_add_notice(__('The ' . $fields["key_name_pairs"][$field_key] . ' field is required', 'dasfalke-jobmessages'), 'error');
                                $errors++;
                            } else if (is_array($dependencies)) {
                                foreach ($dependencies as $dependency) {
                                    if ($_POST[$dependency_name] == $dependency && empty($_POST[$field_key])) {
                                        falke_add_notice(__('The ' . $fields["key_name_pairs"][$field_key] . ' field is required', 'dasfalke-jobmessages'), 'error');
                                        $errors++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($errors <= 0) {
            $post_args = [
                'post_title' => $_POST['job_title'],
                'post_content' => $_POST['job_description'],
            ];

            if (is_edit_job_page()) {
                $post_id = wp_update_post(array_merge($post_args, ['ID' => $_GET['job_id'], 'post_status' => 'draft']));
                $bundleManager->change_status($_GET['job_id'], 0);
                $bundleManager->inactivate_all_featureds_by_post_id($_GET['job_id']);
            } else {
                $post_id = wp_insert_post($post_args);
            }

            foreach ($fields as $type => $field_names) {
                foreach ($field_names as $field_key => $field_name) {
                    if (($type === "required" || $type === "not_required") && (!empty($_POST[$field_name]) || (isset($_POST[$field_name]) && $_POST[$field_name] == 0))) {
                        if ($field_name === 'job_description' || $field_name === 'employment_tasks' || $field_name === 'employment_requirements' || $field_name === 'employment_advantage') {
                            update_post_meta($post_id, $field_name, wp_kses_post($_POST[$field_name]));
                        } else {
                            update_post_meta($post_id, $field_name, sanitize_text_field($_POST[$field_name]));
                        }
                    }
                }
            }

            $admin_email = get_option('admin_email');

            if (is_edit_job_page()) {
                falke_add_notice(__('The job post has been successfully updated', 'dasfalke-jobmessages'), 'success');

                // TEST: Mail template here (the job post has been created, for ADMIN EMAIL only)
                falke_send_mail([
                    'subject' => sprintf(__('A job posting has been updated! Please Check! (%s)', 'dasfalke-email'), $_POST['job_title']),
                    'email' => $admin_email,
                    'template' => '/mailtemplates/email-mod-job-check.php',
                ]);
            } else {
                $bundleManager->update_job_post_id($post_id, eve_get_user()->ID, $_GET['job_id']);
                falke_add_notice(__('The job post has been successfully created', 'dasfalke-jobmessages'), 'success');

                // TODO: Mail template here (the job post has been created)
                // falke_send_mail([
                //    'subject' => sprintf(__('Job successfully created: %s', 'dasfalke_jobapplication'), $_POST['job_title']),
                //    'email' => $_POST['account_email'],
                //    'template' => 'job-application-mail.php',
                // ]);

                // TEST: Mail template here (the job post has been created, for ADMIN EMAIL only)
                falke_send_mail([
                    'subject' => sprintf(__('New job posting! Please check! (%s)', 'dasfalke-email'), $_POST['job_title']),
                    'email' => $admin_email,
                    'template' => '/mailtemplates/email-new-job-check.php',
                ]);
            }

            wp_safe_redirect(home_url("my-account"));
        }

        return false;
    }
}

function check_post_status($new_status, $old_status, $post){
    $user_data = eve_get_user_by_id($post->post_author);

    if ($old_status === 'draft' && $new_status === 'publish') {
        // TEST: Mail template here (job post has been approved by the admin)
		falke_send_mail([
            'subject' => sprintf(__('Your posting "%s" has just been approved - dasfalkepersonal.at', 'dasfalke-email'), $post->post_title),
            'email' => $user_data->user_email,
            'template' => '/mailtemplates/email-job-approved.php',
        ]);
    }
}
add_action('transition_post_status','check_post_status',10,3);

if (!empty($_POST['submit-job-metas'])) {
    add_action('wp', 'edit_or_record_new_job', 99);
}

if (!empty($_POST['change-job-status-button'])) {
    if (!empty($_POST['change-job-status'])) {
        $bundleManager = new DFPBManager();
        $status = 0;

        if ($_POST['change-job-status-button'] === "Activate") {
            $status = 1;
        }

        $post_id = (int)$_POST['change-job-status'];

        $bundleManager->change_status($post_id, $status);
        $associated_featureds = $bundleManager->get_featureds_by_job_id($post_id);

        foreach ($associated_featureds as $associated_featured) {
            $bundleManager->change_status($associated_featured->id, $status, "feature");
        }
    }
}

//if (!empty($_POST['change-feature-status-button'])) {
//    if (!empty($_POST['change-feature-status'])) {
//        $bundleManager = new DFPBManager();
//        $status = 0;
//
//        if ($_POST['change-feature-status-button'] === "Activate") {
//            $status = 1;
//        }
//
//        $bundleManager->change_status((int)$_POST['change-feature-status'], $status, "feature");
//    }
//}

function eve_get_formatted_price($price)
{
    $price = strip_tags(wc_price($price, [
        'decimal_separator' => ',',
        'thousand_separator' => '.',
        'decimals' => 2,
    ]));

    $price = preg_replace( '/\,0++$/', '', $price );
    return $price;
}

function get_profile_key_name_pairs($single_key = "")
{
    $key_name_pairs = [
        "job_title" => 'Job title',
        "job_description" => 'Job description',
        "selected_locations" => 'Address',
        "selected_professions" => 'Profession',
        "employment_type" => 'Employment type',
        "employment_nature" => 'Employment nature',
        "employment_education" => 'Required education',
        "employment_payment" => 'Salary',
        "apply_type" => 'Apply type',
        "employment_email" => 'E-mail address',
        "employment_tasks" => 'Tasks',
        "employment_requirements" => 'Requirements',
        "employment_tags" => 'Tags',
        "employment_advantage" => 'Advantage if applicant has any of these skills',
        "employment_refcode" => 'Reference code',
        "employment_docupload" => 'Documents to upload by applicant',
        "apply_type_site_field" => 'Employer site',
        "employment_payment_extent" => 'Payment extent',
        "employment_payment_doe",
    ];

    if (!empty($single_key) && array_key_exists($single_key, $key_name_pairs)) {
        return $key_name_pairs[$single_key];
    }

    return $key_name_pairs;
}

function get_company_profile_key_name_pairs($single_key = "")
{
    $domain = 'dasfalke-profile';

    $company_data_fields = [
        'billing_company' => __('Company name', $domain),
        'billing_address_1' => __('Address', $domain),
        'billing_city' => __('City', $domain),
        'billing_postcode' => __('ZIP code', $domain),
        'billing_country' => __('Country', $domain),
        'billing_tax_number' => __('UID number', $domain),
        'company_website' => __('Website', $domain),
        'company_whoweare' => __('Who we are', $domain),
        'company_whatweoffer' => __('What we offer', $domain),
        'company_ourexpectation' => __('Our expectations', $domain),
        'company_founding_year' => __('Year of founding', $domain),
        'company_number_of_employees' => __('Number of employees', $domain),
        'company_industry' => __('Industry', $domain),
        'contactperson_honorific' => __('Title', $domain),
        'contactperson_first_name' => __('First name', $domain),
        'contactperson_last_name' => __('Last name', $domain),
        'contactperson_position' => __('Position', $domain),
        'contactperson_email' => __('Email address', $domain),
        'contactperson_phone' => __('Mobile phone number', $domain),
        'profile_image' => __('Company logo', $domain),
    ];

    if (!empty($single_key) && array_key_exists($single_key, $company_data_fields)) {
        return $company_data_fields[$single_key];
    }

    return $company_data_fields;
}

function get_field_if_posted($key, $userdata = false)
{
    if (is_admin() && !empty($post_meta = get_post_meta(get_the_ID(), $key, true))) {
        return $post_meta;
    } else if (is_edit_job_page() && !empty($_GET['job_id']) && !empty($post_meta = get_post_meta($_GET['job_id'], $key, true))) {
        return $post_meta;
    } else if (!empty($_POST[$key]) || (isset($_POST[$key]) && $_POST[$key] == 0)) {
        return $_POST[$key];
    } else if ($userdata === true) {
        $user = eve_get_user();

        if (!empty($user)) {
            return get_user_meta($user->ID, $key, true);
        }
    }

    return "";
}

function get_file_if_posted($meta_type, $meta_key)
{
    $user = eve_get_user();
    $image = [];
    $attachment_id = 0;

    if ($meta_type === 'user_meta' && !empty($user)) {
        $attachment_id = get_user_meta($user->ID, $meta_key, true);
    }

    if (!empty($attachment_id)) {
        $image = wp_get_attachment_metadata($attachment_id);

        if (!empty($image) && array_key_exists('file', $image)) {
            foreach ($image['sizes'] as $size_name => $image_data) {
                $image['sizes'][$size_name]['src'] = wp_get_attachment_image_src($attachment_id, $size_name)[0];
            }

            preg_match('/[^\/]+$/', $image['file'], $matches);

            if (!empty($matches[0])) {
                $image['file_name'] = $matches[0];
            } else {
                $image['file_name'] = "File name not found";
            }
        }
    }

    return $image;
}

function get_field_if_in_get($key)
{
    if (!empty($_GET[$key]) || (isset($_GET[$key]) && $_GET[$key] == 0)) {
        return sanitize_text_field($_GET[$key]);
    }

    return "";
}

function job_save_metas($post_id)
{
    foreach (get_job_field_names() as $field_name) {
        if (!empty($_POST[$field_name]) || (isset($_POST[$field_name]) && $_POST[$field_name] == 0)) {
            update_post_meta($post_id, $field_name, $_POST[$field_name]);
        }
    }
}
add_action('save_post', 'job_save_metas');

function handle_falke_dropdown($taxonomy, $field_labels = [], $extra_classes = "", $placeholder = "", $enable_first_option = null, $required = false)
{
    $clause = "slug = '" . $taxonomy . "'";
    $field_label = "";
    $selected_id = null;

    if (!empty($field_labels[0])) {
        $field_label = $field_labels[0];
    }

    if (isset($_REQUEST['data'])) {
        $request_data = $_REQUEST['data'];
        $term_id = (int)$request_data['term_id'];
        $clause = "term_id = '" . $term_id . "'";
        $taxonomy = sanitize_text_field($request_data['taxonomy']);
        $field_label = sanitize_text_field($request_data['label']);
        $extra_classes = $request_data['extra_classes'];

        if (/*$taxonomy === 'locations' &&*/ !empty($term_id)) {
            $selected_id = $term_id;
        }
    } else if (empty($taxonomy)) {
        return false;
    }

    $name_field = "selected_" . $taxonomy;

    if (!empty($posted_tax_id = get_field_if_posted($name_field)) || !empty($posted_tax_id = get_field_if_in_get($name_field))) {
        $separator = ";";

        $parent_tree_string = get_term_parents_list($posted_tax_id, $taxonomy, [
            'separator' => $separator,
            'link' => false,
        ]);

        $parent_tree = [];

        if (!empty($parent_tree_string)) {
            $parent_tree = array_values(array_filter(explode($separator, $parent_tree_string)));
        }

        $dropdown_datas = [];

        foreach ($parent_tree as $term) {
            $term_data = falke_get_term_by('name', $term);
            $parent_name = $parent_tree[count($parent_tree) - 1];

            if (!empty($parent_name) && property_exists($term_data,'name') && $parent_name !==$term_data->name) {
                $dropdown_datas[] = $term_data;
            }
        }

        foreach ($dropdown_datas as $key => $term_data) {
            $selected_id = isset($dropdown_datas[$key + 1]->term_id) ? $dropdown_datas[$key + 1]->term_id : $posted_tax_id;
            $previous_id = null;

            if (!empty($field_labels[$key])) {
                $field_label = $field_labels[$key];
            }

            if (/*$taxonomy = 'locations' &&*/ $key >= 1) {
                $placeholder = __('All of ', 'dasfalke-dropdowns') . $dropdown_datas[$key]->name;
                $previous_id = $dropdown_datas[$key]->term_id;
            }

            echo make_dropdown(get_terms_by_parent("term_id = '" . $term_data->term_id . "'"), $name_field, $selected_id, $field_label, $extra_classes, $placeholder, $previous_id, $enable_first_option, $required);
        }

        return true;
    }

    if (!is_wien_address($selected_id)) {
        $dropdown = make_dropdown(get_terms_by_parent($clause), $name_field, null, $field_label, $extra_classes, $placeholder, $selected_id, $enable_first_option, $required);

        if (!empty($request_data)) {
            wp_send_json($dropdown);
        }

        echo $dropdown;
    }

    return true;
}

function get_terms_by_parent($clause)
{
    global $wpdb;
    return $wpdb->get_results("select " . $wpdb->prefix . "terms.term_id, " . $wpdb->prefix . "terms.name from " . $wpdb->prefix . "terms
        join " . $wpdb->prefix . "term_taxonomy on " . $wpdb->prefix . "terms.term_id = " . $wpdb->prefix . "term_taxonomy.term_id
        where " . $wpdb->prefix . "term_taxonomy.parent = (select " . $wpdb->prefix . "terms.term_id from " . $wpdb->prefix . "terms where " . $wpdb->prefix . "terms." . $clause . ");", OBJECT);
}

function get_term_children_by_term_id($term_id)
{
    global $wpdb;
    return $wpdb->get_results("select " . $wpdb->prefix . "term_taxonomy.term_id from " . $wpdb->prefix . "term_taxonomy where 
        parent = (select " . $wpdb->prefix . "terms.term_id from " . $wpdb->prefix . "terms where " . $wpdb->prefix . "terms.term_id = '" . $term_id . "');", OBJECT);
}

function falke_get_term_by($clause = 'slug', $value = "")
{
    global $wpdb;
    $result = $wpdb->get_results("select " . $wpdb->prefix . "terms.term_id, " . $wpdb->prefix . "terms.name, " . $wpdb->prefix . "terms.slug from " . $wpdb->prefix . "terms where " . $clause . " = '" . $value . "';", OBJECT);

    if (is_array($result)) {
        $result = reset($result);
    }

    return $result;
}

function make_dropdown($terms, $name_field, $selected = null, $field_label = "", $extra_classes = "", $placeholder = "", $previous_term_id = null, $enable_first_option = false, $required = false)
{
    $dropdown_str = "<div class='form-row select'>";
    $dropdown_str .= "<label class='form-row__label'>" . __($field_label, 'dasfalke-dropdowns') . " " . ($required ? "*" : "") .  "</label>";
    $dropdown_str .= "<div class='form-row__input'>";
    $dropdown_str .= "<select class='" . $extra_classes . "' name='" . $name_field . "'>";

    if (!empty($previous_term_id)) {
        $prev_term_data = falke_get_term_by('term_id', $previous_term_id);

        if (!empty($prev_term_data)) {
            $placeholder = __('All of ', 'dasfalke-dropdowns') . $prev_term_data->name;
        } else {
            $placeholder = __('Please select...', 'dasfalke-dropdowns');
        }

        $dropdown_str .= "<option selected value='" . $previous_term_id . "'>" . $placeholder . "</option>";
    } else {
        if (empty($placeholder)) {
            $placeholder = __('Please select...', 'dasfalke-dropdowns');
        }

        $dropdown_str .= "<option " . ($enable_first_option === false ? 'disabled' : '') . " selected value=''>" . $placeholder . "</option>";
    }

    foreach ($terms as $term_data) {
        $dropdown_str .= "<option " . ($selected == $term_data->term_id ? "selected" : "") . " value='" . esc_attr($term_data->term_id) . "'>";
        $dropdown_str .= $term_data->name;
        $dropdown_str .= "</option>";
    }

    $dropdown_str .= "</select>";
    $dropdown_str .= '<div class="select__arrow"><svg class="i i-arrow" width="16" height="14"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-arrow" href="#i-arrow"></use></svg></div>';
    $dropdown_str .= "</div></div>";
    return $dropdown_str;
}

function make_simple_dropdown($name, $label, $dataset, $selected = "", $default_value = 'Please select...', $key_as_value = false, $required = false)
{
    $wpml_domain = "dasfalke-profile";
    $dropdown_str = "<div class='form-row select'>";
    $dropdown_str .= "<label class='form-row__label'>" . __($label, $wpml_domain) . " " . ($required === true ? "*" : "") . "</label>";
    $dropdown_str .= "<div class='form-row__input'>";
    $dropdown_str .= "<select name='" . $name . "'>";
    $dropdown_str .= "<option disabled selected value=''>" . __($default_value, $wpml_domain) . "</option>";

    foreach ($dataset as $key => $value) {
        $current_val = $value;

        if ($key_as_value === true) {
            $current_val = $key;
        }

        $dropdown_str .= "<option " . ($selected == $current_val ? "selected" : "") . " value='" . $current_val . "'>";
        $dropdown_str .= $value;
        $dropdown_str .= "</option>";
    }

    $dropdown_str .= "</select>";
    $dropdown_str .= '<div class="select__arrow"><svg class="i i-arrow" width="16" height="14"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-arrow" href="#i-arrow"></use></svg></div>';
    $dropdown_str .= "</div></div>";

    return $dropdown_str;
}

function is_wien_address($id) {
    global $wpdb;
    $wien_address_names = get_wien_addresses();
    $result = $wpdb->get_results('SELECT flk_terms.name FROM flk_terms WHERE flk_terms.term_id = "' . $id . '"');

    if (!empty($result[0])) {
        return in_array($result[0]->name, $wien_address_names);
    }

    return false;
}

function get_industries()
{
    return [
        'Automobil / Fahrzeugbau / Zulieferer',
        'Banken / Finanzdienstleistungen',
        'Bauwesen / Immobilien / Gebäudetechnik',
        'Beratung / Consulting',
        'Bildungswesen / Wissenschaft / Forschung',
        'Druck / Papier / Verpackung',
        'Elektronik / Elektrotechnik',
        'Energiewirtschaft / Wasser / Umwelttechnik',
        'Gesundheitswesen / Medizin / Sociales',
        'Handel / Konsumgüter',
        'Handwerk / Gewerbe',
        'Industrie / Produktion',
        'Internet / IT / EDV',
        'Kunst / Kultur',
        'Land / Fortwirtschaft / Holz',
        'Luft- / Raumfahrt',
        'Marketing / PR / Design',
        'Maschinenbau / Anlagenbau',
        'Medien / Verlage',
        'NGO / NPO',
        'Öffentlicher Dienst / Verbände',
        'Personaldienstleistungen',
        'Pharma / Chemie Biotechnologie',
        'Sonstige Branchen',
        'Sport / Freizeit / Entertainment',
        'Telekommunikation',
        'Tourismus / Gastronomie / Hotelliere',
        'Transport / Logistik',
        'Versicherungen',
        'Wirtscahftsprüfungen / Steuern / Recht',
    ];
}

function get_employee_numbers()
{
    return ['1-10', '11-50', '51-200', '201-500', '501-1.000', '1001-5.000', '5001-10.000', '10.000+'];
}

//function parse_and_create_locations()
//{
//    $locations = load_json_data('locations');
//    $parse_ready = [];
//
//    foreach ($locations as $location) {
//        $current_loop_str = "";
//
//        foreach ($location as $key => $inner_location) {
//            if (is_string($inner_location)) {
//                $current_loop_str = $inner_location;
//            } else if (is_array($inner_location)) {
//                $inner_array = [];
//
//                foreach ($inner_location as $inner_key => $inner_inner_locations) {
//                    $city_name = "";
//
//                    foreach ($inner_inner_locations as $city) {
//                        if (is_string($city)) {
//                            $city_name = $city;
//                        } else if (is_array($city)) {
//                            $address_array = [];
//                            $iterrator = 0;
//
//                            foreach ($city as $the_address) {
//                                $address_array[$iterrator] = $the_address;
//                                $iterrator++;
//                            }
//
//                            $inner_array[$city_name] = $address_array;
//                        }
//                    }
//                }
//
//                $parse_ready[$current_loop_str] = $inner_array;
//            }
//        }
//    }
//
//    if (!empty($parse_ready)) {
//        if (is_wp_error($locations_id = wp_insert_term(
//            'Locations',
//            'locations',
//            [
//                'parent' => 0
//            ]
//        ))) {
//            $locations_id = ['term_id' => $locations_id->error_data['term_exists']];
//        };
//
//        foreach ($parse_ready as $province => $city_and_address) {
//            $province_id = wp_insert_term(
//                $province,
//                'locations',
//                [
//                    'parent' => $locations_id['term_id']
//                ]
//            );
//
//            foreach ($city_and_address as $city => $addresses) {
//                $city_id = wp_insert_term(
//                    $city,
//                    'locations',
//                    [
//                        'parent' => $province_id['term_id']
//                    ]
//                );
//
//                foreach ($addresses as $address) {
//                    wp_insert_term(
//                        $address,
//                        'locations',
//                        [
//                            'parent' => $city_id['term_id']
//                        ]
//                    );
//                }
//            }
//        }
//    }
//}

//if (isset($_GET['process_locations'])) {
//    add_action('init', 'parse_and_create_locations');
//}

function get_wien_addresses() {
    return [
        '1010 - Wien',
        '1020 - Wien',
        '1030 - Wien',
        '1040 - Wien',
        '1050 - Wien',
        '1060 - Wien',
        '1070 - Wien',
        '1080 - Wien',
        '1090 - Wien',
        '1100 - Wien',
        '1110 - Wien',
        '1120 - Wien',
        '1130 - Wien',
        '1140 - Wien',
        '1150 - Wien',
        '1160 - Wien',
        '1170 - Wien',
        '1180 - Wien',
        '1190 - Wien',
        '1200 - Wien',
        '1210 - Wien',
        '1220 - Wien',
        '1230 - Wien',
        '1300 - Flughafen Wien-Schwechat'
    ];
}

//function process_wien_locations() {
//    $main_parent = 2313;
//    $level_1 = 'Wien';
//    $level_2 = get_wien_addresses();
//
//    $wien_id = wp_insert_term(
//        $level_1,
//        'locations',
//        [
//            'parent' => $main_parent
//        ]
//    );
//
//    foreach($level_2 as $location) {
//        wp_insert_term(
//            $location,
//            'locations',
//            [
//                'parent' => $wien_id['term_id']
//            ]
//        );
//    }
//}
//if (isset($_GET['process_locations_wien'])) {
//    add_action('init', 'process_wien_locations');
//}

//function fix_professions_category()
//{
//    global $wpdb;
//    $the_term = falke_get_term_by('slug', 'professions');
//    $terms = get_term_children_by_term_id($the_term->term_id);
//
//    $wpdb->query('UPDATE flk_term_taxonomy SET flk_term_taxonomy.taxonomy = "' . $the_term->slug . '" WHERE flk_term_taxonomy.term_id = "' . $the_term->term_id . '"');
//
//    foreach($terms as $term_info_level_1) {
//        $terms_level_2 = get_term_children_by_term_id($term_info_level_1->term_id);
//
//        foreach($terms_level_2 as $term_info_level_2) {
//            $terms_level_3 = get_term_children_by_term_id($term_info_level_2->term_id);
//
//            foreach($terms_level_3 as $term_info_level_3) {
//                $wpdb->query('UPDATE flk_term_taxonomy SET flk_term_taxonomy.taxonomy = "' . $the_term->slug . '" WHERE flk_term_taxonomy.term_id = "' . $term_info_level_3->term_id . '"');
//            }
//
//            $wpdb->query('update flk_term_taxonomy set taxonomy = "' . $the_term->slug . '" where term_id = "' . $term_info_level_2->term_id . '"');
//        }
//
//
//        $wpdb->query('update flk_term_taxonomy set taxonomy = "' . $the_term->slug . '" where term_id = "' . $term_info_level_1->term_id . '"');
//    }
//}

//if (isset($_GET['fix_categories'])) {
//    add_action('init', 'fix_professions_category');
//}

//function get_professions() {
//    $professions_array = explode(PHP_EOL, file_get_contents(get_template_directory() . "/jsons/das_falke_professions.csv"));
//    $level_0_id = 2312;
//    $level_1_id = 0;
//    $level_2_id = 0;
//
//    foreach($professions_array as $key => $profession_array) {
//        $current_value = explode(';', $profession_array);
//        $level = $current_value[0];
//        $value = $current_value[1];
//
//        if ($level == 1 && $level_0_id > 0) {
//            $level_1_id = wp_insert_term(
//                $value,
//                'professions',
//                [
//                    'parent' => $level_0_id,
//                ]
//            );
//        }
//
//        if ($level == 2 && array_key_exists('term_id', $level_1_id) && $level_1_id['term_id'] > 0) {
//            $level_2_id = wp_insert_term(
//                $value,
//                'professions',
//                [
//                    'parent' => $level_1_id['term_id'],
//                ]
//            );
//        }
//
//        if ($level == 3 && array_key_exists('term_id', $level_2_id) && $level_2_id['term_id'] > 0) {
//            wp_insert_term(
//                $value,
//                'professions',
//                [
//                    'parent' => $level_2_id['term_id'],
//                ]
//            );
//        }
//    }
//}
//if (isset($_GET['insert_categories'])) {
//    add_action('init', 'get_professions');
//}

function add_falke_spinner()
{
    echo '<img class="falke-spinner" alt="spinner" style="display: none; max-width: 50px; max-height: 50px;" src="' . get_template_directory_uri() . "/img/spinner.gif" . '; ?>" />';
}

add_action('admin_footer', 'add_falke_spinner');
add_action('wp_footer', 'add_falke_spinner');

function falke_is_doing_search() {
    $possible_search_params = [
        'selected_locations',
        'selected_professions',
        'loc',
        'employment_type',
        'employment_nature',
        'employment_education',
        'search',
    ];

    if (!empty($_GET)) {
        foreach($possible_search_params as $possible_search_param) {
            if (isset($_GET[$possible_search_param])) {
                return true;
            }
        }
    }

    return false;
}

// Must be called from job page (single page)
function apply_to_job()
{
    if (empty($_POST['post_id']) || (!isset($_POST['job_application_nonce']) || !wp_verify_nonce($_POST['job_application_nonce'], 'job-application'))) {
        return false;
    }

    $user_meta_key = 'job_applications';
    $post_id = $_POST['post_id'];

    $job_author_id = get_the_author_meta('ID');

    if (empty($contact_email = get_user_data('contactperson_email', $job_author_id))) {
        if (!empty($job_author = eve_get_user_by_id($job_author_id))) {
            $contact_email = $job_author->user_email;
        }
    }

    if (eve_is_user_jobseeker()) {
        $user_id = eve_get_user()->ID;
        $applied = get_job_application_eligibility($user_id, $post_id);

        if (!empty($applied) && is_array($applied)) {
            $attachments = [];
            $job_title = "";

            if (array_key_exists('job_title', $_POST)) {
                $job_title = $_POST['job_title'];
            }

            if (!empty($_FILES) && !empty($_FILES['job_application_attachments'])) {
                $attachments = $_FILES['job_application_attachments']['tmp_name'];
            }

            // TODO: Mail template here (job applications received)
            $mail_sent_employer = falke_send_mail([
                'subject' => __('New application for your job posting - dasfalkepersonal.at', 'dasfalke-email'),
                'email' => $contact_email,
                'template' => '/mailtemplates/email-job-application.php',
                'attachments' => $attachments
            ]);

            // TODO: Mail template here (Successfully registered for a job)
            $mail_sent = falke_send_mail([
                'subject' => sprintf(__('Your personal data sent in the application for %s - dasfalkepersonal.at', 'dasfalke-email'), $job_title),
                'email' => $_POST['account_email'],
                'template' => '/mailtemplates/email-job-applied.php',
            ]);

            if ($mail_sent && update_user_meta($user_id, $user_meta_key, serialize($applied)) === true) {
                falke_add_notice(__("Successful application!", 'dasfalke-jobpage'), 'success');
            }

            if (!$mail_sent_employer) {
                falke_add_notice(__("Failed to send the application form to the employer", 'dasfalke-jobpage'), 'error');
            }
        } else if ($applied === true) {
            add_user_meta($user_id, $user_meta_key, serialize([$post_id]));
            $attachments = [];
            $job_title = "";

            if (array_key_exists('job_title', $_POST)) {
                $job_title = $_POST['job_title'];
            }

            if (!empty($_FILES) && !empty($_FILES['job_application_attachments'])) {
                $attachments = $_FILES['job_application_attachments']['tmp_name'];
            }

            // TODO: Mail template here (job applications received)
            $mail_sent_employer = falke_send_mail([
                'subject' => __('New application for your job posting - dasfalkepersonal.at', 'dasfalke-email'),
                'email' => $contact_email,
                'template' => '/mailtemplates/email-job-application.php',
                'attachments' => $attachments
            ]);

            // TODO: Mail template here (Successfully registered for a job)
            $mail_sent = falke_send_mail([
                'subject' => sprintf(__('Your personal data sent in the application for %s - dasfalkepersonal.at', 'dasfalke-email'), $job_title),
                'email' => $_POST['account_email'],
                'template' => '/mailtemplates/email-job-applied.php',
            ]);

            if ($mail_sent) {
                falke_add_notice(__("Successful application!", 'dasfalke-jobpage'), 'success');
            }

            if (!$mail_sent_employer) {
                falke_add_notice(__("Failed to send the application form to the employer", 'dasfalke-jobpage'), 'error');
            }
        } else if ($applied === false) {
            falke_add_notice(__("You've already applied to this job", 'dasfalke-jobapplication'), 'error');
        }
    } else if (eve_is_user_employer() === false) {
        $job_author_id = get_the_author_meta('ID');
        $required_fields = [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'account_email' => 'Email address',
        ];

        foreach($required_fields as $name_field => $required_field) {
            if (empty($_POST[$name_field])) {
                falke_add_notice(sprintf(__("The %s field is required!", 'dasfalke-jobapplication'), $required_field), 'error');
                return false;
            }
        }

        $attachments = [];

        if (!empty($_FILES) && !empty($_FILES['job_application_attachments'])) {
            $attachments = $_FILES['job_application_attachments']['tmp_name'];
        }

        // TODO: Mail template here (job applications received without login)
        $mail_sent_employer = falke_send_mail([
            'subject' => __('You have received a new job application from ', 'dasfalke_jobapplication') . $_POST['first_name'] . ' ' . $_POST['last_name'],
            'email' => $contact_email,
            'template' => 'job-application-mail.php',
            'attachments' => $attachments
        ]);

        // TODO: Mail template here (Successfully registered for a job without login)
        $mail_sent = falke_send_mail([
            'subject' => __('You have successfully registered for a job! ', 'dasfalke_jobapplication'),
            'email' => $_POST['account_email'],
            'template' => 'job-application-mail.php',
        ]);

        if ($mail_sent) {
            falke_add_notice(__("Successful application!", 'dasfalke-jobapplication'), 'success');
        }

        if (!$mail_sent_employer) {
            falke_add_notice(__("Failed to send the application form to the employer", 'dasfalke-jobapplication'), 'error');
        }
    }

    return true;
}

function falke_send_mail($parameters) {
    $bundleManager = new DFPBManager();
    return $bundleManager->falke_send_mail($parameters);
}

/**
 * Returns post_ids unserialized containing new post_id as well or true if eligible.
 * Returns false if already applied
 *
 * @param $user_id
 * @param $post_id
 * @param string $user_meta_key
 * @return array|bool|mixed|null
 */
function get_job_application_eligibility($user_id, $post_id, $user_meta_key = 'job_applications')
{
    $post_ids = get_user_meta($user_id, $user_meta_key, true);

    if (!empty($post_ids)) {
        $post_ids = unserialize($post_ids);

        if (!in_array($post_id, $post_ids)) {
            $post_ids[] = $post_id;
            return $post_ids;
        } else {
            return false;
        }
    }

    return true;
}

function saving_company_data()
{
    $company_data_fields = [
        'billing_company' => 'text',
        'billing_address_1' => 'text',
        'billing_city' => 'text',
        'billing_postcode' => 'text',
        'billing_country' => 'text',
        'billing_tax_number' => 'text',
        'company_website' => 'text',
        'company_whoweare' => 'textarea',
        'company_whatweoffer' => 'textarea',
        'company_ourexpectation' => 'textarea',
        'company_founding_year' => 'number',
        'company_number_of_employees' => 'text',
        'company_industry' => 'text',
        'contactperson_honorific' => 'text',
        'contactperson_first_name' => 'text',
        'contactperson_last_name' => 'text',
        'contactperson_position' => 'text',
        'contactperson_email' => 'email',
        'contactperson_phone' => 'text',
        'profile_image' => 'image'
    ];

    $required_fields = [
        'billing_company',
        'profile_image',
        'billing_address_1',
        'billing_city',
        'billing_postcode',
        'billing_country',
        'company_whoweare',
    ];

    $user = eve_get_user();
    $meta_value = "";

    if (empty($user)) {
        return false;
    }

    if (!empty($_FILES)) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
    }

    $errors = 0;

    foreach ($company_data_fields as $field => $field_type) {
        if (array_key_exists($field, $required_fields) && empty($_POST[$field])) {
            falke_add_notice(sprintf(__('The "%s" field is required!', 'dasfalke-profile'), get_company_profile_key_name_pairs($field)), 'error');
            $errors++;
        }

        if (($field_type === 'image' || $field_type === 'file')) {
            if (!empty($_FILES[$field]) && !empty($_FILES[$field]['size'])) {
                $attachment_id = media_handle_upload($field, 0);

                if (is_wp_error($attachment_id)) {
                    falke_add_notice(__('There was an error when uploading your profile image, please try again', 'dasfalke-profile'), 'error');
                } else if ($errors <= 0) {
                    update_user_meta($user->ID, $field, $attachment_id);
                    clear_facebook_avatar_session();
                }
            } else if(empty(get_user_meta($user->ID, $field, true))){
                falke_add_notice(sprintf(__('The "%s" field is required!', 'dasfalke-profile'), get_company_profile_key_name_pairs($field)), 'error');
                $errors++;
            }
        } else if (!empty($_POST[$field])) {
            if ($field_type === 'text') {
                $meta_value = sanitize_text_field($_POST[$field]);
            } else if ($field_type === 'textarea') {
                $meta_value = wp_kses_post($_POST[$field]);
            } else if ($field_type === 'number') {
                if (!is_numeric($_POST[$field])) {
                    falke_add_notice(__('You can only enter numbers here!', 'dasfalke-profile'), 'error');
                    continue;
                } else {
                    $meta_value = sanitize_text_field($_POST[$field]);
                }
            } else if ($field_type === 'email') {
                if (!filter_var($_POST[$field], FILTER_VALIDATE_EMAIL)) {
                    falke_add_notice(__('This email address is incorrect!', 'dasfalke-profile'), 'error');
                    continue;
                } else {
                    $meta_value = sanitize_email($_POST[$field]);
                }
            }

            if ($errors <= 0) {
                update_user_meta($user->ID, $field, $meta_value);
            }
        }
    }

    if ($errors <= 0) {
        falke_add_notice(__('Company Profile changes saved successfully!', 'dasfalke-profile'), 'success');
        wp_safe_redirect(home_url('my-account'), 301);
        exit;
    }

    return false;
}

if (isset($_POST['company_profile_save'])) {
    add_action('init', 'saving_company_data');
}

function redirect_to_if_not_eligible($role, $redirect_to = null, $compare_role = null)
{
    $redirect = false;

    if ($redirect_to === null) {
        $redirect_to = home_url();
    }

    if ($compare_role !== null) {
        if ($role !== $compare_role) {
            $redirect = true;
        }
    } else if (eve_get_logged_in_user_role() !== $role) {
        $redirect = true;
    }

    if ($redirect === true) {
        wp_safe_redirect($redirect_to, 301);
        exit;
    }

    return false;
}

function make_text_field($input_name, $required = false, $type = "text", $placeholder_override = null)
{
    if (empty($input_name) || empty($type)) {
        return false;
    }

    $label = get_company_profile_key_name_pairs($input_name);
    $placeholder = $label;

    if ($placeholder_override !== null) {
        $placeholder = $placeholder_override;
    }

    $textfield = '<div class="form-row ' . $type . '">';
    $textfield .= '<label class="form-row__label" for="' . $input_name . '">' . $label . ($required === true ? ' *' : '') . '</label>';
    $textfield .= '<div class="form-row__input">';
    $textfield .= '<input type="' . $type . '" ' . ($required === true ? 'required' : '') . ' value="' . get_field_if_posted($input_name, true) . '" name="' . $input_name . '" id="' . $input_name . '" placeholder="' . $placeholder . '">';
    $textfield .= '</div>';
    $textfield .= '</div>';

    return $textfield;
}

function make_simple_text_field($input_name, $label, $required, $type = "text", $custom_value = "", $placeholder = null)
{
    if (empty($input_name)) {
        return false;
    }

    if ($placeholder === null) {
        $placeholder = $label;
    }

    $value = $custom_value;

    if (empty($custom_value)) {
        $value = get_field_if_posted($input_name, true);
    }

    $textfield = '<div class="form-row ' . $type . '">';
    $textfield .= '<label class="form-row__label" for="' . $input_name . '">' . $label . ($required === true ? ' *' : '') . '</label>';
    $textfield .= '<div class="form-row__input">';
    $textfield .= '<input type="' . $type . '" ' . ($required === true ? 'required' : '') . ' value="' . $value . '" name="' . $input_name . '" id="' . $input_name . '" placeholder="' . $placeholder . '">';
    $textfield .= '</div>';
    $textfield .= '</div>';

    return $textfield;
}

function add_jobseeker_profile_form()
{
    echo ' enctype="multipart/form-data"';
}

add_action('woocommerce_edit_account_form_tag', 'add_jobseeker_profile_form');

function edit_jobseeker_profile()
{
    $jobseeker_image = get_file_if_posted('user_meta', 'profile_image');
    $jobseeker_image_src = "";

    if (is_array($jobseeker_image) && array_key_exists('sizes', $jobseeker_image)) {
        $jobseeker_image_src = $jobseeker_image['sizes']['profile-image']['src'];
    }

    ?>
    <div class="form-row file">
        <label class="form-row__label" for=""><?php _e('Profile image', 'dasfalke-profile'); ?></label>
        <div class="form-row__input">
            <input type="file" name="profile_image" id="profile_image" value="">

            <?php if (!empty($jobseeker_image) && is_array($jobseeker_image)) : ?>
                <div class="profile__image-wrp">
                    <div class="profile__image-psn"><img class="profile__image-img" src="<?php echo $jobseeker_image_src; ?>" alt="<?php echo $jobseeker_image['file_name']; ?>"></div>
                    <div class="profile__image-name"><?php echo $jobseeker_image['file_name']; ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
add_action('woocommerce_edit_account_form', 'edit_jobseeker_profile');

function add_account_delete_btn() {
    ?>

    <br>
    <br>
    <hr class="job-page__sep">
    <h2><?php _e('Delete account', 'dasfalke-profile'); ?></h2>
    <div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide account-delete">
        <div class="checkbox-with-label">
            <input id="account_delete_confirm" name="account_delete_confirm" type="checkbox">
            <label for="account_delete_confirm"><?php echo eve_is_user_jobseeker() ? __('I understand and accept that all my data and job alarms will be deleted', 'dasfalke-profile') : __('I understand and accept that all my data, job posts and orders will be deleted', 'dasfalke-profile'); ?></label>
        </div>
        <button disabled class="df-btn secondary block" name="delete_account" type="submit"><?php esc_html_e( 'Delete account', 'dasfalke-profile'); ?></button>
    </div>
    <?php
}
add_action('woocommerce_edit_account_form_end', 'add_account_delete_btn');

function save_jobseeker_profile($user_id)
{
    if (empty($_FILES) || empty($user_id)) {
        return false;
    }

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    $field_name = 'profile_image';

    if (!empty($_FILES[$field_name]) && $_FILES[$field_name]['size'] > 0) {
        $attachment_id = media_handle_upload($field_name, 0);

        if (is_wp_error($attachment_id)) {
            if ($attachment_id->get_error_message() !== 'No file was uploaded.') {
                falke_add_notice(__('There was an error when uploading your profile image, please try again', 'dasfalke-profile'), 'error');
            }
        } else {
            update_user_meta($user_id, $field_name, $attachment_id);
        }
    }

    return true;
}
add_action('woocommerce_save_account_details', 'save_jobseeker_profile');

function save_login_details($user_id)
{
    if (!isset($_POST['delete_account']) || $_POST['account_delete_confirm'] !== 'on') {
        return false;
    }

    update_user_meta($user_id, 'account_deleted', 1);
    $bundleManager = new DFPBManager();
    $related_bundle_ids = $bundleManager->get_bundle_ids_by_user_id($user_id);

    if (!empty($related_bundle_ids)) {
        foreach($related_bundle_ids as $bundle_id) {
            if (!property_exists($bundle_id, 'id')) {
                continue;
            }

            $bundleManager->change_status_by_id($bundle_id->id, 'user');
            $bundleManager->instant_expire_all_related_product($bundle_id->id);
        }
    }

    wp_logout();
    falke_add_notice(__('Successfully logged out!', 'dasfalke-logout'), 'success');
    wp_safe_redirect(home_url());
    return true;
}
add_action('woocommerce_save_account_details', 'save_login_details');

function get_featured_data($featured_type)
{
    $bundleManager = new DFPBManager();
    $slider_data = $bundleManager->get_all_available_and_featured_jobs($featured_type, true);

    if (!empty($slider_data) && is_array($slider_data)) {
        $slider_data = reset($slider_data);
    }

    if (!empty($slider_data->user)) {
        return ['author' => $slider_data->user, 'post__in' => explode(",", $slider_data->post_ids)];
    }
}

function is_company_has_featured($user_id) {
    $bundleManager = new DFPBManager();
    $meta_key = $bundleManager->get_slugs_by_typecategory('company_features')[0];

    if (!empty($user_id) && !empty($meta_key)) {
        $meta_data = get_user_meta($user_id, $meta_key, true);

        if (!empty($meta_data) && is_string($meta_data) && is_array($meta_data = unserialize($meta_data))) {
            if ($meta_data['expires_in'] > 0) {
                return $meta_data;
            }
        }
    }

    return false;
}

function get_featured_company_data() {
    $users = get_users();
    $bundleManager = new DFPBManager();
    $meta_key = $bundleManager->get_slugs_by_typecategory('company_features')[0];
    $companies_to_be_featured = [];

    foreach ( $users as $user ) {
        $user_role = "";

        if (is_array($user->roles)) {
            $user_role = reset($user->roles);

            if ($user_role !== EMPLOYER_ROLE) {
                continue;
            }
        }

        $meta_data = get_user_meta( $user->ID, $meta_key, true );

        if(!empty($meta_data) && is_string($meta_data) && is_array($meta_data = unserialize($meta_data))) {
            if ($meta_data['expires_in'] > 0) {
                $companies_to_be_featured[] = $user->ID;
            }
        }
    }

    if (!empty($companies_to_be_featured) && !empty($active_jobs = $bundleManager->get_all_active_job_ids_for_users_by_user_ids($companies_to_be_featured))) {
        foreach($active_jobs as $active_job) {
            $random_company = $active_jobs[array_rand($active_jobs)];

            if (!property_exists($random_company, 'post_ids') || !property_exists($random_company, 'user_id')) {
                continue;
            }

            return [
                'query_args' => ['post__in' => explode(',', $random_company->post_ids)],
                'user_id' => $random_company->user_id
            ];
        }
    }

    return false;
}

function get_parent_terms($post_id, $post_meta_key, $taxonomy)
{
    if (empty($location_id = get_post_meta($post_id, $post_meta_key, true))) {
        return false;
    }

    $separator = ";";

    $term_list = get_term_parents_list($location_id, $taxonomy, [
        'separator' => $separator,
        'link' => false,
    ]);

    $term_list = array_filter(explode($separator, $term_list));
    array_shift($term_list);
    return $term_list;
}

function get_location_string($post_id)
{
    $locations = get_parent_terms($post_id, 'selected_locations', 'locations');
    $location_string = "";
    $locations_count = count($locations);

    if ($locations_count >= 3) {
        $locations_count = 2;
    }

    for ($i = 0; $i < $locations_count; $i++) {
        $location_string .= $locations[$i];

        if ($i < $locations_count - 1) {
            $location_string .= ', ';
        }
    }

    return $location_string;
}

// Call it only on job page, obviously does not work anywhere else
function is_owner_looking_at_own_job_page()
{
    if (is_single() && eve_is_user_employer() && !empty($user = eve_get_user())) {
        if ($user->ID === get_the_author_meta('ID')) {
            return true;
        }
    }

    return false;
}

function get_feature_buy_panel()
{
    if (is_owner_looking_at_own_job_page()) {
        $job_id = get_the_ID();
        $bundleManager = new DFPBManager();
        $all_buyable_featureds = $bundleManager->get_all_non_bundle_feature_product_slugs();
        $all_featureds = $bundleManager->get_field_by_type('feature-product', 'slug');
        $featureds_for_job = $bundleManager->is_featured_available_for_job($job_id, $all_featureds);
        $featured_slugs = [];

        foreach ($featureds_for_job as $slug) {
            if (!empty($slug[0])) {
                $featured_slugs[] = $slug[0];
            }
        }

        $used_type_cats = [];
        $all_type_cats = [];

        foreach($featured_slugs as $featured_slug) {
            $used_type_cats[] = $bundleManager->get_typecategory_by_slug($featured_slug);
        }

        foreach($all_buyable_featureds as $featured_slug) {
            $typecat = $bundleManager->get_typecategory_by_slug($featured_slug);

            if (!in_array($typecat, $all_type_cats)) {
                $all_type_cats[] = $typecat;
            }
        }

        $available_featured_typecats = array_diff($all_type_cats, $used_type_cats);
        $available_featureds = [];

        foreach($available_featured_typecats as $available_featured_typecat) {
            $slugs = $bundleManager->get_slugs_by_typecategory($available_featured_typecat);

            foreach($slugs as $slug) {
                if (strpos($slug, 'bundle') === false) {
                    $available_featureds[] = $slug;
                }
            }
        }

        if (!empty($available_featureds)) {
            $featured_data = [];
            $featured_products = new WP_Query([
                'post_type' => 'product',
                'post_name__in' => $available_featureds,
            ]);

            while ($featured_products->have_posts()) {
                $featured_products->the_post();
                $post_slug = get_post_field( 'post_name', get_the_ID() );
                $data_exists_key = null;

                foreach($featured_data as $key => $featured) {
                    if (!empty($featured['title']) && $featured['title'] === get_the_title()) {
                        $data_exists_key = $key;
                    }
                }

                if ($data_exists_key === null) {
                    $featured_data[] = [
                        'title' => get_the_title(),
                        'type_cat' => $bundleManager->get_product_field_by_slug($post_slug, 'type_category'),
                        'description' => get_post_field( 'post_excerpt', get_the_ID() ),
                        'product_id' => get_the_ID(),
                        'slug' => $post_slug,
                        'price' => $bundleManager->get_product_field_by_slug($post_slug, 'price'),
                        'days' => $bundleManager->get_product_field_by_slug($post_slug, 'expires_in'),
                    ];
                } else {
                    $featured_data[$data_exists_key]['product_id'] = array_merge([get_the_ID()], [$featured_data[$data_exists_key]['product_id']]);
                    $featured_data[$data_exists_key]['slug'] = array_merge([$post_slug], [$featured_data[$data_exists_key]['slug']]);
                    $featured_data[$data_exists_key]['price'] = array_merge([$bundleManager->get_product_field_by_slug($post_slug, 'price')], [$featured_data[$data_exists_key]['price']]);
                    $featured_data[$data_exists_key]['days'] = array_merge([$bundleManager->get_product_field_by_slug($post_slug, 'expires_in')], [$featured_data[$data_exists_key]['days']]);
                }
             }
            wp_reset_query();
             ?>

            <div class="job-page__advopts">
                <h3 class="job-page__advopts-title"><?php _e('Buy extra services', 'dasfalke-jobpage'); ?></h3>
                <div class="job-page__advopts-row">

                    <?php foreach($featured_data as $featured) : ?>
                        <div class="job-page__advopts-col">
                            <div class="job-page__advopt-title"><?php _e($featured['title'], 'dasfalke-product'); ?></div>
                            <div class="job-page__advopt-desc"><?php _e($featured['description'], 'dasfalke-product'); ?></div>
                            <div class="job-page__advopt-icon"><img src="<?php echo get_template_directory_uri(); ?>/img/service-<?php echo $featured['type_cat']; ?>.png"></div>
                            <div class="job-page__advopt-option">
                                <form>
                                    <?php if ($featured['type_cat'] === 'highlight_features') : ?>
                                        <?php if (is_array($featured['days'])) : ?>
                                            <?php for($i = 0; $i < count($featured['days']); $i++) : ?>
                                                <div class="form-row register-role">
                                                    <div class="styled-radio">
                                                        <label>
                                                            <input type="hidden" name="add-to-cart" value="<?php echo $featured['product_id'][$i]; ?>" />
                                                            <span><?php echo $featured['days'][$i]; ?> <?php _e('days', 'dasfalke-register'); ?></span>
                                                            <span class="styled-radio-price"><?php echo eve_get_formatted_price($featured['price'][$i]); ?></span>
                                                        </label>
                                                    </div>

                                                    <?php if ($i < count($featured['days']) - 1) : ?>
                                                        <div class="job-page__advopt-option-sep"></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endfor; ?>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <input type="hidden" name="add-to-cart" value="<?php echo $featured['product_id']; ?>" />
                                        <div class="job-page__advopt-price"><?php echo eve_get_formatted_price($featured['price']); ?></div>
                                        <div class="job-page__advopt-timeframe"><span><?php echo $featured['days']; ?></span>&nbsp;<?php _e('days', 'dasfalke-jobpage'); ?></div>
                                    <?php endif; ?>

                                    <input type="hidden" name="job_post" value="<?php echo $job_id; ?>" />
                                    <div class="job-page__advopt-action"><input class="df-btn primary block small" type="submit" value="<?php _e('BUY NOW', 'dasfalke-jobpage') ?>"></div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
<?php

            return true;
        }
    }

    return false;
}

add_action('feature_buy_panel', 'get_feature_buy_panel');

function add_job_id($cart_item_data, $product_id, $variation_id)
{
    if (empty($_REQUEST['job_post'])) {
        return $cart_item_data;
    }

    $cart_item_data['job_id'] = $_REQUEST['job_post'];
    return $cart_item_data;
}

add_filter('woocommerce_add_cart_item_data', 'add_job_id', 10, 3);

if (!function_exists('falke_pagination')) {
    function falke_pagination($paged, $max_page, $type = 'list', $prev_text = '«', $next_text = '»')
    {
        $big = 999999999;

        return paginate_links([
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, $paged),
            'total' => $max_page,
            'mid_size' => 1,
            'prev_text' => $prev_text,
            'next_text' => $next_text,
            'type' => $type
        ]);
    }
}

function get_presorted_posts_ids($slugs = ['feature-bundle-pre-sort'])
{
    $bundleManager = new DFPBManager();
    $presorted_posts = $bundleManager->get_all_active_post_ids_with_featureds($slugs, null, null);
    $published_posts = [];
    $limit = 3;

    foreach ($presorted_posts as $presorted_post) {
        if (get_post_status($presorted_post) === 'publish') {
            $published_posts[] = $presorted_post;
        }
    }

    if (!empty($published_posts) && count($published_posts) > $limit) {
        $random_posts = [];
        $randomized = array_rand($published_posts, $limit);

        foreach ($randomized as $random_post) {
            $random_posts[] = $published_posts[$random_post];
        }

        return $random_posts;
    }

    return $published_posts;
}

function create_job_alert()
{
    if (empty($_REQUEST['data'])) {
        wp_send_json(send_json_wc_notice(__('No filter set to save job alert', 'dasfalke-jobs'), 'error'));
    }

    if (!eve_is_user_jobseeker()) {
        wp_send_json(send_json_wc_notice(__('You have to be a jobseeker to save job alerts!', 'dasfalke-jobs'), 'error'));
    }

    $data = $_REQUEST['data'];
    $meta_key = 'job_alerts';

    if (!empty($query_string = $data['query_string'])) {
        $user_data = eve_get_user();

        if (!empty($user_data)) {
            if (!empty($job_alerts = unserialize(get_user_meta($user_data->ID, $meta_key, true)))) {
                foreach ($job_alerts as $job_alert) {
                    if ($job_alert !== $query_string) {
                        $job_alerts[] = $query_string;
                    } else {
                        wp_send_json(send_json_wc_notice(__('You have already saved that job alert!', 'dasfalke-jobs'), 'error'));
                    }
                }

                if (!update_user_meta($user_data->ID, $meta_key, serialize($job_alerts))) {
                    wp_send_json(send_json_wc_notice(__('Saving job alerts failed, please try again!', 'dasfalke-jobs'), 'error'));
                }

                wp_send_json(send_json_wc_notice(__('Job alerts successfully saved!', 'dasfalke-jobs'), 'success'));
            } else if (!update_user_meta($user_data->ID, $meta_key, serialize([0 => $query_string]))) {
                wp_send_json(send_json_wc_notice(__('Saving job alert failed, please try again!', 'dasfalke-jobs'), 'error'));
            }

            wp_send_json(send_json_wc_notice(__('Job alert successfully saved!', 'dasfalke-jobs'), 'success'));
        }

        wp_send_json(send_json_wc_notice(__('You need to be logged in first!', 'dasfalke-jobs'), 'error'));
    }

    wp_die();
}

function is_user_has_company_featured($user_id) {
    $bundleManager = new DFPBManager();
    $company_featured_slug = $bundleManager->get_slugs_by_typecategory('company_features')[0];
    $feature_data = get_user_meta($user_id, $company_featured_slug, true);

    if (!empty($feature_data) && is_string($feature_data) && is_array($feature_data = unserialize($feature_data))) {
        if ($feature_data['expires_in'] > 0) {
            return true;
        }
    }

    return false;
}

function get_job_alerts()
{
    $job_alerts = unserialize(get_user_data('job_alerts'));

    if (empty($job_alerts)) {
        return false;
    }

    $parsed_alerts = [];

    foreach ($job_alerts as $job_alert) {
        $decoded_alert = urldecode($job_alert);
        $parse_ready = preg_replace('/^\?/', '', $decoded_alert);
        parse_str($parse_ready, $parsed_data);
        $parsed_alerts[] = $parsed_data;
    }

    return $parsed_alerts;
}

function delete_job_alert()
{
    if (!isset($_POST['delete_job_alert_id'])) {
        return false;
    }

    $job_alert_id = (int)$_POST['delete_job_alert_id'];
    $job_alerts = unserialize(get_user_data('job_alerts'));

    if (!array_key_exists($job_alert_id, $job_alerts) || empty($user_data = eve_get_user()) ||
        (!isset($_POST['delete_job_alert_nonce']) || !wp_verify_nonce($_POST['delete_job_alert_nonce'], 'delete-job-alert'))
    ) {
        return false;
    }

    unset($job_alerts[$job_alert_id]);

    if (!update_user_meta($user_data->ID, 'job_alerts', serialize($job_alerts))) {
        wp_send_json(send_json_wc_notice(__('Failed to delete job alert!', 'dasfalke-jobs'), 'error'));
    }

    return false;
}

if (isset($_POST['delete_job_alert'])) {
    add_action('init', 'delete_job_alert');
}

function get_profile_completeness($return_type = 'percentage')
{
    $full_list = get_company_profile_key_name_pairs();
    $number = 0;

    foreach ($full_list as $meta_key => $meta_value) {
        if (!empty(get_user_data($meta_key))) {
            $number++;
        }
    }

    if ($return_type === 'percentage') {
        return round(($number / count($full_list) * 100)) . "%";
    } else if ($return_type === 'complete_number') {
        return $number;
    }

    return false;
}

add_filter('woocommerce_enable_order_notes_field', '__return_false', 9999);

function falke_add_notice($message, $type = 'error')
{
    $possible_types = ['error', 'success', 'warning'];

    if (empty($message)) {
        falke_session_access_notice('write', [
            'error' => 'Notice message was empty!'
        ]);

        return false;
    }

    if (!in_array($type, $possible_types)) {
        falke_session_access_notice('write', [
            'error' => 'No such notice type!'
        ]);

        return false;
    }

    falke_session_access_notice('write', [
        $type => $message
    ]);

    return true;
}

function falke_print_notices()
{
    if (!empty($notice_storage = falke_session_access_notice('read'))) {
        if (is_array($notice_storage)) {
            foreach($notice_storage as $notice_holder) {
                foreach($notice_holder as $type => $message) {
                    $notice = "<ul class='woocommerce-" . $type . "' role='alert'>";
                    $notice .= "<li>";
                    $notice .= $message;
                    $notice .= "</li>";
                    $notice .= "</ul>";
                    echo $notice;
                }
            }
        }

        falke_clear_notices();
    }

    session_write_close();
}

function falke_session_access_notice($access_type = 'read', $data = [])
{
    $return_val = true;

    if ($access_type === 'read' && isset($_SESSION[FALKE_NOTICES])) {
        $return_val = $_SESSION[FALKE_NOTICES];
    } else if ($access_type === 'write' && !empty($data)) {
        $_SESSION[FALKE_NOTICES][] = $data;
    }

    return $return_val;
}

function falke_clear_notices() {
    $_SESSION[FALKE_NOTICES] = [];
}

/**
 * Auto Complete all WooCommerce orders.
 */
function custom_woocommerce_auto_complete_order( $order_id ) {
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    $order->update_status( 'completed' );
}
add_action( 'woocommerce_thankyou', 'custom_woocommerce_auto_complete_order' );

/*function hide_hidden_product_from_cart( $visible, $cart_item, $cart_item_key ) {
    $product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

    if ( $product->get_catalog_visibility() == 'hidden' ) {
        $visible = false;
    }

    return $visible;
}

function hide_hidden_product_from_order( $visible, $order_item ) {
    $product = $order_item->get_product();

    if ( $product->get_catalog_visibility() == 'hidden' ) {
        $visible = false;
    }

    return $visible;
}
add_filter( 'woocommerce_cart_item_visible', 'hide_hidden_product_from_cart' , 10, 3 );
add_filter( 'woocommerce_widget_cart_item_visible', 'hide_hidden_product_from_cart', 10, 3 );
add_filter( 'woocommerce_checkout_cart_item_visible', 'hide_hidden_product_from_cart', 10, 3 );
add_filter( 'woocommerce_order_item_visible', 'hide_hidden_product_from_order', 10, 2 );
*/
function get_page_url_by_slug($slug) {
    $page = get_page_by_path( $slug );

    if (!empty($page) && property_exists($page, 'ID')) {
        return get_permalink( apply_filters( 'wpml_object_id', $page->ID, 'page', true ) );
    }

    return "";
}

function clear_facebook_avatar_session() {
    $loginManager = new FacebookLoginManager();
    $loginManager->clear_sessions();
}
