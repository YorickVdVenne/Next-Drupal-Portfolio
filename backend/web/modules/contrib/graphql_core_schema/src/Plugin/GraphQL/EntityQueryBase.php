<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Error\UserError;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The base class for entity queries.
 */
class EntityQueryBase extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  const MAX_LIMIT = 1000;

  const OPERATOR_MAPPING = [
    'BETWEEN' => 'BETWEEN',
    'EQUAL' => '=',
    'GREATER_THAN' => '>',
    'GREATER_THAN_OR_EQUAL' => '>=',
    'IN' => 'IN',
    'IS_NOT_NULL' => 'IS NOT NULL',
    'IS_NULL' => 'IS NULL',
    'LIKE' => 'LIKE',
    'NOT_BETWEEN' => 'NOT BETWEEN',
    'NOT_EQUAL' => '!=',
    'NOT_IN' => 'NOT IN',
    'NOT_LIKE' => 'NOT LIKE',
    'SMALLER_THAN' => '<',
    'SMALLER_THAN_OR_EQUAL' => '<=',
  ];

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * EntityQueryBase constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    LanguageManagerInterface $languageManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
  }

  /**
   * Build the base query.
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
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query.
   */
  protected function getBaseQuery($entityTypeKey, $limit, $offset, $revisions, $sort, $filter, FieldContext $metadata) {
    if ($limit > static::MAX_LIMIT) {
      throw new UserError(sprintf('Exceeded maximum query limit: %s.', static::MAX_LIMIT));
    }

    $storage = $this->entityTypeManager->getStorage(strtolower($entityTypeKey));
    $entityType = $storage->getEntityType();
    $query = $storage->getQuery()
      ->accessCheck(TRUE);

    $query->range($offset, $limit);

    if ($sort) {
      $this->applySort($query, $sort);
    }

    if ($revisions) {
      $this->applyRevisionsMode($query, $revisions);
    }

    if ($filter) {
      $this->applyFilter($query, $filter);
    }

    $metadata->addCacheTags($entityType->getListCacheTags());
    $metadata->addCacheContexts($entityType->getListCacheContexts());

    return $query;
  }

  /**
   * Apply the specified revision filtering mode to the query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The entity query object.
   * @param mixed $mode
   *   The revision query mode.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The entity query object.
   */
  protected function applyRevisionsMode(QueryInterface $query, $mode) {
    if ($mode === 'ALL') {
      // Mark the query as such and sort by the revision id too.
      $query->allRevisions();
      $query->addTag('revisions');
    }
    elseif ($mode === 'LATEST') {
      // Mark the query to only include latest revision and sort by revision id.
      $query->latestRevision();
      $query->addTag('revisions');
    }

    return $query;
  }

  /**
   * Apply the specified sort directives to the query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The entity query object.
   * @param mixed $sort
   *   The sort definitions from the field arguments.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The entity query object.
   */
  protected function applySort(QueryInterface $query, $sort) {
    if (!empty($sort) && is_array($sort)) {
      foreach ($sort as $item) {
        $direction = !empty($item['direction']) ? $item['direction'] : 'DESC';
        $language = !empty($item['language']) ? $item['language'] : NULL;
        $query->sort($item['field'], $direction, $language);
      }
    }

    return $query;
  }

  /**
   * Apply the specified filter conditions to the query.
   *
   * Recursively picks up all filters and aggregates them into condition groups
   * according to the nested structure of the filter argument.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The entity query object.
   * @param mixed $filter
   *   The filter definitions from the field arguments.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The entity query object.
   */
  protected function applyFilter(QueryInterface $query, $filter) {
    if (!empty($filter) && is_array($filter)) {
      // Conditions can be disabled. Check we are not adding an empty condition group.
      $filterConditions = $this->buildFilterConditions($query, $filter);
      if (count($filterConditions->conditions())) {
        $query->condition($filterConditions);
      }
    }

    return $query;
  }

  /**
   * Recursively builds the filter condition groups.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The entity query object.
   * @param array $filter
   *   The filter definitions from the field arguments.
   *
   * @return \Drupal\Core\Entity\Query\ConditionInterface
   *   The generated condition group according to the given filter definitions.
   *
   * @throws \GraphQL\Error\Error
   *   If the given operator and value for a filter are invalid.
   */
  protected function buildFilterConditions(QueryInterface $query, array $filter) {
    $conjunction = !empty($filter['conjunction']) ? $filter['conjunction'] : 'AND';
    $group = $conjunction === 'AND' ? $query->andConditionGroup() : $query->orConditionGroup();

    // Apply filter conditions.
    $conditions = !empty($filter['conditions']) ? $filter['conditions'] : [];
    foreach ($conditions as $condition) {
      // Check if we need to disable this condition.
      if (isset($condition['enabled']) && empty($condition['enabled'])) {
        continue;
      }

      $field = $condition['field'];
      $value = !empty($condition['value']) ? $condition['value'] : NULL;
      $operator = !empty($condition['operator']) ? $condition['operator'] : NULL;
      $language = !empty($condition['language']) ? $condition['language'] : NULL;

      // We need at least a value or an operator.
      if (empty($operator) && empty($value)) {
        throw new UserError(sprintf("Missing value and operator in filter for '%s'.", $field));
      }
      // Unary operators need a single value.
      elseif (!empty($operator) && $this->isUnaryOperator($operator)) {
        if (empty($value) || count($value) > 1) {
          throw new UserError(sprintf("Unary operators must be associated with a single value (field '%s').", $field));
        }

        // Pick the first item from the values.
        $value = reset($value);
      }
      // Range operators need exactly two values.
      elseif (!empty($operator) && $this->isRangeOperator($operator)) {
        if (empty($value) || count($value) !== 2) {
          throw new UserError(sprintf("Range operators must require exactly two values (field '%s').", $field));
        }
      }
      // Null operators can't have a value set.
      elseif (!empty($operator) && $this->isNullOperator($operator)) {
        if (!empty($value)) {
          throw new UserError(sprintf("Null operators must not be associated with a filter value (field '%s').", $field));
        }
      }

      // If no operator is set, however, we default to EQUALS or IN, depending
      // on whether the given value is an array with one or more than one items.
      if (empty($operator)) {
        $value = count($value) === 1 ? reset($value) : $value;
        $operator = is_array($value) ? 'IN' : '=';
      }

      // Map GraphQL operator to proper ConditionInterface operator.
      if (isset(static::OPERATOR_MAPPING[$operator])) {
        $operator = static::OPERATOR_MAPPING[$operator];
      }

      // Add the condition for the current field.
      $group->condition($field, $value, $operator, $language);
    }

    // Apply nested filter group conditions.
    $groups = !empty($filter['groups']) ? $filter['groups'] : [];
    foreach ($groups as $args) {
      // By default, we use AND condition groups.
      // Conditions can be disabled. Check we are not adding an empty condition group.
      $filterConditions = $this->buildFilterConditions($query, $args);
      if (count($filterConditions->conditions())) {
        $group->condition($filterConditions);
      }
    }

    return $group;
  }

  /**
   * Checks if an operator is a unary operator.
   *
   * @param string $operator
   *   The query operator to check against.
   *
   * @return bool
   *   TRUE if the given operator is unary, FALSE otherwise.
   */
  protected function isUnaryOperator($operator) {
    $unary = ["=", "<>", "<", "<=", ">", ">=", "LIKE", "NOT LIKE"];
    return in_array($operator, $unary);
  }

  /**
   * Checks if an operator is a null operator.
   *
   * @param string $operator
   *   The query operator to check against.
   *
   * @return bool
   *   TRUE if the given operator is a null operator, FALSE otherwise.
   */
  protected function isNullOperator($operator) {
    $null = ["IS NULL", "IS NOT NULL"];
    return in_array($operator, $null);
  }

  /**
   * Checks if an operator is a range operator.
   *
   * @param string $operator
   *   The query operator to check against.
   *
   * @return bool
   *   TRUE if the given operator is a range operator, FALSE otherwise.
   */
  protected function isRangeOperator($operator) {
    $null = ["BETWEEN", "NOT BETWEEN"];
    return in_array($operator, $null);
  }

}
