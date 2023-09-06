<?php

declare(strict_types = 1);

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer\User;

use Drupal\graphql_core_schema\Plugin\GraphQL\UserMutationBase;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\Response;

/**
 * Send a password reset email.
 *
 * @DataProducer(
 *   id = "user_password_reset",
 *   name = @Translation("User Password Reset"),
 *   description = @Translation("Send a password reset email to the given user."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("The password reset result.")
 *   ),
 *   consumes = {
 *     "username" = @ContextDefinition("string",
 *       label = @Translation("The username."),
 *       required = FALSE,
 *     ),
 *     "email" = @ContextDefinition("string",
 *       label = @Translation("The user email."),
 *       required = FALSE,
 *     ),
 *   }
 * )
 */
class PasswordReset extends UserMutationBase {

  /**
   * The resolver.
   *
   * @param string|null $username
   *   The username of the user.
   * @param string|null $email
   *   The email of the user, used if the username is empty.
   *
   * @return array
   *   The password reset result.
   */
  public function resolve($username, $email) {
    if (!$username && !$email) {
      return [
        'error' => $this->t('Unrecognized username or email address.'),
        'success' => FALSE,
      ];
    }
    try {
      // The path is hardcoded since loading it from route would require a
      // render context.
      $response = $this->doRequest('/user/password?_format=json', [
        'name' => empty($username) ? NULL : $username,
        'mail' => $email,
      ]);

      if ($response->getStatusCode() === Response::HTTP_OK) {
        return [
          'success' => TRUE,
        ];
      }

      // Try to decode the response.
      $data = Json::decode($response->getContent());

      // Get the message. If an error has happened the message should contain
      // the reason.
      $message = $data['message'] ?? NULL;

      // If there is no message and the status code is not 200, throw an
      // exception.
      if (empty($message)) {
        throw new \Exception();
      }

      return [
        'error' => $message,
        'success' => FALSE,
      ];
    }
    catch (\Exception $e) {
      return [
        'error' => $this->t('An unexpected error occurred when trying to reset the password.'),
        'success' => FALSE,
      ];
    }
  }

}
