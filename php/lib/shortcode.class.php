<?php
class iS_FaehreBelgern_Shortcode {
	protected $config = null;
	
	public function __construct() {
		$this->config = iS_FaehreBelgern_Config::get_instance();

		add_action('wp_enqueue_scripts', array($this, 'fe_enqueue_scripts'));
		add_shortcode('faehrebelgern_status', array($this, 'render_status'));
		add_shortcode('faehrebelgern_status_button', array($this, 'render_status_button'));
	}

	public function render_status() {
		$current = iS_FaehreBelgern_Settings::get_current_status();
		$planned = iS_FaehreBelgern_Settings::get_planned_status();
		$status = (int) $current['status'];
		$status_data = $this->config->get('status_data');
		$output = '';

		if(!is_null($planned) && (int) $planned['announce'] == 1 && $planned['status'] !== $current['status'] && !empty($planned['comment'])) {
			$output .= '<div id="faehrebelgern-status-wrapper" class="planned status-'.((int) $planned['status']).'">';
				$output .= '<p>'.esc_html($planned['comment']).'</p>';
			$output .= '</div>';
		}
		$output .= '<div id="faehrebelgern-status-wrapper" class="current status-'.((int) $current['status']).'">';
			if (!empty($current['comment'])) {
				$output .= '<p>'.esc_html($current['comment']).'</p>';
			}
			$output .= '<button class="faehre-status-button status-'.esc_attr($status).'">'.$status_data[$status]['label'].'</button>';
			if (!empty($current['date'])) {
				$formatted_date = $this->format_status_date($current['date']);
				$output .= '<div id="faehrebelgern-status-date">'.esc_html($formatted_date).'</div>';
			}
		$output .= '</div>';

		return $output;
	}

	public function render_status_button() {
		$current = iS_FaehreBelgern_Settings::get_current_status();
		$status = (int) $current['status'];
		$status_data = $this->config->get('status_data');
		
		if (empty($status_data[$status]['label'])) {
			return '';
		}

		return '<button class="faehre-status-button status-'.esc_attr($status).'">'.esc_html($status_data[$status]['label']).'</button>';
	}

	public static function render_header_status_button() {
		$instance = new self();
		return '<div class="faehre-status-button-wrapper">
			<a href="'.get_option('home').'/#is_fb_status">'.
				$instance->render_status_button().
			'</a>'.
		'</div>';
	}

	private function format_status_date($date_string) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $date_string);
		if (!$date) {
			return '';
		}

		$now = new DateTime();
		$diff = $now->diff($date);
		
		if ($diff->days == 0) {
			return sprintf(
				esc_html__('Status from today, %s', $this->config->get('modulName')),
				$date->format('H:i')
			);
		} elseif ($diff->days == 1) {
			return sprintf(
				esc_html__('Status from yesterday, %s', $this->config->get('modulName')),
				$date->format('H:i')
			);
		} else {
			return sprintf(
				esc_html__('Status from %s', $this->config->get('modulName')),
				$date->format('d.m.Y H:i')
			);
		}
	}

	function fe_enqueue_scripts() {
		wp_enqueue_style('faehre_status_css', STYLESHEETURL.'/'.$this->config->get('modulName').'/css/faehre-status.min.css', array(), $this->config->get('version'));
		wp_enqueue_script('faehre_status_js', STYLESHEETURL.'/'.$this->config->get('modulName').'/js/faehre-status.min.js', array('jquery'), $this->config->get('version'), true);
		wp_localize_script(
			'faehre_status_js',
			'isFahreStatusVars',
			array(
				'button_url' => esc_url(get_option('home')).'/wp-json/is_fb/button',
			),
		);
	}
}