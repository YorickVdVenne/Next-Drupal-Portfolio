<?php

namespace Drupal\my_module\Plugin\GraphQL\DataProducer;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;
use Drupal\Core\Entity\EntityInterface;
use Drupal\file\Entity\File;

/**
 * Resolves the image field for a node.
 *
 * @DataProducer(
 *   id = "graphql_content_resolve_image_field",
 *   name = @Translation("Resolve image field for a node"),
 *   description = @Translation("Resolves the image field for a node."),
 *   produces = @ContextDefinition("list", label = @Translation("List of image properties")),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity:node", label = @Translation("Node entity"))
 *   }
 * )
 */
class ResolveImageField extends DataProducerPluginBase {

  /**
   * Resolves the image field for a node.
   */
  public function resolve(EntityInterface $entity) {
    return new Deferred(function () use ($entity) {
      $image = $entity->get('field_image')->entity;

      if ($image instanceof File) {
        return [
          'url' => file_create_url($image->getFileUri()),
          'alt' => $image->get('field_image_alt')->value,
        ];
      }

      return null;
    });
  }

}
