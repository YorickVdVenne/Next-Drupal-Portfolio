<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer\Menu;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Return the menu link of an entity.
 *
 * @DataProducer(
 *   id = "entity_menu_link",
 *   name = @Translation("Entity Menu Link"),
 *   description = @Translation("Returns the menu link for an entity."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Menu link"),
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "menu_name" = @ContextDefinition("string",
 *       label = @Translation("The menu name."),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class EntityMenuLink extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * Menu Link Manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('plugin.manager.menu.link'),
      $container->get('menu.link_tree')
    );
  }

  /**
   * EntityMenuLink constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menuLinkManager
   *   The menu link manager.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menuLinkTree
   *   The menu link tree service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(array $configuration,
    $pluginId,
    $pluginDefinition,
    MenuLinkManagerInterface $menuLinkManager,
    MenuLinkTreeInterface $menuLinkTree) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->menuLinkManager = $menuLinkManager;
    $this->menuLinkTree = $menuLinkTree;
  }

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   */
  public function resolve(EntityInterface $entity, string $menuName = NULL, FieldContext $fieldContext) {
    $url = $entity->toUrl();
    $links = array_values(
      $this->menuLinkManager->loadLinksByRoute(
        $url->getRouteName(),
        $url->getRouteParameters(),
        $menuName
      )
    );
    $link = $links[0] ?? NULL;
    if (!$link) {
      return;
    }
    $fieldContext->addCacheableDependency($link);
    $menuTreeParameters = new MenuTreeParameters();
    $menuTreeParameters->setRoot($link->getPluginId());
    $tree = $this->menuLinkTree->load($link->getMenuName(), $menuTreeParameters);

    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];

    $treeElements = array_filter($this->menuLinkTree->transform($tree, $manipulators), function (MenuLinkTreeElement $item) {
      return $item->link instanceof MenuLinkInterface && $item->link->isEnabled();
    });
    $element = array_values($treeElements)[0] ?? NULL;
    if ($element) {
      $fieldContext->addCacheableDependency($element);
    }
    return $element;
  }

}
