<?php

class PremiumMemberAdminSettings {

	public function __construct() {
		// Hook into the admin menu to create the admin page
		add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );

		// Register settings to save your checkboxes' values
		add_action( 'admin_init', array( $this, 'setup_sections' ) );

//		add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
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
		</div> <?php
	}

	/**
	 * Set up the sections for the plugin settings page.
	 */
	public function setup_sections() {
		add_settings_section(
			'checkbox_section', // id
			'Activate/Deactivate Plugins functionality', // title
			false, // callback
			'raidboxes_premium_member_plugin_settings' // page
		);

		$settingfields = [
			[
				"id" => "registration_active",
				"title" => __('Are users able to register new Account?', 'raidboxes_premium_member'),
				"description" => __( "Enable/Disables the registration", 'raidboxes_premium_member'),
				"callback" => "checkbox_callback",
			],
			[
				"id" => "login_active",
				"title" => __('Should users be able to login?', 'raidboxes_premium_member'),
				"description" => "Enable/Disables the possibility to login ",
				"callback" => "checkbox_callback",
			],
			[
				"id" => "link_expiration_time",
				"title" => __('User Registration Link expiration time', 'raidboxes_premium_member'),
				"description" => "Overwrite the time how long the login link is valid? (in hours). Default are 24 hours.",
				"callback" => "number_callback",
			],
			[
				"id" => "delete_plugin_data",
				"title" => __('Data handling', 'raidboxes_premium_member'),
				"description" => "When deactivating the plugin, should all plugin data be cleared?",
				"callback" => "checkbox_callback",
			],
		];

		foreach($settingfields as $settingField) {

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

			register_setting(
				'raidboxes_premium_member_plugin_settings',
				'raidboxes_premium_member_plugin_settings'
			);

		}
	}

	public function serialize_data($input) {
		return $input;
	}

	public function checkbox_callback( $args ) {
		$options = get_option('raidboxes_premium_member_plugin_settings');
		$value = $options[$args['id']] ?? false;

		echo '<div class="form-check">';
		echo '<input name="raidboxes_premium_member_plugin_settings[' . $args['id'] . ']" id="' . $args['id'] . '" type="checkbox" value="1" class="form-check-input" ' . checked( 1, $value, false ) . ' /> ';
		echo '<label for="' . $args['id'] . '" class="form-check-label"> ' . $args['desc'] . '</label> ';
		echo '</div>';
	}

	public function number_callback( $args ) {
		$options = get_option('raidboxes_premium_member_plugin_settings');
		$value = $options[$args['id']] ?? '';

		echo '<input type="number" name="raidboxes_premium_member_plugin_settings[' . $args['id'] . ']" id="' . $args['id'] . '" value="'. esc_attr($value) .'" /> ' . $args['desc'];
	}

}

new PremiumMemberAdminSettings();
