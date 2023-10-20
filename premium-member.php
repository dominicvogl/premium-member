<?php

/*
Plugin Name: Premium Raidbox Member
Plugin URI: https://raidbox.de
Description: A brief description of the Plugin.
Version: 0.1.0-alpha
Author: Dominic Vogl
Author URI: http://dominicvogl.de
License: GPL2
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Premium Member Plugin Class
 */
class PremiumMember
{

    public function __construct()
    {

		// add ne capability
		add_action('init', array($this, 'add_user_role'));
		// add script for user registration
		add_action('init', array($this, 'add_new_user'));
		add_action('wp_enqueue_scripts', array($this, 'plugin_stylesheets'));

		add_shortcode('user_register_form', array($this, 'registration_form'));
    }

    public function install_plugin()
    {
        // handle install process
    }

    public function uninstall_plugin()
    {
        // handle uninstall process
    }

    public function plugin_stylesheets()
    {
        // get bootstrap minimized css
		$bootstrap_css_url = plugins_url('node_modules/bootstrap/dist/css/bootstrap.min.css', __FILE__);

        // Enqueue bootstrap style
        wp_enqueue_style('plugin-bootstrap-style', $bootstrap_css_url);
    }


    /**
     * Check if user registration is possible, if true display the form, otherwise show only a message
     * @return string
     */
    public function registration_form(): string
    {

        // default message when you are logged in
        $output = __('You are still logged in', 'raidboxes_premium_member');

        // if user is not logged in, display the form
        if (!is_user_logged_in()) {

            // check for options in settings, if user are allowed to register
            if (get_option('users_can_register')) {
                $output = $this->registration_form_fields();
            } // if not, display a message
            else {
                $output = __('User registration is not possible at the moment', 'raidboxes_premium_member');
            }
        }

        return $output;
    }

    public function registration_form_fields()
    {
        ob_start();

		$this->register_messages();

        ?>

        <form id="rpm_user_registration_form" class="user_registration" action="" method="POST">
            <fieldset>

                <div class="mb-3">
                    <label for="rpm_user_name"
                           class="form-label"><?php _e('Username', 'raidboxes_premium_member'); ?></label>
                    <input type="text" class="form-control" id="rpm_user_name" name="rpm_user_name">
                </div>

                <div class="mb-3">
                    <label for="rpm_user_email"
                           class="form-label"><?php _e('E-Mail', 'raidboxes_premium_member'); ?></label>
                    <input type="email" class="form-control" id="rpm_user_email" name="rpm_user_email" aria-describedby="emailHelp">
                    <div id="emailHelp"
                         class="form-text"><?php _e("We'll never share your email with anyone else.", "raidboxes_premium_member"); ?></div>
                </div>
                <div class="mb-3">
                    <label for="rpm_user_first"
                           class="form-label"><?php _e('First Name', 'raidboxes_premium_member'); ?></label>
                    <input type="text" class="form-control" id="rpm_user_first" name="rpm_user_first">
                </div>
                <div class="mb-3">
                    <label for="rpm_user_last"
                           class="form-label"><?php _e('Last Name', 'raidboxes_premium_member'); ?></label>
                    <input type="text" class="form-control" id="rpm_user_last" name="rpm_user_last">
                </div>
                <div class="mb-3">
                    <label for="rpm_user_pass"
                           class="form-label"><?php _e('Password', 'raidboxes_premium_member'); ?></label>
                    <input type="password" class="form-control" id="rpm_user_pass" name="rpm_user_pass">
                </div>
                <div class="mb-3">
                    <label for="rpm_user_pass_confirm"
                           class="form-label"><?php _e('Password confirmation', 'raidboxes_premium_member'); ?></label>
                    <input type="password" class="form-control" id="rpm_user_pass_confirm" name="rpm_user_pass_confirm">
                </div>

                <input type="hidden" name="rpm_nonce" value="<?php echo wp_create_nonce('rpm-nonce'); ?>"/>

                <button type="submit" class="btn btn-primary">Submit</button>

            </fieldset>
        </form>

        <?php
        return ob_get_clean();
    }

    public function add_new_user()
    {

		echo '<pre>';
		var_dump($_POST);
		var_dump(isset($_POST['rpm_user_name']) && wp_verify_nonce($_POST['rpm_nonce'], 'rpm-nonce'));
		echo '</pre>';



        // check for username, and check verify wordpress nonce
		if (isset($_POST['rpm_user_name']) && wp_verify_nonce($_POST['rpm_nonce'], 'rpm-nonce')) {
            $user_data = [
                'user_login' => sanitize_user($_POST['rpm_user_name']),
                'user_email' => sanitize_email($_POST['rpm_user_email']),
                'first_name' => sanitize_text_field($_POST['rpm_user_first']),
                'last_name' => sanitize_text_field($_POST['rpm_user_last']),
                'user_pass' => sanitize_text_field($_POST['rpm_user_pass']),
                'user_pass_confirm' => sanitize_text_field($_POST['rpm_user_pass_confirm']),
            ];

            // required for username check
            require_once(ABSPATH . WPINC . '/registration.php');

            // if username already exists, give message
            if (username_exists($user_data['user_login'])) {
                $this->handle_errors()->add('username_unavailable', __('Username already taken', 'raidboxes_premium_member'));
            }

            // if username is invalid, give message
            if (!validate_username($user_data['user_login'])) {
                $this->handle_errors()->add('username_invalid', __('Invalid username', 'raidboxes_premium_member'));
            }

            // if username is empty, give message
            if ($user_data['user_login'] == '') {
                $this->handle_errors()->add('username_empty', __('Please enter a username', 'raidboxes_premium_member'));
            }

            // if email is invalid, give message
            if (!is_email($user_data['user_email'])) {
                $this->handle_errors()->add('email_invalid', __('Invalid email', 'raidboxes_premium_member'));
            }

            // if email is empty, give message
            if (email_exists($user_data['user_email'])) {
                $this->handle_errors()->add('email_used', __('Email already used', 'raidboxes_premium_member'));
            }

            if (empty($user_data['user_pass'])) {
                $this->handle_errors()->add('password_empty', __('Please enter a password', 'raidboxes_premium_member'));
            }

            // check password confirmation
            if ($user_data['user_pass'] != $user_data['user_pass_confirm']) {
                $this->handle_errors()->add('password_mismatch', __('Passwords do not match', 'raidboxes_premium_member'));
            }

            // get all error messages
            $errors = $this->handle_errors()->get_error_message();

            // if no errors then register user
            if (empty($errors)) {
                $new_user_id = wp_insert_user([
					'user_login' => $user_data['user_login'],
					'user_pass' => $user_data['user_pass'],
					'user_email' => $user_data['user_email'],
					'first_name' => $user_data['first_name'],
					'last_name' => $user_data['last_name'],
					'user_registered' => date('Y-m-d H:i:s'),
					'role' => 'raidboxes_premium_member',
				]);
                // echo 'User registered successfully';

				if($new_user_id) {
					// send mail to admin
					wp_new_user_notification($new_user_id, null, 'both');

					// set cookie for new users login
					wp_setcookie($user_data['user_login'], $user_data['user_pass'], true);

					// log the new user in
					wp_set_current_user($new_user_id, $user_data['user_login']);
					do_action('wp_login', $user_data['user_login']);

					// redirect new user to homepage
					wp_redirect(home_url());
					exit;
				}
            }
        }
    }

	public function register_messages() {
		if($codes = $this->handle_errors()->get_error_codes()) {
			echo '<div class="form_errors">';
			foreach($codes as $code) {
				$message = $this->handle_errors()->get_error_message($code);
				echo '<div class="alert alert-danger" role="alert">' . __('Error: ') . $message . '</div>';
			}
			echo '</div>';
		}
	}

    public function handle_errors()
    {
		static $wp_error;
		return $wp_error ?? ($wp_error = new WP_Error(null, null, null));
    }

    public function add_user_role(): void
    {
        add_role('raidboxes_premium_member', 'Raidboxes Premium Member', [
            'read' => true,
            // Add additional capabilities here
        ]);
    }

    public function render_login_form()
    {
        // Generate and display login form HTML here
    }

    public function handle_user_registration()
    {
        // Handle registration logic here
    }

    public function handle_user_login()
    {
        // Handle registration logic here
    }

    public function handle_password_reset()
    {
        // Handle password reset logic here
    }


}

new PremiumMember();
