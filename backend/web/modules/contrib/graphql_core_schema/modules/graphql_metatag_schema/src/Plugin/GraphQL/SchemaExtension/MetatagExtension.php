<?php

namespace Drupal\graphql_metatag_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;

/**
 * The metatag schema extension.
 *
 * @SchemaExtension(
 *   id = "metatag",
 *   name = "Metatag",
 *   description = "An extension that provides metatag fields.",
 *   schema = "core_composable"
 * )
 */
class MetatagExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface {

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
    $registry->addTypeResolver('Metatag', function ($value) {
      $tag = $value[0]['#tag'];
      if ($tag === 'meta') {
        return 'MetatagMeta';
      }
      elseif ($tag === 'link') {
        return 'MetatagLink';
      }
      return 'Metatag';
    });

    $registry->addFieldResolver('InternalUrl', 'metatags',
      $builder->produce('metatags')->map('url', $builder->fromParent())
    );

    $registry->addFieldResolver('Metatag', 'id',
      $builder->callback(function ($value) {
        return $value[1];
      })
    );

    $registry->addFieldResolver('Metatag', 'tag',
      $builder->callback(function ($value) {
        return $value[0]['#tag'];
      })
    );

    $registry->addFieldResolver('Metatag', 'attributes',
      $builder->callback(function ($value) {
        $attributes = [];
        foreach ($value[0]['#attributes'] as $key => $value) {
          $attributes[] = [
            'key' => $key,
            'value' => $value,
          ];
        }
        return $attributes;
      })
    );

    $registry->addFieldResolver('MetatagAttribute', 'key',
      $builder->callback(function ($value) {
        return $value['key'];
      })
    );
    $registry->addFieldResolver('MetatagAttribute', 'value',
      $builder->callback(function ($value) {
        return $value['value'];
      })
    );
  }

}
