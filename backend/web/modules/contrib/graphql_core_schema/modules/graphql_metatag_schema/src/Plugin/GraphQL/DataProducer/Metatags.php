<?php

namespace Drupal\graphql_metatag_schema\Plugin\GraphQL\DataProducer;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\metatag\MetatagManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A data producer for metatags.
 *
 * @DataProducer(
 *   id = "metatags",
 *   name = @Translation("Metatags"),
 *   description = @Translation("Return the metatags for a route."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Metatags")
 *   ),
 *   consumes = {
 *     "url" = @ContextDefinition("any",
 *       label = @Translation("Route URL"),
 *     )
 *   }
 * )
 */
class Metatags extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Drupal\metatag\MetatagManager definition.
   *
   * @var \Drupal\metatag\MetatagManager
   */
  protected $metatagManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The subrequest buffer.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer
   */
  protected $subRequestBuffer;

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
      $container->get('module_handler'),
      $container->get('metatag.manager', ContainerInterface::NULL_ON_INVALID_REFERENCE),
      $container->get('current_route_match'),
      $container->get('graphql.buffer.subrequest')
    );
  }

  /**
   * Metatags constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\metatag\MetatagManagerInterface $metatagManager
   *   The metatag manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   * @param \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer $subRequestBuffer
   *   The sub-request buffer service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    MetatagManagerInterface $metatagManager,
    RouteMatchInterface $routeMatch,
    SubRequestBuffer $subRequestBuffer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->moduleHandler = $moduleHandler;
    $this->metatagManager = $metatagManager;
    $this->routeMatch = $routeMatch;
    $this->subRequestBuffer = $subRequestBuffer;
  }

  /**
   * The resolver.
   *
   * @param \Drupal\Core\Url $url
   *   The url.
   *
   * @return array
   *   The metatags.
   */
  public function resolve(Url $url) {
    if (!$this->metatagManager) {
      return [];
    }
    $resolve = $this->subRequestBuffer->add($url, function () {
      $tags = metatag_get_tags_from_route();

      // Trigger hook_metatags_attachments_alter().
      // Allow modules to rendered metatags prior to attaching.
      $this->moduleHandler->alter('metatags_attachments', $tags);

      $tags = NestedArray::getValue($tags, ['#attached', 'html_head']) ?: [];
      $tags = array_filter($tags, function ($tag) {
        return is_array($tag) &&
          !NestedArray::getValue($tag, [0, '#attributes', 'schema_metatag']);
      });

      return $tags;
    });

    return $resolve() ?? [];
  }

}
