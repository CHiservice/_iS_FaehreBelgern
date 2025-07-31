<?php

class iS_FaehreBelgern_PriceList extends ET_Builder_Module {
	protected $config        = null;
	protected $pt_helper     = null;
	protected $tax_helper    = null;
	protected $divi_settings = array();

	function init() {
		$this->config          = iS_FaehreBelgern_Config::get_instance();
		$this->name            = esc_html__('FB Price list', $this->config->get('modulName'));
		$this->plural          = esc_html__('FB Price lists', $this->config->get('modulName'));
		$this->slug            = 'et_is_fb_price_list';
		$this->vb_support      = 'on';
		$this->child_slug      = 'et_is_fb_price_list_item';
		$this->child_item_text = esc_html__('FB Price List Item', $this->config->get('modulName'));
		$this->main_css_element = '%%order_class%%';

		$this->divi_settings = iS_General_Divi::get_divi_style_settings();

		$this->settings_modal_toggles = array(
			'general'  => array(
				'toggles' => array(
					'styling'      => esc_html__('Styling', $this->config->get('modulName')),
					'main_content' => esc_html__('Content', 'et_builder'),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'layout'  => esc_html__('Layout', 'et_builder'),
					'overlay' => esc_html__('Overlay', 'et_builder'),
					'image'   => esc_html__('Image', 'et_builder'),
					'text'    => array(
						'title'             => esc_html__('Text', $this->config->get('modulName')),
						'priority'          => 45,
						'tabbed_subtoggles' => true,
						'bb_icons_support'  => true,
						'sub_toggles'       => array(
							'title' => array(
								'name' => esc_html__('Title', $this->config->get('modulName')),
								'icon' => 'text-default',
							),
							'subtitle' => array(
								'name' => esc_html__('Subtitle', $this->config->get('modulName')),
								'icon' => 'text-default',
							),
							'price' => array(
								'name' => esc_html__('Price', $this->config->get('modulName')),
								'icon' => 'text-default',
							),
							'price_free' => array(
								'name' => esc_html__('Price free', $this->config->get('modulName')),
								'icon' => 'text-default',
							),
						),
					),
				),
			),
		);

		$this->advanced_fields = [];

		$this->custom_css_fields = [];
	}

	function get_fields() {
		$fields = [
		];

		return $fields;
	}

	protected function _render_module_wrapper($output = '', $render_slug = '') {
		return $output;
	}

	function render($attrs, $content = null, $render_slug = '') {
		$multi_view     = et_pb_multi_view_options($this);
		$content        = do_shortcode($content);
		$field_settings = array_filter(explode('<!-- is-event-search-field-setting --!>', $content));

		// $id                 = 'list-search'.uniqid();
		// $layout             = is_null($this->props['layout'] && array_key_exists('layout', $attrs)) ? $attrs['layout'] : $this->props['layout'];
		// $accordion_status   = is_null($this->props['accordion_status'] && array_key_exists('accordion_status', $attrs)) ? $attrs['accordion_status'] : $this->props['accordion_status'];
		// $show_search_field  = is_null($this->props['show_search_field'] && array_key_exists('show_search_field', $attrs)) ? $attrs['show_search_field'] : $this->props['show_search_field'];
		// $show_search_button = is_null($this->props['show_search_button'] && array_key_exists('show_search_button', $attrs)) ? $attrs['show_search_button'] : $this->props['show_search_button'];
		// $show_reload_button = is_null($this->props['show_reload_button'] && array_key_exists('show_reload_button', $attrs)) ? $attrs['show_reload_button'] : $this->props['show_reload_button'];
		// $show_reset_button  = is_null($this->props['show_reset_button'] && array_key_exists('show_reset_button', $attrs)) ? $attrs['show_reset_button'] : $this->props['show_reset_button'];
		// $fields             = $this->render_childs($field_settings, $layout, $accordion_status, $show_search_button, $show_reload_button, $show_reset_button);

		// $search = '';
		// if($show_search_field == 'on') {
		// 	$classes = array();
		// 	if($show_search_button == 'on') {
		// 		$classes[] = 'with-button';
		// 	}
		// 	if($fields != '' && $layout == 'accordionjs') {
		// 		$classes[] = 'with-accordion';
		// 	}

		// 	$search = '<div class='search-field '.implode(' ', $classes).''>';
		// 		$search .= '<input id=''.uniqid().'' class='search' type='text' placeholder=''.esc_html__('Search', $this->config->get('modulName')).'' aria-label=''.esc_attr__('Search input field', $this->config->get('modulName')).'' aria-describedby='search-instructions' />';
		// 		$search .= '<div id='search-instructions' class='sr-only'>'.esc_html__('Enter search terms to filter the events. Press Enter to start the search.', $this->config->get('modulName')).'</div>';
		// 		if($show_search_button == 'on') {
		// 			$search .= '<button class='search-button'></button>';
		// 		}
		// 		if($fields != '' && $layout == 'accordionjs') {
		// 			$search .= '<button class='accordion-button'></button>';
		// 		}
		// 	$search .= '</div>';
		// } else {
		// 	$search = esc_html__('Search', $this->config->get('modulName'));
		// }

		// $data_attrs = '';
		// if($layout == 'accordionjs') {
		// 	$data_attrs = 'data-status=''.(!in_array($accordion_status, array('closed', 'open')) ? 'closed' : $accordion_status).''';
		// }

		// $this->add_scripts();
		// $css = $this->add_css($id);
		$output = '';
		// $output = sprintf(
		// 	'<div%1$s class='%2$s %6$s iservice-loader %3$s' data-id='%3$s'>
		// 		<div class='loader_wrapper'>
		// 			<div class='loader'></div>
		// 		</div>
		// 		<ul id=''.uniqid().'' class='search-wrapper %6$s' %7$s>
		// 			<li class='headline'>
		// 				<div class='label'>%4$s</div>
		// 				<div class='search-wrapper'>
		// 					%5$s
		// 				</div>
		// 			</li>
		// 		</ul>
		// 		%8$s
		// 	</div>',
		// 	$this->module_id(), // #1
		// 	$this->module_classname($render_slug), // #2
		// 	$id, // #3
		// 	$search, // #4
		// 	$fields, // #5
		// 	$layout, // #6
		// 	$data_attrs, // #7
		// 	$css // #8
		// );

		return $output;
	}

	public function render_childs($settings) {

	}

	static function add_scripts() {
		// $config  = iS_Events_Config::get_instance();
		// $lang    = str_replace('_', '-', strtolower(get_locale()));
		// $handles = array_merge(array('jquery'), iS_General_Enqueue_Lib::js('jquery.dataTables'));
		// $handles = array_merge($handles, iS_General_Enqueue_Lib::js('handlebars'));
		// $handles = array_merge($handles, iS_General_Enqueue_Lib::js('accordion'));
		// $handles = array_merge($handles, iS_General_Enqueue_Lib::js('pa_calendar', array('lang' => $lang)));

		// // search because Search and List modules are all loading this -> wp is only loading it once for all
		// wp_enqueue_script('is_event_search_js', STYLESHEETURL.'/'.$config->get('modulName').'/js/search.min.js', $handles, $config->get('version'), true);
		// wp_localize_script(
		// 	'is_event_search_js',
		// 	'isEventListVars',
		// 	array(
		// 		'url'        => esc_url(get_option('home')), // avoid wpml lang in url
		// 		'module_url' => STYLESHEETURL.'/'.$config->get('modulName'),
		// 		'lang'       => $lang,
		// 		'l18n'       => array(
		// 			'all_content'  => esc_html__('All content', $config->get('modulName')),
		// 			'info'         => esc_html__('Showing page _PAGE_ of _PAGES_', $config->get('modulName')),
		// 			'zero_records' => esc_html__('Nothing found.', $config->get('modulName')),
		// 			'first'        => esc_html__('First', $config->get('modulName')),
		// 			'previous'     => esc_html__('Previous', $config->get('modulName')),
		// 			'next'         => esc_html__('Next', $config->get('modulName')),
		// 			'last'         => esc_html__('Last', $config->get('modulName')),
		// 			'info_empty'   => esc_html__('No records available', $config->get('modulName')),
		// 			'cancel'       => esc_html__('Cancel', $config->get('modulName')),
		// 			'filter'       => esc_html__('Filter', $config->get('modulName')),
		// 			'day'          => esc_html__('Day', $config->get('modulName')),
		// 			'days'         => esc_html__('Days', $config->get('modulName')),
		// 			'date_label' => esc_html__('Selected date range', $config->get('modulName')),
		// 			'date_format_help' => esc_html__('Choose a date range in the calendar', $config->get('modulName')),
		// 			'date_selection' => esc_html__('Date selection', $config->get('modulName')),
		// 			'delete_selected_date' => esc_html__('Delete selected date', $config->get('modulName')),
		// 			'date_selection_reset' => esc_html__('Date selection was reset', $config->get('modulName')),
		// 			'previous_month' => esc_html__('Previous month', $config->get('modulName')),
		// 			'next_month' => esc_html__('Next month', $config->get('modulName')),
		// 			'calendar' => esc_html__('Calendar', $config->get('modulName')),
		// 		),
		// 	)
		// );
	}

	public function add_css($base_selector) {
		// $config = iS_Events_Config::get_instance();

		// wp_enqueue_style('is_event_search_css', STYLESHEETURL.'/'.$config->get('modulName').'/css/search.min.css', array(), $config->get('version'));

		// $css      = '<style type='text/css'>';
		// $css_file = '';
		// 	$accordion_search_header_bg = str_replace('#', '', $this->props['accordion_search_header_bg']);
		// 	$accordion_header_bg        = str_replace('#', '', $this->props['accordion_header_bg']);
		// 	$accordion_header_open_bg   = str_replace('#', '', $this->props['accordion_header_open_bg']);
		// 	$accordion_icon_color       = str_replace('#', '', $this->props['accordion_icon_color']);
		// 	$datepicker_month_bg        = str_replace('#', '', $this->props['datepicker_month_bg']);
		// 	$datepicker_weekdays        = str_replace('#', '', $this->props['datepicker_weekdays']);
		// 	$datepicker_day             = str_replace('#', '', $this->props['datepicker_day']);
		// 	$datepicker_current_day     = str_replace('#', '', $this->props['datepicker_current_day']);
		// 	$datepicker_today           = str_replace('#', '', $this->props['datepicker_today']);
		// 	$datepicker_selection_bg    = str_replace('#', '', $this->props['datepicker_selection_bg']);
		// 	$checkbox                   = str_replace('#', '', $this->props['checkbox']);

		// 	if(!is_null($accordion_search_header_bg) && $accordion_search_header_bg != '') { 
		// 		$css .= '.'.$base_selector.'.accordionjs .headline {
		// 			background-color: #'.$accordion_search_header_bg.' !important;
		// 		}
		// 		.'.$base_selector.'.accordionjs .search-field .accordion-button,
		// 		.'.$base_selector.'.accordionjs .search-field .search-button {
		// 			color: #'.$accordion_search_header_bg.' !important;
		// 		}';
		// 	}
		// 	if(!is_null($accordion_header_bg) && $accordion_header_bg != '') { 
		// 		$css .= '.'.$base_selector.'.accordionjs,
		// 		.'.$base_selector.'.accordionjs .search-field,
		// 		.'.$base_selector.'.accordionjs .accordionjs .acc_section .acc_head:not(.accordion-button) {
		// 			background-color: #'.$accordion_header_bg.' !important;
		// 		}';
		// 	}
		// 	if(!is_null($accordion_header_open_bg) && $accordion_header_open_bg != '') {
		// 		$css .= '.'.$base_selector.'.accordionjs .accordionjs .acc_section.acc_active > .acc_head:not(.accordion-button),
		// 		.'.$base_selector.'.accordionjs .accordionjs .acc_section.acc_active > .acc_content {
		// 			background-color: #'.$accordion_header_open_bg.' !important;
		// 		}';
		// 	}
		// 	if(!is_null($accordion_icon_color) && $accordion_icon_color != '') {
		// 		$css .= '.'.$base_selector.'.accordionjs .accordionjs .acc_section .acc_head:not(.accordion-button)::after {
		// 			color: #'.$accordion_icon_color.' !important;
		// 		}';
		// 	}
		// 	if(!is_null($datepicker_month_bg) && $datepicker_month_bg != '') {
		// 		$css .= '.'.$base_selector.' .daterangepicker-pa .date-picker-wrapper .PACalendar > header.PAheader {
		// 			background-color: #'.$datepicker_month_bg.' !important;
		// 		}';
		// 		$css_file .= '.range-plugin .header {
		// 			background-color: #'.$datepicker_month_bg.' !important;
		// 		}';
		// 	}
		// 	if(!is_null($datepicker_weekdays) && $datepicker_weekdays != '') {
		// 		$css .= '.'.$base_selector.' .daterangepicker-pa .date-picker-wrapper .PACalendar > section.PAweek span {
		// 			color: #'.$datepicker_weekdays.' !important;
		// 		}';
		// 		$css_file .= '.range-plugin .calendar > .daynames-row > .dayname {
		// 			color: #'.$datepicker_weekdays.' !important;
		// 		}';
		// 	}
		// 	if(!is_null($datepicker_day) && $datepicker_day != '') {
		// 		$css .= '.'.$base_selector.' .daterangepicker-pa .date-picker-wrapper .PACalendar > section.PAmonth span {
		// 			color: #'.$datepicker_day.' !important;
		// 		}';
		// 		$css_file .= '.range-plugin .calendar > .days-grid > .day {
		// 			color: #'.$datepicker_day.' !important;
		// 		}';
		// 	}
		// 	if(!is_null($datepicker_current_day) && $datepicker_current_day != '') {
		// 		$css .= '.'.$base_selector.' .daterangepicker-pa .date-picker-wrapper .PACalendar > section.PAmonth span.PAactive {
		// 			color: #'.$datepicker_current_day.' !important;
		// 		}';
		// 		$css_file .= '.range-plugin .calendar > .days-grid > .day.today {
		// 			color: #'.$datepicker_current_day.' !important;
		// 		}';
		// 	}
		// 	if(!is_null($datepicker_today) && $datepicker_today != '') {
		// 		$css .= '.'.$base_selector.' .daterangepicker-pa .date-picker-wrapper .PACalendar > section.PAmonth span.PAtoday {
		// 			border: solid #'.$datepicker_today.' 1.5px !important;
		// 			color: #'.$datepicker_today.' !important;
		// 		}
		// 		.'.$base_selector.' .daterangepicker-pa .date-picker-wrapper .PACalendar > section.PAmonth span.PAtoday:hover {
		// 			border: 0 !important;
		// 		}';
		// 	}
		// 	if(!is_null($datepicker_selection_bg) && $datepicker_selection_bg != '') {
		// 		$css .= '.'.$base_selector.' .daterangepicker-pa .date-picker-wrapper .PACalendar > section.PAmonth span.PAselected::before,
		// 		.'.$base_selector.' .daterangepicker-pa .date-picker-wrapper .PACalendar > section.PAmonth span.PAbetween {
		// 			background-color: #'.$datepicker_selection_bg.' !important;
		// 			color: #fff !important;
		// 		}
		// 		.'.$base_selector.' .daterangepicker-pa .date-picker-wrapper .PACalendar > section.PAmonth span.PAactive:hover,
		// 		.'.$base_selector.' .daterangepicker-pa .date-picker-wrapper .PACalendar > section.PAmonth span.PAselected {
		// 			color: #fff !important;
		// 		}
		// 		.'.$base_selector.' .daterangepicker-pa .date-picker-wrapper .PACalendar > section.PAmonth span.PAactive:hover::before {
		// 			background-color: #'.$datepicker_selection_bg.' !important;
		// 		}';
		// 		$css_file .= '.container.range-plugin .calendar > .days-grid > .day.start,
		// 		.container.range-plugin .calendar > .days-grid > .day.in-range,
		// 		.container.range-plugin .calendar > .days-grid > .day.end {
		// 			background-color: #'.$datepicker_selection_bg.' !important;
		// 		}';
		// 	}
		// 	if(!is_null($checkbox) && $checkbox != '') {
		// 		$css .= '.'.$base_selector.' .checkbox-wrapper input[type='checkbox']:checked {
		// 			accent-color: #'.$checkbox.' !important;
		// 		}
		// 		.'.$base_selector.' .checkbox-wrapper input[type='checkbox']:focus {
		// 			outline-color: #'.$checkbox.' !important;
		// 		}';
		// 	}

		// 	$terms = get_terms(array(
		// 		'taxonomy'   => 'iservice_event_calender',
		// 		'hide_empty' => false,
		// 	));
		// 	foreach($terms AS $term) {
		// 		$color = rwmb_meta('color', array('object_type' => 'term'), $term->term_id);
		// 		if(!is_null($color) && $color != '') {
		// 			$color = str_replace('#', '', $color);
		// 			if($color != '') {
		// 				$css .= '.'.$base_selector.' .calender.calender_colors .checkbox-wrapper input[value=''.$term->term_id.''][type='checkbox']:checked {
		// 					accent-color: #'.$color.' !important;
		// 				}';
		// 			}
		// 		}
		// 	}
		// $css .= '</style>';

		// $tmp        = STYLESHEETDIR.'/'.$config->get('modulName').'/tmp/';
		// $fileHandle = fopen($tmp.'is_event_'.$base_selector.'.css', 'w');
		// fwrite($fileHandle, $css_file);
		// fclose($fileHandle);
		
		// $files = glob($tmp.'*');
		// $threshold = strtotime('-1 hour');
		// foreach ($files as $file) {
		// 	if (is_file($file)) {
		// 		if ($threshold >= filemtime($file)) {
		// 			unlink($file);
		// 		}
		// 	}
		// }

		// return $css;
	}
}

new iS_FaehreBelgern_PriceList;