<?php
/**
 * Product Table by WBW - LoggerWtbp class.
 *
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

define('WTBP_LOG', true);

class LoggerWtbp {

	public static function getInstance() {
		static $instance;
		if ( ! $instance ) {
			$instance = new LoggerWtbp();
		}

		return $instance;
	}

	public static function _() {
		return self::getInstance();
	}

	/**
	 * log.
	 *
	 * @version 2.3.0
	 *
	 * @param $message
	 * @param $data
	 *
	 * @return void
	 */
	public function log( $message, $data = '' ) {
		if ( defined( 'WTBP_LOG' ) && WTBP_LOG === true ) {
			if ( ! is_string( $data ) && ! is_numeric( $data ) ) {
				$data = wp_json_encode( $data );
			}
			if ( ! function_exists( 'wc_get_logger' ) ) {
				include_once( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
			}
			wc_get_logger()->debug( "{$message} \n\n {$data} \n", array( '_legacy' => true ) );
		}
	}
}
