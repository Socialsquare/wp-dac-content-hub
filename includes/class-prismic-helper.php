<?php

require_once plugin_dir_path( __FILE__ ) . 'class-link-resolver.php';

use Prismic\Api;
use Prismic\Predicates;

/**
 * This class contains helpers for the Prismic API.
 */
class Prismic_Helper {

  public $linkResolver;

  public function __construct()
  {
    $this->linkResolver = new PrismicLinkResolver($this);
  }

  private $api = null;

  public function query($predicates) {
    // $url = $container->get('settings')['prismic.url'];
    // $token = $container->get('settings')['prismic.token'];
    $url = 'https://dac-content-hub.cdn.prismic.io/api';
    $token = null;
    try {
      $this->api = Api::get($url, $token);
    } catch (Exeption $e) {
      echo 'Caught exception: ',  $e->getMessage(), "\n";
    }

    $query = [];
    $type = $predicates['type'] ?: null;
    foreach ($predicates as $name => $value) {
      if ($value && in_array($name, ['type', 'id', 'tags'])) {
        $query[] = Predicates::at("document.$name", $value);
      }
      if ($value && $type && !in_array($name, ['type', 'id', 'tags', 'view_mode'])) {
        switch ($type) {
          case 'organisation':
            // We need to use the group this field is in.
            $query[] = Predicates::at("my.$type.actors.$name", $value);
            break;
          default:
            $query[] = Predicates::at("my.$type.$name", $value);
        }
      }
    }

    return $this->api->query($query);
  }
}
