<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package eve
 */


/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function eve_body_classes($classes)
{
    $invert_bg = false;

    if( ! is_singular() )
        $classes[] = 'hfeed';

    if( is_home() || is_singular() )
        $invert_bg = true;

    if( is_page() )
        $invert_bg = eve_get_field( 'eve_page_invert_colours', get_the_ID() );

    if(
    is_author() ||
    is_search() ||
    is_page_template('template-pages/page-register.php') ||
    is_page_template('template-pages/page-login.php') ||
    is_page_template('template-pages/page-checkout.php') ||
    is_page_template('template-pages/page-archive-author.php') ||
    is_page_template('template-pages/page-pricing.php') ){
        $invert_bg = true;
    }

    if(
    is_post_type_archive('blog') ||
    is_singular('blog')
    ){
        $invert_bg = false;
    }

    if( $invert_bg )
        $classes[] = 'invert';

    return $classes;
}


/**
 * Registers a text field setting for Wordpress 4.7 and higher.
 */
function eve_general_section()
{
    $section_id = 'progresseve';
    $page_name = 'general';

    add_settings_section(
        $section_id,
        'Progresseve Settings', // Section Title
        'progresseve_section_options_callback', // Callback
        $page_name // What Page?  This makes the section show up on the General Settings Page
    );

    add_settings_field( // Option 1
        'eve_gtm_code', // Option ID
        'GTM Code', // Label
        'my_textbox_callback', // !important - This is where the args go!
        $page_name, // Page it will be displayed (General Settings)
        $section_id, // Name of our section
        array( // The $args
            'eve_gtm_code' // Should match Option ID
        )
    );

    add_settings_field(
        'eve_environment_details',
        'Environment',
        'eve_env_dd_callback',
        $page_name,
        $section_id,
        array(
            'eve_environment_details'
        )
    );

    add_settings_field(
        'eve_employe_page_uri',
        'Default employe lander URL',
        'my_textbox_callback',
        $page_name,
        $section_id,
        array(
            'eve_employe_page_uri'
        )
    );

    add_settings_field(
        'eve_social_linkedin_uri',
        'LinkedIn URL',
        'my_textbox_callback',
        $page_name,
        $section_id,
        array(
            'eve_social_linkedin_uri'
        )
    );

    add_settings_field(
        'eve_social_instagram_uri',
        'Instagram URL',
        'my_textbox_callback',
        $page_name,
        $section_id,
        array(
            'eve_social_instagram_uri'
        )
    );

    add_settings_field(
        'eve_social_facebook_uri',
        'Facebook URL',
        'my_textbox_callback',
        $page_name,
        $section_id,
        array(
            'eve_social_facebook_uri'
        )
    );

    add_settings_field(
        'eve_theme_version',
        'Version',
        'my_textbox_callback',
        $page_name,
        $section_id,
        array(
            'eve_theme_version'
        )
    );

    add_settings_field(
        'eve_html_compression',
        'HTML Compression',
        'eve_htmlgen_dd_callback',
        $page_name,
        $section_id,
        array(
            'eve_html_compression'
        )
    );

    register_setting($page_name, 'eve_gtm_code', 'esc_attr');
    register_setting($page_name, 'eve_environment_details', 'esc_attr');
    register_setting($page_name, 'eve_theme_version', 'esc_attr');
    register_setting($page_name, 'eve_employe_page_uri', 'esc_attr');
    register_setting($page_name, 'eve_social_linkedin_uri', 'esc_attr');
    register_setting($page_name, 'eve_social_instagram_uri', 'esc_attr');
    register_setting($page_name, 'eve_social_facebook_uri', 'esc_attr');
    register_setting($page_name, 'eve_html_compression', 'esc_attr');
}

function progresseve_section_options_callback()
{
    echo '<p>General theme settings</p>';
}

function my_textbox_callback($args)
{
    $option = get_option($args[0]);
    echo '<input type="text" id="' . $args[0] . '" name="' . $args[0] . '" value="' . $option . '" />';
}

function eve_env_dd_callback($args)
{
    $options = get_option($args[0]);
    $items = array(
        0 => 'Development',
        1 => 'Live'
    );
    echo '<select id="' . $args[0] . '" name="' . $args[0] . '">';
    foreach ($items as $k => $v) {
        $selected = ($options[0] == $k) ? ' selected="selected"' : '';
        echo '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
    }
    echo "</select>";
}

function eve_htmlgen_dd_callback($args)
{
    $options = get_option($args[0]);
    $items = array(
        0 => 'No',
        1 => 'Yes'
    );
    echo '<select id="' . $args[0] . '" name="' . $args[0] . '">';
    foreach ($items as $k => $v) {
        $selected = ($options[0] == $k) ? ' selected="selected"' : '';
        echo '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
    }
    echo "</select>";
}


/**
 * Theme verison control
 */
if (!function_exists('progresseve_verison_control')) :
    /**
     * Template style loader
     */
    function progresseve_verison_control()
    {
        $e = get_option('eve_environment_details');
        if ($e == false || empty($e)) {
            return time();
        }

        $v = get_option('eve_theme_version');
        if ($v === false || empty($v)) {
            return '1.0.0';
        }
        return $v;
    }
endif;

/**
 * Remove XMLrpc method
 */
function ayn_remove_xmlrpc_pingback_ping($methods)
{
    unset($methods['pingback.ping']);
    return $methods;
}

if (!function_exists('ayn_disable_emojis_tinymce')) :
    /**
     * Filter function used to remove the tinymce emoji plugin.
     *
     * @param    array $plugins
     * @return   array  Difference betwen the two arrays
     */
    function ayn_disable_emojis_tinymce($plugins)
    {
        if (is_array($plugins)) {
            return array_diff($plugins, array('wpemoji'));
        } else {
            return array();
        }
    }
endif;


if (!function_exists('eve_itsme_disable_feed')) :
    /**
     * Redirect feed page
     */
    function eve_itsme_disable_feed()
    {
        wp_die(__('No feed available, please visit the <a href="' . esc_url(home_url('/')) . '">homepage</a>!'));
    }
endif;


if (!function_exists('progresseve_inline_style')) :
    /**
     * Template style loader
     */
    function progresseve_inline_style()
    {
        echo "<style>";
        echo file_get_contents(get_template_directory() . '/inline.css');

        global $template;
        $local_template = basename($template);
        if (!empty($local_template) && is_string($local_template)) {
            $local_template_path = get_template_directory() . '/css/' . $local_template . '.css';

            if (file_exists($local_template_path)) {
                echo file_get_contents($local_template_path);
            }
        }

        echo "</style>\r\n";
    }
endif;


/**
 * HTML Compression script
 */
class WP_HTML_Compression
{
    // Settings
    protected $compress_css = true;
    protected $compress_js = true;
    protected $info_comment = false;
    protected $remove_comments = true;

    // Variables
    protected $html;

    public function __construct($html)
    {
        if (!empty($html)) {
            $this->parseHTML($html);
        }
    }

    public function __toString()
    {
        return $this->html;
    }

    protected function bottomComment($raw, $compressed)
    {
        $raw = strlen($raw);
        $compressed = strlen($compressed);

        $savings = ($raw - $compressed) / $raw * 100;

        $savings = round($savings, 2);

        return '<!--HTML compressed, size saved ' . $savings . '%. From ' . $raw . ' bytes, now ' . $compressed . ' bytes-->';
    }

    protected function minifyHTML($html)
    {
        $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
        $overriding = false;
        $raw_tag = false;
        // Variable reused for output
        $html = '';
        foreach ($matches as $token) {
            $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;

            $content = $token[0];

            if (is_null($tag)) {
                if (!empty($token['script'])) {
                    $strip = $this->compress_js;
                } else if (!empty($token['style'])) {
                    $strip = $this->compress_css;
                } else if ($content == '<!--wp-html-compression no compression-->') {
                    $overriding = !$overriding;

                    // Don't print the comment
                    continue;
                } else if ($this->remove_comments) {
                    if (!$overriding && $raw_tag != 'textarea') {
                        // Remove any HTML comments, except MSIE conditional comments
                        $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
                    }
                }
            } else {
                if ($tag == 'pre' || $tag == 'textarea') {
                    $raw_tag = $tag;
                } else if ($tag == '/pre' || $tag == '/textarea') {
                    $raw_tag = false;
                } else {
                    if ($raw_tag || $overriding) {
                        $strip = false;
                    } else {
                        $strip = true;

                        // Remove any empty attributes, except:
                        // action, alt, content, src
                        $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);

                        // Remove any space before the end of self-closing XHTML tags
                        // JavaScript excluded
                        $content = str_replace(' />', '/>', $content);
                    }
                }
            }

            if ($strip) {
                $content = $this->removeWhiteSpace($content);
            }

            $html .= $content;
        }

        return $html;
    }

    public function parseHTML($html)
    {
        $this->html = $this->minifyHTML($html);

        if ($this->info_comment) {
            $this->html .= "\n" . $this->bottomComment($html, $this->html);
        }
    }

    protected function removeWhiteSpace($str)
    {
        $str = str_replace("\t", ' ', $str);
        $str = str_replace("\n", '', $str);
        $str = str_replace("\r", '', $str);

        while (stristr($str, '  ')) {
            $str = str_replace('  ', ' ', $str);
        }

        return $str;
    }
}

function eve_html_compression_finish($html)
{
    return new WP_HTML_Compression($html);
}

if (!function_exists('eve_html_compression_start')) :
    /**
     * HTML Compression
     */
    function eve_html_compression_start()
    {
        ob_start('eve_html_compression_finish');
    }
endif;


if (!function_exists('progresseve_fonts_url')) :
    /**
     * Register custom fonts.
     * @import url('https://fonts.googleapis.com/css?family=Montserrat:400,400i,500,600,600i,700,700i&subset=latin-ext');
     */
    function progresseve_fonts_url()
    {
        $fonts_url = '';

        $font_families = array();
        $font_families[] = 'Montserrat:500,600,700';

        $query_args = array(
            'family' => urlencode(implode('|', $font_families)),
            // 'subset' => urlencode( 'latin,latin-ext' ),
            // 'subset' => urlencode( 'latin' ),
        );

        $fonts_url = add_query_arg($query_args, 'https://fonts.googleapis.com/css');

        return esc_url_raw($fonts_url);
    }
endif;


if (!function_exists('progresseve_resource_hints')) :
    /**
     * Add preconnect for Google Fonts.
     *
     * @since eve 1.0
     *
     * @param array $urls URLs to print for resource hints.
     * @param string $relation_type The relation type the URLs are printed.
     * @return array $urls           URLs to print for resource hints.
     */
    function progresseve_resource_hints($urls, $relation_type)
    {
        if (wp_style_is('progresseve-fonts', 'queue') && 'preconnect' === $relation_type) {
            $urls[] = array(
                'href' => 'https://fonts.gstatic.com',
                'crossorigin',
            );
        }
        return $urls;
    }
endif;


if (!function_exists('eve_ignore_sticky')) :
    // the function that does the work
    function eve_ignore_sticky($query)
    {
        // sure we're were we want to be.
        if (is_home() && $query->is_main_query())
            $query->set('ignore_sticky_posts', true);
    }
endif;


if (!function_exists('eve_custom_excerpt_length')) :
    function eve_custom_excerpt_length($length)
    {
        return 26;
    }
endif;


if (!function_exists('eve_new_excerpt_more')) :
    function eve_new_excerpt_more($more)
    {
        return '...';
    }
endif;


if (!function_exists('eve_cpt_redirect_post')) :
    function eve_cpt_redirect_post()
    {
        $queried_post_type = get_query_var('post_type');
        if (is_single() && 'transformations' == $queried_post_type) {
            wp_redirect(get_post_type_archive_link('transformations'), 301);
            exit;
        }
    }
endif;


if (!function_exists('eve_cpt_change_sort_order')) :
    function eve_cpt_change_sort_order($query)
    {
        if (is_archive() && is_post_type_archive('members')):
            //If you wanted it for the archive of a custom post type use: is_post_type_archive( $post_type )
            //Set the order ASC or DESC
            $query->set('order', 'ASC');
            //Set the orderby
            // $query->set( 'orderby', 'title' );
        endif;
    }

    ;
endif;


if (!function_exists('eve_remove_jquery_migrate')) :
    /**
     * Dequeue jQuery Migrate script in WordPress.
     */
    function eve_remove_jquery_migrate(&$scripts)
    {
        if (!is_admin()) {
            $scripts->remove('jquery');
            $scripts->add('jquery', false, array('jquery-core'), '1.12.4');
        }
    }

endif;


if (!function_exists('eve_get_current_page_id')) :
    /**
     * Return's current PAGE ID.
     */
    function eve_get_current_page_id()
    {
        return get_queried_object_id();
    }
endif;



if (!function_exists('eve_get_def_employe_page_uri')) :
    /**
     * Retrieves the default employe lander page url
     */
    function eve_get_def_employe_page_uri()
    {
        return get_option('eve_employe_page_uri');
    }
endif;



if (!function_exists('eve_authentication_page_redirect')) :
    /**
     * Redirect from login and register to profile page if user loged in
     */
    function eve_authentication_page_redirect()
    {
        if (!eve_is_user_logged_in()) {
            return;
        }

        wp_redirect(eve_get_user_profile_uri());
        exit;
    }
endif;


if (!function_exists('eve_authenticate')) :
    /**
     * Check for user authentication
     */
    function eve_authenticate()
    {
        if ( eve_is_user_logged_in() ) {
            return;
        }
        wp_redirect( eve_get_login_page_uri() );
        exit;
    }
endif;


if (!function_exists('eve_get_field')) :
    /**
     * Check if ACF is activated
     */
    function eve_get_field($selctor, $id, $format = false)
    {
        if (function_exists('get_field')) {
            $value = get_field($selctor, $id, $format);
            return $value;
        }
        return false;
    }
endif;


if (!function_exists('is_woocommerce_activated')) {
    /**
     * Check if woo is activated
     */
    function is_woocommerce_activated()
    {
        if (class_exists('woocommerce')) {
            return true;
        } else {
            return false;
        }
    }
}


if (!function_exists('eve_my_account_menu_order')) :
    /**
     * Change woo dashboard menu
     */
    function eve_my_account_menu_order()
    {
        $company_and_admin_menu_order = array(
            'dashboard'        => __('Overview', 'dasfalke-profile'),
        /*  'add-job'          => __('Add job', 'dasfalke-profile'), */
        /*  'orders'           => __('Orders', 'dasfalke-profile'), */
            'company-profile'  => __('Company profile', 'dasfalke-profile'),
            'edit-account'     => __('Login details', 'dasfalke-profile'),
            'customer-logout'  => __('Logout', 'dasfalke-profile'),
        );

        $jobseeker_menu_order = array(
            'dashboard'        => __('JOB ALERTS', 'dasfalke-profile'),
            'edit-account'     => __('Login Details', 'dasfalke-profile'),
            'customer-logout'  => __('Logout', 'dasfalke-profile'),
        );

        $user_type = eve_get_logged_in_user_role();

        switch ( $user_type ):
            case EMPLOYER_ROLE:
                return $company_and_admin_menu_order;
                break;
            case JOBSEEKER_ROLE:
                return $jobseeker_menu_order;
                break;
            case 'administrator':
                return $company_and_admin_menu_order;
                break;
            default:
                return $company_and_admin_menu_order;
        endswitch;
    }
endif;


if (!function_exists('eve_my_account_menu_items')) :
    /**
     * Remove downloads menu
     */
    function eve_my_account_menu_items($items)
    {
        unset($items['downloads']);
        unset($items['edit-address']);
        return $items;
    }
endif;


if (!function_exists('eve_my_account_new_endpoints')) :
    /**
     * My account page endpoints
     */
    function eve_my_account_new_endpoints()
    {
        // $user_type = eve_get_logged_in_user_role();

        // if( $user_type == EMPLOYER_ROLE ){
            add_rewrite_endpoint('add-job', EP_ROOT | EP_PAGES);
            add_rewrite_endpoint('edit-job', EP_ROOT | EP_PAGES);
            add_rewrite_endpoint('company-profile', EP_ROOT | EP_PAGES);
            add_rewrite_endpoint('employers', EP_ROOT);
        // }
    }
endif;

if ( ! function_exists('eve_flag_only_language_switcher') ) :
    /**
     * Print lang switcher
     */
    function eve_flag_only_language_switcher() {
        $languages = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );
     
        if ( !empty( $languages ) ) {
            echo '<div class="flags">';
            foreach( $languages as $l ) {
                if( $l['active'] )
                    continue;

                ?><a class="flags__item" href="<?php echo $l['url']; ?>"><img class="flags__item-img" src="<?php echo $l['country_flag_url']; ?>" width="18" height="18" alt="<?php echo  $l['language_code']; ?>"></a><?php
            }
            echo '</div>';
        }
    }
endif;


if (!function_exists('eve_get_homepage_featured')) :
    /**
     * Return a number of featured posts
     */
    function eve_get_homepage_featured()
    {
        $bundleManager = new DFPBManager();
        $available_homepage_featured_jobs = $bundleManager->get_all_available_and_featured_jobs(["feature-homepage", "feature-bundle-homepage"], false);
        $homepage_posts = [];

        foreach($available_homepage_featured_jobs as $job) {
            $homepage_posts = array_merge($homepage_posts, explode(",", $job->post_ids));
        }

        return new WP_Query([
            'post__in' => $homepage_posts,
            'posts_per_page' => 10,
            'orderby'=> 'rand',
        ]);
    }
endif;


if ( ! function_exists('eve_new_default_gravatar')) :
    /**
     * Change default gravatar
     */
    function eve_new_default_gravatar( $avatar_defaults ) {
        $falke = get_template_directory_uri() .'/img/default.jpg';
        $avatar_defaults[$falke] = "Falke Gravatar";
        return $avatar_defaults;
    }
endif;


if ( ! function_exists('eve_redirect_after_logout') ) :
    /**
     * Redirect user after logout
     */
    function eve_redirect_after_logout(){
        wp_redirect( home_url() );
        exit();
    }
endif;


if ( ! function_exists('eve_foo')) :
    /**
     * Comment
     */
    function eve_foo()
    {

    }
endif;

// WOO: Checkout UID-Nummer not required, phone unset
add_filter( 'woocommerce_checkout_fields' , 'dfp_override_default_checkout_fields' );
function dfp_override_default_checkout_fields( $fields ) {
     $fields['billing']['billing_tax_number']['required'] = false;
     unset($fields['billing']['billing_phone']);
	 return $fields;
}

// WOO: Change some translations texts
add_filter( 'gettext', 'dfp_translate_woocommerce_strings', 999 );
function dfp_translate_woocommerce_strings( $translated ) {
	$translated = str_ireplace( 'Anzeigename', 'Benutzername', $translated );	
	//Jelszó változtatás
	$translated = str_ireplace( 'Current password (leave blank to leave unchanged)', 'Current password', $translated );
	$translated = str_ireplace( 'New password (leave blank to leave unchanged)', 'New password', $translated );
	$translated = str_ireplace( 'Aktuelles Passwort (leer lassen für keine Änderung)', 'Aktuelles Passwort', $translated );
	$translated = str_ireplace( 'Neues Passwort (leer lassen für keine Änderung)', 'Neues Passwort', $translated );
	$translated = str_ireplace( 'Jelenlegi jelszó (hagyd üresen ha nem kívánod módosítani)', 'Jelenlegi jelszó', $translated );
	$translated = str_ireplace( 'Új jelszó (hagyd üresen ha nem kívánod módosítani)', 'Új jelszó', $translated );
	// Chaeckout
	$translated = str_ireplace( 'Auschecken', 'Payment', $translated );
	$translated = str_ireplace( 'Deine Bestellung', 'Ihre Bestellung', $translated );
return $translated;
}

// WOO: Button on order-received page
add_action( 'woocommerce_thankyou', 'dfp_add_button_thankyou' );
function dfp_add_button_thankyou() {
?>
<a class="df-btn primary small" href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="<?php _e('My Account',''); ?>"><?php _e('Back to My Profile','dasfalke-checkout'); ?></a>
<?php
}
remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );