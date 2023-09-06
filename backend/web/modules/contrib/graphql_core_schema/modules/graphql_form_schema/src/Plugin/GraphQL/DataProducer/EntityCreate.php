<?php

namespace Drupal\graphql_form_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Form\FormState;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql_form_schema\Plugin\GraphQL\EntityFormBase;

/**
 * Builds a form to create an entity.
 *
 * @DataProducer(
 *   id = "form_entity_create",
 *   name = @Translation("Form: Entity Create"),
 *   description = @Translation("Builds a form to create an entity."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("The form object.")
 *   ),
 *   consumes = {
 *     "entityType" = @ContextDefinition("string",
 *       label = @Translation("The entity type."),
 *     ),
 *     "values" = @ContextDefinition("any",
 *       label = @Translation("The additional values to create the entity."),
 *       required = FALSE,
 *     ),
 *     "operation" = @ContextDefinition("string",
 *       label = @Translation("The form operation."),
 *       required = FALSE,
 *     ),
 *   }
 * )
 */
class EntityCreate extends EntityFormBase {

  /**
   * The resolver.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $id
   *   The entity ID.
   *
   * @return \Drupal\Core\Form\FormInterface|null
   *   The FormObject.
   */
  public function resolve(string $entityType, $values, $operation, FieldContext $fieldContext) {
    $storage = $this->entityTypeManager->getStorage($entityType);
    $entity = $storage->create($values ?? []);
    $formObject = $this->entityTypeManager->getFormObject($entityType, $operation ?? 'default');
    $formObject->setEntity($entity);

    $formState = new FormState();
    $fieldContext->setContextValue('form_state', $formState);
    return $formObject;
  }

}
