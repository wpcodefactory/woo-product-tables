<?php
/**
 * Product Table by WBW - Db class.
 *
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shell - class to work with $wpdb global object
 */
class DbWtbp {

	/**
	 * Object cache group used to cache results of get().
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	private static $_cacheGroup = 'woo-product-tables';

	/**
	 * Get (or seed) the cache incrementor used to invalidate all cached
	 * get() results at once whenever query() performs a write.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 *
	 * @return string current incrementor value
	 */
	private static function _getCacheIncrementor() {
		$incrementor = wp_cache_get('wtbp_db_cache_incrementor', self::$_cacheGroup, false, $found);
		if ( ! $found ) {
			$incrementor = microtime();
			wp_cache_set( 'wtbp_db_cache_incrementor', $incrementor, self::$_cacheGroup );
		}
		return $incrementor;
	}

	/**
	 * Invalidate all cached get() results.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	public static function flushCache() {
		wp_cache_delete('wtbp_db_cache_incrementor', self::$_cacheGroup);
	}

	/**
	 * Execute query and return results
	 *
	 * @param string $query query to be executed
	 * @param string $get what must be returned - one value (one), one row (row), one col (col) or all results (all - by default)
	 * @param const $outputType type of returned data
	 * @return mixed data from DB
	 *
	 * @version 2.3.0
	 */
	public static $query = '';
	public static function get( $query, $get = 'all', $outputType = ARRAY_A ) {
		global $wpdb;
		$get = strtolower($get);
		$res = null;
		$query = self::prepareQuery($query);
		self::$query = $query;
		$wpdb->wtbp_prepared_query = $query;
		$cacheKey = 'wtbp_' . md5(self::_getCacheIncrementor() . $query . $get . $outputType);
		$cached = wp_cache_get($cacheKey, self::$_cacheGroup, false, $found);
		if ($found) {
			return $cached;
		}
		switch ($get) {
			case 'one':
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom plugin table, no WP core API available; results are cached above via wp_cache.
				$res = $wpdb->get_var($wpdb->wtbp_prepared_query);
				break;
			case 'row':
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom plugin table, no WP core API available; results are cached above via wp_cache.
				$res = $wpdb->get_row($wpdb->wtbp_prepared_query, $outputType);
				break;
			case 'col':
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom plugin table, no WP core API available; results are cached above via wp_cache.
				$res = $wpdb->get_col($wpdb->wtbp_prepared_query);
				break;
			case 'all':
			default:
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom plugin table, no WP core API available; results are cached above via wp_cache.
				$res = $wpdb->get_results($wpdb->wtbp_prepared_query, $outputType);
				break;
		}
		wp_cache_set($cacheKey, $res, self::$_cacheGroup);
		return $res;
	}
	/**
	 * Execute one query
	 *
	 * @version 2.3.0
	 */
	public static function query( $query ) {
		global $wpdb;
		$wpdb->wtbp_prepared_query = self::prepareQuery($query);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write/DDL query, not cacheable; invalidates the read cache in get() below.
		$res = $wpdb->query($wpdb->wtbp_prepared_query);
		if (false !== $res) {
			self::flushCache();
		}
		return ! ( false === $res );
	}
	/**
	 * Get last insert ID
	 *
	 * @return int last ID
	 */
	public static function insertID() {
		global $wpdb;
		return $wpdb->insert_id;
	}
	/**
	 * Get number of rows returned by last query
	 *
	 * @return int number of rows
	 */
	public static function numRows() {
		global $wpdb;
		return $wpdb->num_rows;
	}
	/**
	 * Replace prefixes in custom query. Suported next prefixes:
	 * #__  Worwtbpess prefix
	 * ^__  Store plugin tables prefix (@see WTBP_DB_PREF if config.php)
	 *
	 * @__  Compared of WP table prefix + Store plugin prefix (@example wp_s_)
	 *
	 * @param string $query query to be executed
	 */
	public static function prepareQuery( $query ) {
		global $wpdb;
		return str_replace(
				array('#__', '^__', '@__'),
				array($wpdb->prefix, WTBP_DB_PREF, $wpdb->prefix . WTBP_DB_PREF),
				$query);
	}
	public static function getError() {
		global $wpdb;
		return $wpdb->last_error;
	}
	public static function lastID() {
		global $wpdb;
		return $wpdb->insert_id;
	}
	public static function timeToDate( $timestamp = 0 ) {
		if ($timestamp) {
			if (!is_numeric($timestamp)) {
				$timestamp = dateToTimestampWtbp($timestamp);
			}
			return gmdate('Y-m-d', $timestamp);
		} else {
			return gmdate('Y-m-d');
		}
	}
	public static function dateToTime( $date ) {
		if (empty($date)) {
			return '';
		}
		if (strpos($date, WTBP_DATE_DL)) {
			return dateToTimestampWtbp($date);
		}
		$arr = explode('-', $date);
		return dateToTimestampWtbp($arr[2] . WTBP_DATE_DL . $arr[1] . WTBP_DATE_DL . $arr[0]);
	}
	public static function exist( $table, $column = '', $value = '' ) {
		if (empty($column) && empty($value)) {       //Check if table exist
			$res = self::get('SHOW TABLES LIKE "' . $table . '"', 'one');
		} elseif (empty($value)) {                   //Check if column exist
			$res = self::get('SHOW COLUMNS FROM ' . $table . ' LIKE "' . $column . '"', 'one');
		} else {                                    //Check if value in column table exist
			$res = self::get('SELECT COUNT(*) AS total FROM ' . $table . ' WHERE ' . $column . ' = "' . $value . '"', 'one');
		}
		return !empty($res);
	}
	public static function prepareHtml( $d ) {
		if (is_array($d)) {
			foreach ($d as $i => $el) {
				$d[ $i ] = self::prepareHtml( $el );
			}
		} else {
			$d = esc_html($d);
		}
		return $d;
	}
	public static function prepareHtmlIn( $d ) {
		if (is_array($d)) {
			foreach ($d as $i => $el) {
				$d[ $i ] = self::prepareHtml( $el );
			}
		} else {
			$d = wp_filter_nohtml_kses($d);
		}
		return $d;
	}
	public static function escape( $data ) {
		global $wpdb;
		return $wpdb->_escape($data);
	}
	public static function getAutoIncrement( $table ) {
		return (int) self::get('SELECT AUTO_INCREMENT
			FROM information_schema.tables
			WHERE table_name = "' . $table . '"
			AND table_schema = DATABASE( );', 'one');
	}
	public static function setAutoIncrement( $table, $autoIncrement ) {
		return self::query('ALTER TABLE `' . $table . '` AUTO_INCREMENT = ' . $autoIncrement . ';');
	}
}
