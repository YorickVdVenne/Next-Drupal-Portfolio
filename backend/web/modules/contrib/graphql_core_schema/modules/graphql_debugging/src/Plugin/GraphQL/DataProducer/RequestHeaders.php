<?php

namespace Drupal\graphql_debugging\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Produces the current request headers.
 *
 * @DataProducer(
 *   id = "request_headers",
 *   name = @Translation("Request Headers"),
 *   description = @Translation("Returns the request headers."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Request Headers"),
 *     required = FALSE
 *   )
 * )
 */
class RequestHeaders extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

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
      $container->get('request_stack')
    );
  }

  /**
   * Constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    RequestStack $requestStack
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->requestStack = $requestStack;
  }

  /**
   * Resolver.
   *
   * @return array
   *   The request headers.
   */
  public function resolve() {
    $request = $this->requestStack->getCurrentRequest();

    $headers = [];
    foreach ($request->headers->all() as $key => $value) {
      $headers[] = [
        'key' => $key,
        'value' => $value,
      ];
    }

    return $headers;
  }

}
