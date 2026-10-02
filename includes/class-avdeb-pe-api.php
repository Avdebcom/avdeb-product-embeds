<?php
/**
 * AVDEB catalog API client: query, validate, cache.
 *
 * @package AvdebProductEmbeds
 */

defined( 'ABSPATH' ) || exit;

/**
 * Calls https://api.avdeb.com/v1/catalog server-side. Every query is cached on its own (transient);
 * the last good answer is kept for a week and served when AVDEB can't be reached.
 */
final class AVDEB_PE_API {

	const BASE          = 'https://api.avdeb.com/v1/catalog';
	const VERSION_OPT   = 'avdeb_pe_cache_v';
	const STALE_TTL     = WEEK_IN_SECONDS;
	const BACKOFF_TTL   = 15 * MINUTE_IN_SECONDS;
	const KEY_INFO_TTL  = 10 * MINUTE_IN_SECONDS;
	const SECRET_KEY_RE = '/^avdeb_sk_[A-Za-z0-9]{32}$/';

	/**
	 * Products for a query.
	 *
	 * @param array<string, mixed> $args creator, tag, handle, q, limit.
	 * @return array<int, array<string, mixed>>
	 */
	public static function products( $args ) {
		$query = array();
		foreach ( array( 'creator', 'tag', 'handle' ) as $k ) {
			if ( ! empty( $args[ $k ] ) ) {
				$query[ $k ] = sanitize_title( $args[ $k ] );
			}
		}
		if ( ! empty( $args['q'] ) ) {
			$query['q'] = substr( sanitize_text_field( $args['q'] ), 0, 100 );
		}
		$max            = '' !== self::api_key() ? 48 : 12;
		$query['limit'] = isset( $args['limit'] ) ? min( $max, max( 1, absint( $args['limit'] ) ) ) : 6;
		if ( ! empty( $query['handle'] ) ) {
			$query['limit'] = 1;
		}
		// Without a key the API adds the manually entered referral code; with a key it adds the owner's.
		$manual_ref = AVDEB_PE_Settings::clean_ref( AVDEB_PE_Settings::get( 'ref_code' ) );
		if ( '' === self::api_key() && '' !== $manual_ref ) {
			$query['ref'] = $manual_ref;
		}

		$id    = self::cache_id( 'q', $query );
		$fresh = get_transient( $id );
		if ( is_array( $fresh ) ) {
			return $fresh;
		}

		$res = self::request( '/products', $query );
		if ( is_array( $res ) && isset( $res['products'] ) && is_array( $res['products'] ) ) {
			$products = array();
			foreach ( $res['products'] as $raw ) {
				$p = self::normalize( $raw );
				if ( $p ) {
					$products[] = $p;
				}
			}
			$hours = max( 1, (int) AVDEB_PE_Settings::get( 'cache_hours' ) );
			set_transient( $id, $products, $hours * HOUR_IN_SECONDS );
			set_transient( $id . '_s', $products, self::STALE_TTL );
			return $products;
		}

		// Failure: serve the last good copy and don't retry for a while.
		$stale = get_transient( $id . '_s' );
		$stale = is_array( $stale ) ? $stale : array();
		set_transient( $id, $stale, self::BACKOFF_TTL );
		return $stale;
	}

	/**
	 * The saved secret key ('' when none).
	 *
	 * @return string
	 */
	public static function api_key() {
		$key = (string) AVDEB_PE_Settings::get( 'api_key' );
		return preg_match( self::SECRET_KEY_RE, $key ) ? $key : '';
	}

	/**
	 * Who the saved key belongs to: ['ok' => true, 'owner' => [type, name, ref]] or ['ok' => false, 'error' => …].
	 * Null when no key is saved.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function key_info() {
		if ( '' === self::api_key() ) {
			return null;
		}
		$id     = self::cache_id( 'key', array() );
		$cached = get_transient( $id );
		if ( is_array( $cached ) ) {
			return $cached;
		}
		$error = '';
		$res   = self::request( '/key', array(), $error );
		if ( is_array( $res ) && isset( $res['owner'] ) && is_array( $res['owner'] ) ) {
			$info = array(
				'ok'    => true,
				'owner' => array(
					'type' => isset( $res['owner']['type'] ) ? sanitize_key( $res['owner']['type'] ) : '',
					'name' => isset( $res['owner']['name'] ) ? sanitize_text_field( $res['owner']['name'] ) : '',
					'ref'  => isset( $res['owner']['ref'] ) ? AVDEB_PE_Settings::clean_ref( $res['owner']['ref'] ) : '',
				),
			);
		} else {
			$info = array(
				'ok'    => false,
				'error' => '' !== $error ? $error : __( 'AVDEB could not be reached.', 'avdeb-product-embeds' ),
			);
		}
		set_transient( $id, $info, $info['ok'] ? self::KEY_INFO_TTL : 5 * MINUTE_IN_SECONDS );
		return $info;
	}

	/**
	 * Invalidate every cached query (settings saved, "Refresh products now"). Old transients expire on their own.
	 */
	public static function flush() {
		update_option( self::VERSION_OPT, (int) get_option( self::VERSION_OPT, 1 ) + 1, false );
	}

	/**
	 * Transient name for a query (includes the key, so changing it invalidates).
	 *
	 * @param string               $kind  Cache kind.
	 * @param array<string, mixed> $query Query.
	 * @return string
	 */
	private static function cache_id( $kind, $query ) {
		$v = (int) get_option( self::VERSION_OPT, 1 );
		return 'avdeb_pe_' . $v . '_' . md5( $kind . '|' . wp_json_encode( $query ) . '|' . self::api_key() );
	}

	/**
	 * GET a catalog endpoint.
	 *
	 * @param string               $path  Path under BASE.
	 * @param array<string, mixed> $query Query args.
	 * @param string               $error Set to the API error message on failure.
	 * @return array<string, mixed>|null
	 */
	private static function request( $path, $query, &$error = '' ) {
		$headers = array( 'Accept' => 'application/json' );
		$key     = self::api_key();
		if ( '' !== $key ) {
			$headers['Authorization'] = 'Bearer ' . $key;
		}
		$url = self::BASE . $path;
		if ( ! empty( $query ) ) {
			$url = add_query_arg( array_map( 'rawurlencode', $query ), $url );
		}
		$res = wp_remote_get(
			$url,
			array(
				'timeout'    => 8,
				'user-agent' => 'AVDEB-Product-Embeds/' . AVDEB_PE_VERSION . '; ' . home_url( '/' ),
				'headers'    => $headers,
			)
		);
		if ( is_wp_error( $res ) ) {
			$error = $res->get_error_message();
			return null;
		}
		$data = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
			$error = is_array( $data ) && isset( $data['error'] ) ? sanitize_text_field( $data['error'] ) : 'HTTP ' . (int) wp_remote_retrieve_response_code( $res );
			return null;
		}
		return is_array( $data ) ? $data : null;
	}

	/**
	 * Keep only the fields the plugin renders, validated.
	 *
	 * @param mixed $p Raw product.
	 * @return array<string, mixed>|null
	 */
	private static function normalize( $p ) {
		if ( ! is_array( $p ) || empty( $p['handle'] ) || empty( $p['title'] ) || empty( $p['url'] ) ) {
			return null;
		}
		$url = esc_url_raw( (string) $p['url'] );
		if ( ! AVDEB_PE_Render::is_avdeb_url( $url ) ) {
			return null;
		}
		$image = isset( $p['image'] ) ? esc_url_raw( (string) $p['image'] ) : '';
		if ( 0 !== strpos( $image, 'https://' ) ) {
			$image = '';
		}
		$currency = isset( $p['currency'] ) ? strtoupper( preg_replace( '/[^A-Za-z]/', '', (string) $p['currency'] ) ) : '';
		$creator  = null;
		if ( isset( $p['creator'] ) && is_array( $p['creator'] ) && ! empty( $p['creator']['slug'] ) ) {
			$creator = array(
				'slug' => sanitize_title( $p['creator']['slug'] ),
				'name' => isset( $p['creator']['name'] ) ? sanitize_text_field( $p['creator']['name'] ) : '',
			);
		}
		return array(
			'handle'           => sanitize_title( $p['handle'] ),
			'title'            => sanitize_text_field( $p['title'] ),
			'url'              => $url,
			'image'            => $image,
			'price'            => isset( $p['price'] ) && is_numeric( $p['price'] ) ? (float) $p['price'] : null,
			'compare_at_price' => isset( $p['compare_at_price'] ) && is_numeric( $p['compare_at_price'] ) ? (float) $p['compare_at_price'] : null,
			'currency'         => '' !== $currency ? substr( $currency, 0, 3 ) : 'USD',
			'creator'          => $creator,
		);
	}
}
