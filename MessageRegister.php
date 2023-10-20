<?php
// MessageRegister.php

class MessageRegister {

	/**
	 * @return mixed|WP_Error
	 */
	public function handle_errors()
	{
		static $wp_error;
		return $wp_error ?? ($wp_error = new WP_Error(null, null, null));
	}

	public function register_messages($status = 'error')
	{

		// if there are errors, loop through them
		if ($codes = $this->handle_errors()->get_error_codes()) {
			echo '<div class="form_errors">';
			foreach ($codes as $code) {
				$message = $this->handle_errors()->get_error_message($code);
				if ($code === 'password_reset') {
					echo '<div class="alert alert-success" role="alert">' . $message . '</div>';
				} else {
					echo '<div class="alert alert-danger" role="alert">' . $message . '</div>';
				}
			}
			echo '</div>';
		}
	}
}
