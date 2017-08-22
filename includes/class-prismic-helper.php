<?php
/**
 * @file Prismic helper.
 */

use Prismic\Api;
use Prismic\Predicates;

require_once plugin_dir_path( __FILE__ ) . 'class-link-resolver.php';

/**
 * This class contains helpers for the Prismic API.
 */
class Prismic_Helper {

	/**
	 * Prismic link resolver class.
	 *
	 * @var PrismicLinkResolver $link_resolver
	 */
	public $link_resolver;

	/**
	 * Prismic API endpoint.
	 *
	 * @var string $api_endpoint
	 */
	public $api_endpoint;

	/**
	 * Prismic API endpoint.
	 *
	 * @var string $api_token
	 */
	public $api_token;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->link_resolver = new PrismicLinkResolver( $this );
		// Get settings.
		$this->api_endpoint = get_option( 'dac_api_endpoint' );
		$this->api_token = get_option( 'dac_api_token' );
	}

	/**
	 * Expose api.
	 *
	 * @throws Error Error message.
	 * @return Api
	 */
	public function get_api() {
		try {
			return Api::get( $this->api_endpoint, $this->api_token );
		} catch ( Exeption $e ) {
			throw new Error( $e->getMessage() );
		}
	}

	/**
	 * Format range query.
	 *
	 * @param string $type Content type.
	 * @param string $name Field name.
	 * @param string $value Field value.
	 *
	 * @return array List of query predicates.
	 */
	private function range_query( $type, $name, $value ) {
		$numbers = preg_match_all( '/([\d]+)/', $value, $matches )
		? array_map( 'intval', $matches[1] )
		: null;
		unset( $matches );
		switch ( count( $numbers ) ) {
			case 1:
				return Predicates::at( "my.$type.$name", (int) reset( $numbers ) );

			case 2:
				return Predicates::inRange( "my.$type.$name", $numbers[0], $numbers[1] );

			default:
				return false;
		}
	}

	/**
	 * Query builder.
	 *
	 * @param array $predicates Query predicates.
	 */
	public function query( $predicates ) {
		$query = [];
		$type = $predicates['type'] ?: null;
		foreach ( $predicates as $name => $value ) {
			if ( $value && in_array( $name, [ 'type', 'id' ], true ) ) {
				$query[] = Predicates::at( "document.$name", $value );
			}

			if ( $value && $type && ! in_array( $name, [ 'type', 'id', 'view_mode' ], true ) ) {
				switch ( $name ) {
					case 'organisation':
						// We need to use the group this field is in.
						$query[] = Predicates::at( "my.$type.actors.$name", $value );
						break;

					case 'build_year':
						// Call range query builder.
						$result = $this->range_query( $type, $name, $value );
						if ( $result ) {
							$query[] = $result;
						}
						break;

					case 'tags':
						$tags = explode( ' ', $value );
						$query[] = Predicates::any( "document.$name", $tags );
						break;

					default:
						$query[] = Predicates::at( "my.$type.$name", $value );
						break;
				}
			}
		}

		return $this->api->query( $query );
	}

}
