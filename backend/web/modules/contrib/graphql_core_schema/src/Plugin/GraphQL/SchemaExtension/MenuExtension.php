<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;
use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A schema extension to load menu links.
 *
 * @SchemaExtension(
 *   id = "menu",
 *   name = "Menu",
 *   description = "Load menu links.",
 *   schema = "core_composable"
 * )
 */
class MenuExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface, ContainerFactoryPluginInterface {

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
      $container->get('entity_type.manager'),
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
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $moduleHandler);

    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return ['menu', 'menu_link_content'];
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionDependencies() {
    return ['routing'];
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseDefinition() {
    $base = $this->loadDefinitionFile('base');
    $enum = $this->buildMenuNameEnum();
    return $base . $enum;
  }

  /**
   * Generates the MenuName enum.
   *
   * @return string
   *   The GraphQL enum for the schema extension.
   */
  private function buildMenuNameEnum() {
    $storage = $this->entityTypeManager->getStorage('menu');
    $ids = array_keys($storage->getQuery()->accessCheck(FALSE)->execute());
    $values = [];

    foreach ($ids as $id) {
      /** @var \Drupal\system\Entity\Menu $menu */
      $menu = $storage->load($id);
      $key = strtoupper(str_replace('-', '_', $id));
      $values[$key] = [
        'value' => $key,
        'description' => $menu->getDescription(),
      ];
    }
    $schema = new Schema([
      'types' => [
        new EnumType([
          'name' => 'MenuName',
          'values' => $values,
        ]),
      ],
    ]);

    return SchemaPrinter::doPrint($schema);
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();
    $registry->addTypeResolver('MenuLinkTreeElement', function ($value) {
      if ($value instanceof MenuLinkTreeElement) {
        return 'MenuLinkTreeElement';
      }
    });
    $registry->addTypeResolver('MenuLink', function ($value) {
      if ($value instanceof MenuLinkInterface) {
        return 'MenuLink';
      }
    });

    $registry->addFieldResolver(
      'Query',
      'menuByName',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('menu'))
        ->map('id', $builder->callback(function ($value, $args) {
          // Convert the enum from MY_MENU_NAME to my-menu-name.
          return str_replace('_', '-', strtolower($args['name']));
        }))
    );

    $registry->addFieldResolver('Menu', 'links', $builder->compose(
      $builder->produce('menu_links')->map('menu', $builder->fromParent()),
      $builder->produce('filter_menu_links')->map('links', $builder->fromParent()),
    ));

    $registry->addFieldResolver('MenuLinkTreeElement', 'link', $builder->compose(
      $builder->produce('menu_tree_link')->map('element', $builder->fromParent())
    ));

    $registry->addFieldResolver('MenuLinkTreeElement', 'subtree', $builder->compose(
      $builder->produce('menu_tree_subtree')->map('element', $builder->fromParent()),
      $builder->produce('filter_menu_links')->map('links', $builder->fromParent()),
    ));

    $registry->addFieldResolver('MenuLink', 'label', $builder->compose(
      $builder->produce('menu_link_label')->map('link', $builder->fromParent())
    ));

    $registry->addFieldResolver('MenuLink', 'description', $builder->compose(
      $builder->produce('menu_link_description')->map('link', $builder->fromParent())
    ));

    $registry->addFieldResolver('MenuLink', 'expanded', $builder->compose(
      $builder->produce('menu_link_expanded')->map('link', $builder->fromParent())
    ));

    $registry->addFieldResolver('MenuLink', 'attribute', $builder->compose(
      $builder->produce('menu_link_attribute')
        ->map('link', $builder->fromParent())
        ->map('attribute', $builder->fromArgument('name'))
    ));

    $registry->addFieldResolver('MenuLink', 'url', $builder->compose(
      $builder->produce('menu_link_url')->map('link', $builder->fromParent())
    ));

    // Extract the menu_link_content entity if available.
    $registry->addFieldResolver('MenuLink', 'content', $builder->compose(
      $builder->callback(function ($value) {
        if ($value instanceof MenuLinkContent) {
          return $value->getDerivativeId();
        }
      }),
      $builder->produce('entity_load_by_uuid')
        ->map('type', $builder->fromValue('menu_link_content'))
        ->map('uuid', $builder->fromParent())
    ));
  }

}
