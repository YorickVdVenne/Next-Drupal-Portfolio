<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer\Menu;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Filter the given array of menu links.
 *
 * @DataProducer(
 *   id = "filter_menu_links",
 *   name = @Translation("Filter Menu Links"),
 *   description = @Translation("Filter the menu links to contain only accessible links."),
 *   produces = @ContextDefinition("list",
 *     label = @Translation("Filtered menu links"),
 *   ),
 *   consumes = {
 *     "links" = @ContextDefinition("list",
 *       label = @Translation("Menu links")
 *     ),
 *   }
 * )
 */
class FilterMenuLinks extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param array $links
   *
   * @return array
   */
  public function resolve(array $links) {
    return array_filter($links, function ($link) {
      if ($link instanceof MenuLinkTreeElement) {
        return $link->access && $link->access->isAllowed();
      }

      // @TODO: Is this correct?
      return FALSE;
    });
  }

}
