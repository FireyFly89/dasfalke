<?php

/* Template Name: Falke Registration */

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
          <h1 class="auth__title"><?php _e('Register', 'dasfalke-register') ?></h1>

            <?php if (!empty($fb_url)) : ?>
                <div class="login__facebook"><a class="df-btn facebook block" href="<?php echo $fb_url; ?>" data-plugin="nsl" data-action="connect" data-redirect="current" data-provider="facebook" data-popupwidth="475" data-popupheight="175"><?php _e('Register with Facebook', $textdomain) ;?></a></div>
                <div class="auth__sep"><span class="auth__sep-bg"><?php _e('OR', $textdomain) ;?></span></div>
            <?php endif; ?>

          <form id="userRequest" action="<?php echo get_current_url(); ?>" method="post">
            <div class="form-row register-role">
              <div class="styled-radio">
                <input type="radio" id="radioSeeker" name="role" checked value="<?php echo JOBSEEKER_ROLE; ?>">
                <label for="radioSeeker"><?php _e('Job Seeker', 'dasfalke-register') ;?></label>
              </div>
              <div class="styled-radio">
                <input type="radio" id="radioEmployer" name="role" value="<?php echo EMPLOYER_ROLE; ?>">
                <label for="radioEmployer"><?php _e('Employer', 'dasfalke-register') ;?></label>
              </div>
            </div>

            <div class="form-row text">
                <label class="form-row__label" for="registerCompany"><?php _e('Company name', $textdomain); ?> *</label>
                <div class="form-row__input">
                    <input type="text" name="company_name" id="registerCompany" placeholder="<?php _e("Company name", $textdomain); ?>" value="<?php echo get_field_if_posted('company_name'); ?>">
                </div>
            </div>

            <div class="form-row text">
              <label class="form-row__label" for="registerLogin"><?php _e('E-mail', $textdomain); ?> *</label>
              <div class="form-row__input">
                <input type="email" name="email" id="registerLogin" placeholder="<?php _e("E-mail", $textdomain); ?>" value="<?php echo get_field_if_posted('email'); ?>">
              </div>
            </div>

            <div class="form-row text">
              <label class="form-row__label" for="registerPassword"><?php _e('Passwort', $textdomain); ?> *</label>
              <div class="form-row__input">
                <input type="password" autocomplete="off" name="password" id="registerPassword" placeholder="<?php _e("Passwort", $textdomain); ?>">
              </div>
            </div>
            
            <div class="form-submit">
              <input type="hidden" name="request" value="register">
              <input class="df-btn primary" type="submit" value="<?php _e('Register', 'dasfalke-register') ;?>">
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>
</div>
<?php

get_footer();
