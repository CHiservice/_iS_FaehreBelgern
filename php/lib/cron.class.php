<?php
class iS_FaehreBelgern_Cron {
	protected $config = null;
	
	public function __construct() {
		$this->config = iS_FaehreBelgern_Config::get_instance();
	}

	public static function check_planned_changes() {
		$settings = new iS_FaehreBelgern_Settings();
		$planned_changes = $settings::get_planned_status();

		if (is_null($planned_changes)) {
			return;
		}

		$planned_timestamp = self::parse_date($planned_changes['date']);
		$current_timestamp = current_time('timestamp');

		if ($current_timestamp >= $planned_timestamp) {
			$settings->apply_planned_status_with_date(
				$planned_changes['status'],
				$planned_changes['comment'],
				$planned_changes['date']
			);
		}
	}

	public static function chart() {
		try {
			$result = iS_FaehreBelgern_Chart::save_new_chart_data();
			
			return rest_ensure_response(array(
				'success' => true,
				'message' => 'Chart data updated successfully',
				'data' => $result
			));
		} catch (Exception $e) {
			return new WP_Error('chart_update_error', $e->getMessage(), array('status' => 500));
		}
	}

	public static function parse_date($date_string) {
		$date_parts = explode('.', $date_string);
		if (count($date_parts) === 3) {
			$day = intval($date_parts[0]);
			$month = intval($date_parts[1]);
			$year = intval($date_parts[2]);
			return mktime(0, 0, 0, $month, $day, $year);
		}
		return false;
	}
}