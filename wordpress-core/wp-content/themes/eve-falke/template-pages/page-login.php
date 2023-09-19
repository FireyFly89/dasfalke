<?php

/* Template Name: Falke Login */

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

if (class_exists('FacebookLoginManager')) {
    $loginManager = new FacebookLoginManager();
    $fb_url = $loginManager->get_facebook_login_url(eve_get_login_page_uri() . "/");
    $loginManager->get_facebook_access_token();
}

get_header();

$textdomain = 'dasfalke-login';
?>
<div class="ctn max">
  <div class="content-area">
    <div class="content-main">
      <main class="auth">
        <div class="auth-forms-wrap">
        <!--  <h1 class="auth__title"><?php _e('Sign in', $textdomain) ?></h1> -->

        <?php if (!empty($fb_url)) : ?>
          <div class="login__facebook"><a class="df-btn facebook block" href="<?php echo $fb_url; ?>" data-provider="facebook"><?php _e('Sign in with Facebook', $textdomain) ;?></a></div>
          <div class="auth__sep"><span class="auth__sep-bg"><?php _e('OR', $textdomain) ;?></span></div>
        <?php endif; ?>

          <form id="userRequest" action="<?php echo get_current_url(); ?>" method="post">

            <div class="form-row text">
              <label class="form-row__label" for="loginLogin"><?php _e('E-mail', $textdomain); ?></label>
              <div class="form-row__input">
                <input type="email" name="email" id="loginLogin" placeholder="<?php _e("E-mail", $textdomain); ?>" value="<?php echo ( !empty($_REQUEST['email']) ? $_REQUEST['email'] : '')?>">
              </div>
            </div>

            <div class="form-row text">
              <label class="form-row__label" for="loginPassword"><?php _e('Passwort', $textdomain); ?></label>
              <div class="form-row__input">
                <input type="password" autocomplete="off" name="password" id="loginPassword" placeholder="<?php _e("Passwort", $textdomain); ?>">
              </div>
            </div>

            <input type="hidden" name="request" value="login">
            <div class="form-submit">
              <input class="df-btn primary" type="submit" value="<?php _e('Sign in', $textdomain) ;?>">
            </div>

          </form>

          <br><br>

          <div class="login__lost-pass"><a class="login__action-btn" href="/lost-password/"><?php _e('Forgot your password?', $textdomain) ;?></a></div>

          <div class="login__register">
            <div class="login__register-label"><?php _e('Don\'t have a Das Falke account? ', $textdomain) ;?></div>
            <a class="login__action-btn" href="/register/"><?php _e('Register', $textdomain) ;?></a>
          </div>

        </div>
      </main>
    </div>
  </div>
</div>
<?php

get_footer();
