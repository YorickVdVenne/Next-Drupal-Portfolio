<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the entity in a given language with optional fallback.
 *
 * Drupal graphql provides a data producer called entity_load, which we use to
 * load the entity. It also performs access checks. While this data producer
 * also has the option to load a translation, it will throw an exception if
 * the translation is not valid. This is why we had to create this separate
 * data producer that is chained after entity_load. So first we call
 * entity_load without a language, it does access checks and returns the entity.
 * Then our entity_translation_fallback producer is executed. It can be called
 * without a langcode, in which case it just returns the entity. If a langcode
 * is provided, it loads the translation and now does access check on the
 * translated entity.
 *
 * It is assumed that the passed in entity has already been access checked.
 *
 * In order to support various use cases, passing fallback=TRUE will return the
 * entity even if it doesn't have the requested translation language.
 *
 * @DataProducer(
 *   id = "entity_translation_fallback",
 *   name = @Translation("Entity translation fallback"),
 *   description = @Translation("Returns the translated entity with optional fallback."),
 *   produces = @ContextDefinition("entity",
 *     label = @Translation("Translated entity")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "langcode" = @ContextDefinition("string",
 *       label = @Translation("Langcode"),
 *       required = FALSE
 *     ),
 *     "fallback" = @ContextDefinition("boolean",
 *       label = @Translation("Return same entity as fallback"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class EntityTranslationFallback extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string|null $langcode
   *   The langcode to get the translation from.
   * @param bool|null $fallback
   *   Whether to return the current entity if the requested translation does not exist.
   * @param FieldContext $field
   *   The GraphQL field context.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The translated entity.
   */
  public function resolve(EntityInterface $entity, ?string $langcode, ?bool $fallback, FieldContext $field) {
    if (!$langcode) {
      return $entity;
    }

    // @TODO: Should we call isTranslatable() here first?
    if ($entity instanceof TranslatableInterface && $entity->isTranslatable() && $entity->hasTranslation($langcode)) {
      $entity = $entity->getTranslation($langcode);
      $entity->addCacheContexts(["static:language:{$langcode}"]);
      /** @var \Drupal\Core\Access\AccessResultInterface $accessResult */
      $accessResult = $entity->access('view', NULL, TRUE);
      if (!$accessResult->isAllowed()) {
        return NULL;
      }
      $field->setContextValue('language', $langcode);
      return $entity;
    }

    // Fallback to returning same entity in current language.
    if ($fallback) {
      return $entity;
    }

    return NULL;
  }

}
