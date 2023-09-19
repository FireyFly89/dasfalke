<?php
/**
 * eve functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package eve
 */

if ( ! function_exists( 'eve_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function eve_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on eve, use a find and replace
		 * to change 'eve' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'eve', get_template_directory() . '/languages' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );


		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );


		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'header-menu-desktop'   => esc_html__( 'Header Menu Desktop', 'eve' ),
			'header-menu-mobile-1'  => esc_html__( 'Header Menu Mobile Visitor', 'eve' ),
			'header-menu-mobile-2'  => esc_html__( 'Header Menu Mobile Employer', 'eve' ),
			'header-menu-mobile-3'  => esc_html__( 'Header Menu Mobile Jobseeker', 'eve' ),
			'footer-menu-1'         => esc_html__( 'Footer Menu 1', 'eve' ),
			'footer-menu-2'         => esc_html__( 'Footer Menu 2', 'eve' ),
		));


		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );


		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'eve_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		) );

		/**
		 * Image sizes
		 */
		add_image_size( 'one-tree-page-banner-thumb', 120, 40, true );

		add_image_size( 'one-tree-page-banner-mobile-full', 700, 500, true );
		add_image_size( 'one-tree-page-banner-desktop-full', 1300, 400, true );

		add_image_size( 'user-profile-image', 200, 200, true );

		add_image_size( 'blog-thumbnail', 560, 290, true );
	}
endif;
add_action( 'after_setup_theme', 'eve_setup' );



/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function eve_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar for pages', 'eve' ),
		'id'            => 'sidebar-page',
		'description'   => esc_html__( 'Add widgets here.', 'eve' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar for jobs', 'eve' ),
		'id'            => 'sidebar-single',
		'description'   => esc_html__( 'Add widgets here.', 'eve' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar for author page', 'eve' ),
		'id'            => 'sidebar-author',
		'description'   => esc_html__( 'Add widgets here.', 'eve' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar for company page', 'eve' ),
		'id'            => 'sidebar-company',
		'description'   => esc_html__( 'Add widgets here.', 'eve' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'eve_widgets_init' );


/**
 * Enqueue scripts and styles.
 */
function eve_scripts()
{
	if( file_exists( get_template_directory() . '/inline.css' ) ){
		add_action( 'wp_head', 'progresseve_inline_style', 100 );
	}else{
		wp_enqueue_style( 'eve-style', get_stylesheet_uri() );
	}
	wp_enqueue_script( 'jquery' );
	add_action( 'wp_footer', 'progresseve_scripts', 1 );
	wp_enqueue_style( 'progresseve-fonts', progresseve_fonts_url(), array(), null );
	wp_dequeue_style( 'wp-block-library' );
	wp_deregister_style( 'wp-block-library' );

  // wp_enqueue_script( 'eve-scripts', get_template_directory_uri() . '/js/eve.js', array(), '20151215', true );
    
	// if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
	// 	wp_enqueue_script( 'comment-reply' );
	// }
}
add_action( 'wp_enqueue_scripts', 'eve_scripts' );



/**
 * Progresseve improvements
 */
require get_template_directory() . '/template-inc/template-functions.php';

/**
 * Progresseve template views
 */
require get_template_directory() . '/template-inc/template-views.php';

/**
 * Gallery metabox
 */
// require get_template_directory() . '/template-inc/admin/gallery-metabox/gallery-metabox.php';

/**
 * Das Falke Personal
 */
require get_template_directory() . '/template-inc/template-dasfalkepersonal.php';

/**
 * Blog CPT
 */
require get_template_directory() . '/template-inc/template-blog.php';

/**
 * 
 * Template hooks
 * 
 */

// Admin actions
add_action( 'admin_init', 'eve_general_section' );
if( get_option('eve_html_compression') == 1 && get_option('eve_environment_details') == 1 ){
    add_action( 'get_header', 'eve_html_compression_start' );
}


// Setup
add_filter( 'body_class', 'eve_body_classes' );
// add_filter( 'avatar_defaults', 'eve_new_default_gravatar' );
add_filter( 'wp_resource_hints', 'progresseve_resource_hints', 0, 2 );
add_filter( 'xmlrpc_methods', 'ayn_remove_xmlrpc_pingback_ping' );
add_filter( 'wp_default_scripts', 'eve_remove_jquery_migrate' );
add_filter( 'widget_text', 'do_shortcode' );
add_action( 'wp_footer', 'eve_theme_icons', 10 );
add_action( 'wp_logout','eve_redirect_after_logout' );


// GTM Tracking Code
add_action( 'wp_head', 'progresseve_gtm_core', 5 );
add_action( 'eve_body_opening', 'progresseve_gtm_noscript', 10 );


// Blog and posts
add_action( 'pre_get_posts', 'eve_ignore_sticky' );
// add_filter( 'excerpt_length', 'custom_excerpt_length', 999 ); // Excerpt length
// add_filter( 'excerpt_more', 'new_excerpt_more' ); // Read more button text
// add_action( 'template_redirect', 'eve_cpt_redirect_post' ); // Redirect post type
// add_action( 'pre_get_posts', 'eve_cpt_change_sort_order'); // Change post order


// Template actions
add_action( 'page_sidebar_before_content', 'eve_page_sidebar_content_wrappers_start', 10 );
add_action( 'page_sidebar_before_content', 'eve_page_sidebar_content_main_start', 20 );
add_action( 'page_sidebar_between_content', 'eve_page_sidebar_content_main_end', 10 );
add_action( 'page_sidebar_between_content', 'eve_page_sidebar_aside_wrp_start', 20 );
add_action( 'page_sidebar_after_content', 'eve_page_sidebar_aside_wrp_end', 10 );
add_action( 'page_sidebar_after_content', 'eve_page_sidebar_content_wrappers_end', 20 );
add_action( 'single_sidebar_before_widget', 'eve_single_sidebar', 10 );
add_action( 'author_sidebar_before_widget', 'eve_author_sidebar', 10 );
add_action( 'company_sidebar_before_widget', 'eve_company_sidebar', 10 );


// Init and other
function eve_init()
{
  // Display the links to the extra feeds such as category feeds
  remove_action( 'wp_head', 'feed_links_extra', 3 );

  // Display the link to the Really Simple Discovery service endpoint, EditURI link
  remove_action( 'wp_head', 'rsd_link' );

  // Display the link to the Windows Live Writer manifest file.
  remove_action( 'wp_head', 'wlwmanifest_link' );

  // Display the XHTML generator that is generated on the wp_head hook, WP version
  remove_action( 'wp_head', 'wp_generator' );
  remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
  remove_action( 'wp_head', 'feed_links', 2 );

  // Turn off oEmbed auto discovery; Don't filter oEmbed results.
  remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10);

  // Remove oEmbed discovery links.
  remove_action( 'wp_head', 'wp_oembed_add_discovery_links');

  // Remove oEmbed-specific JavaScript from the front-end and back-end.
  remove_action( 'wp_head', 'wp_oembed_add_host_js');

  //Remove emoji script
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

  // Remove emoji style from head
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_action( 'admin_print_styles', 'print_emoji_styles' );
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
  // add_filter( 'tiny_mce_plugins', 'ayn_disable_emojis_tinymce' );
  add_filter( 'emoji_svg_url', '__return_false' );

  // Remove recent comments style
  add_filter( 'show_recent_comments_widget_style', function() { return false; });

  // Disable feed
  add_action( 'do_feed', 'eve_itsme_disable_feed', 1 );
  add_action( 'do_feed_rdf', 'eve_itsme_disable_feed', 1 );
  add_action( 'do_feed_rss', 'eve_itsme_disable_feed', 1 );
  add_action( 'do_feed_rss2', 'eve_itsme_disable_feed', 1 );
  add_action( 'do_feed_atom', 'eve_itsme_disable_feed', 1 );
  add_action( 'do_feed_rss2_comments', 'eve_itsme_disable_feed', 1 );
	add_action( 'do_feed_atom_comments', 'eve_itsme_disable_feed', 1 );
}
add_action( 'init', 'eve_init', 666 );


// WP Hook
function eve_wp(){
	if(
		is_page_template('template-pages/page-login.php') ||
		is_page_template('template-pages/page-register.php') ){
			eve_authentication_page_redirect();
	}

	if( 
		is_page_template('template-pages/page-profile.php') ){
			eve_authenticate();
	}

	if( 
		is_home() ||
		is_search() ){
			add_action( 'page_sidebar_before_content', 'eve_index_page_search_filter', 5 );
			add_action( 'single_sidebar_before_widget', 'eve_index_page_filters', 10 );
	}
	
	// if( 
	// 	is_author() ){		
	// 		add_action( 'page_sidebar_before_content', 'eve_author_page_top', 5 );
	// }
}
add_action( 'wp', 'eve_wp', 666 );


// Woocommerce
if( is_woocommerce_activated() ){
	remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation' );

	add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
	add_filter ( 'woocommerce_account_menu_items', 'eve_my_account_menu_order' );
	add_filter( 'woocommerce_account_menu_items', 'eve_my_account_menu_items' );

	// Checkout
	add_action( 'woocommerce_review_order_before_payment', 'eve_checkout_payment_heading' );
	
	add_action( 'init', 'eve_my_account_new_endpoints' );

	if( eve_is_user_employer() ){
		add_action( 'woocommerce_account_dashboard', 'eve_woo_profile_active_joblistings', 10 );
	}

	if( eve_is_user_jobseeker() ){
		add_action( 'woocommerce_account_dashboard', 'eve_woo_profile_active_alerts', 10 );
	}

	add_action( 'profile_sidebar_navlist', 'woocommerce_account_navigation', 100 );
	add_action( 'woocommerce_account_add-job_endpoint', 'eve_woo_profile_company_edit_job' );
    add_action( 'woocommerce_account_edit-job_endpoint', 'eve_woo_profile_company_edit_job' );
	add_action( 'woocommerce_account_company-profile_endpoint', 'eve_woo_profile_company_company_profile' );
}
