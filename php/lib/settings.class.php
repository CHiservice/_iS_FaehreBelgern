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
		
		register_setting("faehrebelgern_settings", "is_faehrebelgern_settings_date_planned");
		register_setting("faehrebelgern_settings", "is_faehrebelgern_settings_status_planned");
		register_setting("faehrebelgern_settings", "is_faehrebelgern_settings_comment_planned");
	}

	function settings_page() {
		$handles = array_merge(array("jquery"), iS_General_Enqueue_Lib::js("daterangepicker"));
		wp_enqueue_style("fb_settings_css", STYLESHEETURL."/".$this->config->get("modulName")."/css/settings.min.css", array(), $this->config->get("version"));
		wp_enqueue_script("fb_settings_js", STYLESHEETURL."/".$this->config->get("modulName")."/js/settings.min.js", $handles, $this->config->get("version"), true);

		$status = (int) get_option("is_faehrebelgern_settings_status");
		$comment = get_option("is_faehrebelgern_settings_comment");

		$planned_changes = $this->get_planned_changes();
		
		$status_labels = [
			0 => esc_html__("In Service", $this->config->get("modulName")),
			1 => esc_html__("Limited Service", $this->config->get("modulName")),
			2 => esc_html__("Out of Service", $this->config->get("modulName"))
		];
		?>
		<div class="wrap" id="faehrebelgern_settings">
			<h1><?php echo esc_html__("Fähre Belgern Settings", $this->config->get("modulName")) ?></h1>
			
			<div class="current-status-info">
				<h2><?php echo esc_html__("Aktueller Status", $this->config->get("modulName")) ?></h2>
				<table class="form-table status-display status-<?php echo $status; ?>" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php echo esc_html__("Status:", $this->config->get("modulName")) ?></th>
							<td><?php echo $status_labels[$status]; ?></td>
						</tr>
						<?php if (!empty($comment)): ?>
						<tr>
							<th scope="row"><?php echo esc_html__("Kommentar:", $this->config->get("modulName")) ?></th>
							<td class="current-comment"><em><?php echo nl2br(esc_html($comment)); ?></em></td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
				<?php if (!is_null($planned_changes)): ?>
				<table class="form-table status-display status-<?php echo $planned_changes['status']; ?>" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php echo esc_html__("Geplante Änderung:", $this->config->get("modulName")) ?></th>
							<td class="planned-info">
								<strong><?php echo esc_html($planned_changes['date']); ?>:</strong> 
								<?php echo $status_labels[$planned_changes['status']]; ?>
								<?php if (!empty($planned_changes['comment'])): ?>
									<br><em><?php echo nl2br(esc_html($planned_changes['comment'])); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					</tbody>
				</table>
				<?php endif; ?>
			</div>
			<h2><?php echo esc_html__("Status ändern", $this->config->get("modulName")) ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields("faehrebelgern_settings"); ?>
				<?php do_settings_sections("faehrebelgern_settings"); ?>
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php echo esc_html__("Date", $this->config->get("modulName")) ?></th>
							<td>
								<select id="is_faehrebelgern_settings_type">
									<option value="immediately" selected><?php echo esc_html__("Immediately", $this->config->get("modulName")) ?></option>
									<option value="date"><?php echo esc_html__("Select date", $this->config->get("modulName")) ?></option>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				
				<div id="immediately-settings">
					<h3><?php echo esc_html__("Sofortige Änderung", $this->config->get("modulName")) ?></h3>
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php echo esc_html__("Status", $this->config->get("modulName")) ?></th>
								<td>
									<select id="is_faehrebelgern_settings_status" name="is_faehrebelgern_settings_status">
										<option value="0" <?php selected($status, 0); ?> data-default-text=""><?php echo esc_html__("In Service", $this->config->get("modulName")) ?></option>
										<option value="1" <?php selected($status, 1); ?> data-default-text="Aufgrund des niedrigen Wasserstandes werden ab dem [date] nur Fahrzeuge bis 3.5t befördert."><?php echo esc_html__("Limited Service", $this->config->get("modulName")) ?></option>
										<option value="2" <?php selected($status, 2); ?> data-default-text="Aufgrund des niedrigen Wasserstandes ist die Fähre ab dem [date] leider außer Betrieb!"><?php echo esc_html__("Out of Service", $this->config->get("modulName")) ?></option>
									</select>
								</td>
							</tr>
							<tr class="default_text_tr hidden">
								<th><?php echo esc_html__("Vorschlag für den Infotext", $this->config->get("modulName")) ?></th>
								<td class="default_text_wrapper">
									<div class="default_text status-0"></div>
									<button><?php echo esc_html__("übernehmen", $this->config->get("modulName")) ?></button>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo esc_html__("Comment", $this->config->get("modulName")) ?></th>
								<td>
									<textarea class="status-<?php echo $status; ?>" id="is_faehrebelgern_settings_comment" name="is_faehrebelgern_settings_comment" rows="5" cols="50"><?php echo esc_textarea($comment); ?></textarea>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				
				<div id="planned-settings" class="hidden">
					<h3><?php echo esc_html__("Geplante Änderung", $this->config->get("modulName")) ?></h3>
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php echo esc_html__("Datum", $this->config->get("modulName")) ?></th>
								<td>
									<input type="text" id="is_faehrebelgern_settings_date_planned" name="is_faehrebelgern_settings_date_planned" value="<?php echo date('d.m.Y', strtotime('+1day')); ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo esc_html__("Status", $this->config->get("modulName")) ?></th>
								<td>
									<select id="is_faehrebelgern_settings_status_planned" name="is_faehrebelgern_settings_status_planned">
										<option value="0" selected data-default-text=""><?php echo esc_html__("In Service", $this->config->get("modulName")) ?></option>
										<option value="1" data-default-text="Aufgrund des niedrigen Wasserstandes werden ab dem [date] nur Fahrzeuge bis 3.5t befördert."><?php echo esc_html__("Limited Service", $this->config->get("modulName")) ?></option>
										<option value="2" data-default-text="Aufgrund des niedrigen Wasserstandes ist die Fähre ab dem [date] leider außer Betrieb!"><?php echo esc_html__("Out of Service", $this->config->get("modulName")) ?></option>
									</select>
								</td>
							</tr>
							<tr class="default_text_tr_planned hidden">
								<th><?php echo esc_html__("Vorschlag für den Infotext", $this->config->get("modulName")) ?></th>
								<td class="default_text_wrapper">
									<div class="default_text_planned status-0"></div>
									<button class="planned"><?php echo esc_html__("übernehmen", $this->config->get("modulName")) ?></button>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo esc_html__("Comment", $this->config->get("modulName")) ?></th>
								<td>
									<textarea class="status-0" id="is_faehrebelgern_settings_comment_planned" name="is_faehrebelgern_settings_comment_planned" rows="5" cols="50"></textarea>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
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

	public function apply_planned_status_with_date($new_status, $new_comment, $planned_date) {
		global $wpdb;
		$table_name = $wpdb->prefix.$this->config->get("tableName");
		
		$old_status = get_option("is_faehrebelgern_settings_status");
		
		$planned_datetime = $planned_date . ' 00:00:00';
		$end_datetime = date('Y-m-d H:i:s', strtotime($planned_date . ' -1 day') + (23 * 3600 + 59 * 60 + 59));
		
		$wpdb->update(
			$table_name,
			["end" => $end_datetime],
			["status" => $old_status, "end" => null],
			["%s"],
			["%d", "%s"]
		);

		$wpdb->insert(
			$table_name,
			[
				"status" => $new_status,
				"start" => $planned_datetime,
				"end" => null,
				"comment" => $new_comment
			],
			["%d", "%s", "%s", "%s"]
		);
		
		update_option("is_faehrebelgern_settings_status", $new_status);
		update_option("is_faehrebelgern_settings_comment", $new_comment);
		
		$this->clear_planned_changes();
	}

	public function get_planned_changes() {
		$planned_date = get_option('is_faehrebelgern_settings_date_planned');
		$planned_status = get_option('is_faehrebelgern_settings_status_planned');
		$planned_comment = get_option('is_faehrebelgern_settings_comment_planned');
		
		if (empty($planned_date)) {
			return null;
		}
		
		return [
			'date' => $planned_date,
			'status' => $planned_status,
			'comment' => $planned_comment
		];
	}

	private function clear_planned_changes() {
		delete_option('is_faehrebelgern_settings_date_planned');
		delete_option('is_faehrebelgern_settings_status_planned');
		delete_option('is_faehrebelgern_settings_comment_planned');
	}
}