<?php

namespace Drupal\graphql_environment_indicator\Plugin\GraphQL\SchemaExtension;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * The environment_indicator schema extension.
 *
 * @SchemaExtension(
 *   id = "environment_indicator",
 *   name = "Environment Indicator",
 *   description = "Adds a field to get the current environment.",
 *   schema = "core_composable"
 * )
 */
class EnvironmentIndicatorExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('Query', 'activeEnvironment',
      $builder->produce('active_environment')
    );

    $registry->addFieldResolver('ActiveEnvironment', 'name',
      $builder->callback(function (ImmutableConfig $value) {
        return $value->get('name');
      })
    );
    $registry->addFieldResolver('ActiveEnvironment', 'fgColor',
      $builder->callback(function (ImmutableConfig $value) {
        return $value->get('fg_color');
      })
    );
    $registry->addFieldResolver('ActiveEnvironment', 'bgColor',
      $builder->callback(function (ImmutableConfig $value) {
        return $value->get('bg_color');
      })
    );
  }

}
