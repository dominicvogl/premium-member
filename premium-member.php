<?php

/*
Plugin Name: Premium Raidboxes Member
Plugin URI: https://raidboxes.de
Description: A Plugin to get Raidboxes Employee =).
Version: 0.1.0-alpha
Author: Dominic Vogl
Author URI: https://dominicvogl.de
License: GPL2
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// Include the MessageRegister class.
require_once plugin_dir_path( __FILE__ ) . 'MessageRegister.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/Admin.php';


register_deactivation_hook(__FILE__, 'deactivate_rpm');

function deactivate_rpm()
{
	// remove options
	$plugin = new PremiumMember();
	$plugin->plugin_deactivate();
}


/**
 * Premium Member Plugin Class
 */
class PremiumMember
{

	private $messageRegister;

	public $plugin_path;

	private $plugin_textdomain;

	public function __construct()
	{

		$this->plugin_path = plugin_dir_path( __FILE__);

		$this->plugin_textdomain = 'raidboxes_premium_member';

		$this->messageRegister = new MessageRegister();

		// add roles
		$this->add_roles();
		// handle the user registration process
		$this->handle_user_registration();
		// add plugin scripts and styles
		$this->add_plugin_scripts();
		// generate form shortcodes
		$this->create_shortcodes();
		// load textdomain for the plugin
		$this->load_plugin_textdomain();
	}

	public function plugin_deactivate() {
		// Write a message to the PHP error log
		error_log("The plugin_uninstall function was called.");

		$is_deletion_active = unserialize(get_option('delete_plugin_data'));

		error_log($is_deletion_active);

		if( $is_deletion_active === '1') {

			global $wpdb;

			error_log("Deletion is active");

			$users = get_users(array('role' => 'rpm_role'));

			foreach ($users as $user) {
				wp_delete_user($user->ID);
				error_log("Deleted user with ID: {$user->ID}");
			}

			// remove the role
			remove_role('rpm_role');

			// Get all transients that start with 'pm_'
			$transients = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_pm_%' OR option_name LIKE '_transient_timeout_pm_%';");
			foreach($transients as $transient) {
				// Strip away the WordPress prefix in order to use WordPress functions
				$transient = str_replace('_transient_timeout_', '', $transient);
				$transient = str_replace('_transient_', '', $transient);
				delete_transient($transient);
			}

			error_log("Deleted all options, roles, users with role 'rpm_role', and transients starting with 'pm_'");

			// remove the options from the admin page options
			// can be improved to load the field names from the Admin Class, but for now it's ok to prevent missing something
			$options_to_delete = ['delete_plugin_data', 'registration_active', 'login_active', 'link_expiration_time'];

			foreach($options_to_delete as $option) {
				delete_option($option);
			}




			error_log("Deleted all options and roles");
			flush_rewrite_rules();
		}
	}



	public function add_roles()
	{
		add_action('init', array($this, 'add_user_role'), 10);
	}


	public function handle_user_registration()
	{
		add_action('init', array($this, 'add_new_user'), 20);
		add_action('init', array($this, 'verify_account'), 21);
		add_action('init', array($this, 'handle_password_reset'), 22);
		add_action('init', array($this, 'handle_custom_login'));
		// Add authentication error handling
//		add_action('wp_authenticate', array($this, 'handle_authentication_errors'), 10, 2);
	}

	public function create_shortcodes()
	{
		add_shortcode('user_register_form', array($this, 'registration_form'));
		add_shortcode('user_detail_page', array($this, 'user_detail_page'));
		add_shortcode('user_password_reset', array($this, 'user_password_reset'));
		add_shortcode('user_login_form', array($this, 'user_login_form'));
	}

	public function add_plugin_scripts()
	{
		add_action('wp_enqueue_scripts', array($this, 'plugin_stylesheets'));
	}

	public function plugin_stylesheets()
	{
		// get bootstrap minimized css
		$bootstrap_css_url = plugins_url('node_modules/bootstrap/dist/css/bootstrap.min.css', __FILE__);

		// Enqueue bootstrap style
		wp_enqueue_style('plugin-bootstrap-style', $bootstrap_css_url);
	}

	/**
	 * @return void
	 */
	public function add_user_role(): void
	{
		add_role('rpm_role', __('Raidboxes VIP Member', $this->plugin_textdomain), [
			'read' => true,
		]);
	}


	/**
	 * Check if user registration is possible, if true display the form, otherwise show only a message
	 * @return string
	 */
	public function registration_form(): string
	{

		// default message when you are logged in
		$output = '<div class="alert alert-info" role="alert">'.__('You are still logged in', 'raidboxes_premium_member').'</div>';

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

		$this->messageRegister->register_messages();

		// check if registration is active (via Admin in the Backend)

		$registration_active_state = unserialize(get_option('registration_active'));

		if(!$registration_active_state)  {
			echo '<div class="alert alert-warning" role="alert">'.__('Registration is currently not possible. Come please later again.', 'raidboxes_premium_member').'</div>';
			return ob_get_clean();
		}
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
					<input type="email" class="form-control" id="rpm_user_email" name="rpm_user_email"
						   aria-describedby="emailHelp">
					<div id="emailHelp"
						 class="form-text"><?php _e("We'll never share your email with anyone else.", "raidboxes_premium_member"); ?></div>
				</div>

				<input type="hidden" name="rpm_nonce" value="<?php echo wp_create_nonce('rpm-nonce'); ?>"/>

				<button type="submit" class="btn btn-success"><?php _e('Register now', 'raidboxes_premium_member'); ?></button>

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
				$this->messageRegister->handle_errors()->add('username_unavailable', __('Username already taken', 'raidboxes_premium_member'));
			}

			// if username is invalid, give message
			if (!validate_username($user_data['user_login'])) {
				$this->messageRegister->handle_errors()->add('username_invalid', __('Invalid username', 'raidboxes_premium_member'));
			}

			// if username is empty, give message
			if ($user_data['user_login'] == '') {
				$this->messageRegister->handle_errors()->add('username_empty', __('Please enter a username', 'raidboxes_premium_member'));
			}

			// if email is invalid, give message
			if (!is_email($user_data['user_email'])) {
				$this->messageRegister->handle_errors()->add('email_invalid', __('Invalid email', 'raidboxes_premium_member'));
			}

			// if email is empty, give message
			if (email_exists($user_data['user_email'])) {
				$this->messageRegister->handle_errors()->add('email_used', __('Email already used', 'raidboxes_premium_member'));
			}

			// get all error messages
			$errors = $this->messageRegister->handle_errors()->get_error_message();

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

		// set default transient time for login expiration link
		$transient_time = (HOUR_IN_SECONDS * 24);

		$link_expiration_time = intval(unserialize(get_option('link_expiration_time')));

		// overwrite transient time if set in settings is defined
		if(!empty(get_option('link_expiration_time'))) {
			$transient_time = (HOUR_IN_SECONDS * $link_expiration_time);
		}

		// save key in transient cache, for 24 hours
		set_transient('pm_'.$verification_key, $user_data, $transient_time);

		// save key in database permanently
		// update_option('pm_' . $verification_key, $user_data, false);

		// generate verification link
		$verification_link = add_query_arg(array('action' => 'verify_account', 'key' => $verification_key), home_url());

		// Erstellen Sie die E-Mail-Nachricht
		$message = sprintf(__('Hello %s,', 'raidboxes_premium_member'), $user_data['user_login']);
		$message .= "\r\n\r\n";
		$message .= __('Thank you for your website Registration.', 'raidboxes_premium_member');
		$message .= __(' Please click on the following link to confirm your account:', 'raidboxes_premium_member');
		$message .= "\r\n\r\n<a href='" . $verification_link . "'>" . $verification_link . "</a>\r\n";

		// Senden Sie die E-Mail
		wp_mail($user_data['user_email'], __('Confirm your account', 'raidboxes_premium_member'), $message);

		// Reset content type to default
		remove_filter('wp_mail_content_type', 'set_html_content_type');

		return true;
	}

	public function verify_account()
	{
		if (isset($_GET['action']) && $_GET['action'] == 'verify_account' && isset($_GET['key'])) {
			// sanitize the key
			$verification_key = sanitize_text_field($_GET['key']);
			// get the key from database
//			$user_data = get_option('pm_' . $verification_key, false);
			$user_data = get_transient('pm_' . $verification_key);

			// if key exists, create new user
			if ($user_data !== false) {

				$new_user_id = wp_insert_user([
					'user_login' => $user_data['user_login'],
					'user_email' => $user_data['user_email'],
					'user_registered' => date('Y-m-d H:i:s'),
					'role' => 'rpm_role',    // Alle Benutzer erhalten zunächst die Standardrolle
				]);

				// check if new user was created
				if (!is_wp_error($new_user_id)) {
					// if new user was created, send mail to admin
					wp_new_user_notification($new_user_id, null, 'both');

					// set cookie for new users login
					wp_setcookie($user_data['user_login'], $user_data['user_pass'], true);

					// log the new user in
					wp_set_current_user($new_user_id, $user_data['user_login']);
					do_action('wp_login', $user_data['user_login']);

					// remove key after registration
					delete_transient('pm_' . $verification_key);

					// redirect new user to user detail page
					wp_redirect(home_url() . '/user-detail-page/');
				}
			}
		}
	}


	public function user_login_form() {
		// Start output buffering
		ob_start();

		// get error messages
		$this->messageRegister->register_messages();

		$login_active_state = unserialize(get_option('login_active'));

		// check if login is active
		if(!$login_active_state) {
			echo '<div class="alert alert-danger" role="alert">'.__('Login is currently not possible. Please come later again.', 'raidboxes_premium_member').'</div>';
		}

		// HTML Form here
		?>
		<form method="post" action="">
			<input type="hidden" name="premium_custom_login" value="1">
			<div class="form-group mb-3">
				<label for="user_login">Username</label>
				<input type="text" name="user_login" id="user_login" class="form-control" value="">
			</div>
			<div class="form-group mb-3">
				<label for="user_password">Password</label>
				<input type="password" name="user_password" id="user_password" class="form-control" value="">
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary" <?php echo ($login_active_state) ? '' : 'disabled'; ?>><?php _e('Login', 'raidboxes_premium_member'); ?></button>
			</div>
		</form>
		<?php

		// Get the buffered content and end buffering
		$output = ob_get_clean();

		return $output;
	}

	public function handle_authentication_errors($username, $password) {
		// Initialize messageRegister object

		$login_data = array(
			'user_login' => sanitize_user($username),
			'user_password' => sanitize_text_field($password),
		);

		if(empty($login_data['user_login']) || empty($login_data['user_password'])) {
			$this->messageRegister->handle_errors()->add('empty_fields', __('Username and password are required.', 'raidboxes_premium_member'));
			return;
		}

		$user = get_user_by('login', $login_data['user_login']);
		if (!$user) {
			return;
		}

		if (!wp_check_password($login_data['user_password'], $user->data->user_pass, $user->ID)) {
			$this->messageRegister->handle_errors()->add('wrong_password', __('Incorrect Password', 'raidboxes_premium_member'));
			return;
		}
	}

	public function handle_custom_login() {

		// check if we are in the login form
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['premium_custom_login'])) {

			// check hot to sanitize the fields
			$user_login = sanitize_user($_POST['user_login']);

			// is is mail, for login, sanitize it as email
			if(is_email($_POST['user_login'])) {
				$user_login = sanitize_email($_POST['user_login']);
			}

			$user = wp_signon(array(
				'user_login'    => $user_login,
				'user_password' => sanitize_text_field($_POST['user_password']),
				'remember'      => true
			));

			// check if there is any error with the login
			if(is_wp_error($user)) {
				$this->messageRegister->handle_errors()->add('login_failed', $user->get_error_message());
			} else {
				// Redirect user to a specified URL or home page
				wp_redirect(empty($redirect) ? home_url() : $redirect);
				exit;
			}
		}
	}


	public function user_detail_page()
	{
		// Check if user is logged in
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();

			ob_start();

			// not the niciest HTML Markup, but it works for now ;-)
			?>

			<h2><?php _e('Your User Details', 'raidboxes_premium_member'); ?></h2>

			<table class="table">
				<tr>
					<td><label
							for="user_login"><?php _e('User Name / User Login:', 'raidboxes_premium_member'); ?></label>
					</td>
					<td>
						<input type="text" id="user_login" class="form-control" readonly
							   value="<?php echo esc_attr__($current_user->user_login); ?>"
							   aria-describedby="helpBlock">
					</td>
				</tr>
				<tr>
					<td><label for="user_email"><?php _e('Email:', 'raidboxes_premium_member'); ?></label></td>
					<td>
						<input type="text" id="user_email" class="form-control" readonly
							   value="<?php echo esc_attr__($current_user->user_email); ?>"
							   aria-describedby="helpBlock">
					</td>
				</tr>
				<tr>
					<td><label for="user_pass"><?php _e('Password:', 'raidboxes_premium_member'); ?></label></td>
					<td>
						<input type="text" id="user_pass" class="form-control" readonly
							   value="<?php echo esc_attr__($current_user->user_pass); ?>" aria-describedby="helpBlock">
						<p class="alert alert-warning">Here you can think about whether you should really output the
							password as plain text or not. (Keyword: Security)</p>
					</td>
				</tr>
			</table>


			<?php
			$output = ob_get_clean();
		} else {
			$output = '<div class="alert alert-info" role="alert">';
			$output .= __('You are not logged in.', 'raidboxes_premium_member');
			$output .= '</div>';
		}

		return $output;
	}

	public function user_password_reset()
	{
		ob_start();

		$this->messageRegister->register_messages();

		?>
		<form id="password-reset-form" method="POST">
			<div class="form-group mb-4">
				<label for="user_email"><?php _e('Enter your email address: ', 'raidboxes_premium_member'); ?></label>
				<input type="email" class="form-control" name="user_email" id="user_email" required/>
			</div>
			<button class="btn btn-danger" type="submit"
					name="submit_password_reset"><?php _e('Reset password', 'raidboxes_premium_member'); ?></button>
		</form>
		<?php
		$output = ob_get_clean();

		return $output;
	}

	public function handle_password_reset()
	{
		// Handle password reset logic here
		if (isset($_POST['submit_password_reset'])) {
			$user_email = sanitize_email($_POST['user_email']);
			$user = get_user_by('email', $user_email);

			if (!$user) {
				$this->messageRegister->handle_errors()->add('email_not_found', __('No user with that email address exists.', 'raidboxes_premium_member'));
			}

			$errors = $this->messageRegister->handle_errors()->get_error_message();

			if (empty($errors)) {
				$this->begin_password_reset($user);
				$this->messageRegister->handle_errors()->add('password_reset', __('Your password has been reset. Please check your mails', 'raidboxes_premium_member'));
			}
		}
	}

	public function begin_password_reset($user)
	{
		// Get the key
		$key = get_password_reset_key($user);

		// Create a new message
		$message = __('Someone has requested a password reset for the following account on ', 'raidboxes_premium_member') . get_bloginfo('name') . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'raidboxes_premium_member') . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:', 'raidboxes_premium_member') . "\r\n\r\n";
		$message .= '<a href="' . esc_url(network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login')) . '">' . __('Click here to reset Passwort', 'raidboxes_premium_member') . '</a>';

		// Sending our email
		// Make sure to set content type to HTML
		add_filter('wp_mail_content_type', function ($content_type) {
			return 'text/html';
		});

		// send the mail
		wp_mail($user->user_email, __('Raidbox Password Reset'), $message);

		// Reset content type
		remove_filter('wp_mail_content_type', 'set_html_content_type');
	}

	/**
	 * Load textdomain of the plugin for makint it translateable
	 */

	/**
	 * Load textdomain of the plugin for makint it translateable
	 */

	public function load_plugin_textdomain()
	{
		// set domain value
		$domain = $this->plugin_textdomain;

		$locale = apply_filters('plugin_locale', $this->get_locale(), $domain);
		$mofile = $domain . '-' . $locale . '.mo';

		// load from the languages directory first
		load_textdomain($domain, WP_LANG_DIR . '/plugins/' . $mofile);

		// redirect missing translations
		$mofile = str_replace('en_EN', 'en_EN', $mofile);

		// load from plugin lang folder
		load_textdomain($domain, $this->get_path('language/' . $mofile));
	}

	public function get_locale(): string
	{
		return is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
	}

	public function get_path($path = ''): string
	{
		return $this->plugin_path . $path;
	}

}

new PremiumMember();
