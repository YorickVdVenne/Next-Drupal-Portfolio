<?php

namespace Drupal\Tests\graphql_core_schema\Traits;

use Drupal\graphql\Entity\ServerInterface;

/**
 * Trait for testing the schema
 */
trait CoreComposableSchemaTrait {

  /**
   * Generate the schema.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $server
   *   The server id.
   *
   * @return \GraphQL\Type\Schema
   *   The schema.
   */
  protected function getSchema(ServerInterface $server) {
    /** @var \GraphQL\Server\ServerConfig $config */
    $config = $server->configuration();
    return $config->getSchema();
  }

}
