<?php

namespace Drupal\graphql_messenger\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_messenger\MessageWrapper;

/**
 * A schema extension to get information about restricted content.
 *
 * @SchemaExtension(
 *   id = "messenger",
 *   name = "Messenger",
 *   description = "An extension that provides a field to access Drupal messenger messages.",
 *   schema = "core_composable"
 * )
 */
class MessengerExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('Mutation', 'messengerMessages', $builder->callback(function () {
      return new MessageWrapper();
    }));

    $registry->addFieldResolver('Query', 'messengerMessages', $builder->callback(function () {
      return new MessageWrapper();
    }));
  }

}
