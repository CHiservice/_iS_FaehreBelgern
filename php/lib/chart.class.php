<?php
class iS_FaehreBelgern_Chart {
	protected $config = null;

	public function __construct() {
		$this->config = iS_FaehreBelgern_Config::get_instance();
		$this->create_tables();

		add_action('wp_enqueue_scripts', array($this, 'fe_enqueue_scripts'));
	}

	function fe_enqueue_scripts() {
		wp_enqueue_style('faehre_chart_css', STYLESHEETURL.'/'.$this->config->get('modulName').'/css/faehre-chart.min.css', array(), $this->config->get('version'));
		wp_enqueue_script('faehre_chart_js', STYLESHEETURL.'/'.$this->config->get('modulName').'/js/faehre-chart.min.js', array('jquery'), $this->config->get('version'), true);
		wp_localize_script(
			'faehre_chart_js',
			'isFahreChartVars',
			array(
				'chart_url' => esc_url(get_option('home')).'/wp-json/is_fb/chart',
			),
		);
	}

	public static function save_new_chart_data() {
		global $wpdb;
		
		$station_id = '83bbaedb-5d81-4bc6-9f66-3bd700c99c1f';
		$table_current = $wpdb->prefix.'faehrebelgern_water_current';
		$table_forecast = $wpdb->prefix.'faehrebelgern_water_forecast';

		$current_url = "https://www.pegelonline.wsv.de/webservices/rest-api/v2/stations/{$station_id}/W/measurements.json";
		$current_data = self::fetch_api_data($current_url);
		
		if ($current_data) {
			foreach ($current_data as $measurement) {
				if (isset($measurement['timestamp']) && isset($measurement['value'])) {
					$timestamp = sanitize_text_field($measurement['timestamp']);
					$value = floatval($measurement['value']);
					
					$wpdb->replace(
						$table_current,
						array(
							'timestamp' => $timestamp,
							'value' => $value,
							'created_at' => current_time('mysql')
						),
						array('%s', '%f', '%s')
					);
				}
			}
		}
		
		$forecast_url = "https://www.pegelonline.wsv.de/webservices/rest-api/v2/stations/{$station_id}/WV/measurements.json";
		$forecast_data = self::fetch_api_data($forecast_url);
		
		if ($forecast_data) {
			foreach ($forecast_data as $measurement) {
				if (isset($measurement['timestamp']) && isset($measurement['value'])) {
					$timestamp = sanitize_text_field($measurement['timestamp']);
					$value = floatval($measurement['value']);
					
					$wpdb->replace(
						$table_forecast,
						array(
							'timestamp' => $timestamp,
							'value' => $value,
							'created_at' => current_time('mysql')
						),
						array('%s', '%f', '%s')
					);
				}
			}
		}
		
		return array(
			'current_count' => count($current_data ?: array()),
			'forecast_count' => count($forecast_data ?: array())
		);
	}
	
	private static function fetch_api_data($url) {
		$response = wp_remote_get($url, array(
			'timeout' => 30,
			'headers' => array(
				'User-Agent' => 'FaehreBelgern/1.0'
			)
		));
		
		if (is_wp_error($response)) {
			error_log('Fehler beim Abrufen der Pegeldaten: '.$response->get_error_message());
			return false;
		}
		
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);
		
		if (json_last_error() !== JSON_ERROR_NONE) {
			error_log('JSON-Fehler beim Parsen der Pegeldaten: '.json_last_error_msg());
			return false;
		}
		
		return $data;
	}
	
	private function create_tables() {
		if (get_option('is_faehrebelgern_water_tables_created')) {
			return;
		}
		
		global $wpdb;
		
		$table_current = $wpdb->prefix.'faehrebelgern_water_current';
		$table_forecast = $wpdb->prefix.'faehrebelgern_water_forecast';
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql_current = "CREATE TABLE IF NOT EXISTS $table_current (
			id int(11) NOT NULL AUTO_INCREMENT,
			timestamp varchar(50) NOT NULL,
			value decimal(10,2) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY unique_timestamp (timestamp)
		) $charset_collate;";
		
		$sql_forecast = "CREATE TABLE IF NOT EXISTS $table_forecast (
			id int(11) NOT NULL AUTO_INCREMENT,
			timestamp varchar(50) NOT NULL,
			value decimal(10,2) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY unique_timestamp (timestamp)
		) $charset_collate;";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_current);
		dbDelta($sql_forecast);
		
		update_option('is_faehrebelgern_water_tables_created', true);
	}
}