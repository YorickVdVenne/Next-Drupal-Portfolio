<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Breadcrumb\BreadcrumbManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The breadcrumb data producer.
 *
 * @DataProducer(
 *   id = "breadcrumb",
 *   name = @Translation("Breadcrumb"),
 *   description = @Translation("Return the breadcrumb for the route."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Breadcrumb")
 *   ),
 *   consumes = {
 *     "url" = @ContextDefinition("any",
 *       label = @Translation("Route URL"),
 *     )
 *   }
 * )
 */
class Breadcrumb extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The breadcrumb manager service.
   *
   * @var \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface
   */
  protected $breadcrumbManager;

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
      $container->get('breadcrumb'),
      $container->get('current_route_match'),
      $container->get('graphql.buffer.subrequest')
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
   * @param \Drupal\Core\Breadcrumb\BreadcrumbManager $breadcrumbManager
   *   The breadcrumb manager service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   * @param \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer $subRequestBuffer
   *   The sub-request buffer service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    BreadcrumbManager $breadcrumbManager,
    RouteMatchInterface $routeMatch,
    SubRequestBuffer $subRequestBuffer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->breadcrumbManager = $breadcrumbManager;
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
   *   The breadcrumb links.
   */
  public function resolve(Url $url, FieldContext $context) {
    $resolve = $this->subRequestBuffer->add($url, function () use ($context) {
      $breadcrumb = $this->breadcrumbManager->build($this->routeMatch);
      $context->addCacheableDependency($breadcrumb);
      return $breadcrumb->getLinks();
    });

    return $resolve() ?? [];
  }

}
