<?php
class iS_FaehreBelgern_Settings {
	protected $config = null;
	
	public function __construct() {
		$this->config = iS_FaehreBelgern_Config::get_instance();
		
		add_action("admin_menu", array($this, "settings_menu"));
		add_action("admin_init", array($this, "settings_init"));
	}

	function settings_menu(){
		add_options_page(
			esc_html__("Fähre Belgern Settings", $this->config->get("modulName")),
			'<img class="faehrebelgern_settings_icon" src="'.STYLESHEETURL.'/'.$this->config->get("modulName").'/css/img/icon.png" alt="">'.__("Fähre Belgern", $this->config->get("modulName")),
			"manage_options",
			"faehrebelgern_settings",
			array($this, "settings_page")
		);
	}

	function settings_init() {
		register_setting("faehrebelgern_settings", "is_faehrebelgern_settings_status", [
			"sanitize_callback" => [$this, "new_log_status_change"]
		]);
		register_setting("faehrebelgern_settings", "is_faehrebelgern_settings_comment", [
			"sanitize_callback" => [$this, "comment_log_status_change"]
		]);
	}

	function settings_page() {
		// wp_enqueue_style("settings_css", STYLESHEETURL."/".$this->config->get("modulName")."/css/settings.min.css", array(), $this->config->get("version"));
		// wp_enqueue_script("settings_js", STYLESHEETURL."/".$this->config->get("modulName")."/js/settings.min.js", array("jquery"),$this->config->get("version"), true);

		$status = (int) get_option("is_faehrebelgern_settings_status");
		?>
		<div class="wrap" id="faehrebelgern_settings">
			<h1><?php echo esc_html__("Fähre Belgern Settings", $this->config->get("modulName")) ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields("faehrebelgern_settings"); ?>
				<?php do_settings_sections("faehrebelgern_settings"); ?>

				<table class="form-table" role="presentation">
					<tbody>
						<tr class="iservice-settings-select is_faehrebelgern_settings_status">
							<th scope="row"><?php echo esc_html__("Status", $this->config->get("modulName")) ?></th>
							<td>
								<select id="is_faehrebelgern_settings_status" name="is_faehrebelgern_settings_status">
									<option value="0" <?php echo $status == 0 ? "selected" : ""; ?>><?php echo esc_html__("In Service", $this->config->get("modulName")) ?></option>
									<option value="1" <?php echo $status == 1 ? "selected" : ""; ?>><?php echo esc_html__("Limited Service", $this->config->get("modulName")) ?></option>
									<option value="2" <?php echo $status == 2 ? "selected" : ""; ?>><?php echo esc_html__("Out of Service", $this->config->get("modulName")) ?></option>
								</select>
							</td>
						</tr>
						<tr class="iservice-settings-select is_faehrebelgern_settings_comment">
							<th scope="row"><?php echo esc_html__("Comment", $this->config->get("modulName")) ?></th>
							<td>
								<textarea id="is_faehrebelgern_settings_comment" name="is_faehrebelgern_settings_comment" rows="5" cols="50"><?php echo get_option("is_faehrebelgern_settings_comment"); ?></textarea>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	function new_log_status_change($new_value) {
		global $wpdb;
		$table_name = $wpdb->prefix.$this->config->get("tableName");
		
		$old_value = get_option("is_faehrebelgern_settings_status");
		if ($old_value != $new_value) {
			$wpdb->update(
				$table_name,
				["end" => current_time("mysql")],
				["status" => $old_value, "end" => null],
				["%s"],
				["%d", "%s"]
			);

			$wpdb->insert(
				$table_name,
				[
					"status" => $new_value,
					"start" => current_time("mysql"),
					"end" => null
				],
				["%d", "%s", "%s"]
			);
		}
	
		return $new_value;
	}

	function comment_log_status_change($new_value) {
		global $wpdb;
		$table_name = $wpdb->prefix.$this->config->get("tableName");
		
		$last_entry = $wpdb->get_row(
			"SELECT * FROM $table_name WHERE end IS NULL ORDER BY start DESC LIMIT 1"
		);

		if ($last_entry) {
			$wpdb->update(
				$table_name,
				["comment" => $new_value],
				["ID" => $last_entry->ID],
				["%s"],
				["%d"]
			);
		}

		return $new_value;
	}

	static function get_current_status() {
		return [
			"status"  => (int) get_option("is_faehrebelgern_settings_status"),
			"comment" => get_option("is_faehrebelgern_settings_comment"),
		];
	}
}