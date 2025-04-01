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

		$this->create_custom_table();
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