<?php
class iS_FaehreBelgern_Shortcode {
	protected $config = null;
	
	public function __construct() {
		$this->config = iS_FaehreBelgern_Config::get_instance();

		add_action("wp_enqueue_scripts", array($this, "fe_enqueue_scripts"));
		add_shortcode('faehrebelgern_status', array($this, 'render_status_button'));
	}

	public function render_status_button() {
		$data    = iS_FaehreBelgern_Settings::get_current_status();
		$status= (int) $data['status'];
		$comment = $data['comment'];

		$button_class = '';
		switch ($status) {
			case 1:
				$status       = 'Limited Service';
				$button_class = 'limited-service';
				break;
			case 2:
				$status       = 'Out of Service';
				$button_class = 'out-of-service';
				break;
			default:
				$status       = 'In Service';
				$button_class = 'in-service';
				break;
	}

		$output = '<div class="faehrebelgern-status-wrapper '.esc_attr($button_class).'">';
			$output .= '<button class="faehrebelgern-status-button">'.esc_html__($status, $this->config->get("modulName")).'</button>';
			$output .= '<div>'.esc_html($comment).'</div>';
		$output .= '</div>';

		return $output;
	}

	function fe_enqueue_scripts() {
		wp_enqueue_style("faehre_status_css", STYLESHEETURL."/".$this->config->get("modulName")."/css/faehre-status.min.css", array(), $this->config->get("version"));
	}
}