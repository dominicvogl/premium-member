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
    }

    public function install_plugin() {
        // handle install process
    }

    public function uninstall_plugin() {
        // handle uninstall process
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
            if(get_option('user_can_register')) {
                $output = $this->registration_form_fields();
            }
            // if not, display a message
            else {
                $output = __('User registration is not possible at the moment', 'raidboxes_premium_member');
            }
        }

        return $output;
    }

    public function registration_form_fields(): string {
        return '';
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