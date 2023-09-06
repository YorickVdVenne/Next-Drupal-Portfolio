<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;
use Drupal\graphql_core_schema\TypeAwareSchemaExtensionInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds additional fields for images.
 *
 * @SchemaExtension(
 *   id = "image",
 *   name = "Image",
 *   description = "Additional fields for images, like image derivatives.",
 *   schema = "core_composable"
 * )
 */
class ImageExtension extends SdlSchemaExtensionPluginBase implements ContainerFactoryPluginInterface, CoreSchemaExtensionInterface, TypeAwareSchemaExtensionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('entity_type.manager')
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
   */
  public function __construct(
    array $configuration,
    $pluginId,
    array $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $moduleHandler);

    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return ['file'];
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
    $storage = $this->entityTypeManager->getStorage('image_style');

    $values = [];
    foreach ($storage->loadMultiple() as $imageStyle) {
      $values[strtoupper($imageStyle->id())] = [
        'value' => $imageStyle->id(),
        'description' => $imageStyle->label(),
      ];
    }

    $schema = new Schema([
      'types' => [
        new EnumType([
          'name' => 'ImageStyleId',
          'values' => $values,
        ]),
      ],
    ]);

    $content = [
      SchemaPrinter::doPrint($schema),
      $this->loadDefinitionFile('base'),
    ];

    return implode("\n", $content);
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
    // Check if the type has been genereated at all.
    if (!in_array('FieldItemTypeImage', $types)) {
      return '';
    }

    return $this->loadDefinitionFile('FieldItemTypeImage');
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('FieldItemTypeImage', 'derivative',
      $builder->produce('image_derivative')
        ->map('entity', $builder->callback(function (ImageItem $item) {
          return $item->entity;
        }))
        ->map('style',
          $builder->compose(
            $builder->fromArgument('style'),
            $builder->callback(function ($v) {
              return strtolower($v);
            })
          )
        )
    );

    $registry->addFieldResolver('ImageResource', 'urlPath', $builder->callback(function ($result) {
      return $result['url'] ?? NULL;
    }));
  }

}
