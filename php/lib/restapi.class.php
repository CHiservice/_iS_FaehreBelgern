<?php

class iS_FaehreBelgern_RestApi {
	public function __construct() {
		add_action("rest_api_init", function () {
			register_rest_route("is_fb_settings", "save", array(
				"methods"             => "POST",
				"callback"            => array($this, "save"),
				"permission_callback" => array($this, "check_permissions"),
			));
			register_rest_route("is_fb_settings", "planned", array(
				"methods"             => "DELETE",
				"callback"            => array($this, "planned"),
				"permission_callback" => array($this, "check_permissions"),
			));
			register_rest_route("is_fb_cron", "planned", array(
				"methods"             => "GET",
				"callback"            => array(iS_FaehreBelgern_Cron::class, "check_planned_changes")
			));
			register_rest_route("is_fb_cron", "chart", array(
				"methods"             => "GET",
				"callback"            => array(iS_FaehreBelgern_Cron::class, "chart")
			));
			register_rest_route("is_fb", "button", array(
				"methods"             => "GET",
				"callback"            => array($this, "status_button"),
			));
			register_rest_route("is_fb", "chart", array(
				"methods"             => "GET",
				"callback"            => array($this, "chart"),
			));
		});
	}

	public function check_permissions() {
		return current_user_can('manage_options');
	}

	public function save($request) {
		$status = sanitize_text_field($request->get_param('status'));
		$comment = sanitize_textarea_field($request->get_param('comment'));
		$date = sanitize_text_field($request->get_param('date'));
		$announce = (bool) $request->get_param('announce');

		if ($status === null) {
			return new WP_Error('missing_status', 'Status is required', array('status' => 400));
		}

		$settings = new iS_FaehreBelgern_Settings();

		try {
			$result = $settings->save_status($status, $comment, $date, $announce);

			if ($result === true) {
				return rest_ensure_response(array(
					'success' => true,
					'message' => 'Settings saved successfully'
				));
			} else {
				return new WP_Error('save_failed', $result, array('status' => 500));
			}
		} catch (Exception $e) {
			return new WP_Error('save_error', $e->getMessage(), array('status' => 500));
		}
	}

	public function planned($request) {
		$settings = new iS_FaehreBelgern_Settings();

		try {
			$planned = $settings->get_planned_status();
			if(!is_null($planned) && date('Y-m-d', strtotime($planned['date'])) == date('Y-m-d')) {
				$settings->apply_planned_status_with_date($planned['status'], $planned['comment'], $planned['date']);
				
				return rest_ensure_response(array(
					'success' => true,
					'message' => 'Planned changes deleted successfully'
				));
			} else {
				return rest_ensure_response(array(
					'success' => true,
					'message' => 'No planned changes found'
				));
			}
		} catch (Exception $e) {
			return new WP_Error('cron_error', $e->getMessage(), array('status' => 500));
		}
	}

	public function status_button($request) {
		$button = iS_FaehreBelgern_Shortcode::render_header_status_button();

		return rest_ensure_response(array(
			'button' => $button,
		));
	}

	public function chart($request) {
		$button = iS_FaehreBelgern_Chart::chart_data();

		return rest_ensure_response(array(
			'data' => $button,
		));
	}
}