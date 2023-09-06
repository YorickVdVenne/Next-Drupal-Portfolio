<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;

/**
 * A schema extension for language links.
 *
 * @SchemaExtension(
 *   id = "language_switch_links",
 *   name = "Language Switch Links",
 *   description = "An extension that provides language switch links.",
 *   schema = "core_composable"
 * )
 */
class LanguageSwitchLinksExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return ['configurable_language'];
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
    $registry->addFieldResolver('InternalUrl', 'languageSwitchLinks', $builder->compose(
      $builder->produce('language_switch_links')->map('url', $builder->fromParent())
    ));

    $registry->addFieldResolver('LanguageSwitchLink', 'active',
      $builder->callback(function ($link) {
        return $link['active'] ?? FALSE;
      })
    );

    $registry->addFieldResolver('LanguageSwitchLink', 'title',
      $builder->callback(function ($link) {
        return $link['title'] ?? '';
      })
    );

    $registry->addFieldResolver('LanguageSwitchLink', 'language',
      $builder->callback(function ($link) {
        return $link['language'];
      })
    );

    $registry->addFieldResolver('LanguageSwitchLink', 'url',
      $builder->callback(function ($link) {
        return $link['url'];
      })
    );
  }

}
