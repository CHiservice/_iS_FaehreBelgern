<?php
class iS_FaehreBelgern_Config extends iS_Module_Config {
	private static $instance;

	private function __clone() {}

	public static function get_instance() {
		if (!iS_FaehreBelgern_Config::$instance instanceof self) {
			iS_FaehreBelgern_Config::$instance = new self();
		}

		return iS_FaehreBelgern_Config::$instance;
	}

	public function __construct() {
		$this->set("version", parent::get_version("iS_FaehreBelgern"));
		$this->set("modulName", "iS_FaehreBelgern");
		$this->set("customPrefix", "iS_");
		$this->set("tableName", "faehrebelgern_log");

		$this->set_status_data();
		$this->create_custom_table();
	}

	private function set_status_data() {
		$this->set("status_data", [
			0 => [
				'label' => esc_html__("Ferry operates according to schedule", $this->get("modulName")),
				'default_comment' => esc_html__("", $this->get("modulName")),
				'planned_default_comment' => esc_html__("Good news! The ferry will be back in service from [date].", $this->get("modulName"))
			],
			1 => [
				'label' => esc_html__("Ferry operates with limited service according to schedule", $this->get("modulName")),
				'default_comment' => esc_html__("Due to low water levels, only vehicles up to 3.5t will be transported from [date].", $this->get("modulName")),
				'planned_default_comment' => esc_html__("Due to low water levels, only vehicles up to 3.5t will be transported from [date].", $this->get("modulName")),
			],
			2 => [
				'label' => esc_html__("Ferry is out of service", $this->get("modulName")),
				'default_comment' => esc_html__("Due to low water levels, the ferry is unfortunately out of service from [date]!", $this->get("modulName")),
				'planned_default_comment' => esc_html__("Due to low water levels, the ferry is unfortunately out of service from [date]!", $this->get("modulName")),
			]
		]);
	}

	function create_custom_table() {
		global $wpdb;
		$table_name = $wpdb->prefix.$this->get("tableName");

		// if (get_option("is_faehrebelgern_table_created") === "1") {
		// 	return;
		// }
	
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			`ID` BIGINT(20) NOT NULL AUTO_INCREMENT,
			`status` TINYINT(1) NOT NULL DEFAULT 0,
			`comment` TEXT NULL DEFAULT NULL,
			`start` datetime NOT NULL DEFAULT NOW(),
			`end` datetime NULL DEFAULT NULL,
			PRIMARY KEY (ID)
		) $charset_collate;";
	
		require_once ABSPATH . "wp-admin/includes/upgrade.php";
		dbDelta($sql);
	
		// update_option("is_faehrebelgern_table_created", "1");
	}
}