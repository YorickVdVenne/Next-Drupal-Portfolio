<?php

namespace Drupal\graphql_form_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormState;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql_form_schema\Plugin\GraphQL\EntityFormBase;

/**
 * Builds a form to edit an entity.
 *
 * @DataProducer(
 *   id = "form_entity_edit",
 *   name = @Translation("Form: Entity Edit"),
 *   description = @Translation("Builds a form to edit an entity."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("The form object.")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("The entity."),
 *     ),
 *     "operation" = @ContextDefinition("string",
 *       label = @Translation("The form operation."),
 *       required = FALSE,
 *     ),
 *   }
 * )
 */
class EntityEdit extends EntityFormBase {

  /**
   * The resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $operation
   *   The operation.
   *
   * @return \Drupal\Core\Form\FormInterface|null
   *   The FormObject.
   */
  public function resolve(EntityInterface $entity, $operation, FieldContext $fieldContext) {
    $formObject = $this->entityTypeManager->getFormObject(
      $entity->getEntityTypeId(),
      $operation ?? 'default'
    );
    $formObject->setEntity($entity);

    $formState = new FormState();
    $fieldContext->setContextValue('form_state', $formState);
    return $formObject;
  }

}
