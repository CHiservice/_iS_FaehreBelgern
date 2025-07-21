<?php
class iS_FaehreBelgern_Cron {
	protected $config = null;
	
	public function __construct() {
		$this->config = iS_FaehreBelgern_Config::get_instance();
	}

	public function check_planned_changes() {
		$settings = new iS_FaehreBelgern_Settings();
		$planned_changes = $settings::get_planned_status();

		if (is_null($planned_changes)) {
			return;
		}

		$planned_timestamp = $this->parse_date($planned_changes['date']);
		$current_timestamp = current_time('timestamp');

		if ($current_timestamp >= $planned_timestamp) {
			$settings->apply_planned_status_with_date(
				$planned_changes['status'], 
				$planned_changes['comment'], 
				$planned_changes['date']
			);
		}
	}

	private function parse_date($date_string) {
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