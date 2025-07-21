<?php
class iS_FaehreBelgern_Settings {
	protected $config = null;
	
	public function __construct() {
		$this->config = iS_FaehreBelgern_Config::get_instance();
		
		add_action('admin_menu', array($this, 'settings_menu'));
	}

	function settings_menu(){
		add_options_page(
			esc_html__('Ferry Belgern Settings', $this->config->get('modulName')),
			'<img class="faehrebelgern_settings_icon" src="'.STYLESHEETURL.'/'.$this->config->get('modulName').'/css/img/icon.png" alt="">'.__('Ferry Belgern', $this->config->get('modulName')),
			'manage_options',
			'faehrebelgern_settings',
			array($this, 'settings_page')
		);
	}

	function settings_page() {
		$handles = array_merge(array('jquery'), iS_General_Enqueue_Lib::js('daterangepicker'));
		wp_enqueue_style('fb_settings_css', STYLESHEETURL.'/'.$this->config->get('modulName').'/css/settings.min.css', array(), $this->config->get('version'));
		wp_enqueue_script('fb_settings_js', STYLESHEETURL.'/'.$this->config->get('modulName').'/js/settings.min.js', $handles, $this->config->get('version'), true);
		wp_localize_script(
			'fb_settings_js',
			'isFahreSettingsVars',
			array(
				'save_url' => esc_url(get_option('home')).'/wp-json/is_fb_settings/save',
				'delete_planned_url' => esc_url(get_option('home')).'/wp-json/is_fb_settings/planned',
				'nonce' => wp_create_nonce('wp_rest'),
				'l18n' => array(
					'delete_confirm' => esc_html__('Do you really want to delete the planned change?', $this->config->get('modulName')),
					'delete_error' => esc_html__('Error while deleting:', $this->config->get('modulName')),
					'deleting' => esc_html__('Deleting...', $this->config->get('modulName')),
					'delete' => esc_html__('Delete', $this->config->get('modulName'))
				),
				'daterangepicker' => $this->get_daterangepicker_locale()
			),
		);

		$status_data = $this->config->get('status_data');

		$status = (int) get_option('is_faehrebelgern_settings_status');
		$comment = get_option('is_faehrebelgern_settings_comment');

		$planned_changes = self::get_planned_status();
		$first_available_new_status = 0;
		foreach ($status_data as $value => $status_info) {
			$is_current = ((int) $status == (int) $value);
			if ($first_available_new_status === 0 && !$is_current) {
				$first_available_new_status = $value;
			}
		}

		$status_labels = array_column($status_data, 'label');
		?>
		<div class="wrap" id="faehrebelgern_settings">
			<h1><?php echo esc_html__('Ferry Belgern Settings', $this->config->get('modulName')) ?></h1>

			<div class="current-status-info">
				<h2><?php echo esc_html__('Current Status', $this->config->get('modulName')) ?></h2>

				<div class="status-display-view">
					<table class="form-table status-display status-<?php echo $status; ?>" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php echo esc_html__('Status:', $this->config->get('modulName')) ?></th>
								<td><?php echo $status_labels[$status]; ?></td>
							</tr>
							<?php if (!empty($comment)): ?>
							<tr>
								<th scope="row"><?php echo esc_html__('Comment:', $this->config->get('modulName')) ?></th>
								<td class="current-comment"><em><?php echo nl2br(esc_html($comment)); ?></em></td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
					<p class="submit">
						<button type="button" class="button button-primary edit-current-status">
							<?php echo esc_html__('Edit', $this->config->get('modulName')) ?>
						</button>
					</p>
				</div>
				<div class="status-edit-form hidden">
					<form id="current-status-form">
						<table class="form-table status-<?php echo $status; ?>" role="presentation">
							<tbody>
								<tr>
									<th scope="row">
										<label for="current_status"><?php echo esc_html__('Status:', $this->config->get('modulName')) ?></label>
									</th>
									<td>
										<select id="current_status" name="status">
											<?php foreach ($status_data as $value => $status_info): ?>
												<option value="<?php echo $value; ?>" 
													<?php selected((int) $status, (int) $value); ?>
													data-default-comment='<?php echo esc_attr($status_info['default_comment']); ?>'>
													<?php echo $status_info['label']; ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr class="default-comment-preview-tr <?php echo ($status === 0) ? 'hidden' : ''; ?>">
									<th scope="row">
										<label for="current_comment"><?php echo esc_html__('Comment:', $this->config->get('modulName')) ?></label>
									</th>
									<td>
										<div class="default-comment-preview">
											<span class="default-text" id="default-comment-text"></span>
											<button type="button" class="button button-small use-default-comment">
												<?php echo esc_html__('Apply', $this->config->get('modulName')) ?>
											</button>
										</div>
									</td>
								</tr>
								<tr>
									<th scope="row"></th>
									<td>
										<textarea id="current_comment" name="comment" rows="3" cols="50"><?php echo esc_textarea($comment); ?></textarea>
									</td>
								</tr>
							</tbody>
						</table>
						<p class="submit">
							<button type="submit" class="button button-primary save-current-status">
								<?php echo esc_html__('Save', $this->config->get('modulName')) ?>
							</button>
							<button type="button" class="button cancel-edit-status">
								<?php echo esc_html__('Cancel', $this->config->get('modulName')) ?>
							</button>
						</p>
					</form>
				</div>
				<div class="planned-display-view">
					<?php if (!is_null($planned_changes)): ?>
					<h3><?php echo esc_html__('Planned Change', $this->config->get('modulName')) ?></h3>
					<table class="form-table status-display status-<?php echo $planned_changes['status']; ?>" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php echo esc_html__('Announce:', $this->config->get('modulName')) ?></th>
								<td>
									<?php if ($planned_changes['announce']): ?>
										<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
										<?php echo esc_html__('Yes', $this->config->get('modulName')) ?>
									<?php else: ?>
										<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
										<?php echo esc_html__('No', $this->config->get('modulName')) ?>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo esc_html__('Date:', $this->config->get('modulName')) ?></th>
								<td><strong><?php echo esc_html($planned_changes['date']); ?></strong></td>
							</tr>
							<tr>
								<th scope="row"><?php echo esc_html__('Status:', $this->config->get('modulName')) ?></th>
								<td><?php echo $status_labels[$planned_changes['status']]; ?></td>
							</tr>
							<?php if (!empty($planned_changes['comment'])): ?>
							<tr>
								<th scope="row"><?php echo esc_html__('Comment:', $this->config->get('modulName')) ?></th>
								<td class="planned-comment"><em><?php echo nl2br(esc_html($planned_changes['comment'])); ?></em></td>
							</tr>
							<?php endif; ?>

						</tbody>
					</table>
					<p class="submit">
						<button type="button" class="button button-primary edit-planned-status">
							<?php echo esc_html__('Edit', $this->config->get('modulName')) ?>
						</button>
						<button type="button" class="button button-secondary cancel-planned-status">
							<?php echo esc_html__('Delete', $this->config->get('modulName')) ?>
						</button>
					</p>
				<?php else: ?>
				<h3><?php echo esc_html__('Planned Change', $this->config->get('modulName')) ?></h3>
				<p><?php echo esc_html__('No planned changes available.', $this->config->get('modulName')) ?></p>
				<p class="submit">
					<button type="button" class="button button-primary add-planned-status">
						<?php echo esc_html__('Create New Planning', $this->config->get('modulName')) ?>
					</button>
				</p>
				<?php endif; ?>
				</div>
				<div class="planned-edit-form hidden">
					<form id="planned-status-form">
						<table class="form-table status-<?php echo !is_null($planned_changes) ? $planned_changes['status'] : ''; ?>" role="presentation">
							<tbody>
								<tr>
									<th scope="row"><?php echo esc_html__('Announce:', $this->config->get('modulName')) ?></th>
									<td>
										<label>
											<input type="checkbox" id="planned_announce" name="announce" value="1" <?php echo (!is_null($planned_changes) && isset($planned_changes['announce']) && $planned_changes['announce']) ? 'checked' : ''; ?> />
											<?php echo esc_html__('Announce planned status change', $this->config->get('modulName')) ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="planned_date"><?php echo esc_html__('Date:', $this->config->get('modulName')) ?></label>
									</th>
									<td>
										<input type="text" id="planned_date" name="date" value="<?php echo !is_null($planned_changes) ? esc_attr($planned_changes['date']) : ''; ?>" />
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="planned_status"><?php echo esc_html__('Status:', $this->config->get('modulName')) ?></label>
									</th>
									<td>
										<select id="planned_status" name="status">
											<?php
											foreach ($status_data as $value => $status_info):
												$is_current = ((int) $status == (int) $value);
											?>
												<option class='<?php echo $is_current ? 'hidden' : '' ?>' value='<?php echo $value; ?>' 
													<?php 
													if (!is_null($planned_changes)) {
														selected((int) $planned_changes['status'], (int) $value);
													} else {
														selected($value, $first_available_new_status);
													}
													?>
													data-default-comment='<?php echo esc_attr($status_info['planned_default_comment']); ?>'>
													<?php echo $status_info['label']; ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr class="default-comment-preview-tr <?php echo (!is_null($planned_changes) && $planned_changes['status'] === 0) ? 'hidden' : 'hidden'; ?>">
									<th scope="row">
										<label for="planned_comment"><?php echo esc_html__('Comment:', $this->config->get('modulName')) ?></label>
									</th>
									<td>
										<div class="default-comment-preview">
											<span class="default-text" id="planned-default-comment-text"></span>
											<button type="button" class="button button-small use-planned-default-comment">
												<?php echo esc_html__('Apply', $this->config->get('modulName')) ?>
											</button>
										</div>
									</td>
								</tr>
								<tr>
									<th scope="row"></th>
									<td>
										<textarea id="planned_comment" name="comment" rows="3" cols="50"><?php echo !is_null($planned_changes) ? esc_textarea($planned_changes['comment']) : ''; ?></textarea>
									</td>
								</tr>
							</tbody>
						</table>
						<p class="submit">
							<button type="submit" class="button button-primary save-planned-status">
								<?php echo esc_html__('Save', $this->config->get('modulName')) ?>
							</button>
							<button type="button" class="button cancel-edit-planned">
								<?php echo esc_html__('Cancel', $this->config->get('modulName')) ?>
							</button>
						</p>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	private function get_daterangepicker_locale() {
		return array(
			'locale' => array(
				'format' => 'DD.MM.YYYY',
				'separator' => ' - ',
				'applyLabel' => esc_html__('Apply', $this->config->get('modulName')),
				'cancelLabel' => esc_html__('Cancel', $this->config->get('modulName')),
				'fromLabel' => esc_html__('From', $this->config->get('modulName')),
				'toLabel' => esc_html__('To', $this->config->get('modulName')),
				'customRangeLabel' => esc_html__('Custom', $this->config->get('modulName')),
				'weekLabel' => esc_html__('W', $this->config->get('modulName')),
				'daysOfWeek' => array(
					esc_html__('Su', $this->config->get('modulName')),
					esc_html__('Mo', $this->config->get('modulName')),
					esc_html__('Tu', $this->config->get('modulName')),
					esc_html__('We', $this->config->get('modulName')),
					esc_html__('Th', $this->config->get('modulName')),
					esc_html__('Fr', $this->config->get('modulName')),
					esc_html__('Sa', $this->config->get('modulName'))
				),
				'monthNames' => array(
					esc_html__('January', $this->config->get('modulName')),
					esc_html__('February', $this->config->get('modulName')),
					esc_html__('March', $this->config->get('modulName')),
					esc_html__('April', $this->config->get('modulName')),
					esc_html__('May', $this->config->get('modulName')),
					esc_html__('June', $this->config->get('modulName')),
					esc_html__('July', $this->config->get('modulName')),
					esc_html__('August', $this->config->get('modulName')),
					esc_html__('September', $this->config->get('modulName')),
					esc_html__('October', $this->config->get('modulName')),
					esc_html__('November', $this->config->get('modulName')),
					esc_html__('December', $this->config->get('modulName'))
				),
				'firstDay' => 1
			)
		);
	}

	static function get_current_status() {
		return [
			'date'    => get_option('is_faehrebelgern_settings_date'),
			'status'  => (int) get_option('is_faehrebelgern_settings_status'),
			'comment' => get_option('is_faehrebelgern_settings_comment'),
		];
	}

	static function get_planned_status() {
		$planned_date = get_option('is_faehrebelgern_settings_date_planned');
		$planned_status = get_option('is_faehrebelgern_settings_status_planned');
		$planned_comment = get_option('is_faehrebelgern_settings_comment_planned');
		$planned_announce = get_option('is_faehrebelgern_settings_announce_planned');
		
		if (empty($planned_date)) {
			return null;
		}
		
		return [
			'date' => $planned_date,
			'status' => $planned_status,
			'comment' => $planned_comment,
			'announce' => (bool) $planned_announce
		];
	}

	public function apply_planned_status_with_date($new_status, $new_comment, $planned_date) {
		global $wpdb;
		$table_name = $wpdb->prefix.$this->config->get('tableName');
		
		$old_status = get_option('is_faehrebelgern_settings_status');
		
		$planned_datetime = $planned_date . ' 00:00:00';
		$end_datetime = date('Y-m-d H:i:s', strtotime($planned_date . ' -1 day') + (23 * 3600 + 59 * 60 + 59));
		
		$wpdb->update(
			$table_name,
			['end' => $end_datetime],
			['status' => $old_status, 'end' => null],
			['%s'],
			['%d', '%s']
		);

		$wpdb->insert(
			$table_name,
			[
				'status' => $new_status,
				'start' => $planned_datetime,
				'end' => null,
				'comment' => $new_comment
			],
			['%d', '%s', '%s', '%s']
		);
		
		update_option('is_faehrebelgern_settings_date', date('Y-m-d H:i:s'));
		update_option('is_faehrebelgern_settings_status', $new_status);
		update_option('is_faehrebelgern_settings_comment', $new_comment);
		
		$this->clear_planned_changes();
	}
 
	private function clear_planned_changes() {
		delete_option('is_faehrebelgern_settings_date_planned');
		delete_option('is_faehrebelgern_settings_status_planned');
		delete_option('is_faehrebelgern_settings_comment_planned');
		delete_option('is_faehrebelgern_settings_announce_planned');
	}

	public function delete_planned_changes() {
		$this->clear_planned_changes();

		return true;
	}

	public function save_status($status, $comment, $date = null, $announce = false) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->config->get('tableName');
		
		if ($status === null || $status === '') {
			return 'Status is required';
		}

		$status = (int) $status;
		if (!in_array($status, [0, 1, 2])) {
			return 'Invalid status';
		}

		if (!empty($date)) {
			$date = sanitize_text_field($date);
			
			$input_date = DateTime::createFromFormat('d.m.Y', $date);
			$today = new DateTime();
			$today->setTime(0, 0, 0);
			
			if (!$input_date || $input_date <= $today) {
				return 'Date must be in the future';
			}

			update_option('is_faehrebelgern_settings_date_planned', $date);
			update_option('is_faehrebelgern_settings_status_planned', $status);
			update_option('is_faehrebelgern_settings_comment_planned', sanitize_textarea_field($comment));
			update_option('is_faehrebelgern_settings_announce_planned', $announce ? 1 : 0);
		} else {
			$old_status = get_option('is_faehrebelgern_settings_status');
			
			$wpdb->update(
				$table_name,
				['end' => current_time('mysql')],
				['status' => $old_status, 'end' => null],
				['%s'],
				['%d', '%s']
			);

			$wpdb->insert(
				$table_name,
				[
					'status' => $status,
					'start' => current_time('mysql'),
					'end' => null,
					'comment' => sanitize_textarea_field($comment)
				],
				['%d', '%s', '%s', '%s']
			);

			update_option('is_faehrebelgern_settings_date', date('Y-m-d H:i:s'));
			update_option('is_faehrebelgern_settings_status', $status);
			update_option('is_faehrebelgern_settings_comment', sanitize_textarea_field($comment));
		}

		return true;
	}
}