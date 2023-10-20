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
		// add roles
		$this->add_roles();
		// handle the user registration process
		$this->handle_user_registration();
		// add plugin scripts and styles
		$this->add_plugin_scripts();
		// generate form shortcodes
		$this->create_shortcodes();
	}

	public function add_roles()
	{
		add_action('init', array($this, 'add_user_role'), 10);
	}

	public function add_plugin_scripts()
	{
		add_action('wp_enqueue_scripts', array($this, 'plugin_stylesheets'));
	}

	public function handle_user_registration()
	{
		add_action('init', array($this, 'add_new_user'), 20);
		add_action('init', array($this, 'verify_account'), 21);
	}

	public function create_shortcodes()
	{
		add_shortcode('user_register_form', array($this, 'registration_form'));
		add_shortcode('user_detail_page', array($this, 'user_detail_page'));
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

                <input type="hidden" name="rpm_nonce" value="<?php echo wp_create_nonce('rpm-nonce'); ?>"/>

                <button type="submit" class="btn btn-lg btn-success">Submit</button>

            </fieldset>
        </form>

        <?php
        return ob_get_clean();
    }

    public function add_new_user()
    {

        // check for username, and check verify WordPress nonce
		if (isset($_POST['rpm_user_name']) && wp_verify_nonce($_POST['rpm_nonce'], 'rpm-nonce')) {
            $user_data = [
                'user_login' => sanitize_user($_POST['rpm_user_name']),
                'user_email' => sanitize_email($_POST['rpm_user_email'])
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

            // get all error messages
            $errors = $this->handle_errors()->get_error_message();

            // if no errors then send verification mail
            if (empty($errors)) {

				$this->send_verification_email($user_data);

				// can be done nicier, but for now it's ok
				echo 'Ihre Registrierung war erfolgreich und wir haben Ihnen eine Bestätigung per E-Mail gesendet. Prüfen Sie Ihren Posteingang und vielleicht auch Ihren Spam-Ordner.';
				exit;
            }
        }
    }

	public function send_verification_email($user_data)
	{

		// Set content type to HTML
		add_filter('wp_mail_content_type', function ($content_type) {
			return 'text/html';
		});

		// generate key
		$verification_key = wp_generate_password(20, false);

		// save key in database
		update_option('pm_' . $verification_key, $user_data, false);

		// generate verification link
		$verification_link = add_query_arg(array('action' => 'verify_account', 'key' => $verification_key), home_url());

		// Erstellen Sie die E-Mail-Nachricht
		$message = sprintf(__('Hallo %s,', 'raidboxes_premium_member'), $user_data['user_login']);
		$message .= "\r\n\r\n";
		$message .= __('Danke für die Registrierung auf unserer Website.', 'raidboxes_premium_member');
		$message .= __('Bitte klicken Sie auf den folgenden Link, um Ihr Konto zu bestätigen:', 'raidboxes_premium_member');
		$message .= "\r\n\r\n<a href='" . $verification_link . "'>" . $verification_link . "</a>\r\n";

		// Senden Sie die E-Mail
		wp_mail($user_data['user_email'], __('Bestätigen Sie Ihr Konto', 'raidboxes_premium_member'), $message);

		// Reset content type to default
		remove_filter('wp_mail_content_type', 'set_html_content_type');

		return;
	}

	public function verify_account()
	{
		if (isset($_GET['action']) && $_GET['action'] == 'verify_account' && isset($_GET['key'])) {
			// sanitize the key
			$verification_key = sanitize_text_field($_GET['key']);
			// get the key from database
			$user_data = get_option('pm_' . $verification_key, false);

			// if key exists, create new user
			if ($user_data !== false) {

				$new_user_id = wp_insert_user([
					'user_login' => $user_data['user_login'],
					'user_email' => $user_data['user_email'],
					'user_registered' => date('Y-m-d H:i:s'),
					'role' => 'raidboxes_premium_member',	// Alle Benutzer erhalten zunächst die Standardrolle
				]);

				// check if new user was created
				if($new_user_id) {
					// if new user was created, send mail to admin
					wp_new_user_notification($new_user_id, null, 'both');

					// set cookie for new users login
					wp_setcookie($user_data['user_login'], $user_data['user_pass'], true);

					// log the new user in
					wp_set_current_user($new_user_id, $user_data['user_login']);
					do_action('wp_login', $user_data['user_login']);

					// redirect new user to user detail page
					wp_redirect(home_url() . '/user-detail-page/');
				}

				if (!is_wp_error($new_user_id)) {
					// Benutzer erfolgreich erstellt, entfernen Sie die Option
					delete_option('pm_' . $verification_key);
					// An dieser Stelle würden Sie den Benutzer wahrscheinlich zu einer "Erfolg"-Seite umleiten
					wp_redirect(home_url());
				}
			}
		}
	}

	public function register_messages() {

		// if there are errors, loop through them
		if($codes = $this->handle_errors()->get_error_codes()) {
			echo '<div class="form_errors">';
			foreach($codes as $code) {
				$message = $this->handle_errors()->get_error_message($code);
				echo '<div class="alert alert-danger" role="alert">' . __('Error: ') . $message . '</div>';
			}
			echo '</div>';
		}
	}

	/**
	 * @return mixed|WP_Error
	 */
	public function handle_errors()
    {
		static $wp_error;
		return $wp_error ?? ($wp_error = new WP_Error(null, null, null));
    }

	/**
	 * @return void
	 */
	public function add_user_role(): void
    {
        add_role('raidboxes_premium_member', 'Raidboxes Premium Member', [
            'read' => true,
            // Add additional capabilities here
        ]);
    }

	public function user_detail_page()
	{
		// Check if user is logged in
		if(is_user_logged_in()) {
			$current_user = wp_get_current_user();

			ob_start();

			// not the niciest HTML Markup, but it works for now ;-)
			?>

			<h2><?php _e('Your User Details', 'raidboxes_premium_member'); ?></h2>

			<table class="table">
				<tr>
					<td><label for="user_login"><?php _e('User Name / User Login:', 'raidboxes_premium_member'); ?></label></td>
					<td>
						<input type="text" id="user_login" class="form-control" readonly value="<?php echo esc_attr__($current_user->user_login); ?>" aria-describedby="helpBlock">
					</td>
				</tr>
				<tr>
					<td><label for="user_email"><?php _e('Email:', 'raidboxes_premium_member'); ?></label></td>
					<td>
						<input type="text" id="user_email" class="form-control" readonly value="<?php echo esc_attr__($current_user->user_email); ?>" aria-describedby="helpBlock">
					</td>
				</tr>
				<tr>
					<td><label for="user_pass"><?php _e('Password:', 'raidboxes_premium_member'); ?></label></td>
					<td>
						<input type="text" id="user_pass" class="form-control" readonly value="<?php echo esc_attr__($current_user->user_pass); ?>" aria-describedby="helpBlock">
					</td>
				</tr>
			</table>


			<?php
			$output = ob_get_clean();
		}
		else {
			$output = '<div class="alert alert-info" role="alert">';
			$output .= __('You are not logged in.', 'raidboxes_premium_member');
			$output .= '</div>';
		}

		return $output;
	}

    public function handle_password_reset()
    {
        // Handle password reset logic here
    }


}

new PremiumMember();
