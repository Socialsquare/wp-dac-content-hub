<?php

use Prismic\LinkResolver;

/**
 * The link resolver is the code building URLs for pages corresponding to
 * a Prismic document.
 *
 * If you want to change the URLs of your site, you need to update this class
 * as well as the routes in app.php.
 */
class PrismicLinkResolver extends LinkResolver
{
  private $prismic;

  public function __construct($prismic)
  {
    $this->prismic = $prismic;
  }

  public function resolve($link)
  {
    // TODO: Implement links
    // Example link resolver for custom type with API ID of 'example-page'
    if ($link->getType() == 'example-page') {
      return '/example-page/' . $link->getUid();
    }

    // Default case returns the homepage
    return '/';
  }
}
