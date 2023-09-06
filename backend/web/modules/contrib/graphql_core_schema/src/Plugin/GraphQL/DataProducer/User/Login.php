<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer\User;

use Drupal\graphql_core_schema\Plugin\GraphQL\UserMutationBase;

/**
 * Log in an user.
 *
 * @DataProducer(
 *   id = "user_login",
 *   name = @Translation("User Login"),
 *   description = @Translation("Login a user using the given username and password."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("The login result.")
 *   ),
 *   consumes = {
 *     "username" = @ContextDefinition("string",
 *       label = @Translation("The user name."),
 *     ),
 *     "password" = @ContextDefinition("string",
 *       label = @Translation("The password."),
 *     )
 *   }
 * )
 */
class Login extends UserMutationBase {

  /**
   * The resolver.
   *
   * @param string $username
   *   The username.
   * @param string $password
   *   The password.
   *
   * @return array
   *   The login result.
   */
  public function resolve(string $username, string $password) {
    try {
      $response = $this->doRequest('/user/login?_format=json', [
        'name' => $username,
        'pass' => $password,
      ]);

      // Get the content.
      $content = $response->getContent();

      // Decode the response. If the response is not valid, e.g. because Drupal
      // has thrown a server error in the subrequest, this will fail, so we can
      // return an errors message in the GraphQL response.
      $data = json_decode($content, TRUE);

      // Get the CSRF token.
      $csrfToken = $data['csrf_token'] ?? NULL;

      // We can't use the uid or name to determine if login was successful,
      // because a field access check is performed and these values might be
      // NULL if the user does not have permission to view these fields.
      // Checking for a CSRF token is the only way to know it login was
      // successful, as this is always present after a successful login.
      $success = !empty($csrfToken);

      return [
        'error' => $data['message'] ?? NULL,
        'uid' => $data['current_user']['uid'] ?? NULL,
        'name' => $data['current_user']['name'] ?? NULL,
        'csrf_token' => $csrfToken,
        'logout_token' => $data['logout_token'] ?? NULL,
        'success' => $success,
      ];
    }
    catch (\Exception $e) {
      return [
        'error' => $this->t('An unexpected error occured when trying to login.'),
        'success' => FALSE,
      ];
    }
  }

}
