<?php

class PremiumMemberAdminSettings {

	public function __construct() {
		// Hook into the admin menu to create the admin page
		add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );

		// Register settings to save your checkboxes' values
		add_action( 'admin_init', array( $this, 'setup_sections' ) );

		// I like to use the WordPress default styles in the backend, feels more integrated
		// add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
	}

	public function create_plugin_settings_page() {
		// Create an "options" page under the "Settings" menu
		add_options_page(
			__('Raidboxes Premium Member Plugin Settings', 'raidboxes_premium_member'), // page_title
			__('Raidboxes Premium Member', 'raidboxes_premium_member'), // menu_title
			'manage_options', // capability
			'raidboxes_premium_member_plugin_settings', // menu_slug
			array( $this, 'plugin_settings_page_content' ) // function
		);
	}

	public function plugin_settings_page_content() { ?>
		<div class="wrap">
			<h1><?php _e('Raidboxes PremiumMember Plugin Settings', 'raidboxes_premium_member'); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'raidboxes_premium_member_plugin_settings' );
				do_settings_sections( 'raidboxes_premium_member_plugin_settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Set up the sections for the plugin settings page.
	 */
	public function setup_sections() {

		// define section for the option fields
		add_settings_section(
			'checkbox_section', // id
			'Activate/Deactivate Plugins functionality', // title
			false, // callback
			'raidboxes_premium_member_plugin_settings' // page
		);

		// define needed fields
		$settingfields = [
			[
				"id" => "registration_active",
				"title" => __('Are users able to register new Account?', 'raidboxes_premium_member'),
				"description" => __( 'Enable/Disables the registration', 'raidboxes_premium_member'),
				"callback" => "checkbox_callback",
			],
			[
				"id" => "login_active",
				"title" => __('Should users be able to login?', 'raidboxes_premium_member'),
				"description" => __('Enable/Disables the possibility to login', 'raidboxes_premium_member'),
				"callback" => "checkbox_callback",
			],
			[
				"id" => "link_expiration_time",
				"title" => __('User Registration Link expiration time', 'raidboxes_premium_member'),
				"description" => __('Overwrite the time how long the login link is valid? (in hours). Default are 24 hours.', 'raidboxes_premium_member'),
				"callback" => "number_callback",
			],
			[
				"id" => "delete_plugin_data",
				"title" => __('Data handling', 'raidboxes_premium_member'),
				"description" => __('When deactivating the plugin, should all plugin data be cleared?', 'raidboxes_premium_member'),
				"callback" => "checkbox_callback",
			],
		];

		// loop through the settings fields and register them
		foreach($settingfields as $settingField) {

			// add field settings to section
			add_settings_field(
				$settingField['id'],
				$settingField['title'],
				[$this, $settingField['callback']],
				'raidboxes_premium_member_plugin_settings',
				'checkbox_section',
				[
					'id' => $settingField['id'],
					'desc' => $settingField['description']
				]
			);

			// register the new settings
			register_setting(
				'raidboxes_premium_member_plugin_settings',
				'raidboxes_premium_member_plugin_settings'
			);
		}
	}

	/**
	 * Callback for Settings Field, markup for checkbox
	 * @param $args
	 * @return void
	 */
	public function checkbox_callback( $args ) {
		$options = get_option('raidboxes_premium_member_plugin_settings');
		$value = $options[$args['id']] ?? false;

		echo '<div class="form-check">';
		echo '<input name="raidboxes_premium_member_plugin_settings[' . $args['id'] . ']" id="' . $args['id'] . '" type="checkbox" value="1" class="form-check-input" ' . checked( 1, $value, false ) . ' /> ';
		echo '<label for="' . $args['id'] . '" class="form-check-label"> ' . $args['desc'] . '</label> ';
		echo '</div>';
	}

	/**
	 * Callback for Settings Field, markup for Numbers input
	 * @param $args
	 * @return void
	 */
	public function number_callback( $args ) {
		$options = get_option('raidboxes_premium_member_plugin_settings');
		$value = $options[$args['id']] ?? '';

		echo '<input type="number" name="raidboxes_premium_member_plugin_settings[' . $args['id'] . ']" id="' . $args['id'] . '" value="'. esc_attr($value) .'" /> ' . $args['desc'];
	}

}

new PremiumMemberAdminSettings();
