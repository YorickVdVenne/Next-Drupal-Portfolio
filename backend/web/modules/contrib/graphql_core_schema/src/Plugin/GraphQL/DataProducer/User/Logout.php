<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer\User;

use Drupal\graphql_core_schema\Plugin\GraphQL\UserMutationBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logout the current user.
 *
 * @DataProducer(
 *   id = "user_logout",
 *   name = @Translation("User Logout"),
 *   description = @Translation("Logout the current user."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("The logout result.")
 *   ),
 *   consumes = {
 *     "logoutToken" = @ContextDefinition("string",
 *       label = @Translation("The logout token."),
 *     ),
 *     "csrfToken" = @ContextDefinition("string",
 *       label = @Translation("The CSRF token."),
 *     )
 *   }
 * )
 */
class Logout extends UserMutationBase {

  /**
   * Logout the current user.
   *
   * @param string $logoutToken
   *   The logout token.
   * @param string $csrfToken
   *   The CSRF token.
   *
   * @return array
   *   The logout result.
   */
  public function resolve(string $logoutToken, string $csrfToken) {
    try {
      $response = $this->doRequest(
        '/user/logout?_format=json&token=' . $logoutToken,
        [],
        ['x-csrf-token' => $csrfToken]
      );

      // The status code 204 (No Content) implies that logging out was
      // successful.
      if ($response->getStatusCode() === Response::HTTP_NO_CONTENT) {
        return [
          'success' => TRUE,
        ];
      }

      // Try to decode the response.
      $data = json_decode($response->getContent(), TRUE);

      // Get the message. If an error has happened the message should contain
      // the reason.
      $message = $data['message'] ?? NULL;

      // If there is no message and the status code is not 204, throw an
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
        'error' => $this->t('An unexpected error occured when trying to logout.'),
        'success' => FALSE,
      ];
    }
  }

}
