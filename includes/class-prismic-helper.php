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
	 * Prismic API helper.
	 *
	 * @var API $api
	 */
	private $api;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->link_resolver = new PrismicLinkResolver( $this );

		// $url = $container->get('settings')['prismic.url'];
		// $token = $container->get('settings')['prismic.token'];
		$url = 'https://dac-content-hub.cdn.prismic.io/api';
		$token = null;
		try {
			$this->api = Api::get( $url, $token );
		} catch ( Exeption $e ) {
			throw new Error( $e->getMessage() );
		}
	}

	/**
	 * Expose api.
	 */
	public function get_api() {
		return $this->api;
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
			if ( $value && [ $name, [ 'type', 'id', 'tags' ], true ] ) {
				$query[] = Predicates::at( "document.$name", $value );
			}
			if ( $value && $type && ! in_array( $name, [ 'type', 'id', 'tags', 'view_mode' ], true ) ) {
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

					default:
						$query[] = Predicates::at( "my.$type.$name", $value );
				}
			}
		}

		return $this->api->query( $query );
	}

}
