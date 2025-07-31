<?php

class iS_FaehreBelgern_PriceListItem extends ET_Builder_Module {
	protected $taxonomies = array();
	protected $role_list  = array();

	function init() {
		$this->config          = iS_FaehreBelgern_Config::get_instance();
		$this->name            = esc_html__('FB Price list item', $this->config->get('modulName'));
		$this->plural          = esc_html__('FB Price list items', $this->config->get('modulName'));
		$this->slug            = 'et_is_fb_price_list_item';
		$this->vb_support      = 'on';
		$this->type            = 'child';
		$this->child_title_var = 'content';
		$this->main_css_element = '.et_is_fb_price_list .item %%order_class%%';


		$this->settings_modal_toggles = array(
			'general'  => array(
				'toggles' => [
					'main_content' => esc_html__('Content', 'et_builder'),
				],
			),
		);

		$this->custom_css_fields = [];

		$this->advanced_fields = [];
	}

	function get_fields() {
		$fields = array(
			'title' => array(
				'label'            => esc_html__('Title', $this->config->get('modulName')),
				'type'             => 'text',
				'custom_color'     => true,
				'depends_show_if'  => 'on',
				'toggle_slug'      => 'elements',
				'mobile_options'   => false,
			),
			'sub_title' => array(
				'label'            => esc_html__('Subtitle', $this->config->get('modulName')),
				'type'             => 'text',
				'custom_color'     => true,
				'depends_show_if'  => 'on',
				'toggle_slug'      => 'elements',
				'mobile_options'   => false,
			),
			'free' => array(
				'label'            => esc_html__('Free', $this->config->get('modulName')),
				'type'             => 'yes_no_button',
				'option_category'  => 'configuration',
				'options'          => array(
					'off' => esc_html__('No', 'et_builder'),
					'on'  => esc_html__('Yes', 'et_builder'),
				),
				'default_on_front' => 'off',
				'affects'          => array(
					'price',
				),
				'toggle_slug'      => 'elements',
			),

			'price' => array(
				'label'            => esc_html__('Price', $this->config->get('modulName')),
				'type'             => 'text',
				'custom_color'     => true,
				'toggle_slug'      => 'elements',
				'mobile_options'   => false,
				'depends_show_if'  => 'off',

			),
		);

		return $fields;
	}

	protected function _render_module_wrapper($output = '', $render_slug = '') {
		return $output;
	}

	public function render($attrs, $content, $render_slug) {
		// $settings = array(
		// 	'class'             => $this->module_classname($render_slug),
		// 	'type'              => $this->props['field'],
		// 	'show_label'        => $this->props['show_label'],
		// 	'show_custom_label' => $this->props['show_custom_label'],
		// 	'custom_label'      => $this->props['custom_label'],
		// 	'filter'            => null,
		// );
		// if($this->props['field'] == 'calender') {
		// 	$settings['calender_color'] = $this->props['calender_color'];
		// }

		// if(in_array($this->props['field'], array('easepick', 'datepicker_open'))) {
		// 	$set_limit_from = is_null($this->props['set_limit_from'] && array_key_exists('set_limit_from', $attrs)) ? $attrs['set_limit_from'] : $this->props['set_limit_from'];
		// 	$set_limit_end  = is_null($this->props['set_limit_end'] && array_key_exists('set_limit_end', $attrs)) ? $attrs['set_limit_end'] : $this->props['set_limit_end'];
		// 	if($set_limit_from == 'on') {
		// 		$range[0] = $this->props['limit_from'];
		// 		if(is_null($range[0]) || $range[0] == '') {
		// 			$range[0] = 'now';
		// 		}
		// 	}
		// 	if($set_limit_end == 'on') {
		// 		$range[1] = $this->props['limit_end'];
		// 		if(is_null($range[1]) || $range[1] == '') {
		// 			$range[1] = 'now';
		// 		}
		// 	}
		// 	$settings['pre_filter'] = $range;
		// }

		// if(array_key_exists($this->props['field'], $this->taxonomies)) {
		// 	$filter = is_null('filter_'.$this->props['field'] && array_key_exists('filter_'.$this->props['field'], $attrs)) ? $attrs['filter_'.$this->props['field']] : $this->props['filter_'.$this->props['field']];
		// 	if($this->props['field'] == 'role') {
		// 		$settings['pre_filter'] = array();
		// 		foreach ($this->role_list as $role) {
		// 			$role_filter = is_null($this->props['prefilter_'.$this->props['field'].'_'.$role->slug] && array_key_exists('prefilter_'.$this->props['field'].'_'.$role->slug, $attrs)) ? $attrs['prefilter_'.$this->props['field'].'_'.$role->slug] : $this->props['prefilter_'.$this->props['field'].'_'.$role->slug];
					
		// 			if($role_filter == 'on') {
		// 				$settings['pre_filter'][] = $role->slug;
		// 			}
		// 		}
		// 	} else {
		// 		$settings['pre_filter'] = explode(',', is_null($this->props['prefilter_'.$this->props['field']] && array_key_exists('prefilter_'.$this->props['field'], $attrs)) ? $attrs['prefilter_'.$this->props['field']] : $this->props['prefilter_'.$this->props['field']]);
		// 	}
		// 	if(is_array($settings['pre_filter']) && (count($settings['pre_filter']) == 0 || $settings['pre_filter'][0] == '')) {
		// 		$settings['pre_filter'] = null;
		// 	}
			
		// 	if($filter == 'defined') {
		// 		if($this->props['field'] == 'role') {
		// 			$settings['filter'] = array();
		// 			foreach ($this->role_list as $role) {
		// 				$role_filter = is_null($this->props['filter_define_'.$this->props['field'].'_'.$role->slug] && array_key_exists('filter_define_'.$this->props['field'].'_'.$role->slug, $attrs)) ? $attrs['filter_define_'.$this->props['field'].'_'.$role->slug] : $this->props['filter_define_'.$this->props['field'].'_'.$role->slug];
		// 				if($role_filter == 'on') {
		// 					$settings['filter'][] = $role->slug;
		// 				}
		// 			}
		// 		} else {
		// 			$settings['filter'] = explode(',', is_null($this->props['filter_define_'.$this->props['field']] && array_key_exists('filter_define_'.$this->props['field'], $attrs)) ? $attrs['filter_define_'.$this->props['field']] : $this->props['filter_define_'.$this->props['field']]);
		// 		}
		// 		// filter -> remove invalid preselects
		// 		if(is_array($settings['pre_filter'])) {
		// 			foreach($settings['preselect'] AS $key => $value) {
		// 				if(!in_array($value, $settings['filter'])) {
		// 					unset($settings['pre_filter'][$key]);
		// 				}
		// 			}
		// 		}
		// 	}

		// 	if(is_array($settings['filter']) && count($settings['filter']) == 0) {
		// 		$settings['filter'] = null;
		// 	}
		// }

		// $content = json_encode($settings).'<!-- is-event-search-field-setting --!>'; // kommentar zum exploden wichtig!

		$content = '';
		return $content;
	}
}

new iS_FaehreBelgern_PriceListItem();