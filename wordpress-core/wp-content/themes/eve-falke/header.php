<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package eve
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta http-equiv="cleartype" content="on">
<meta name="MobileOptimized" content="320">
<meta name="HandheldFriendly" content="True">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="dns-prefetch" href="//www.googletagmanager.com">
<link rel="dns-prefetch" href="//ajax.googleapis.com">
<?php wp_head(); ?>
<script src="//widget.manychat.com/248620935660739.js" async="async"></script>
<meta name="theme-color" content="#3E76EB">
<link rel="icon" sizes="192x192" href="<?php echo get_template_directory_uri(); ?>/img/eve-highres.png">
</head>
<body <?php body_class(); ?>>
<?php do_action( 'eve_body_opening' ); ?>
<?php if( wp_is_mobile() ): ?>
<div class="slideout-menu" id="slideout-menu">
	<div class="header-mobile">
		<div class="header-mobile-header">
			<div class="header-mobile-header-psn">
				<?php if (!eve_is_user_logged_in()) : ?>
					<div class="header-mobile-header-left">
						<a class="df-btn small" href="<?php echo eve_get_login_page_uri(); ?>"><?php _e( 'Sign in', 'dasfalke-header' ); ?></a>
					</div>
					<div class="header-mobile-header-right">
						<a class="df-btn primary small" href="<?php echo eve_get_register_page_uri(); ?>"><?php _e( 'Register', 'dasfalke-header' ); ?></a>
					</div>
				<?php else: ?>
					<div class="header-mobile-header-right">
						<a class="df-btn small" href="<?php echo wp_logout_url(); ?>"><?php _e( 'Log out', 'dasfalke-header' ); ?></a>
					</div>
					<div class="header-mobile-header-left"></div>
				<?php endif; ?>
			</div>
		</div>
		<div class="header-mobile-langselect"><?php eve_flag_only_language_switcher(); ?></div>
		<?php
        $menu_type = [
            'theme_location' => 'header-menu-mobile-1',
            'menu_id'        => 'header-menu-mobile-1',
            'container'      => false
        ];

		if( eve_is_user_employer() ){
            $menu_type = [
                'theme_location' => 'header-menu-mobile-2',
                'menu_id'        => 'header-menu-mobile-2',
                'container'      => false
            ];
        } else if( eve_is_user_jobseeker() ){
            $menu_type = [
                'theme_location' => 'header-menu-mobile-3',
                'menu_id'        => 'header-menu-mobile-3',
                'container'      => false
            ];
		}

        wp_nav_menu($menu_type);
		?>
		<?php if( ! eve_is_user_jobseeker() ){ ?>
		<div class="advertise-job">
			<h3><?php _e( 'Advertise your job today', 'dasfalke-header' ); ?></h3>
			<a href="<?php echo eve_get_def_employe_page_uri(); ?>" class="df-btn primary"><?php _e( 'Add job', 'dasfalke-profile' ); ?></a>
		</div>
		<?php } ?>
		<div class="header-bottom">
			<?php eve_get_social_links(); ?>
			<p class="copyright">&copy; <?php echo date('Y'); ?> <?php _e( 'Das Falke Personal Limited, all rights reserved.', 'dasfalke-header' ); ?></p>
		</div>
	</div>
</div>
<?php endif; ?>
<div class="slideout-panel" id="slideout-panel">
<div class="site">
<header class="site-header">
	<div class="ctn max">
		<div class="brand" data-slideout-ignore>
			<a class="brand-uri fr" href="<?php echo apply_filters( 'wpml_home_url', get_option( 'home' ) ); ?>"><svg class="i i-logo brand-img" width="116.217" height="37.348" title="<?php get_bloginfo('name') ?>"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-logo" href="#i-logo"></use></svg></a>
		</div>
		<nav id="site-navigation" class="main-navigation">
			<div class="auth-links only-mobile">
				<?php if (!eve_is_user_logged_in()) : ?>
					<a href="<?php echo eve_get_login_page_uri(); ?>"><?php _e( 'Sign in', 'dasfalke-header' ); ?></a>
				<?php else: ?>
					<a href="<?php echo wp_logout_url(); ?>"><?php _e( 'Log out', 'dasfalke-header' ); ?></a>
				<?php endif; ?>
				<button class="slideout-toggle-button">☰</button>
			</div>
			<?php
				wp_nav_menu([
					'theme_location' => 'header-menu-desktop',
					'menu_id'        => 'header-menu-desktop',
					'container'      => false
				]);
			?>
			<div class="auth-links only-desktop">
				<?php eve_flag_only_language_switcher(); ?>

				<?php if (!eve_is_user_logged_in()) : ?>
					<a href="<?php echo eve_get_login_page_uri(); ?>"><?php _e( 'Sign in', 'dasfalke-header' ); ?></a>
					<a href="<?php echo eve_get_register_page_uri(); ?>" class="df-btn outline small"><?php _e( 'Register', 'dasfalke-header' ); ?></a>
				<?php else: ?>
					<a href="<?php echo wp_logout_url(); ?>"><?php _e( 'Log out', 'dasfalke-header' ); ?></a>
					<?php // if( eve_is_user_jobseeker() ): ?>
					<!-- 	<a href="<?php // echo eve_get_user_profile_uri(); ?>"><?php //_e( 'Alerts', 'dasfalke-header' ); ?></a> -->
					<?php // endif; ?>
				<?php endif; ?>
				<?php if ( eve_is_user_logged_in() ) : ?>
				<a href="<?php echo eve_get_user_profile_uri(); ?>" class="auth-user">
					<div class="auth-user__name"><?php echo get_user_name(); ?></div>
					<div class="auth-user__pic">
						<div class="auth-user__pic-psn"><img class="auth-user__pic-img" src="<?php echo get_user_avatar(); ?>" title="<?php echo get_user_name(); ?>"></div>
					</div>
				</a>
				<?php endif; ?>
			</div>
		</nav>
	</div>
</header>
<?php wc_print_notices(); ?>
<?php falke_print_notices(); ?>
<div class="site-content">
