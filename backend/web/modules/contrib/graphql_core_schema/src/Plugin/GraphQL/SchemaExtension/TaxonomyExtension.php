<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;
use Drupal\graphql_core_schema\EntitySchemaHelper;
use Drupal\graphql_core_schema\TypeAwareSchemaExtensionInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A schema extension for taxonomy.
 *
 * @SchemaExtension(
 *   id = "taxonomy",
 *   name = "Taxonomy",
 *   description = "Adds additional fields for taxonomy terms.",
 *   schema = "core_composable"
 * )
 */
class TaxonomyExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface, TypeAwareSchemaExtensionInterface {

  /**
   * Bundle info manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('entity_type.bundle.info'),
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
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    array $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $moduleHandler);
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return ['taxonomy_term'];
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
  public function getBaseDefinition() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionDefinition() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeExtensionDefinition(array $types) {
    // Extend the TaxonomyTerm interface.
    $extension = ["extend interface TaxonomyTerm {
      children: [TaxonomyTerm]
    }",
    ];

    // Extend all taxonomy vocabulary types.
    $bundles = array_keys($this->entityTypeBundleInfo->getBundleInfo('taxonomy_term'));
    foreach ($bundles as $bundle) {
      $typeName = EntitySchemaHelper::toPascalCase(['taxonomy_term', $bundle]);
      if (in_array($typeName, $types)) {
        $extension[] = "extend type $typeName {
          children: [$typeName]
        }";
      }
    }

    return implode("\n", $extension);
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();
    $registry->addFieldResolver(
      'TaxonomyTerm',
      'children',
      $builder->produce('taxonomy_load_tree')
        ->map('vid', $builder->callback(function (Term $value) {
          return $value->bundle();
        }))
        ->map('parent', $builder->callback(function (Term $value) {
          return $value->id();
        }))
        ->map('max_depth', $builder->fromValue(1))
    );
  }

}
