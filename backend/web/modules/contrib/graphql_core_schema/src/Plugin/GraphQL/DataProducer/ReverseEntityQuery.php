<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql_core_schema\Plugin\GraphQL\EntityQueryBase;
use Drupal\graphql_core_schema\Wrappers\QueryConnection;

/**
 * The data producer for reverse entity queries.
 *
 * @DataProducer(
 *   id = "reverse_entity_query",
 *   name = @Translation("Reverse Entity Query"),
 *   description = @Translation("Query entities"),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("EntityQuery connection")
 *   ),
 *   consumes = {
 *     "targetId" = @ContextDefinition("string",
 *       label = @Translation("The target entity ID for the reverse lookup."),
 *       required = TRUE
 *     ),
 *     "referenceFields" = @ContextDefinition("list",
 *       label = @Translation("The fields to use for the lookup."),
 *       required = TRUE
 *     ),
 *     "entityType" = @ContextDefinition("string",
 *       label = @Translation("Entity type"),
 *       required = TRUE
 *     ),
 *     "limit" = @ContextDefinition("integer",
 *       label = @Translation("Limit"),
 *       required = FALSE
 *     ),
 *     "offset" = @ContextDefinition("integer",
 *       label = @Translation("Offset"),
 *       required = FALSE
 *     ),
 *     "revisions" = @ContextDefinition("any",
 *       label = @Translation("Revisions"),
 *       required = FALSE
 *     ),
 *     "sort" = @ContextDefinition("any",
 *       label = @Translation("Sort"),
 *       required = FALSE
 *     ),
 *     "filter" = @ContextDefinition("any",
 *       label = @Translation("Filter"),
 *       required = FALSE
 *     ),
 *   }
 * )
 */
class ReverseEntityQuery extends EntityQueryBase {

  /**
   * Resolver.
   *
   * @param string $targetId
   *   The target ID for the lookup.
   * @param string[] $referenceFields
   *   The fields to use for the lookup.
   * @param string $entityTypeKey
   *   The entity type key.
   * @param int $limit
   *   The limit.
   * @param int $offset
   *   The offset.
   * @param mixed $revisions
   *   Revision mode.
   * @param mixed $sort
   *   The sort.
   * @param mixed $filter
   *   The filters.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The metadata.
   *
   * @return \Drupal\graphql_core_schema\Wrappers\QueryConnection
   *   The result.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function resolve($targetId, $referenceFields, $entityTypeKey, $limit, $offset, $revisions, $sort, $filter, FieldContext $metadata) {
    $query = $this->getBaseQuery($entityTypeKey, $limit, $offset, $revisions, $sort, $filter, $metadata);

    // Build the condition group for the reverse lookup.
    $group = $query->orConditionGroup();
    foreach ($referenceFields as $fieldName) {
      $group->condition($fieldName, $targetId);
    }
    $query->condition($group);

    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    return new QueryConnection($query, $langcode);
  }

}
