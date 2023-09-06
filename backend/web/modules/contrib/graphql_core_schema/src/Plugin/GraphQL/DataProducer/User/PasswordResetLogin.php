<?php

declare(strict_types = 1);

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer\User;

use Drupal\graphql_core_schema\Plugin\GraphQL\UserMutationBase;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Login with timestamp and hash from email.
 *
 * @DataProducer(
 *   id = "user_password_reset_login",
 *   name = @Translation("User Password Reset Login"),
 *   description = @Translation("Login a user with timestamp and hash from reset email."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("The password reset login result.")
 *   ),
 *   consumes = {
 *     "uid" = @ContextDefinition("string",
 *       label = @Translation("The user ID."),
 *     ),
 *     "timestamp" = @ContextDefinition("string",
 *       label = @Translation("The timestamp of the initial request."),
 *     ),
 *     "hash" = @ContextDefinition("string",
 *       label = @Translation("The hash from the reset password email."),
 *     ),
 *   }
 * )
 */
class PasswordResetLogin extends UserMutationBase {

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface $userStorage
   */
  protected $userStorage;

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
        $container->get('entity_type.manager')->getStorage('user')
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
   * @param \Drupal\user\UserStorageInterface $userStorage
   *   The user storage.
   */
  public function __construct(
      array $configuration,
      $pluginId,
      $pluginDefinition,
      HttpKernelInterface $httpKernel,
      RequestStack $requestStack,
      UserStorageInterface $userStorage
  ) {
    parent::__construct(
        $configuration,
        $pluginId,
        $pluginDefinition,
        $httpKernel,
        $requestStack
    );
    $this->userStorage = $userStorage;
  }

  /**
   * The resolver.
   *
   * @param string $uid
   *   The ID of the user.
   * @param string $timestamp
   *   The timestamp of the initial request.
   * @param string hash
   *   The hash from the reset password email.
   *
   * @return array
   *   The password reset login result.
   */
  public function resolve(string $uid, string $timestamp, string $hash) {
    try {
      // The path is hardcoded since loading it from route would require a
      // render context.
      $response = $this->doRequest(
        "/user/reset/{$uid}/{$timestamp}/{$hash}/login"
      );

      // The status code 302 (Found) implies that the request was successful.
      if ($response->getStatusCode() === Response::HTTP_FOUND) {
        // Get query from location header
        $location = $response->headers->all('location');
        $urlComponents = parse_url(reset($location));
        parse_str($urlComponents['query'] ?? '', $query);

        // The query being empty implies the request was invalid.
        if (empty($query)) {
          return [
            'success' => FALSE,
            'error' => $this->t('The provided credentials are no longer valid.'),
          ];
        }

        return [
          'success' => TRUE,
          'token' => $query['pass-reset-token'],
          'user' => $this->userStorage->load($uid),
        ];
      }
      elseif ($response->getStatusCode() === Response::HTTP_FORBIDDEN) {
        // The status code 403 (Forbidden) implies that the request was invalid.
        return [
          'success' => FALSE,
          'error' => $this->t('The provided credentials are not valid.'),
        ];
      }
      else {
        throw new \Exception();
      }
    }
    catch (\Exception $e) {
      return [
        'error' => $this->t('An unexpected error occurred when trying to login.'),
        'success' => FALSE,
      ];
    }
  }

}
