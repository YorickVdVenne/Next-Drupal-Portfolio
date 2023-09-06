<?php

namespace Drupal\graphql_rokka_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;

/**
 * Adds Node data to the GraphQL Compose GraphQL API.
 *
 * @SchemaExtension(
 *   id = "rokka",
 *   name = "Rokka Schema Extension",
 *   description = "Rokka Route Extension.",
 *   schema = "core_composable"
 * )
 */
class RokkaExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return ['file', 'rokka_metadata'];
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('File', 'rokkaMetadata', $builder->compose(
      $builder->produce('rokka')->map('file', $builder->fromParent())
    ));
  }

}
