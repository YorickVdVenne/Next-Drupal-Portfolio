<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Resolves a field.
 *
 * @DataProducer(
 *   id = "get_field",
 *   name = @Translation("Get Field"),
 *   description = @Translation("Return the specified field."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Field")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *     ),
 *     "fieldName" = @ContextDefinition("string",
 *       label = @Translation("Name of the field.")
 *     )
 *   }
 * )
 */
class GetField extends DataProducerPluginBase {

  /**
   * The resolver.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface  $entity
   *   The fieldable entity.
   * @param string $fieldName
   *   The name of the field.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The field context.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface $field
   *   The field.
   */
  public function resolve(FieldableEntityInterface $entity, string $fieldName, FieldContext $context) {
    $field = $entity->get($fieldName);
    $context->addCacheableDependency($field);
    return $field;
  }

}
