<?php
class iS_FaehreBelgern extends iS_Module {
	protected $config = null;

	public function _init() {
		$this->config = iS_FaehreBelgern_Config::get_instance();

		new iS_FaehreBelgern_Divi();
		new iS_FaehreBelgern_Settings();
		new iS_FaehreBelgern_Shortcode();
		new iS_FaehreBelgern_RestApi();
		new iS_FaehreBelgern_Cron();
		new iS_FaehreBelgern_Chart();
	}
}