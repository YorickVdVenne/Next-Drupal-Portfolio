<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql_core_schema\Plugin\GraphQL\EntityQueryBase;
use Drupal\graphql_core_schema\Wrappers\QueryConnection;

/**
 * The data producer for entity queries.
 *
 * @DataProducer(
 *   id = "entity_query",
 *   name = @Translation("Query entities"),
 *   description = @Translation("Query entities"),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("EntityQuery connection")
 *   ),
 *   consumes = {
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
class EntityQuery extends EntityQueryBase {

  /**
   * Resolver.
   *
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
  public function resolve($entityTypeKey, $limit, $offset, $revisions, $sort, $filter, FieldContext $metadata) {
    $query = $this->getBaseQuery($entityTypeKey, $limit, $offset, $revisions, $sort, $filter, $metadata);
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    return new QueryConnection($query, $langcode);
  }

}
