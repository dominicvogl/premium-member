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

	public function setup_sections() {
		add_settings_section(
			'checkbox_section', // id
			'Activate/Deactivate Plugins functionality', // title
			false, // callback
			'raidboxes_premium_member_plugin_settings' // page
		);

		add_settings_field(
			'checkbox_1', // id
			'Checkbox One', // title
			array( $this, 'checkbox_callback' ),
			'raidboxes_premium_member_plugin_settings',
			'checkbox_section',
			array( 'id' => 'checkbox_1', 'desc' => 'This is a description for checkbox 1.' )
		);

		add_settings_field(
			'checkbox_2', // id
			'Checkbox Two', // title
			array( $this, 'checkbox_callback' ),
			'raidboxes_premium_member_plugin_settings',
			'checkbox_section',
			array( 'id' => 'checkbox_2', 'desc' => 'This is a description for checkbox 2.' )
		);

		add_settings_field(
			'checkbox_3', // id
			'Checkbox Three', // title
			array( $this, 'checkbox_callback' ),
			'raidboxes_premium_member_plugin_settings',
			'checkbox_section',
			array( 'id' => 'checkbox_3', 'desc' => 'This is a description for checkbox 3.' )
		);

		register_setting( 'raidboxes_premium_member_plugin_settings', 'checkbox_1' );
		register_setting( 'raidboxes_premium_member_plugin_settings', 'checkbox_2' );
		register_setting( 'raidboxes_premium_member_plugin_settings', 'checkbox_3' );
	}

	public function checkbox_callback( $args ) {
		// Get the value of the setting we've registered with register_setting()
		$value = get_option( $args['id'] );
		// Creating the checkbox field
		echo '<div class="form-check">';
		echo '<input name="' . $args['id'] . '" id="' . $args['id'] . '" type="checkbox" value="1" class="form-check-input" ' . checked( 1, $value, false ) . ' /> ';
		echo '<label for="' . $args['id'] . '" class="form-check-label"> ' . $args['desc'] . '</label> ';
		echo '</div>';
	}

}

new PremiumMemberAdminSettings();
