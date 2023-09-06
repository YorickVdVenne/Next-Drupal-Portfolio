<?php

namespace Drupal\graphql_masquerade_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * A schema extension to get information about restricted content.
 *
 * @SchemaExtension(
 *   id = "masquerade",
 *   name = "Masquerade",
 *   description = "Provides integration for the masquerade module.",
 *   schema = "core_composable"
 * )
 */
class MasqueradeExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver(
      'Query',
      'masqueradeContext',
      $builder->produce('masquerade_context')
    );

    $registry->addFieldResolver(
      'Mutation',
      'masqueradeSwitchBack',
      $builder->produce('masquerade_switch_back')
    );
  }

}
