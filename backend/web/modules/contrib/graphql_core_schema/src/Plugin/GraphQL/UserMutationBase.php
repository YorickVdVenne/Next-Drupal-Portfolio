<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Base class for user subrequest mutations.
 */
class UserMutationBase extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The Drupal kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $kernel;

  /**
   * The request stack service.
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
      $container->get('kernel'),
      $container->get('request_stack'),
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
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The http kernel service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    HttpKernelInterface $httpKernel,
    RequestStack $requestStack
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->kernel = $httpKernel;
    $this->requestStack = $requestStack;
  }

  /**
   * Perform the subrequest.
   *
   * @param string $path
   *   The path for the subrequest.
   * @param array|null $body
   *   The request parameters.
   * @param array|null $headers
   *   The request headers.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The subrequest response.
   */
  protected function doRequest(string $path, ?array $body = [], ?array $headers = [], ?string $method = 'POST'): Response {
    // Get the current request.
    $currentRequest = $this->requestStack->getCurrentRequest();
    // Override middleware port. Middlware was using random ports eg. 44830.
    // Different port for each request.
    // TODO: Fix forwarding of client ip / port form middleware
    $port = (string) $currentRequest->getPort() !== '80' ? '443' : '80';
    $currentRequest->server->set('HTTP_X_FORWARDED_PORT', $port);

    // Build the request body.
    $body = !empty($body) ? json_encode($body) : '';

    $request = Request::create(
      $path,
      $method,
      [],
      $currentRequest->cookies->all(),
      [],
      $currentRequest->server->all(),
      $body,
    );

    // Add any headers provided to the request.
    if (!empty($headers)) {
      foreach ($headers as $key => $value) {
        $request->headers->set($key, $value);
      }
    }

    // Set current session so that it behaves as if the request was made
    // directly.
    $request->setSession($currentRequest->getSession());

    // Handle the request.
    return $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
  }

}
