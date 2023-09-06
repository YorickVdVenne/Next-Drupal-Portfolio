<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer\Menu;

use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load a menu link.
 *
 * @DataProducer(
 *   id = "load_menu_link",
 *   name = @Translation("Load Menu Link"),
 *   description = @Translation("Loads a menu link by plugin ID."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Menu link"),
 *   ),
 *   consumes = {
 *     "id" = @ContextDefinition("string",
 *       label = @Translation("Menu Link Plugin ID")
 *     ),
 *   }
 * )
 */
class LoadMenuLink extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
      $container->get('plugin.manager.menu.link')
    );
  }

  /**
   * LoadMenuLink constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menuLinkManager
   *   The menu link manager.
   *
   * @codeCoverageIgnore
   */
  public function __construct(array $configuration,
      $pluginId,
      $pluginDefinition,
      MenuLinkManagerInterface $menuLinkManager,
    ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->menuLinkManager = $menuLinkManager;
  }

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   */
  public function resolve(string $id) {
    if ($id) {
      return $this->menuLinkManager->createInstance($id);
    }
  }

}
