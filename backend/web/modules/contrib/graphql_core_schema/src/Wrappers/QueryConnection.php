<?php

namespace Drupal\graphql_core_schema\Wrappers;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Deferred;

/**
 * Helper class that wraps entity queries.
 */
class QueryConnection {

  /**
   * The query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $query;

  /**
   * The language code.
   *
   * @var string
   */
  protected $langcode;

  /**
   * QueryConnection constructor.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The query.
   * @param string $langcode
   *   The language code.
   */
  public function __construct(QueryInterface $query, string $langcode) {
    $this->query = $query;
    $this->langcode = $langcode;
  }

  /**
   * Get the total results.
   *
   * @return int
   *   The number of results.
   */
  public function total() {
    $query = clone $this->query;
    $query->range(NULL, NULL)->count();
    /** @var int */
    return $query->execute();
  }

  /**
   * Get the items.
   *
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The resolve context.
   *
   * @return array|\GraphQL\Deferred
   *   The result.
   */
  public function items(ResolveContext $context) {
    $result = $this->query->execute();
    if (empty($result)) {
      return [];
    }

    $callback = $this->getResolveCallback($result);
    return new Deferred(function () use ($callback, $context) {
      $entities = $callback();
      $items = [];

      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      foreach (array_values($entities) as $entity) {
        if (
          $this->langcode &&
          $entity instanceof TranslatableInterface &&
          $entity->hasTranslation($this->langcode)
        ) {
          $entity = $entity->getTranslation($this->langcode);
        }
        $entity->addCacheContexts(["static:language:{$this->langcode}"]);
        $access = $entity->access('view', NULL, TRUE);
        $context->addCacheableDependency($access);

        if ($access->isAllowed()) {
          $items[] = $entity;
        }
        else {
          $items[] = NULL;
        }
      }

      return $items;
    });
  }

  /**
   * Return the correct buffer callback for loading the entities.
   *
   * @param array $result
   *   The query result.
   *
   * @return mixed
   *   The callback
   */
  private function getResolveCallback(array $result) {
    // Check if the entity type is revisionable.
    $entityType = \Drupal::entityTypeManager()->getDefinition($this->query->getEntityTypeId());
    if ($entityType->isRevisionable()) {
      $buffer = \Drupal::service('graphql.buffer.entity_revision');
      return $buffer->add($this->query->getEntityTypeId(), array_keys($result));
    }

    $buffer = \Drupal::service('graphql.buffer.entity');
    return $buffer->add($this->query->getEntityTypeId(), array_values($result));
  }

}
