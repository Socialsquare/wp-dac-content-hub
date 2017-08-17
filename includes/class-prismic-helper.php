<?php

require_once plugin_dir_path( __FILE__ ) . 'class-link-resolver.php';

use Prismic\Api;
use Prismic\Predicates;

/**
 * This class contains helpers for the Prismic API.
 */
class Prismic_Helper {

  public $linkResolver;
  private $api = null;

  public function __construct() {
    $this->linkResolver = new PrismicLinkResolver($this);

    // $url = $container->get('settings')['prismic.url'];
    // $token = $container->get('settings')['prismic.token'];
    $url = 'https://dac-content-hub.cdn.prismic.io/api';
    $token = null;
    try {
      $this->api = Api::get($url, $token);
    } catch (Exeption $e) {
      echo 'Caught exception: ',  $e->getMessage(), "\n";
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
   */
  private function rangeQuery($type, $name, $value) {
    $numbers = preg_match_all('/([\d]+)/', $value, $matches)
      ? array_map('intval', $matches[1])
      : NULL;
    unset($matches);
    switch (count($numbers)) {
      case 1:
        return Predicates::at("my.$type.$name", (int) reset($numbers));

      case 2:
        return Predicates::inRange("my.$type.$name", $numbers[0], $numbers[1]);

      default:
        return FALSE;
    }
  }

  /**
   * Query builder.
   */
  public function query($predicates) {
    $query = [];
    $type = $predicates['type'] ?: NULL;
    foreach ($predicates as $name => $value) {
      if ($value && in_array($name, ['type', 'id', 'tags'])) {
        $query[] = Predicates::at("document.$name", $value);
      }
      if ($value && $type && !in_array($name, ['type', 'id', 'tags', 'view_mode'])) {
        switch ($name) {
          case 'organisation':
            // We need to use the group this field is in.
            $query[] = Predicates::at("my.$type.actors.$name", $value);
            break;

          case 'build_year':
            // Call range query builder.
            $result = $this->rangeQuery($type, $name, $value);
            if ($result) {
              $query[] = $result;
            }
            break;

          default:
            $query[] = Predicates::at("my.$type.$name", $value);
        }
      }
    }

    return $this->api->query($query);
  }

}
