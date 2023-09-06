<?php

namespace Drupal\graphql_debugging\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * A schema extension to add some debugging fields.
 *
 * @SchemaExtension(
 *   id = "debugging",
 *   name = "Debugging",
 *   description = "Adds useful fields for debugging during development.",
 *   schema = "core_composable"
 * )
 */
class DebuggingExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('Query', 'requestHeaders', $builder->produce('request_headers'));
  }

}
