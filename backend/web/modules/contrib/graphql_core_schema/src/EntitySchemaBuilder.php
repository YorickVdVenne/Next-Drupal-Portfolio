<?php

namespace Drupal\graphql_core_schema;

use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem;
use Drupal\Core\Field\Plugin\Field\FieldType\EmailItem;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\Plugin\Field\FieldType\IntegerItem;
use Drupal\Core\Field\Plugin\Field\FieldType\LanguageItem;
use Drupal\Core\Field\Plugin\Field\FieldType\MapItem;
use Drupal\Core\Field\Plugin\Field\FieldType\NumericItemBase;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItemBase;
use Drupal\Core\Field\Plugin\Field\FieldType\TimestampItem;
use Drupal\Core\Field\TypedData\FieldItemDataDefinition;
use Drupal\Core\Field\TypedData\FieldItemDataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\file\Plugin\Field\FieldType\FileItem;
use Drupal\options\Plugin\Field\FieldType\ListStringItem;
use Drupal\telephone\Plugin\Field\FieldType\TelephoneItem;
use Drupal\text\Plugin\Field\FieldType\TextItemBase;
use Drupal\text\Plugin\Field\FieldType\TextWithSummaryItem;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\TypeWithFields;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;

/**
 * The EntitySchemaBuilder class.
 */
class EntitySchemaBuilder {

  /**
   * The entity field manager.
   */
  protected EntityFieldManagerInterface|null $entityFieldManager = NULL;

  /**
   * The entity type bundle info service.
   */
  protected EntityTypeBundleInfoInterface|null $entityTypeBundleInfo = NULL;

  /**
   * The type data manager.
   */
  protected TypedDataManagerInterface|null $typedDataManager = NULL;

  /**
   * The typed config manager.
   */
  protected TypedConfigManagerInterface|null $typedConfigManager = NULL;

  /**
   * Fields that are not resolved by the default field resolver and exist on all entities.
   *
   * @var string[]
   */
  const EXCLUDED_ENTITY_FIELDS = [
    'id',
    'uuid',
    'label',
    'langcode',
  ];

  /**
   * Possible base fields on the entity interface.
   *
   * These are configurable via the schema configuration.
   *
   * @var string[]
   */
  const BASE_ENTITY_FIELDS = [
    'uuid' => 'The unique UUID.',
    'label' => 'The label of this entity.',
    'langcode' => 'The langcode of this entity.',
    'toArray' => 'Gets an array of all property values.',
    'getConfigTarget' => 'Gets the configuration target identifier for the entity.',
    'uriRelationships' => 'Gets a list of URI relationships supported by this entity.',
    'referencedEntities' => 'Gets a list of entities referenced by this entity.',
    'entityTypeId' => 'The entity type ID.',
    'entityBundle' => 'The entity bundle.',
    'isNew' => 'Determines whether the entity is new.',
    'accessCheck' => 'Check entity access for the given operation, defaults to view.',
    'entityBundle' => 'The bundle of the entity.',
  ];

  /**
   * The enabled entity types.
   *
   * @var string[]
   */
  protected $enabledEntityTypes;

  /**
   * The interface extenders.
   *
   * @var array
   */
  protected $interfaceExtenders;

  /**
   * Enabled extensions.
   *
   * @var string[]
   */
  protected $enabledExtensions = [];

  /**
   * Enabled fields.
   *
   * @var string[][]
   */
  protected $enabledFields = [];

  /**
   * Generate value fields.
   *
   * @var bool
   */
  protected $generateValueFields;

  /**
   * Types that should never be generated.
   *
   * @var string[]
   */
  const EXCLUDED_TYPES = [
    'password',
    '_core_config_info',
  ];

  /**
   * The generated schema.
   *
   * @var \GraphQL\Type\Definition\TypeWithFields[]
   */
  protected $types;

  /**
   * Array of types that have been or will be generated.
   *
   * Unlike the $types property, this array may contain a type that is in the
   * process of being generated, e.g. that the type can already be referenced
   * using a callable, since it will be available once the callables are
   * called.
   *
   * @var string[]
   */
  protected $generatedTypeNames;

  /**
   * Entity type definitions.
   *
   * @var EntityTypeInterface[]
   */
  protected array $entityTypeDefinitions;

  /**
   * A custom scalar type for untyped values.
   */
  protected CustomScalarType $mapDataType;

  /**
   * The constructor.
   *
   * @param array $enabledEntityTypes
   *   The enabled entity types.
   * @param array $enabledExtensions
   *   The enabled extensions.
   * @param array $enabledFields
   *   List of enabled fields.
   * @param array $enabledEntityFields
   *   List of enabled entity base fields.
   * @param array $interfaceExtenders
   *   Array of interface extenders.
   * @param EntityTypeInterface[] $entityTypeDefinitions
   *   Array of entity type definitions.
   */
  public function __construct(
    bool $generateValueFields,
    array $enabledEntityTypes,
    array $enabledExtensions,
    array $enabledFields,
    array $enabledEntityFields,
    array $interfaceExtenders,
    array $entityTypeDefinitions
  ) {
    $this->generatedTypeNames = [];
    $this->types = [];
    $this->generateValueFields = $generateValueFields;
    $this->enabledExtensions = $enabledExtensions;
    $this->enabledEntityTypes = $enabledEntityTypes;
    $this->enabledFields = $enabledFields;
    $this->interfaceExtenders = $interfaceExtenders;
    $this->entityTypeDefinitions = $entityTypeDefinitions;

    // Create scalars.
    $this->mapDataType = new CustomScalarType([
      'name' => 'MapData',
    ]);
    $this->types['MapData'] = $this->mapDataType;

    // Loop over the base entity interface fields and remove those that have
    // been disabled via schema configuration.
    $baseEntityFieldDefinitions = $this->getBaseEntityFieldDefinitions();
    foreach (array_keys($baseEntityFieldDefinitions) as $key) {
      if ($key !== 'id' && !in_array($key, $enabledEntityFields)) {
        unset($baseEntityFieldDefinitions[$key]);
      }
    }

    // Create entity interfaces.
    $this->createInterface('Entity', 'A common interface for all entity objects.', $baseEntityFieldDefinitions);

    $this->createInterface('EntityTranslatable', 'An entity that is translatable.', [
      'translations' => [
        'type' => fn() => Type::listOf($this->types['EntityTranslatable']),
        'description' => 'Get all translations.',
      ],
      'translation' => [
        'type' => fn() => $this->types['EntityTranslatable'],
        'args' => [
          'langcode' => fn() => Type::nonNull($this->types['Langcode']),
          'fallback' => [
            'type' => Type::boolean(),
            'description' => 'Return entity in current language if translation language does not exist.',
          ],
        ],
        'description' => 'Get a specific translation.',
      ],
    ]);
    $this->createInterface('EntityLinkable', 'An entity that is linkable.', [
      'url' => [
        'type' => fn() => $this->types['Url'],
        'args' => [
          'rel' => Type::string(),
        ],
        'description' => 'Get the URL, defaults to canonical.',
      ],
    ]);

    $this->createInterface('EntityDescribable', 'An entity that has a description.', [
      'entityDescription' => Type::string(),
    ]);

    // Create field item and field item list interfaces.
    $baseFieldItemListFields = [
      'isEmpty' => [
        'type' => fn() => Type::nonNull(Type::boolean()),
        'description' => 'True if the field list has no items.',
      ],
      'count' => [
        'type' => fn() => Type::nonNull(Type::int()),
        'description' => 'The number of field items.',
      ],
      'getString' => [
        'type' => fn() => Type::nonNull(Type::string()),
        'description' => 'Get a string representation of all field items.',
      ],
      'entity' => [
        'type' => fn() => $this->types['Entity'],
        'description' => 'Get the entity the field belongs to.',
      ],
    ];

    $baseFieldItemFields = [
      'isEmpty' => [
        'type' => Type::nonNull(Type::boolean()),
        'description' => 'True if this item is considered empty.',
      ],
    ];

    if ($this->extensionEnabled('field_config')) {
      $baseFieldItemListFields['fieldConfig'] = function () {
        return fn() => Type::nonNull($this->types['FieldDefinition']);
      };

      // Create interface for the field definition.
      $this->createInterface('FieldDefinition', 'Interface for field definitions.', [
        'name' => Type::string(),
      ]);
    }

    $this->createInterface('FieldItemList', 'A field item list containing items.', $baseFieldItemListFields);
    $this->createInterface('FieldItemType', 'An item in a field list.', $baseFieldItemFields);

    // Interface for an URL.
    $urlInterface = $this->createInterface('Url', 'Interface for an URL.', [
      'path' => Type::string(),
    ]);

    $this->types['DefaultUrl'] = new ObjectType([
      'name' => 'DefaultUrl',
      'interfaces' => [$urlInterface],
      'fields' => [
        'path' => Type::string(),
      ],
    ]);

    $this->createInterface('LanguageInterface', 'Interface for a language.', [
      'name' => Type::nonNull(Type::string()),
      'id' => Type::nonNull(Type::string()),
      'direction' => Type::nonNull(Type::int()),
      'weight' => Type::nonNull(Type::int()),
      'isLocked' => Type::nonNull(Type::boolean()),
    ]);

    $this->types['Language'] = new ObjectType([
      'name' => 'Language',
      'interfaces' => [$this->types['LanguageInterface']],
      'fields' => $this->types['LanguageInterface']->getFields(),
    ]);

    // Ping fields to make sure that the schema can be generated even if no
    // query or mutation fields exist.
    $this->types['Query'] = new ObjectType([
      'name' => 'Query',
      'fields' => [
        'ping' => Type::string(),
      ],
    ]);
    $this->types['Mutation'] = new ObjectType([
      'name' => 'Mutation',
      'fields' => [
        'ping' => Type::string(),
      ],
    ]);
  }

  /**
   * Get the entity field manager.
   *
   * @return EntityFieldManagerInterface
   *   The entity field manager.
   */
  private function getEntityFieldManager(): EntityFieldManagerInterface {
    if (empty($this->entityFieldManager)) {
      $this->entityFieldManager = \Drupal::service('entity_field.manager');
    }

    return $this->entityFieldManager;
  }

  /**
   * Get the entity type bundle info service.
   *
   * @return EntityTypeBundleInfoInterface
   *   The entity type bundle info service.
   */
  private function getEntityTypeBundleInfo(): EntityTypeBundleInfoInterface {
    if (empty($this->entityTypeBundleInfo)) {
      $this->entityTypeBundleInfo = \Drupal::service('entity_type.bundle.info');
    }

    return $this->entityTypeBundleInfo;
  }

  /**
   * Get the typed data manager.
   *
   * @return TypedDataManagerInterface
   *   The typed data manager.
   */
  private function getTypedDataManager(): TypedDataManagerInterface {
    if (empty($this->typedDataManager)) {
      $this->typedDataManager = \Drupal::service('typed_data_manager');
    }

    return $this->typedDataManager;
  }

  /**
   * Get the typed config manager.
   *
   * @return TypedConfigManagerInterface
   *   The typed config manager.
   */
  private function getTypedConfigManager(): TypedConfigManagerInterface {
    if (empty($this->typedConfigManager)) {
      $this->typedConfigManager = \Drupal::service('config.typed');
    }

    return $this->typedConfigManager;
  }

  /**
   * Get a type.
   *
   * @param string $name
   *   The name of the type.
   *
   * @return TypeWithFields|null
   *   The type if it exists.
   */
  public function getType(string $name): TypeWithFields|null {
    return $this->types[$name] ?? NULL;
  }

  /**
   * Create an interface.
   *
   * @param string $name
   *   The name of the interface.
   * @param string $description
   *   The description.
   * @param array $fields
   *   An array of fields.
   * @param InterfaceType[] $interfaces
   *   Additional interfaces the interface implements.
   *
   * @return InterfaceType
   *   The interface.
   */
  protected function createInterface(string $name, string $description, array $fields, array $interfaces = []): InterfaceType {
    // Get interface extenders.
    $extenders = $this->interfaceExtenders[$name] ?? NULL;

    if ($extenders) {
      // Execute the interface extenders.
      foreach (array_values($extenders) as $extender) {
        $extender($this, $fields);
      }
    }
    $this->types[$name] = new InterfaceType([
      'name' => $name,
      'interfaces' => $interfaces,
      'fields' => $fields,
      'description' => $description,
    ]);

    return $this->types[$name];
  }

  /**
   * Get the schema mapping for a config entity type.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityTypeInterface $type
   *   The config entity type.
   *
   * @return array
   *   The schema mapping.
   */
  private function getConfigEntityMapping(ConfigEntityTypeInterface $type): array {
    $configPrefix = $type->getConfigPrefix();
    $typedConfigDefinition = $this->getTypedConfigManager()->getDefinition($configPrefix . '.*');
    $mapping = $typedConfigDefinition['mapping'] ?? [];
    if (empty($mapping)) {
      $typedConfigDefinition = $this->getTypedConfigManager()->getDefinition($configPrefix . '.*.*');
      $mapping = $typedConfigDefinition['mapping'] ?? [];
    }
    if (empty($mapping)) {
      $typedConfigDefinition = $this->getTypedConfigManager()->getDefinition($configPrefix . '.*.*.*');
      $mapping = $typedConfigDefinition['mapping'] ?? [];
    }

    return $mapping;
  }

  /**
   * Return the base field definitions for the Entity interface.
   *
   * @return array
   *   The field definitions.
   */
  public function getBaseEntityFieldDefinitions() {
    return [
      'id' => [
        'type' => Type::string(),
      ],
      'uuid' => [
        'type' => Type::nonNull(Type::string()),
        'description' => 'The unique UUID.',
      ],
      'label' => [
        'type' => Type::string(),
        'description' => 'The label of this entity.',
      ],
      'langcode' => [
        'type' => Type::string(),
        'description' => 'The langcode of this entity.',
      ],
      'toArray' => [
        'type' => $this->mapDataType,
        'description' => 'Gets an array of all property values.',
      ],
      'getConfigTarget' => [
        'type' => Type::string(),
        'description' => 'Gets the configuration target identifier for the entity.',
      ],
      'uriRelationships' => [
        'type' => Type::listOf(Type::string()),
        'description' => 'Gets a list of URI relationships supported by this entity.',
      ],
      'entityBundle' => [
        'type' => Type::string(),
        'description' => 'The bundle ID of the entity.',
      ],
      'referencedEntities' => [
        'type' => fn () => Type::listOf($this->getType('Entity')),
        'description' => 'Gets a list of entities referenced by this entity.',
      ],
      'entityTypeId' => [
        'type' => Type::nonNull(Type::string()),
        'description' => 'The entity type ID.',
      ],
      'entityBundle' => [
        'type' => Type::nonNull(Type::string()),
        'description' => 'The bundle of the entity.',
      ],
      'isNew' => [
        'type' => Type::nonNull(Type::boolean()),
        'description' => 'Determines whether the entity is new.',
      ],
      'accessCheck' => [
        'type' => Type::nonNull(Type::boolean()),
        'args' => [
          'operation' => Type::string(),
        ],
        'description' => 'Check entity access for the given operation, defaults to view.',
      ],
    ];
  }

  /**
   * Check if the given field is enabled.
   *
   * @param string $entityTypeId
   *   The entity type ID.
   * @param string $fieldName
   *   The field name.
   *
   * @return bool
   *   TRUE if the field is enabled for this entity type.
   */
  private function fieldIsEnabled(string $entityTypeId, string $fieldName) {
    if (empty($this->enabledFields[$entityTypeId])) {
      return FALSE;
    }
    return in_array($fieldName, $this->enabledFields[$entityTypeId]);
  }

  /**
   * Check if the given extension is enabled.
   *
   * @param string $id
   *   The ID of the extension.
   *
   * @return bool
   *   The extension is enabled.
   */
  private function extensionEnabled(string $id): bool {
    return in_array($id, $this->enabledExtensions);
  }

  /**
   * Check if the given entity type is enabled.
   *
   * @param string $id
   *   The ID of the entity type.
   *
   * @return bool
   *   The entity type is enabled.
   */
  private function entityTypeIsEnabled(string $id): bool {
    return in_array($id, $this->enabledEntityTypes);
  }

  /**
   * Get the GraphQL field definition for an entity value field.
   *
   * This will return a type for value fields, where instead of the entire
   * field the direct field value is resolved. For example a simple text
   * field will directly resolve to a string scalar.
   *
   * If no appropriate scalar is found the type for the field item is returned.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   The field definition.
   *
   * @return \GraphQL\Type\Definition\Type|array|null
   *   The GraphQL type if found.
   */
  private function buildGraphqlValueField(FieldDefinitionInterface $fieldDefinition) {
    $description = (string) $fieldDefinition->getDescription();
    $fieldName = $fieldDefinition->getName();

    if (!$description) {
      $description = (string) $fieldDefinition->getFieldStorageDefinition()->getDescription();
    }
    $description .= " [field: $fieldName]";
    $description .= ' (field value)';
    $storageDefinition = $fieldDefinition->getFieldStorageDefinition();
    $isMultiple = $storageDefinition->isMultiple();

    // Create a field item we can use to determine what scalar this should
    // resolve to.
    $itemDefinition = $fieldDefinition->getItemDefinition();
    $typedData = $this->getTypedDataManager()->create($itemDefinition);

    if ($fieldName === 'metatag' && $typedData instanceof MapItem) {
      return NULL;
    }

    if (
      $typedData instanceof StringItem ||
      $typedData instanceof StringItemBase ||
      $typedData instanceof EmailItem ||
      $typedData instanceof ListStringItem ||
      $typedData instanceof TelephoneItem
    ) {
      return [
        'type' => $this->wrapTypeList(Type::string(), $isMultiple),
        'description' => $description,
      ];
    }
    elseif ($typedData instanceof LanguageItem) {
      return [
        'type' => $this->wrapTypeList($this->getType('LanguageInterface'), $isMultiple),
        'description' => $description,
      ];
    }
    elseif ($typedData instanceof IntegerItem) {
      return [
        'type' => $this->wrapTypeList(Type::int(), $isMultiple),
        'description' => $description,
      ];
    }
    elseif ($typedData instanceof NumericItemBase) {
      return [
        'type' => $this->wrapTypeList(Type::float(), $isMultiple),
        'description' => $description,
      ];
    }
    elseif ($typedData instanceof BooleanItem) {
      return [
        'type' => $this->wrapTypeList(Type::boolean(), $isMultiple),
        'description' => $description,
      ];
    }
    elseif ($typedData instanceof EntityReferenceItem && !$typedData instanceof FileItem) {
      $type = $this->getTypeForEntityReferenceFieldItem($itemDefinition, $isMultiple);
      if ($type) {
        return [
          'type' => fn () => $this->wrapTypeList($this->types[$type] ?? $this->types['Entity'], $isMultiple),
          'description' => $description,
        ];
      }
      // The entity type that is referenced is not enabled, so we don't output
      // this field at all.
      return NULL;
    }
    elseif ($typedData instanceof TextWithSummaryItem) {
      return [
        'type' => $this->wrapTypeList(Type::string(), $isMultiple),
        'description' => $description,
        'args' => [
          'summary' => Type::boolean(),
        ],
      ];
    }
    elseif ($typedData instanceof TextItemBase) {
      return [
        'type' => $this->wrapTypeList(Type::string(), $isMultiple),
        'description' => $description,
      ];
    }
    elseif ($typedData instanceof TimestampItem) {
      return [
        'type' => $this->wrapTypeList(Type::string(), $isMultiple),
        'description' => $description,
      ];
    }
    elseif ($typedData instanceof MapItem) {
      return [
        'type' => $this->wrapTypeList($this->mapDataType, $isMultiple),
        'description' => $description,
      ];
    }

    // The field type is not scalar, try to get the GraphQL type for this item
    // definition.
    $itemType = $this->getFieldItemType($itemDefinition);
    if ($itemType) {
      return [
        'type' => $this->wrapTypeList($itemType, $isMultiple),
        'description' => $description,
      ];
    }

    return NULL;
  }

  /**
   * Generate the GraphQL type for an entity reference field item.
   *
   * @param \Drupal\Core\Field\TypedData\FieldItemDataDefinitionInterface $itemDefinition
   *   The field definition.
   *
   * @return string|null
   *   The name of the referenced type.
   */
  private function getTypeForEntityReferenceFieldItem(FieldItemDataDefinitionInterface $itemDefinition): ?string {
    $targetType = $itemDefinition->getSetting('target_type');

    // Check if the target entity type is enabled.
    if (!in_array($targetType, $this->enabledEntityTypes)) {
      return NULL;
    }

    // Get the target bundles that can be referenced. This value is a bit
    // random, either a string or an array.
    $handlerSettings = $itemDefinition->getSetting('handler_settings') ?? [];
    $targetBundles = $handlerSettings['target_bundles'] ?? [];
    if (is_string($targetBundles)) {
      $targetBundles = [$targetBundles];
    }
    $targetBundles = array_values($targetBundles);

    // Handle case where target bundles have been defined.
    if (!empty($targetBundles)) {
      // Any or more than 1 target bundles allowed. The field type will be the
      // entity type.
      if (count($targetBundles) > 1) {
        return EntitySchemaHelper::toPascalCase([$targetType]);
      }

      // If the target bundle is the same as the type we ignore it.
      if ($targetType !== $targetBundles[0]) {
        // Only a single bundle is allowed. The field type will be this specific
        // bundle.
        return EntitySchemaHelper::toPascalCase([
          $targetType,
          $targetBundles[0],
        ]);
      }
    }

    if ($targetType) {
      return EntitySchemaHelper::toPascalCase([$targetType]);
    }

    return NULL;
  }

  /**
   * Build the FieldItemList type for a field type.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   The field definition.
   *
   * @return \GraphQL\Type\Definition\Type|null
   *   The GraphQL type.
   */
  private function getFieldItemListType(FieldDefinitionInterface $fieldDefinition): ?Type {
    // e.g. string, boolean, email, string_long, text_with_summary.
    $fieldTypeName = $fieldDefinition->getType();

    // e.g. FieldItemListEmail.
    $graphqlTypeName = EntitySchemaHelper::toPascalCase([
      'field_item_list_',
      $fieldTypeName,
    ]);

    // Type was already generated.
    if (!empty($this->types[$graphqlTypeName])) {
      return $this->types[$graphqlTypeName];
    }

    $fields = [
      ...$this->types['FieldItemList']->getFields(),
    ];

    $itemDefinition = $fieldDefinition->getItemDefinition();
    if ($itemDefinition instanceof FieldItemDataDefinition) {
      $fieldItemType = $this->getFieldItemType($itemDefinition);
      if ($fieldItemType) {
        $fields['list'] = [
          'type' => fn() => Type::listOf($fieldItemType),
          'description' => 'Array of field items.',
        ];
        $fields['first'] = [
          'type' => fn() => $fieldItemType,
          'description' => 'The first field item.',
        ];
      }
    }
    if (empty($fields)) {
      return NULL;
    }

    $type = [
      'name' => $graphqlTypeName,
      'interfaces' => [
        fn() => $this->types['FieldItemList'],
      ],
      'fields' => $fields,
      'description' => (string) $fieldDefinition->getLabel(),
    ];

    $objectType = new ObjectType($type);
    $this->types[$graphqlTypeName] = $objectType;
    return $this->types[$graphqlTypeName];
  }

  /**
   * Check if the given entity reference field should be added.
   */
  private function shouldAddField(string $fieldName, FieldDefinitionInterface $definition) {
    if (in_array($fieldName, self::EXCLUDED_ENTITY_FIELDS)) {
      return FALSE;
    }
    $fieldType = $definition->getType();
    if (in_array($fieldType, self::EXCLUDED_TYPES)) {
      return FALSE;
    }
    if ($fieldType === 'entity_reference' || $fieldType === 'entity_reference_revisions') {
      $targetType = $definition->getSetting('target_type');
      return $this->entityTypeIsEnabled($targetType);
    }

    return TRUE;
  }

  /**
   * Build the type for a field item definition.
   *
   * @param \Drupal\Core\Field\TypedData\FieldItemDataDefinitionInterface $itemDefinition
   *   The field item data definition.
   *
   * @return \GraphQL\Type\Definition\Type|null
   *   The GraphQL type if available.
   */
  private function getFieldItemType(FieldItemDataDefinitionInterface $itemDefinition): ?Type {
    $fieldDefinition = $itemDefinition->getFieldDefinition();
    // The type, e.g. string, text_with_summary, email, telephone.
    $type = $fieldDefinition->getType();

    // e.g. FielditemTypeTextWithSummary.
    $graphqlDataTypeName = EntitySchemaHelper::toPascalCase(
      ['field_item_type_', $type]
    );

    // Type has already been generated.
    if (!empty($this->types[$graphqlDataTypeName])) {
      return $this->types[$graphqlDataTypeName];
    }

    $propertyDefinitions = $itemDefinition->getPropertyDefinitions();

    // Is set to TRUE if there is a "value" property of type "string".
    $hasStringValue = FALSE;
    // Is set to TRUE if there is a "value" property of type "integer".
    $hasIntegerValue = FALSE;

    // Array of all the fields of this type.
    $fields = [];

    foreach ($propertyDefinitions as $name => $propertyDefinition) {
      if ($propertyDefinition instanceof DataDefinition) {
        $propertyFieldType = $this->getDataPropertyType($propertyDefinition->toArray());
        // Field item types that share the same value field with the same type
        // all get an additional interface.
        if ($name === 'value') {
          if ($propertyFieldType instanceof StringType) {
            $hasStringValue = TRUE;
          }
          elseif ($propertyFieldType instanceof IntType) {
            $hasIntegerValue = TRUE;
          }
        }
        if ($propertyFieldType) {
          $propertyFieldName = EntitySchemaHelper::toCamelCase($name);
          // If a field with the same name has alrady been generated, use the
          // original Drupal name instead. The conversion from snake to camel
          // case can result in two snake case field names having the same
          // camel case string.
          if (!empty($fields[$propertyFieldName])) {
            $propertyFieldName = $name;
          }
          $fields[$propertyFieldName] = [
            'type' => fn() => $propertyFieldType,
            'description' => (string) $propertyDefinition->getLabel() . " [field: $name]",
          ];
        }
      }
    }

    // Field item types are always generated, even if no fields have been
    // derived. This is so that schema extensions can easily extend these types
    // by implementing missing fields.
    $typedData = $this->getTypedDataManager()->create($itemDefinition);
    $interfaces = [
      fn() => $this->types['FieldItemType'],
    ];

    // Add additional interfaces for certain field item types.
    // Interface for timestamp/date field items.
    if ($typedData instanceof TimestampItem || $typedData instanceof DateTimeItem) {
      if (empty($this->types['FieldItemTypeTimestampInterface'])) {
        $this->createInterface(
          'FieldItemTypeTimestampInterface',
          'Interface for field item types with a timestamp value.', [
            'value' => Type::string(),
          ]
        );
      }
      $interfaces[] = fn() => $this->types['FieldItemTypeTimestampInterface'];
    }

    // Interface for field item types whose "value" field is a string.
    if ($hasStringValue) {
      if (empty($this->types['FieldItemTypeStringInterface'])) {
        $this->createInterface(
          'FieldItemTypeStringInterface',
          'Base class for string field types.', [
            'value' => Type::string(),
          ]
        );
      }
      $interfaces[] = fn() => $this->types['FieldItemTypeStringInterface'];
    }

    // Interface for field item types whose "value" field is an integer.
    if ($hasIntegerValue) {
      if (empty($this->types['FieldItemTypeIntegerInterface'])) {
        $this->createInterface(
          'FieldItemTypeIntegerInterface',
          'Interface for field item types with an integer value.', [
            'value' => Type::int(),
          ]
        );
      }
      $interfaces[] = fn() => $this->types['FieldItemTypeIntegerInterface'];
    }

    $type = new ObjectType([
      'name' => $graphqlDataTypeName,
      'interfaces' => $interfaces,
      'fields' => [...$fields, ...$this->types['FieldItemType']->getFields()],
      'description' => (string) $itemDefinition->getLabel(),
    ]);

    $this->types[$graphqlDataTypeName] = $type;
    return $this->types[$graphqlDataTypeName];
  }

  /**
   * Add types for the entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type.
   * @param \Drupal\Core\Field\FieldDefinitionInterface[] $fieldDefinitions
   *   The base field definitions of the entity type.
   */
  public function addContentEntityType(EntityTypeInterface $entityType, array $fieldDefinitions): Type|null {
    $typeName = EntitySchemaHelper::toPascalCase([$entityType->id()]);
    $hasBundles = $entityType->hasKey('bundle');
    $description = (string) $entityType->getLabel();

    $interfaces = $this->getInterfacesForEntityType($entityType);
    $fields = $this->mergeInterfaceFields(
      $this->createEntityFields($entityType->id(), $fieldDefinitions),
      $interfaces
    );

    if ($hasBundles) {
      return $this->createInterface($typeName, $description, $fields, $interfaces);
    }

    $this->types[$typeName] = new ObjectType([
      'name' => $typeName,
      'interfaces' => $interfaces,
      'fields' => $fields,
      'description' => $description,
    ]);

    return $this->types[$typeName];
  }

  /**
   * Generate a GraphQL type for an entity type.
   *
   * @param string $entityTypeId
   *   The entity type ID.
   *
   * @return Type|null
   *   The generated GraphQL type.
   */
  public function generateTypeForEntityType(string $entityTypeId): Type|null {
    // Don't generate types for disabled entity types.
    if (!$this->entityTypeIsEnabled($entityTypeId)) {
      return NULL;
    }

    $graphqlTypeName = EntitySchemaHelper::toPascalCase($entityTypeId);
    $existingType = $this->getType($graphqlTypeName);
    if ($existingType) {
      return $existingType;
    }

    $entityType = $this->entityTypeDefinitions[$entityTypeId] ?? NULL;

    if (!$entityType) {
      return NULL;
    }

    if ($entityType instanceof ConfigEntityTypeInterface) {
      $mapping = $this->getConfigEntityMapping($entityType);
      return $this->addConfigEntityType($entityType, $mapping);
    }
    else {
      $hasBundles = $entityType->hasKey('bundle');
      $fieldDefinitions = $hasBundles
          ? $this->getEntityFieldManager()->getBaseFieldDefinitions($entityTypeId)
          : $this->getEntityFieldManager()->getFieldDefinitions($entityTypeId, $entityTypeId);

      $generatedType = $this->addContentEntityType($entityType, $fieldDefinitions);

      if ($generatedType && $hasBundles) {
        $bundles = $this->getEntityTypeBundleInfo()->getBundleInfo($entityTypeId);
        foreach (array_keys($bundles) as $bundleId) {
          $bundleFieldDefinitions = $this->getEntityFieldManager()->getFieldDefinitions($entityTypeId, $bundleId);
          $this->addContentEntityBundleType($entityType, $bundleId, $bundles[$bundleId], $bundleFieldDefinitions);
        }
      }

      return $generatedType;
    }

    return NULL;
  }

  /**
   * Add types for the configuration entity type.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityType $entityType
   *   The config entity type ID.
   * @param array $mapping
   *   The schema mapping for the config entity.
   *
   * @return Type|null
   *   The generated GraphQL type.
   */
  public function addConfigEntityType(ConfigEntityType $entityType, array $mapping): Type|null {
    $entityTypeId = $entityType->id();
    $typeName = EntitySchemaHelper::toPascalCase([$entityTypeId]);
    $fields = [
      ...$this->types['Entity']->getFields(),
    ];

    foreach ($mapping as $propertyName => $definition) {
      $graphqlFieldName = EntitySchemaHelper::toCamelCase($propertyName);
      if (in_array($propertyName, self::EXCLUDED_ENTITY_FIELDS)) {
        continue;
      }
      if (!$this->fieldIsEnabled($entityTypeId, $propertyName)) {
        continue;
      }

      $type = $this->getDataPropertyType($definition);
      if ($type) {
        if (!empty($fields[$graphqlFieldName])) {
          $graphqlFieldName = $propertyName;
        }
        $fields[$graphqlFieldName] = [
          'type' => fn() => $type,
          'description' => "[field: $propertyName]",
        ];
      }
    }
    $interfaces = [
      $this->types['Entity'],
    ];

    if ($entityTypeId === 'field_config' && $this->extensionEnabled('field_config')) {
      $interfaces[] = $this->types['FieldDefinition'];
      $fields = array_merge($fields, $this->types['FieldDefinition']->getFields());
    }
    elseif ($entityTypeId === 'configurable_language') {
      $interfaces[] = $this->types['LanguageInterface'];
      $fields = array_merge($fields, $this->types['LanguageInterface']->getFields());
    }

    $type = new ObjectType([
      'name' => $typeName,
      'interfaces' => $interfaces,
      'fields' => $fields,
      'description' => (string) $entityType->getLabel(),
    ]);

    $this->types[$typeName] = $type;
    return $type;
  }

  /**
   * Add types for the entity bundle type.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entityType
   *   The entity type.
   * @param string $bundleId
   *   The bundle ID.
   * @param array $bundleInfo
   *   The bundle info.
   * @param \Drupal\Core\Field\FieldDefinitionInterface[] $fieldDefinitions
   *   The field definitions of the bundle.
   */
  public function addContentEntityBundleType(ContentEntityTypeInterface $entityType, string $bundleId, array $bundleInfo, array $fieldDefinitions) {
    $entityTypeId = $entityType->id();
    $entityTypeName = EntitySchemaHelper::toPascalCase([$entityTypeId]);
    $bundleTypeName = EntitySchemaHelper::toPascalCase(
      [$entityTypeId, $bundleId]
    );

    $interfaces = $this->getInterfacesForEntityType($entityType);
    $interfaces[] = $this->types[$entityTypeName];
    $fields = $this->mergeInterfaceFields(
      $this->createEntityFields($entityType->id(), $fieldDefinitions),
      $interfaces
    );

    // Add the interface and fields for a translatable entity.
    // We do this separately so that we can use the entity's
    // type as the type for both fields.
    if (!empty($bundleInfo['translatable'])) {
      $interfaces[] = $this->types['EntityTranslatable'];
      $fields['translations'] = [
        'type' => fn() => Type::listOf($this->types[$bundleTypeName]),
      ];
      $fields['translation'] = [
        'type' => fn() => $this->types[$bundleTypeName],
        'args' => [
          'langcode' => fn() => Type::nonNull($this->types['Langcode']),
          'fallback' => [
            'type' => Type::boolean(),
            'description' => 'Return entity in current language if translation language does not exist.',
          ],
        ],
      ];
    }

    $type = new ObjectType([
      'name' => $bundleTypeName,
      'interfaces' => $interfaces,
      'fields' => $fields,
      'description' => (string) $bundleInfo['label'] ?? NULL,
    ]);

    $this->types[$bundleTypeName] = $type;
  }

  /**
   * Get interfaces for the entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type.
   *
   * @return \GraphQL\Type\Definition\InterfaceType[]
   *   The interfaces.
   */
  public function getInterfacesForEntityType(EntityTypeInterface $entityType): array {
    $pairs = [
      '\Drupal\Core\Entity\EntityDescriptionInterface' => 'EntityDescribable',
    ];

    $interfaces = [
      $this->types['Entity'],
    ];

    foreach ($pairs as $dependency => $interface) {
      if ($entityType->entityClassImplements($dependency)) {
        $interfaces[] = $this->types[$interface];
      }
    }

    $isLinkable = !empty($entityType->getLinkTemplates());
    if ($isLinkable) {
      $interfaces[] = $this->types['EntityLinkable'];
    }

    return $interfaces;
  }

  /**
   * Merge fields from the given interfaces with the base fields.
   *
   * @param array $fields
   *   The type fields.
   * @param \GraphQL\Type\Definition\InterfaceType[] $interfaces
   *   The interfaces.
   *
   * @return array
   *   The type fields merged with the interface fields.
   */
  public function mergeInterfaceFields(array $fields, array $interfaces): array {
    $mergedFields = $fields;

    foreach ($interfaces as $interface) {
      $mergedFields = array_merge($mergedFields, $interface->getFields());
    }

    return $mergedFields;
  }

  /**
   * Create GraphQL fields given the entity field definitions.
   *
   * @param string $entityTypeId
   *   The entity type ID the fields belong to.
   * @param \Drupal\Core\Field\FieldDefinitionInterface[] $fieldDefinitions
   *   The field definitions.
   *
   * @return array
   *   The array of GraphQL fields.
   */
  private function createEntityFields(string $entityTypeId, array $fieldDefinitions): array {
    $fields = [];
    foreach ($fieldDefinitions as $fieldName => $definition) {
      // Try to get the type first. This way we can make sure that we generate
      // a type for every field type, even if no entity has a field with that
      // type.
      $type = $this->getFieldItemListType($definition);

      if (!$this->fieldIsEnabled($entityTypeId, $fieldName)) {
        continue;
      }
      if (!$this->shouldAddField($fieldName, $definition)) {
        continue;
      }
      if ($type) {
        $graphqlFieldName = EntitySchemaHelper::toCamelCase(
          [$fieldName, '_raw_field']
        );
        if (!empty($fields[$graphqlFieldName])) {
          $graphqlFieldName = $fieldName . 'RawField';
        }
        $fields[$graphqlFieldName] = [
          'type' => $type,
          'description' => (string) $definition->getDescription() . " [field: $fieldName]",
        ];
      }
      if ($this->generateValueFields) {
        $valueField = $this->buildGraphqlValueField($definition);
        if ($valueField) {
          $valueFieldName = EntitySchemaHelper::toCamelCase($fieldName);
          if (!empty($fields[$valueFieldName])) {
            $valueFieldName = $fieldName;
          }
          $fields[$valueFieldName] = $valueField;
        }
      }
    }

    return $fields;
  }

  /**
   * Get the GraphQL type for a data property definition.
   *
   * This is the lowest possible leaf in the entity schema. It usually resolves
   * to a scalar, but special handling is implemented for sequence types and
   * entity reference types.
   *
   * @param array $definition
   *   The property definition.
   *
   * @return \GraphQL\Type\Definition\Type|null
   *   The GraphQL type.
   */
  protected function getDataPropertyType(array $definition) {
    $type = $definition['type'];

    if (in_array($type, self::EXCLUDED_TYPES)) {
      return NULL;
    }

    // Basic types.
    switch ($type) {
      case 'string':
      case 'email':
      case 'text':
      case 'label':
      case 'path':
      case 'color_hex':
      case 'date_form':
      case 'filter_format':
      case 'datetime_iso8601':
      case 'timestamp':
        return Type::string();

      case 'boolean':
        return Type::boolean();

      case 'integer':
        return Type::int();

      case 'float':
        return Type::float();

      case 'uri':
        return $this->types['Url'];

      case 'config_dependencies':
        return $this->mapDataType;

      case '_core_config_info':
        return NULL;

      case 'entity_reference':
      case 'entity_revision_reference':
        $targetEntityType = $definition['constraints']['EntityType'] ?? NULL;
        if ($targetEntityType) {
          $generatedType = $this->generateTypeForEntityType($targetEntityType);
          if ($generatedType) {
            return $generatedType;
          }
        }
        return $this->types['Entity'];

      case 'language_reference':
        return $this->types['LanguageInterface'];
    }

    // A sequence is an array of items.
    if ($type === 'sequence' && !empty($definition['sequence']['type'])) {
      // Try to find a possible type for the sequence.
      $sequenceType = $this->getDataPropertyType($definition['sequence']);
      if ($sequenceType) {
        return Type::listOf($sequenceType);
      }
      return NULL;
    }

    // Try to find a matching data definition for this type.
    if ($this->getTypedDataManager()->hasDefinition($type)) {
      $dataDefinition = $this->getTypedDataManager()->getDefinition($type);
      $instance = $this->getTypedDataManager()->createDataDefinition($type);
      $typedData = $this->getTypedDataManager()->create($instance);

      if ($typedData instanceof StringData) {
        return Type::string();
      }
      if ($instance instanceof ComplexDataDefinitionInterface) {
        $propertyDefinitions = $instance->getPropertyDefinitions();
        return $this->getComplexDataType($type, $propertyDefinitions);
      }
    }
    elseif ($this->getTypedConfigManager()->hasDefinition($type)) {
      $dataDefinition = $this->getTypedConfigManager()->getDefinition($type);
      $instance = $this->getTypedConfigManager()->createDataDefinition($type);

      // A mapping is basically a type that references another type.
      // The method being called here will eventually call this method again. If
      // the referenced map type again references a map type, it might end up
      // here a third time and so on. In the end we have eventually resolved to a
      // scalar type being returned above.
      // This allows us to fully resolve config schema types down to the last
      // property, if supported.
      if ($dataDefinition && $instance) {
        if (!empty($dataDefinition['mapping'])) {
          return $this->getTypeForMapping($type, $dataDefinition['mapping']);
        }
      }
    }

    return NULL;
  }

  /**
   * Try to infer the type for a mapping property.
   *
   * @param string $mappingName
   *   The name of the mapping.
   * @param array $mapping
   *   The mapping configuration.
   *
   * @return \GraphQL\Type\Definition\Type|null
   *   The GraphQL type if found.
   */
  private function getTypeForMapping(string $mappingName, array $mapping) {
    // E.g. DataTypeLinkitMatcher.
    $graphqlTypeName = $this->getGraphqlTypeNameForMapping($mappingName);

    // Type already generated.
    if (!empty($this->types[$graphqlTypeName])) {
      return $this->types[$graphqlTypeName];
    }
    // Type is being generated in this recursive callstack. Assume it will be
    // there when the schema is being resolved.
    if (in_array($graphqlTypeName, $this->generatedTypeNames)) {
      return fn() => $this->types[$graphqlTypeName];
    }

    // Store this type name in a special variable to make sure we don't end up
    // in an infinite loop.
    $this->generatedTypeNames[] = $graphqlTypeName;

    $mappingFields = [];
    foreach ($mapping as $mappingProperty => $mappingDefinition) {
      $mappingType = $this->getDataPropertyType($mappingDefinition);
      if ($mappingType) {
        $mappingFields[$mappingProperty] = $mappingType;
      }
    }
    if (!empty($mappingFields)) {
      $this->types[$graphqlTypeName] = new ObjectType([
        'name' => $graphqlTypeName,
        'fields' => $mappingFields,
        'description' => "The $mappingName schema mapping.",
      ]);
      return $this->types[$graphqlTypeName];
    }

    // Fallback to the MapData scalar so that the data is at least somehow
    // available.
    return $this->mapDataType;
  }

  /**
   * Try to generate a type for ComplexDataDefinition with properties.
   *
   * @param string $type
   *   The name of the complex data.
   * @param \Drupal\Core\TypedData\DataDefinition[] $propertyDefinitions
   *   The property defintions.
   *
   * @return \Graphql\Type\Definition\TypeWithFields|null
   *   The GraphQL type.
   */
  private function getComplexDataType(string $type, array $propertyDefinitions) {
    // e.g. DataTypeShipmentItem.
    $graphqlDataTypeName = EntitySchemaHelper::toPascalCase(
      ['data_type_', $type]
    );

    // Type has already been generated.
    if (!empty($this->types[$graphqlDataTypeName])) {
      return $this->types[$graphqlDataTypeName];
    }

    $fields = [];
    foreach ($propertyDefinitions as $name => $propertyDefinition) {
      if ($propertyDefinition instanceof DataDefinition) {
        $propertyFieldType = $this->getDataPropertyType($propertyDefinition->toArray());
        if ($propertyFieldType) {
          $propertyFieldName = EntitySchemaHelper::toCamelCase($name);
          $fields[$propertyFieldName] = [
            'type' => fn() => $propertyFieldType,
            'description' => (string) $propertyDefinition->getLabel(),
          ];
        }
      }
    }

    if (empty($fields)) {
      return NULL;
    }

    $type = new ObjectType([
      'name' => $graphqlDataTypeName,
      'fields' => $fields,
    ]);
    $this->types[$graphqlDataTypeName] = $type;
    return $this->types[$graphqlDataTypeName];
  }

  /**
   * Create the enum for the date formats.
   *
   * @param \Drupal\Core\Datetime\Entity\DateFormat[] $formats
   *   The date format entities.
   */
  public function addDateFormatEnum(array $formats) {
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter */
    $dateFormatter = \Drupal::service('date.formatter');
    $timestamp = 1668433941;

    $values = [];
    foreach ($formats as $format) {
      $id = (string) $format->id();
      $key = strtoupper(EntitySchemaHelper::toSnakeCase($id));
      $description = $dateFormatter->format($timestamp, $id);
      $values[$key] = [
        'value' => $id,
        'description' => $description,
      ];
    }
    $enum = new EnumType([
      'name' => 'DrupalDateFormat',
      'values' => $values,
    ]);
    $this->types['DrupalDateFormat'] = $enum;
  }

  /**
   * Create the enum for the entity types.
   *
   * @param \Drupal\Core\Entity\EntityType[] $types
   *   The entity type definitions.
   */
  public function addEntityTypeEnum(array $types) {
    $values = [];
    foreach ($types as $type) {
      $key = (string) $type->id();
      if (in_array($key, $this->enabledEntityTypes)) {
        $key = strtoupper(EntitySchemaHelper::toSnakeCase($key));
        $values[$key] = [
          'value' => $type->id(),
          'description' => (string) $type->getLabel(),
        ];
      }
    }
    $enum = new EnumType([
      'name' => 'EntityType',
      'values' => $values,
    ]);
    $this->types['EntityTypeEnum'] = $enum;
  }

  /**
   * Create the enum for the langcodes.
   *
   * @param \Drupal\Core\Language\Language[] $languages
   *   The entity type definitions.
   */
  public function addLangcodeEnum(array $languages) {
    $values = [];
    foreach ($languages as $language) {
      $key = (string) $language->getId();
      $key = strtoupper(EntitySchemaHelper::toSnakeCase($key));
      $values[$key] = [
        'value' => $language->getId(),
        'description' => $language->getName(),
      ];
    }
    $enum = new EnumType([
      'name' => 'Langcode',
      'values' => $values,
    ]);
    $this->types['Langcode'] = $enum;
  }

  /**
   * Get the generated schema.
   *
   * @return string
   *   The generated schema.
   */
  public function getGeneratedSchema() {
    $schema = new Schema([
      'types' => $this->types,
    ]);

    $result = SchemaPrinter::doPrint($schema);
    return $result;
  }

  /**
   * Return the generated entity type types.
   *
   * @return string[]
   *   Array of all generated GraphQL entity types.
   */
  public function getGeneratedTypes(): array {
    return array_keys($this->types);
  }

  /**
   * Helper to wrap a type in a list type (when it should resolve to an array).
   *
   * @param \GraphQL\Type\Definition\Type $type
   *   The GraphQL type.
   * @param bool $isList
   *   TRUE if the type should be wrapped as a list.
   *
   * @return \GraphQL\Type\Definition\Type
   *   The type, wrapped as a list type if desired.
   */
  protected function wrapTypeList(Type $type, bool $isList): Type {
    if ($isList && $type) {
      return Type::listOf($type);
    }
    return $type;
  }

  /**
   * Get the GraphQL type name for a schema type mapping.
   *
   * @param string $mappingName
   *   The name of the mapping.
   *
   * @return string
   *   The GraphQL type name.
   */
  protected function getGraphqlTypeNameForMapping(string $mappingName) {
    return EntitySchemaHelper::toPascalCase([
      'data_type_',
      $mappingName,
    ]);
  }

}
