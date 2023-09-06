<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Render\Element\Checkboxes;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;
use Drupal\graphql_core_schema\EntitySchemaHelper;
use Drupal\graphql_core_schema\ViewsSchemaBuilder;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Plugin\views\pager\PagerPluginBase;
use Drupal\views\Plugin\views\sort\SortPluginBase;
use Drupal\views\ViewEntityInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The views schema extension.
 *
 * @SchemaExtension(
 *   id = "views",
 *   name = "Views",
 *   description = "An extension that provides integration with views.",
 *   schema = "core_composable"
 * )
 */
class ViewsExtension extends SdlSchemaExtensionPluginBase implements ContainerFactoryPluginInterface, ConfigurableInterface, PluginFormInterface, CoreSchemaExtensionInterface {

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
   * The field type plugin manager.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypeManager;

  /**
   * The type data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_field.manager'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('typed_data_manager'),
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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Instance of an entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   Instance of the entity bundle info service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $fieldTypePluginManager
   *   The field type plugin manager.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed data manager.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    array $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    EntityFieldManagerInterface $entityFieldManager,
    FieldTypePluginManagerInterface $fieldTypePluginManager,
    TypedDataManagerInterface $typedDataManager,
    TypedConfigManagerInterface $typedConfigManager,
    LanguageManagerInterface $languageManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $moduleHandler);

    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->entityFieldManager = $entityFieldManager;
    $this->fieldTypeManager = $fieldTypePluginManager;
    $this->typedDataManager = $typedDataManager;
    $this->typedConfigManager = $typedConfigManager;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return ['view'];
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration): void {
    $this->configuration = NestedArray::mergeDeep($this->defaultConfiguration(), $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['enabled_views'] = [
      '#type' => 'checkboxes',
      '#required' => FALSE,
      '#title' => $this->t('Enabled views'),
      '#options' => Views::getViewsAsOptions(),
      '#default_value' => $this->configuration['enabled_views'] ?? [],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $formState): void {
    // @todo Validate dependencies between extensions.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $formState): void {
    $value = $formState->getValue('enabled_views');
    $checked = Checkboxes::getCheckedCheckboxes($value);
    $formState->setValue('enabled_views', $checked);
  }

  /**
   * Get the enabled views.
   *
   * @return string[]
   *   Array of enabled views and displays.
   */
  public function getEnabledViewDisplays() {
    $configuration = $this->getConfiguration();
    return array_values(array_filter($configuration['enabled_views'] ?? []));
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseDefinition() {
    $schemaBuilder = new ViewsSchemaBuilder($this->getEnabledViewDisplays());
    $viewStorage = $this->entityTypeManager->getStorage('view');
    $views = $viewStorage->loadMultiple();

    /** @var \Drupal\views\ViewEntityInterface $view */
    foreach ($views as $view) {
      $schemaBuilder->generateViewType($view);
    }

    return $schemaBuilder->getGeneratedSchema();
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();
    $this->registerPagerResolvers($registry, $builder);
    $this->registerFilterResolvers($registry, $builder);
    $this->registerSortResolvers($registry, $builder);

    $registry->addTypeResolver('ViewExecutable', function ($executable) {
      if ($executable instanceof ViewExecutable) {
        if ($executable->executed) {
          return 'ViewExecutableResult';
        }
        return ViewsSchemaBuilder::getGraphqlTypeName($executable);
      }
    });

    $registry->addFieldResolver('View', 'executable',
      $builder->compose(
        // The check for enabled views.
        // That way implementors can still expose a view executable themselves
        // using the view_executable data producer.
        $builder->callback(function (ViewEntityInterface $view, $args) {
          $displayId = $args['displayId'] ?? 'default';
          $key = $view->id() . ':' . $displayId;

          // Only expose enabled view displays.
          if (!in_array($key, $this->getEnabledViewDisplays())) {
            return NULL;
          }
          return $view;
        }),
        $builder->produce('view_executable')
          ->map('view', $builder->fromParent())
          ->map('displayId', $builder->fromArgument('displayId'))
      ),
    );

    $registry->addFieldResolver('ViewExecutable', 'itemsPerPage',
      $builder->callback(function (ViewExecutable $executable) {
        return $executable->getItemsPerPage() ?? 0;
      }
    ));

    $registry->addFieldResolver('Query', 'getView', $builder->compose(
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('view'))
        ->map('id', $builder->callback(function ($parent, $args) {
          return strtolower($args['id']);
        }))
    ));

    $registry->addFieldResolver('ViewExecutable', 'execute', $builder->compose(
      $builder->produce('view_executor')
        ->map('viewExecutable', $builder->fromParent())
        ->map('page', $builder->fromArgument('page'))
        ->map('sortBy', $builder->fromArgument('sortBy'))
        ->map('sortOrder', $builder->fromArgument('sortOrder'))
        ->map('filters', $builder->callback(function ($v, $args) {
          $filters = [];
          foreach ($args as $name => $value) {
            if ($name === 'page' || $name === 'sortBy' || $name === 'sortOrder') {
              continue;
            }
            $filters[EntitySchemaHelper::toSnakeCase($name)] = $value;
          }
          return $filters;
        }))
      )
    );

    $registry->addFieldResolver('ViewExecutable', 'executeWithQueryParams', $builder->compose(
      $builder->produce('view_executor')
        ->map('viewExecutable', $builder->fromParent())
        ->map('queryParams', $builder->callback(function ($v, $args) {
          if (!empty($args['queryParams'])) {
            return array_reduce($args['queryParams'], function ($acc, $arg) {
              $acc[$arg['key']] = $arg['value'];
              return $acc;
            });
          }
        }))
      )
    );

    $registry->addFieldResolver('ViewExecutableResult', 'rows',
      $builder->callback(function ($result) {
        return $result['rows'];
      }
    ));
  }

  /**
   * Add resolvers for the ViewPager type.
   *
   * @param ResolverRegistryInterface $registry
   *   The resolver registry.
   * @param ResolverBuilder $builder
   *   The resolver builder.
   */
  private function registerPagerResolvers(ResolverRegistryInterface $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver('ViewExecutable', 'pager',
      $builder->callback(function (ViewExecutable $executable) {
        return $executable->getPager();
      }
    ));

    $registry->addFieldResolver('ViewPager', 'perPage',
      $builder->callback(function (PagerPluginBase $pager) {
        return $pager->getItemsPerPage();
      }
    ));

    $registry->addFieldResolver('ViewPager', 'totalItems',
      $builder->callback(function (PagerPluginBase $pager) {
        return $pager->getTotalItems();
      }
    ));
  }

  /**
   * Add resolvers for the ViewSort type.
   *
   * @param ResolverRegistryInterface $registry
   *   The resolver registry.
   * @param ResolverBuilder $builder
   *   The resolver builder.
   */
  private function registerSortResolvers(ResolverRegistryInterface $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver('ViewExecutable', 'sorts',
      $builder->callback(function (ViewExecutable $executable) {
        return $executable->sort;
      }
    ));

    $registry->addFieldResolver('ViewSort', 'pluginId',
      $builder->callback(function (SortPluginBase $sort) {
        return $sort->getPluginId();
      }
    ));

    $registry->addFieldResolver('ViewSort', 'baseId',
      $builder->callback(function (SortPluginBase $sort) {
        return $sort->getBaseId();
      }
    ));

    $registry->addFieldResolver('ViewSort', 'field',
      $builder->callback(function (SortPluginBase $sort) {
        return $sort->field;
      }
    ));

    $registry->addFieldResolver('ViewSort', 'realField',
      $builder->callback(function (SortPluginBase $sort) {
        return $sort->realField;
      }
    ));
  }

  /**
   * Add resolvers for the ViewFilters type.
   *
   * @param ResolverRegistryInterface $registry
   *   The resolver registry.
   * @param ResolverBuilder $builder
   *   The resolver builder.
   */
  private function registerFilterResolvers(ResolverRegistryInterface $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver('ViewExecutable', 'filters',
      $builder->callback(function (ViewExecutable $executable) {
        return $executable->filter;
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'pluginId',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->getPluginId();
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'baseId',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->getBaseId();
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'field',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->field;
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'adminLabel',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->adminLabel();
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'adminLabelShort',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->adminLabel(TRUE);
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'adminSummary',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->adminSummary();
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'options',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->options;
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'groupInfo',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->options['group_info'] ?? NULL;
      }
    ));

    $registry->addFieldResolver('ViewFilterGroupInfo', 'groupItems',
      $builder->callback(function ($value) {
        if (is_array($value)) {
          $values = array_values($value['group_items'] ?? []);
          return $values;
        }

        return [];
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'realField',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->realField;
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'table',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->table;
      }
    ));
    $registry->addFieldResolver('ViewFilter', 'value',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->value;
      }
    ));
    $registry->addFieldResolver('ViewFilter', 'operator',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->operator;
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'noOperator',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->no_operator;
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'alwaysRequired',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->always_required;
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'isExposed',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->isExposed();
      }
    ));

    $registry->addFieldResolver('ViewFilter', 'isAGroup',
      $builder->callback(function (FilterPluginBase $filter) {
        return $filter->isAGroup();
      }
    ));
  }

}
