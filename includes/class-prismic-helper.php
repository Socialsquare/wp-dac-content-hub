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

  public function get_api() {
    // $url = $container->get('settings')['prismic.url'];
    // $token = $container->get('settings')['prismic.token'];
    $url = 'https://dac-content-hub.prismic.io/api';
    $token = null;
    if ($this->api == null) {
      $this->api = Api::get($url, $token);
    }

    return $this->api;
  }
}
