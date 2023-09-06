<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The data producer for local tasks.
 *
 * @DataProducer(
 *   id = "local_tasks",
 *   name = @Translation("Local Tasks"),
 *   description = @Translation("Return the local tasks for an URL."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Local Tasks")
 *   ),
 *   consumes = {
 *     "url" = @ContextDefinition("any",
 *       label = @Translation("Route URL"),
 *     )
 *   }
 * )
 */
class LocalTasks extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The local task manager.
   *
   * @var \Drupal\Core\Menu\LocalTaskManagerInterface
   */
  protected $localTaskManager;

  /**
   * The entity buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\EntityBuffer
   */
  protected $entityBuffer;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The subrequest buffer.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer
   */
  protected $subRequestBuffer;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('plugin.manager.menu.local_task'),
      $container->get('graphql.buffer.subrequest'),
    );
  }

  /**
   * The constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Menu\LocalTaskManagerInterface $localTaskManager
   *   The local task manager.
   * @param \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer $subRequestBuffer
   *   The subrequest buffer.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    LocalTaskManagerInterface $localTaskManager,
    SubRequestBuffer $subRequestBuffer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->localTaskManager = $localTaskManager;
    $this->subRequestBuffer = $subRequestBuffer;
  }

  /**
   * The resolver.
   *
   * @param mixed $url
   *   The url.
   *
   * @return array
   *   The result.
   */
  public function resolve($url) {
    if ($url instanceof Url) {
      $routeName = $url->getRouteName();

      $resolve = $this->subRequestBuffer->add($url, function () use ($routeName) {
        $localTasks = $this->localTaskManager->getLocalTasks($routeName, 0);
        $tabs = $localTasks['tabs'] ?? [];
        $visible = [];

        foreach ($tabs as $key => $tab) {
          if (Element::isVisibleElement($tab)) {
            $visible[] = [
              '_key' => $key,
              ...$tab, //phpcs:ignore
            ];
          }
        }

        return $visible;
      });

      return $resolve() ?? [];
    }
    return [];
  }

}
