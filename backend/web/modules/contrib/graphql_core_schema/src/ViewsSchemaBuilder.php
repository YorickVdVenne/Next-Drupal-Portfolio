<?php

namespace Drupal\graphql_core_schema;

use Drupal\views\ViewEntityInterface;
use Drupal\views\ViewExecutable;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\SchemaPrinter;

/**
 * The ViewsSchemaBuilder class.
 */
class ViewsSchemaBuilder {

  /**
   * The generated types.
   *
   * @var \GraphQL\Type\Definition\TypeWithFields[]
   */
  protected $types;

  /**
   * The ViewExecutable base fields.
   *
   * @var array
   */
  protected $baseFields;

  /**
   * The Entity scalar.
   *
   * Only used as a placeholder, will not be part of the generated schema.
   *
   * @var \GraphQL\Type\Definition\CustomScalarType
   */
  protected $entityScalar;

  /**
   * The enabled views and displays.
   *
   * @var string[]
   */
  protected $enabledViewDisplays;

  /**
   * The constructor.
   *
   * @param array $enabled
   *   List of enabled views.
   */
  public function __construct(array $enabled) {
    $this->enabledViewDisplays = $enabled;
    $this->entityScalar = new CustomScalarType(['name' => 'Entity']);
    $this->types = [];
    $this->baseFields = [
      'itemsPerPage' => Type::nonNull(Type::int()),
      'filters' => fn () => Type::listOf($this->types['ViewFilter']),
      'sorts' => fn () => Type::listOf($this->types['ViewSort']),
      'pager' => fn () => $this->types['ViewPager'],
      'execute' => [
        'type' => fn () => $this->types['ViewExecutableResult'],
      ],
      'executeWithQueryParams' => [
        'type' => fn () => $this->types['ViewExecutableResult'],
        'args' => [
          'queryParams' => fn() => Type::listOf($this->types['ViewExecuteQueryParam']),
        ],
      ],
    ];
    $this->types['ViewExecuteQueryParam'] = new InputObjectType([
      'name' => 'ViewExecuteQueryParam',
      'fields' => [
        'key' => Type::nonNull(Type::string()),
        'value' => fn () => $this->types['ViewValue'],
      ],
    ]);
    $this->types['ViewExecutable'] = new InterfaceType([
      'name' => 'ViewExecutable',
      'fields' => $this->baseFields,
    ]);
    $this->types['ViewExecutableResult'] = new ObjectType([
      'name' => 'ViewExecutableResult',
      'fields' => [
        'rows' => fn () => Type::listOf($this->entityScalar),
      ],
    ]);
    $this->types['ViewValue'] = new CustomScalarType([
      'name' => 'ViewValue',
    ]);
    $this->types['ViewFilterGroupItem'] = new ObjectType([
      'name' => 'ViewFilterGroupItem',
      'fields' => [
        'title' => Type::string(),
        'operator' => Type::string(),
        'value' => Type::string(),
      ],
    ]);
    $this->types['ViewFilterGroupInfo'] = new ObjectType([
      'name' => 'ViewFilterGroupInfo',
      'fields' => [
        'label' => Type::string(),
        'description' => Type::string(),
        'identifier' => Type::string(),
        'optional' => Type::boolean(),
        'widget' => Type::string(),
        'multiple' => Type::boolean(),
        'remember' => Type::boolean(),
        'defaultGroup' => Type::string(),
        'groupItems' => fn () => Type::listOf($this->types['ViewFilterGroupItem']),
      ],
    ]);
    $this->types['ViewFilter'] = new ObjectType([
      'name' => 'ViewFilter',
      'fields' => [
        'pluginId' => Type::string(),
        'baseId' => Type::string(),
        'adminLabel' => Type::string(),
        'adminLabelShort' => Type::string(),
        'adminSummary' => Type::string(),
        'field' => Type::string(),
        'realField' => Type::string(),
        'table' => Type::string(),
        'value' => fn () => $this->types['ViewValue'],
        'options' => fn () => $this->types['ViewValue'],
        'groupInfo' => fn () => $this->types['ViewFilterGroupInfo'],
        'operator' => Type::string(),
        'noOperator' => Type::boolean(),
        'alwaysRequired' => Type::boolean(),
        'isExposed' => Type::boolean(),
        'isAGroup' => Type::boolean(),
      ],
    ]);

    $this->types['ViewSort'] = new ObjectType([
      'name' => 'ViewSort',
      'fields' => [
        'pluginId' => Type::string(),
        'baseId' => Type::string(),
        'field' => Type::string(),
        'realField' => Type::string(),
      ],
    ]);
    $this->types['ViewPager'] = new ObjectType([
      'name' => 'ViewPager',
      'fields' => [
        'id' => Type::string(),
        'perPage' => Type::int(),
        'totalItems' => Type::int(),
      ],
    ]);
  }

  /**
   * Get the GraphQL type for a view.
   *
   * @param \Drupal\views\ViewExecutable $executable
   *   The view executable.
   *
   * @return string
   *   The GraphQL type name for the view.
   */
  public static function getGraphqlTypeName(ViewExecutable $executable) {
    return EntitySchemaHelper::toPascalCase([
      'view',
      'executable',
      $executable->storage->id(),
      $executable->getDisplay()->getBaseId(),
    ]);
  }

  /**
   * Generate the type for the view.
   *
   * @param \Drupal\views\ViewEntityInterface $view
   *   The view entity.
   */
  public function generateViewType(ViewEntityInterface $view) {
    $displays = $view->get('display');

    foreach ($displays as $displayId => $options) {
      $key = $view->id() . ':' . $displayId;
      $executable = $view->getExecutable();
      $executable->setDisplay($displayId);

      // Only expose enabled view displays.
      if (!in_array($key, $this->enabledViewDisplays)) {
        continue;
      }

      $graphqlTypeName = self::getGraphqlTypeName($executable);
      $display = $executable->getDisplay();
      $filters = array_reduce(array_filter($display->getOption('filters') ?: [], function ($filter) {
        return array_key_exists('exposed', $filter) && $filter['exposed'];
      }), function ($carry, $current) {
        return $carry + [
          $current['expose']['identifier'] => $current,
        ];
      }, []);

      // Build field arguments.
      $args = [
        'page' => Type::id(),
      ];

      // Check if sorting is enabled.
      if (!empty($display->getOption('sorts'))) {
        $args['sortBy'] = Type::string();
        $args['sortOrder'] = Type::string();
      }

      foreach ($filters as $filterName => $filter) {
        if ($this->isGenericInputFilter($filter)) {
          // @todo Implement correctly.
          // return $this->createGenericInputFilterDefinition($filter);
          continue;
        }
        $argTypeName = EntitySchemaHelper::toCamelCase($filterName);
        $args[$argTypeName] = $filter['expose']['multiple'] ? Type::listOf(Type::string()) : Type::string();
      }

      $this->types[$graphqlTypeName] = new ObjectType([
        'name' => $graphqlTypeName,
        'interfaces' => [fn () => $this->types['ViewExecutable']],
        'fields' => [
          ...$this->baseFields,
          'execute' => [ //phpcs:ignore
            'type' => fn () => $this->types['ViewExecutableResult'],
            'args' => $args,
          ],
        ],
      ]);
    }
  }

  /**
   * Checks if a filter definition is a generic input filter.
   *
   * @param mixed $filter
   *   The filter.
   *   $filter['value'] = [];
   *   $filter['value'] = [
   *     "text",
   *     "test"
   *   ];
   *   $filter['value'] = [
   *     'distance' => 10,
   *     'distance2' => 30,
   *     ...
   *   ];.
   *
   * @return bool
   *   Returns true if it is a generic filter.
   */
  public function isGenericInputFilter($filter) {
    if (!is_array($filter['value']) || count($filter['value']) == 0) {
      return FALSE;
    }

    $firstKey = array_keys($filter['value'])[0];
    return is_string($firstKey);
  }

  /**
   * Creates a definition for a generic input filter.
   *
   * @param mixed $filter
   *   The filter.
   *   $filter['value'] = [];
   *   $filter['value'] = [
   *     "text",
   *     "test"
   *   ];
   *   $filter['value'] = [
   *     'distance' => 10,
   *     'distance2' => 30,
   *     ...
   *   ];.
   *
   * @return array
   *   The filter definition.
   */
  public function createGenericInputFilterDefinition($filter) {
    $filterId = $filter['expose']['identifier'];

    $id = implode('_', [
      $filter['expose']['multiple'] ? $filterId : $filterId . '_multi',
      'view',
      'filter',
      'input',
    ]);

    $fields = [];
    foreach ($filter['value'] as $fieldKey => $fieldDefaultValue) {
      $fields[$fieldKey] = [
        'type' => Type::string(),
      ];
    }

    $genericInputFilter = [
      'id' => $id,
      'name' => EntitySchemaHelper::toPascalCase([$id]),
      'fields' => $fields,
    ];

    $this->derivatives[$id] = $genericInputFilter;

    return [
      'type' => $filter['expose']['multiple'] ? $genericInputFilter['name'] : $genericInputFilter['name'],
    ];
  }

  /**
   * Get the generated schema.
   *
   * @return string
   *   The generated schema.
   */
  public function getGeneratedSchema() {
    $result = '';
    foreach ($this->types as $type) {
      $result .= SchemaPrinter::printType($type) . "\n";
    }

    return $result;
  }

}
