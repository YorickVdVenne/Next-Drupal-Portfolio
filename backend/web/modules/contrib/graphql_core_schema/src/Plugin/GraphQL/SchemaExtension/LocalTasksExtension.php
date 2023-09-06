<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;

/**
 * The local task schema extension.
 *
 * @SchemaExtension(
 *   id = "local_tasks",
 *   name = "Local Tasks",
 *   description = "An extension that provides local tasks.",
 *   schema = "core_composable"
 * )
 */
class LocalTasksExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionDependencies() {
    return ['routing'];
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('InternalUrl', 'localTasks',
      $builder->produce('local_tasks')
        ->map('url', $builder->fromParent())
    );

    $registry->addFieldResolver('LocalTask', 'baseId',
      $builder->callback(function ($value) {
        return $value['_key'];
      })
    );

    $registry->addFieldResolver('LocalTask', 'active',
      $builder->callback(function ($value) {
        return $value['#active'] ?? FALSE;
      })
    );

    $registry->addFieldResolver('LocalTask', 'title',
      $builder->callback(function ($value) {
        return $value['#link']['title'] ?? '';
      })
    );

    $registry->addFieldResolver('LocalTask', 'weight',
      $builder->callback(function ($value) {
        return $value['#weight'] ?? 0;
      })
    );

    $registry->addFieldResolver('LocalTask', 'url',
      $builder->callback(function ($value) {
        return $value['#link']['url'] ?? NULL;
      })
    );
  }

}
