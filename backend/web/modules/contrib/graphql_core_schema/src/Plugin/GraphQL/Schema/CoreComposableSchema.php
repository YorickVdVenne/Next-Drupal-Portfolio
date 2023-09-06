<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\Schema;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Render\Element\Checkboxes;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\Schema\ComposableSchema;
use Drupal\graphql\Plugin\SchemaExtensionPluginInterface;
use Drupal\graphql\Plugin\SchemaExtensionPluginManager;
use Drupal\graphql_core_schema\CoreComposableResolver;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;
use Drupal\graphql_core_schema\CoreSchemaInterfaceExtensionInterface;
use Drupal\graphql_core_schema\EntitySchemaBuilder;
use Drupal\graphql_core_schema\TypeAwareSchemaExtensionInterface;
use Drupal\typed_data\DataFetcherTrait;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Utils\SchemaExtender;
use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extendable core schema.
 *
 * @Schema(
 *   id = "core_composable",
 *   name = "Core Composable Schema"
 * )
 */
class CoreComposableSchema extends ComposableSchema {

  use TypedDataTrait;
  use DataFetcherTrait;
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Bundle info manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Array of generated GraphQL types.
   *
   * The types are only present if the schema is being generated. In a
   * normal production environment this is empty, beccause it's only needed
   * when the schema is extended.
   *
   * @var string[]
   */
  protected $generatedTypes;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.graphql.ast'),
      $container->get('module_handler'),
      $container->get('plugin.manager.graphql.schema_extension'),
      $container->getParameter('graphql.config'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_field.manager'),
      $container->get('config.typed'),
      $container->get('language_manager')
    );
  }

  /**
   * The constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Cache\CacheBackendInterface $astCache
   *   The cache bin for caching the parsed SDL.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\graphql\Plugin\SchemaExtensionPluginManager $extensionManager
   *   The schema extension plugin manager.
   * @param array $config
   *   The service configuration.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    array $pluginDefinition,
    CacheBackendInterface $astCache,
    ModuleHandlerInterface $moduleHandler,
    SchemaExtensionPluginManager $extensionManager,
    array $config,
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    EntityFieldManagerInterface $entityFieldManager,
    TypedConfigManagerInterface $typedConfigManager,
    LanguageManagerInterface $languageManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $astCache, $moduleHandler, $extensionManager, $config);

    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->entityFieldManager = $entityFieldManager;
    $this->typedConfigManager = $typedConfigManager;
    $this->languageManager = $languageManager;
  }

  /**
   * Return an array of enabled entity types.
   *
   * @return string[]
   *   The enabled entity type IDs.
   */
  private function getEnabledEntityTypes() {
    return Checkboxes::getCheckedCheckboxes($this->configuration['enabled_entity_types'] ?? []);
  }

  /**
   * Return an array of enabled extension IDs.
   *
   * @return string[]
   *   The enabled extension IDs.
   */
  private function getEnabledExtensions() {
    return Checkboxes::getCheckedCheckboxes($this->configuration['extensions'] ?? []);
  }

  /**
   * Get the enabled entity fields.
   *
   * @return string[]
   *   The enabled entity fields, keyed by entity type.
   */
  private function getEnabledEntityFields() {
    return $this->configuration['fields'] ?? [];
  }

  /**
   * Get the enabled base entity fields.
   *
   * @return string[]
   *   The enabled base entity fields.
   */
  private function getEnabledBaseEntityFields() {
    return Checkboxes::getCheckedCheckboxes($this->configuration['entity_base_fields']['fields'] ?? []);
  }

  /**
   * Return if the value fields should be generated.
   *
   * @return bool
   *   The value fields should be generated.
   */
  private function shouldGenerateValueFields() {
    return !empty($this->configuration['generate_value_fields']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition(): string {
    $enabledEntityTypes = $this->getEnabledEntityTypes();
    $enabledExtensions = $this->getEnabledExtensions();
    $enabledFields = $this->getEnabledEntityFields();
    $enabledBaseEntityFields = $this->getEnabledBaseEntityFields();

    // Throw an exception here because the schema is broken if no entity type
    // is enabled.
    if (empty($enabledEntityTypes)) {
      throw new \Exception('At least one entity type must be enabled for the schema to work properly.');
    }

    // Collect entity interface extenders.
    $interfaceExtenders = [];
    $extensions = $this->getExtensions();

    foreach ($extensions as $extension) {
      if ($extension instanceof CoreSchemaInterfaceExtensionInterface) {
        $extender = $extension->getInterfaceExtender();
        $interfaceExtenders = array_merge_recursive($interfaceExtenders, $extender);
      }
    }

    $entityTypeDefintions = $this->entityTypeManager->getDefinitions();
    $schemaBuilder = new EntitySchemaBuilder(
      $this->shouldGenerateValueFields(),
      $enabledEntityTypes,
      $enabledExtensions,
      $enabledFields,
      $enabledBaseEntityFields,
      $interfaceExtenders,
      $entityTypeDefintions
    );

    $schemaBuilder->addEntityTypeEnum($entityTypeDefintions);

    foreach (array_keys($entityTypeDefintions) as $typeId) {
      $schemaBuilder->generateTypeForEntityType($typeId);
    }

    // Add date format enums.
    $dateFormatStorage = $this->entityTypeManager->getStorage('date_format');
    $dateFormats = array_values($dateFormatStorage->loadMultiple());
    $schemaBuilder->addDateFormatEnum($dateFormats);

    // Add langcode enums.
    $languages = $this->languageManager->getLanguages();
    $schemaBuilder->addLangcodeEnum($languages);

    $this->generatedTypes = $schemaBuilder->getGeneratedTypes();

    return $schemaBuilder->getGeneratedSchema();
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
    $typedConfigDefinition = $this->typedConfigManager->getDefinition($configPrefix . '.*');
    $mapping = $typedConfigDefinition['mapping'] ?? [];
    if (empty($mapping)) {
      $typedConfigDefinition = $this->typedConfigManager->getDefinition($configPrefix . '.*.*');
      $mapping = $typedConfigDefinition['mapping'] ?? [];
    }
    if (empty($mapping)) {
      $typedConfigDefinition = $this->typedConfigManager->getDefinition($configPrefix . '.*.*.*');
      $mapping = $typedConfigDefinition['mapping'] ?? [];
    }

    return $mapping;
  }

  /**
   * {@inheritdoc}
   */
  protected function getExtensions() {
    return array_map(function ($id) {
      $extensionConfiguration = $this->configuration['extension_' . $id] ?? [];
      if ($this->extensionManager->hasDefinition($id)) {
        return $this->extensionManager->createInstance($id, $extensionConfiguration);
      }
    }, array_filter($this->getConfiguration()['extensions'] ?? []));
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Adds the extensions form element.
    $form = parent::buildConfigurationForm($form, $form_state);

    // Sort list of extensions alphabetically.
    ksort($form['extensions']['#options']);

    $form['generate_value_fields'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable value fields'),
      '#description' => $this->t('Value fields directly return a scalar or entity type instead of a FieldItemList type.'),
      '#default_value' => $this->configuration['generate_value_fields'] ?? FALSE,
    ];

    $extensions = $this->getExtensions();
    foreach ($extensions as $extension) {
      if ($extension instanceof PluginFormInterface) {
        $subformKey = 'extension_' . $extension->getBaseId();
        $form[$subformKey] = [
          '#type' => 'details',
          '#title' => $extension->getPluginDefinition()['name'],
        ];
        $subform_state = SubformState::createForSubform($form[$subformKey], $form, $form_state);
        $form[$subformKey] = $extension->buildConfigurationForm($form[$subformKey], $subform_state);
      }
    }

    $form['entity_base_fields'] = [
      '#type' => 'details',
      '#title' => $this->t('Enabled entity base fields'),
    ];

    $form['entity_base_fields']['fields'] = [
      '#type' => 'tableselect',
      '#sticky' => TRUE,
      '#caption' => $this->t('Select the fields you want to enable on all entity types.'),
      '#header' => [
        'name' => $this->t('Field'),
        'description' => $this->t('Description'),
      ],
      '#options' => [],
      '#default_value' => $this->configuration['entity_base_fields']['fields'] ?? [],
    ];

    $baseEntityFieldDefinitions = EntitySchemaBuilder::BASE_ENTITY_FIELDS;
    foreach ($baseEntityFieldDefinitions as $key => $description) {
      $form['entity_base_fields']['fields']['#options'][$key] = [
        'name' => $key,
        'description' => $description,
      ];
    }

    $entityTypeDefintions = $this->entityTypeManager->getDefinitions();
    ksort($entityTypeDefintions);

    $form['enabled_entity_types'] = [
      '#type' => 'details',
      '#title' => $this->t('Enabled entity types'),
    ];

    foreach ($entityTypeDefintions as $key => $type) {
      $label = $type->getLabel();
      $form['enabled_entity_types'][$key] = [
        '#id' => $key,
        '#type' => 'checkbox',
        '#title' => $key . " ($label)",
        '#default_value' => $this->configuration['enabled_entity_types'][$key] ?? FALSE,
        '#ajax' => [
          'callback' => [$this, 'reloadFields'],
          'disable-refocus' => FALSE,
          'event' => 'change',
          'wrapper' => 'field-wrapper',
          'progress' => [
            'type' => 'throbber',
            'message' => $this->t('Reloading fields...'),
          ],
        ],
      ];
    }

    $this->buildEntityFieldForm($form, $form_state);

    $form['#attached']['library'][] = 'graphql_core_schema/tweaks';
    return $form;
  }

  /**
   *
   * Ajax Callback for form reload.
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function reloadFields(array &$form, FormStateInterface $form_state) {
    return $form['schema_configuration']['core_composable']['fields'];
  }

  /**
   * Build the enabled field form.
   */
  private function buildEntityFieldForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // Use the form state to rebuild the options if there was an AJAX call.
    if (!empty($values['schema_configuration']['core_composable']['enabled_entity_types'])) {
      $entityTypes = Checkboxes::getCheckedCheckboxes($values['schema_configuration']['core_composable']['enabled_entity_types']);
    }
    else {
      $entityTypes = $this->getEnabledEntityTypes();
    }

    $form['fields'] = [
      '#prefix' => '<div id="field-wrapper">',
      '#suffix' => '</div>',
      '#type' => 'details',
      '#title' => $this->t('Enabled fields'),
    ];

    foreach ($entityTypes as $entityTypeId) {
      $entityType = $this->entityTypeManager->getDefinition($entityTypeId);
      if (!$entityType) {
        continue;
      }

      $form['fields'][$entityTypeId] = [
        '#type' => 'tableselect',
        '#caption' => $entityTypeId . ' (' . $entityType->getLabel() . ')',
        '#sticky' => TRUE,
        '#header' => [
          'machine_name' => $this->t('Machine name'),
          'label' => $this->t('Label'),
          'type' => $this->t('Type'),
          'description' => $this->t('Description'),
        ],
        '#options' => [],
        '#default_value' => $this->configuration['fields'][$entityTypeId] ?? [],
        '#empty' => $this->t('No fields available'),
        '#attributes' => [
          'class' => ['graphql-core-schema-field-table'],
        ],
      ];

      if ($entityType instanceof ConfigEntityTypeInterface) {
        $mapping = $this->getConfigEntityMapping($entityType);
        ksort($mapping);
        foreach ($mapping as $fieldName => $definition) {
          $type = $definition['type'] ?? '';
          if (!in_array($fieldName, EntitySchemaBuilder::EXCLUDED_ENTITY_FIELDS) && !in_array($type, EntitySchemaBuilder::EXCLUDED_TYPES)) {
            $form['fields'][$entityTypeId]['#options'][$fieldName] = [
              'machine_name' => $fieldName,
              'label' => $definition['label'] ?? '',
              'type' => $type,
              'description' => '',
            ];
          }
        }
      }
      else {
        /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $definitions */
        $definitions = [];

        $fieldDefinitions = $this->entityFieldManager->getBaseFieldDefinitions($entityTypeId);
        foreach ($fieldDefinitions as $fieldDefinition) {
          $definitions[$fieldDefinition->getName()] = $fieldDefinition;
        }

        $bundles = $this->entityTypeBundleInfo->getBundleInfo($entityTypeId);
        foreach (array_keys($bundles) as $bundleId) {
          $bundleFieldDefinitions = $this->entityFieldManager->getFieldDefinitions($entityTypeId, $bundleId);
          foreach ($bundleFieldDefinitions as $bundleFieldDefinition) {
            $fieldName = $bundleFieldDefinition->getName();
            if (empty($definitions[$fieldName])) {
              $definitions[$fieldName] = $bundleFieldDefinition;
            }
          }
        }

        ksort($definitions);

        foreach ($definitions as $fieldName => $definition) {
          $type = $definition->getType();
          if (!in_array($fieldName, EntitySchemaBuilder::EXCLUDED_ENTITY_FIELDS) && !in_array($type, EntitySchemaBuilder::EXCLUDED_TYPES)) {
            $form['fields'][$entityTypeId]['#options'][$fieldName] = [
              'machine_name' => $fieldName,
              'label' => $definition->getLabel(),
              'type' => $type,
              'description' => $definition->getFieldStorageDefinition()->getDescription(),
            ];
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $formState): void {
    $values = $formState->getValues();
    $extensions = array_filter(array_values($formState->getValue('extensions')));
    $entityTypesArray = is_array($values['enabled_entity_types']) ? $values['enabled_entity_types'] : [];
    $entityTypes = Checkboxes::getCheckedCheckboxes($entityTypesArray);

    foreach ($extensions as $extensionId) {
      $instance = $this->extensionManager->createInstance($extensionId);
      if ($instance instanceof CoreSchemaExtensionInterface) {
        $requiredEntityIds = $instance->getEntityTypeDependencies();
        foreach ($requiredEntityIds as $entityId) {
          if (!in_array($entityId, $entityTypes)) {
            $element = $form['enabled_entity_types'][$entityId];
            $formState->setError(
              $element,
              $this->t('Extension "@extension" requires entity type "@type" to be enabled', [
                '@extension' => $instance->getBaseId(),
                '@type' => $entityId,
              ]
            ));
          }
        }

        $requiredExtensions = $instance->getExtensionDependencies();
        foreach ($requiredExtensions as $requiredExtensionId) {
          if (!in_array($requiredExtensionId, $extensions)) {
            $formState->setErrorByName(
              $requiredExtensionId . '_' . $extensionId,
              $this->t('Extension "@extension" requires extension "@dependency" to be enabled', [
                '@extension' => $instance->getBaseId(),
                '@dependency' => $requiredExtensionId,
              ]
            ));
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    // Create the registry and provide our default field and type resolvers.
    // As the name suggests these are called if no other field or type resolver
    // matched. This means that, to "override" the behavior of a field you can
    // just register your own resolver for this specific field.
    $registry = new ResolverRegistry(
      function ($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field) {
        // First access check.
        $valueChecked = $this->filterAccessible($value);
        if (empty($valueChecked)) {
          return $valueChecked;
        }
        $returnType = $info->returnType;
        $isArrayType = $returnType instanceof ListOfType;

        // @todo Is this a good idea? Had some problems with getting metatag
        // properties here due to leaked metadata, but also other fields that
        // leaked.
        $renderContext = new RenderContext();
        /** @var \Drupal\Core\Render\RendererInterface $renderer */
        $renderer = \Drupal::service('renderer');
        $result = $renderer->executeInRenderContext($renderContext, fn () => CoreComposableResolver::resolveFieldDefault($valueChecked, $args, $context, $info, $field));

        if (!$renderContext->isEmpty()) {
          $context->addCacheableDependency($renderContext->pop());
        }

        // Set cache dependencies.
        if ($result instanceof CacheableDependencyInterface) {
          $context->addCacheableDependency($result);
        }

        // Get the current language from the context.
        // If no language is set, set it from the current language.
        $language = $field->getContextValue('language');
        if (!$language) {
          $language = $this->languageManager->getCurrentLanguage()->getId();
          $field->setContextValue('language', $language);
        }
        $translated = $this->translateResolvedValue($result, $language, $isArrayType);

        // Second access check for the resolved result.
        // The resolveFieldDefault resolver does not perform any access checks
        // while for example resolving references. This is all done here.
        // It's important to note that this is NOT called when using custom
        // field resolvers (e.g. in schema extensions) return an entity.
        return $this->filterAccessible($translated, $isArrayType);
      },
      function ($value, ResolveContext $context, ResolveInfo $info) {
        return CoreComposableResolver::resolveTypeDefault($value, $context, $info);
      }
    );

    $builder = new ResolverBuilder();
    CoreComposableResolver::registerPingResolvers($registry, $builder);
    CoreComposableResolver::registerEntityResolvers($registry, $builder);
    CoreComposableResolver::registerFieldListResolvers($registry, $builder);
    CoreComposableResolver::registerLanguageResolvers($registry, $builder);
    CoreComposableResolver::registerUrlResolvers($registry, $builder);
    return $registry;
  }

  /**
   * Translate the resolved values.
   *
   * @param mixed $resolvedValue
   *   The resolved value.
   * @param string|null $language
   *   The target language.
   * @param bool $isArray
   *   If the resolved value is an array (in GraphQL terms).
   *
   * @return mixed
   *   The resolved value, translated.
   */
  protected function translateResolvedValue($resolvedValue, string $language, bool $isArray) {
    if ($isArray && is_array($resolvedValue)) {
      $translated = [];
      foreach ($resolvedValue as $item) {
        $translated[] = $this->translateResolvedValue($item, $language, FALSE);
      }
      return $translated;
    }

    if ($resolvedValue instanceof TranslatableInterface) {
      if ($resolvedValue->hasTranslation($language)) {
        return $resolvedValue->getTranslation($language);
      }
    }

    return $resolvedValue;
  }

  /**
   * Filter the input to only return accessible values.
   *
   * If the input is an AccessibleInterface object the method returns
   * either the object or NULL.
   * If the input is an array, it will iterate over the values and perform the
   * check if the values are AccessibleInterface objects. Those that fail the
   * check will be replaced with NULL.
   * Any other inputs are returned as is.
   *
   * @param mixed $value
   *   The input value, either object or an array.
   * @return mixed
   *   The filtered result.
   */
  private function filterAccessible($value) {
    if ($value instanceof AccessibleInterface) {
      if (!$value->access('view')) {
        return NULL;
      }
    }

    // Check arrays of "Accessible" objects.
    if (is_array($value)) {
      $checked = [];
      foreach ($value as $key => $item) {
        if ($item instanceof AccessibleInterface) {
          $checked[$key] = $item->access('view') ? $item : NULL;
        }
        else {
          $checked[$key] = $item;
        }
      }
      return $checked;
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema(ResolverRegistryInterface $registry) {
    $extensions = $this->getExtensions();
    $resolver = [$registry, 'resolveType'];
    $document = $this->getSchemaDocument($extensions);

    // Performance optimization.
    // Do not validate the schema on every request by passing the option: ['assumeValid' => true] to the build function.
    $options = ['assumeValid' => TRUE];

    $schema = BuildSchema::build($document, function ($config, TypeDefinitionNode $type) use ($resolver) {
      if ($type instanceof InterfaceTypeDefinitionNode || $type instanceof UnionTypeDefinitionNode) {
        $config['resolveType'] = $resolver;
      }

      return $config;
    }, $options);

    if (empty($extensions)) {
      return $schema;
    }

    foreach ($extensions as $extension) {
      $extension->registerResolvers($registry);
    }

    if ($extendSchema = $this->getExtensionDocument($extensions)) {
      // Generate the AST from the extended schema and save it to the cache.
      // This is important, because the Drupal graphql module is not caching the extended schema.
      // During schema extension, a very expensive function \GraphQL\Type\Schema::getTypeMap() is called.
      // Caching the AST of the extended schema improved greatly the performance.
      // This process will remove all directives, as this is still not supported by the SchemaPrinter.
      // See https://github.com/webonyx/graphql-php/issues/552
      $document = $this->getExtensionSchemaAst($schema, $extendSchema);
      $options = ['assumeValid' => TRUE];
      $extended_schema = BuildSchema::build($document, function ($config, TypeDefinitionNode $type) use ($resolver) {
        if ($type instanceof InterfaceTypeDefinitionNode || $type instanceof UnionTypeDefinitionNode) {
          $config['resolveType'] = $resolver;
        }
        return $config;
      }, $options);
      return $extended_schema;
    }

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchemaDocument(array $extensions = []) {
    // @todo Remove this function as soon as
    // https://github.com/drupal-graphql/graphql/pull/1314
    // is merged.
    $cid = "schema:{$this->getPluginId()}";
    if (empty($this->inDevelopment) && $cache = $this->astCache->get($cid)) {
      return $cache->data;
    }

    $extensionDefinitions = array_filter(
      array_map(function (SchemaExtensionPluginInterface $extension) {
        return $extension->getBaseDefinition();
      }, $extensions),
      function ($definition) {
        return !empty($definition);
      }
    );

    $schema = array_merge([$this->getSchemaDefinition()], $extensionDefinitions);

    // This option avoids WSOD / recursion issues.
    $options = ['noLocation' => TRUE];
    $ast = Parser::parse(implode("\n\n", $schema), $options);
    if (empty($this->inDevelopment)) {
      $this->astCache->set($cid, $ast, CacheBackendInterface::CACHE_PERMANENT, ['graphql']);
    }

    return $ast;
  }

  /**
   * {@inheritdoc}
   */
  protected function getExtensionDocument(array $extensions = []) {
    // Only use caching of the parsed document if we aren't in development mode.
    $cid = "extension:{$this->getPluginId()}";
    if (empty($this->inDevelopment) && $cache = $this->astCache->get($cid)) {
      return $cache->data;
    }

    $extensions = array_filter(array_map(function (SchemaExtensionPluginInterface $extension) {
      $extensionSchema = $extension->getExtensionDefinition();

      // Extensions implementing this interface can additionally extend the
      // schema conditionally. They get an array of all generated GraphQL types
      // as the first argument.
      if ($extension instanceof TypeAwareSchemaExtensionInterface) {
        $typeExtensionSchema = $extension->getTypeExtensionDefinition($this->generatedTypes);
        if ($typeExtensionSchema) {
          $extensionSchema .= "\n\n" . $typeExtensionSchema;
        }
      }

      return $extensionSchema;
    }, $extensions), function ($definition) {
      return !empty($definition);
    });

    $ast = !empty($extensions) ? Parser::parse(implode("\n\n", $extensions)) : NULL;
    if (empty($this->inDevelopment)) {
      $this->astCache->set($cid, $ast, CacheBackendInterface::CACHE_PERMANENT, ['graphql']);
    }

    return $ast;
  }

  /**
   * Get the AST from an extension.
   *
   * @param \GraphQL\Type\Schema $schema
   *   The base schema.
   * @param \GraphQL\Language\AST\DocumentNode $extendSchema
   *   The extension schema.
   *
   * @return \GraphQL\Language\AST\DocumentNode
   *   The AST of the schema.
   *
   * @throws \GraphQL\Error\Error
   * @throws \GraphQL\Error\SyntaxError
   */
  public function getExtensionSchemaAst(Schema $schema, DocumentNode $extendSchema) {
    $cid = "schema_extension:{$this->getPluginId()}";
    if (empty($this->inDevelopment) && $cache = $this->astCache->get($cid)) {
      return $cache->data;
    }

    $schema = SchemaExtender::extend($schema, $extendSchema);
    $schema_string = SchemaPrinter::doPrint($schema);
    $options = ['noLocation' => TRUE];
    $ast = Parser::parse($schema_string, $options);
    if (empty($this->inDevelopment)) {
      $this->astCache->set($cid, $ast, CacheBackendInterface::CACHE_PERMANENT, ['graphql']);
    }

    return $ast;
  }

}
