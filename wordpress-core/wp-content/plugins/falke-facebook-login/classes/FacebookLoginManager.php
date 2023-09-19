<?php

/**
 * Class FacebookLoginManager
 *
 * Das Falke Personal Facebook Login Manager
 * @package Das Falke Personal plugins
 */
class FacebookLoginManager
{
    private $default_graph_version = 'v3.2';
    private $redirect_login_helper;
    private $facebook;

    /**
     * FacebookLoginManager constructor.
     */
    public function __construct() {}

    public function get_facebook_login_url($login_url)
    {
        if (!session_id()) {
            session_start();
        }

        // Load facebook SDK
        require_once FB_MANAGER_PLUGIN_PATH . '/FacebookSDK/autoload.php';

        try {
            $this->facebook = new Facebook\Facebook([
                'app_id' => FB_APP_ID,
                'app_secret' => FB_APP_SECRET,
                'default_graph_version' => $this->default_graph_version,
            ]);

            $this->redirect_login_helper = $this->facebook->getRedirectLoginHelper();

            if (isset($_GET['state'])) {
                $this->redirect_login_helper->getPersistentDataHandler()->set('state', $_GET['state']);
            }

            return $this->redirect_login_helper->getLoginUrl($login_url, ['email']);
        } catch (Exception $e) {
            error_log('Caught exception on facebook login: ', $e->getMessage(), "\n");
        }

        return false;
    }

    public function get_facebook_access_token()
    {
        try {
            $accessToken = $this->redirect_login_helper->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            error_log('Graph returned an error: ' . $e->getMessage());
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            error_log('Facebook SDK returned an error: ' . $e->getMessage());
        }

        if (!empty($accessToken)) {
            // OAuth 2.0 client handler and validations
            $oAuth2Client = $this->facebook->getOAuth2Client();

            if (!$accessToken->isLongLived()) {
                // Exchanges a short-lived access token for a long-lived one
                try {
                    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                } catch (Facebook\Exceptions\FacebookSDKException $e) {
                    error_log("Error getting long-lived access token: " . $e->getMessage() . "\n");
                }
            }

            try {
                $tokenMetadata = $oAuth2Client->debugToken($accessToken);
                $tokenMetadata->validateAppId((string)FB_APP_ID);
                $tokenMetadata->validateExpiration();
                $response = $this->facebook->get('/me?fields=id,name,email,first_name,last_name,picture.width(200).height(200)', $accessToken);
                $user = $response->getGraphUser();
                $user_fb_id = $user->getId();
                $user_email = $user->getEmail();
                $user_name = $user->getName();
                $user_firstname = $user->getFirstName();
                $user_lastname = $user->getLastName();

                if (!eve_is_user_logged_in() && !empty($user_fb_id) && !empty($user_email) && !empty($user_name) && !empty($user_firstname) && !empty($user_lastname)) {
                    $user_obj = get_user_by('email', $user_email);

                    if (empty($user_obj)) {
                        $wp_user_id = wp_insert_user([
                            'user_login' => generate_unique_username($user_name),
                            'user_email' => $user_email,
                            'display_name' => $user_name,
                            'user_pass' => md5($user_email . $user_fb_id . time())
                        ]);

                        $wp_user = new WP_User($wp_user_id);
                        $wp_user->set_role(JOBSEEKER_ROLE);
                        send_mail_to_new_users(JOBSEEKER_ROLE, $user_email);

                        update_user_meta($wp_user_id, 'first_name', $user_firstname);
                        update_user_meta($wp_user_id, 'last_name', $user_lastname);
                        update_user_meta($wp_user_id, 'billing_first_name', $user_firstname);
                        update_user_meta($wp_user_id, 'billing_last_name', $user_lastname);
                        update_user_meta($wp_user_id, 'facebook_user_id', $user_fb_id);

                        $this->set_facebook_avatar_session($user, $wp_user_id);
                        $this->login_user_by_id($wp_user_id);
                    } else {
                        if (empty(get_user_data('facebook_user_id', $user_fb_id))) {
                            update_user_meta($user_obj->ID, 'facebook_user_id', $user_fb_id);
                        }

                        $this->set_facebook_avatar_session($user, $user_obj->ID);
                        $tokenMetadata->validateUserId($user_fb_id);
                        $this->login_user_by_id($user_obj->ID);
                    }
                }
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                error_log('Graph returned an error: ' . $e->getMessage());
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                error_log("Error validating failed, someone might be trying to trick the validation: " . $e->getMessage() . "\n");
                // Exit gracefully, since something is definitely not right here
                wp_die("Are you trying to trick the validation?");
            }
        }
    }

    private function login_user_by_id($user_id)
    {
        if (empty($user_id)) {
            return false;
        }

        wp_clear_auth_cookie();
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        wp_safe_redirect(eve_get_login_page_uri());
        exit();
    }

    private function set_facebook_avatar_session($graph_user, $wp_user_id)
    {
        $_SESSION['facebook_avatar'] = [];

        if (!empty($user_avatar_src = $graph_user->getPicture()->getUrl())) {
            $_SESSION['facebook_avatar'][] = [
                'user_id' => $wp_user_id,
                'src' => $user_avatar_src,
            ];
        }
    }

    public function clear_sessions()
    {
        if (!session_id()) {
            session_start();
        }

        unset($_SESSION['facebook_avatar']);
    }
}
