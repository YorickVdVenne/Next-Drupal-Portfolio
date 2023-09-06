<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;

/**
 * A schema extension to get an entity display.
 *
 * @SchemaExtension(
 *   id = "entity_display",
 *   name = "Entity Display",
 *   description = "An extension that provides entity form and view display fields.",
 *   schema = "core_composable"
 * )
 */
class EntityDisplayExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return ['entity_form_display', 'entity_view_display'];
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
    // @TODO: Not implemented yet.
  }

}
