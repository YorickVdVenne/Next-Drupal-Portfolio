<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;
use Drupal\graphql_core_schema\CoreSchemaInterfaceExtensionInterface;
use Drupal\graphql_core_schema\EntitySchemaBuilder;

/**
 * A schema extension to provide additional fields for media entities.
 *
 * @SchemaExtension(
 *   id = "media",
 *   name = "Media",
 *   description = "An extension that provides additional media fields.",
 *   schema = "core_composable"
 * )
 */
class MediaExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface, CoreSchemaInterfaceExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return ['media'];
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
  public function getBaseDefinition() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionDefinition() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getInterfaceExtender() {
    return [
      'Media' => [
        function (EntitySchemaBuilder $builder, array &$fields) {
          $fields['mediaFileUrl'] = [
            'type' => fn () => $builder->getType('Url'),
            'description' => 'The URL of the file belonging to the media.',
          ];
        },
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('Media', 'mediaFileUrl',
      $builder->produce('media_file_url')->map('media', $builder->fromParent())
    );
  }

}
