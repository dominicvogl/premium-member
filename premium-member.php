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
class PremiumMember {

    public function __construct() {

        add_action( 'init', array($this, 'plugin_init') );

    }

    public function plugin_init() {
        add_shortcode('user_register_form', array($this, 'registration_form'));

        add_action( 'wp_enqueue_scripts', array( $this, 'plugin_stylesheets' ) );
        // other add_action() or add_filter() calls
    }

    public function install_plugin() {
        // handle install process
    }

    public function uninstall_plugin() {
        // handle uninstall process
    }

    public function plugin_stylesheets() {
        $bootstrap_css_url = plugins_url( 'node_modules/bootstrap/dist/css/bootstrap.min.css', __FILE__ );

        // Enqueue bootstrap style
        wp_enqueue_style( 'plugin-bootstrap-style', $bootstrap_css_url );
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
        if(!is_user_logged_in()) {

            // check for options in settings, if user are allowed to register
            if(get_option('users_can_register')) {
                $output = $this->registration_form_fields();
            }
            // if not, display a message
            else {
                $output = __('User registration is not possible at the moment', 'raidboxes_premium_member');
            }
        }

        return $output;
    }

    public function registration_form_fields() {
        ob_start();
        ?>

        <form>
            <div class="mb-3">
                <label for="rpm_user_name" class="form-label"><?php _e('Username', 'raidboxes_premium_member'); ?></label>
                <input type="text" class="form-control" id="rpm_user_name">
            </div>

            <div class="mb-3">
                <label for="exampleInputEmail1" class="form-label"><?php _e('Username', 'raidboxes_premium_member'); ?></label>
                <input type="email" class="form-control" id="rpm_user_email" aria-describedby="emailHelp">
                <div id="emailHelp" class="form-text"><?php _e("We'll never share your email with anyone else.", "raidboxes_premium_member"); ?></div>
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" class="form-control" id="exampleInputPassword1">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="exampleCheck1">
                <label class="form-check-label" for="exampleCheck1">Check me out</label>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <?php
        return ob_get_clean();
    }

    public function add_user_role(): void
    {
        add_role('raidboxes_premium_member', 'Raidboxes Premium Member', [
            'read' => true,
            // Add additional capabilities here
        ]);
    }

    public function render_login_form() {
        // Generate and display login form HTML here
    }

    public function handle_user_registration() {
        // Handle registration logic here
    }

    public function handle_user_login() {
        // Handle registration logic here
    }

    public function handle_password_reset() {
        // Handle password reset logic here
    }


}

new PremiumMember();