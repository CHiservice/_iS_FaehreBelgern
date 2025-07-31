<?php
class iS_FaehreBelgern_Chart {
	protected $config = null;
	
	protected static $table_current = 'faehrebelgern_water_current';
	protected static $table_forecast = 'faehrebelgern_water_forecast';
	protected static $api_base_url = 'https://www.pegelonline.wsv.de/webservices/rest-api/v2/stations/';
	protected static $station_id = '83bbaedb-5d81-4bc6-9f66-3bd700c99c1f';

	public function __construct() {
		$this->config = iS_FaehreBelgern_Config::get_instance();
		$this->create_tables();
	}

	public static function chart_data() {
		global $wpdb;
		$config = iS_FaehreBelgern_Config::get_instance();
		

		
		$current_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT timestamp, value FROM ".$wpdb->prefix.self::$table_current." 
				WHERE timestamp >= %s 
				ORDER BY timestamp ASC",
				date('Y-m-d H:i:s', strtotime('-7 days'))
			),
			ARRAY_A
		);
		
		$forecast_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT timestamp, value FROM ".$wpdb->prefix.self::$table_forecast." 
				WHERE timestamp >= %s
				ORDER BY timestamp ASC",
				date('Y-m-d H:i:s', strtotime('-1 day'))
			),
			ARRAY_A
		);
		
		$datasets = array();
		$max_value = 0;
		
		if (!empty($current_data)) {
			$current_formatted = array();
			foreach ($current_data as $row) {
				$value = floatval($row['value']);
				$max_value = max($max_value, $value);
				$current_formatted[] = array(
					'x' => date('d.m.Y H:i', strtotime($row['timestamp'])),
					'y' => $value
				);
			}
			
			$datasets[] = array(
				'label' => esc_html__('Current Water Level', $config->get('modulName')),
				'data' => $current_formatted,
				'borderColor' => 'rgb(75, 192, 192)',
				'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
				'tension' => 0.8,
				'pointRadius' => 0,
				'pointHoverRadius' => 0,
				'cubicInterpolationMode' => 'monotone'
			);
		}
		
		if (!empty($forecast_data)) {
			$forecast_formatted = array();
			foreach ($forecast_data as $row) {
				$value = floatval($row['value']);
				$max_value = max($max_value, $value);
				$forecast_formatted[] = array(
					'x' => date('d.m.Y H:i', strtotime($row['timestamp'])),
					'y' => $value
				);
			}
			
			$datasets[] = array(
				'label' => esc_html__('Forecast Water Level', $config->get('modulName')),
				'data' => $forecast_formatted,
				'borderColor' => 'rgb(255, 99, 132)',
				'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
				'borderDash' => array(5, 5),
				'tension' => 0.8,
				'pointRadius' => 0,
				'pointHoverRadius' => 0,
				'cubicInterpolationMode' => 'monotone'
			);
		}
		
		$datasets[] = array(
			'label' => esc_html__('Min Level (45cm)', $config->get('modulName')),
			'data' => array(
				array('x' => !empty($current_data) ? date('Y-m-d H:i', strtotime($current_data[0]['timestamp'])) : date('Y-m-d H:i'), 'y' => 45),
				array('x' => !empty($current_data) ? date('Y-m-d H:i', strtotime(end($current_data)['timestamp'])) : date('Y-m-d H:i'), 'y' => 45)
			),
			'borderColor' => 'rgb(255, 165, 0)',
			'backgroundColor' => 'rgba(255, 165, 0, 0.1)',
			'borderWidth' => 2,
			'borderDash' => array(10, 5),
			'pointRadius' => 0,
			'tension' => 0
		);
		
		if ($max_value >= 200) {
			$datasets[] = array(
				'label' => esc_html__('Max Level (230cm)', $config->get('modulName')),
				'data' => array(
					array('x' => !empty($current_data) ? date('Y-m-d H:i', strtotime($current_data[0]['timestamp'])) : date('Y-m-d H:i'), 'y' => 230),
					array('x' => !empty($current_data) ? date('Y-m-d H:i', strtotime(end($current_data)['timestamp'])) : date('Y-m-d H:i'), 'y' => 230)
				),
				'borderColor' => 'rgb(255, 0, 0)',
				'backgroundColor' => 'rgba(255, 0, 0, 0.1)',
				'borderWidth' => 2,
				'borderDash' => array(10, 5),
				'pointRadius' => 0,
				'tension' => 0
			);
		}
		
		return array(
			'type' => 'line',
			'data' => array(
				'datasets' => $datasets
			),
			'options' => array(
				'responsive' => true,
				'scales' => array(
					'x' => array(
						'type' => 'category',
						'title' => array(
							'display' => true,
							'text' => esc_html__('Time', $config->get('modulName'))
						)
					),
					'y' => array(
						'title' => array(
							'display' => true,
							'text' => esc_html__('Water Level (cm)', $config->get('modulName')),
						)
					)
				),
				'plugins' => array(
					'title' => array(
						'display' => true,
						'text' => esc_html__('Water Level (Torgau)', $config->get('modulName'))
					)
				)
			)
		);
	}

	public static function save_new_chart_data() {
		global $wpdb;

		$current_url = self::$api_base_url.self::$station_id.'/W/measurements.json';
		$current_data = self::fetch_api_data($current_url);
		
		if ($current_data) {
			foreach ($current_data as $measurement) {
				if (isset($measurement['timestamp']) && isset($measurement['value'])) {
					$timestamp = date('Y-m-d H:i:s', strtotime($measurement['timestamp']));
					$value = floatval($measurement['value']);
					
					$wpdb->replace(
						$wpdb->prefix.self::$table_current,
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
		
		$forecast_url = self::$api_base_url.self::$station_id.'/WV/measurements.json';
		$forecast_data = self::fetch_api_data($forecast_url);
		
		if ($forecast_data) {
			foreach ($forecast_data as $measurement) {
				if (isset($measurement['timestamp']) && isset($measurement['value'])) {
					$timestamp = date('Y-m-d H:i:s', strtotime($measurement['timestamp']));
					$value = floatval($measurement['value']);
					
					$wpdb->replace(
						$wpdb->prefix.self::$table_forecast,
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
		$charset_collate = $wpdb->get_charset_collate();
				
		$sql_current = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix.self::$table_current." (
			id int(11) NOT NULL AUTO_INCREMENT,
			timestamp datetime NOT NULL,
			value decimal(10,2) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY unique_timestamp (timestamp)
		) $charset_collate;";
		
		$sql_forecast = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix.self::$table_forecast." (
			id int(11) NOT NULL AUTO_INCREMENT,
			timestamp datetime NOT NULL,
			value decimal(10,2) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY unique_timestamp (timestamp)
		) $charset_collate;";
		
		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta($sql_current);
		dbDelta($sql_forecast);
		
		update_option('is_faehrebelgern_water_tables_created', true);
	}
}