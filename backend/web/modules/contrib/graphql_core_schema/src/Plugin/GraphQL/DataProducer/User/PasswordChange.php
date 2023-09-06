<?php

declare(strict_types = 1);

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer\User;

use Drupal\graphql_core_schema\Plugin\GraphQL\UserMutationBase;
use Drupal\user\UserInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Change a users password.
 *
 * Either the currentPassword or the passResetToken must be provided.
 *
 * @DataProducer(
 *   id = "user_password_change",
 *   name = @Translation("User Password Change"),
 *   description = @Translation("Change the password of a user."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("The password change result."),
 *     required = FALSE,
 *   ),
 *   consumes = {
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("The user to change the password for."),
 *     ),
 *     "newPassword" = @ContextDefinition("string",
 *       label = @Translation("The user's desired new password."),
 *     ),
 *     "currentPassword" = @ContextDefinition("string",
 *       label = @Translation("The user's current password."),
 *       required = FALSE,
 *     ),
 *     "passResetToken" = @ContextDefinition("string",
 *       label = @Translation("The pass-reset-token query param."),
 *       required = FALSE,
 *     ),
 *   }
 * )
 */
class PasswordChange extends UserMutationBase {

  /**
   * The resolver.
   *
   * @param UserInterface $user
   *   The user to change the password for.
   * @param string $newPassword
   *   The new password.
   * @param string|null $currentPassword
   *   The current password of the user.
   * @param string|null $passResetToken
   *   The pass-reset-token value.
   *
   * @return string
   *   The error message.
   */
  public function resolve(UserInterface $user, string $newPassword, ?string $currentPassword, ?string $passResetToken) {
    $error = $this->changePassword($user, $newPassword, $currentPassword, $passResetToken);

    return [
      'success' => empty($error),
      'error' => $error,
    ];
  }

  /**
   * The resolver.
   *
   * @param UserInterface $user
   *   The user to change the password for.
   * @param string $newPassword
   *   The new password.
   * @param string|null $currentPassword
   *   The current password of the user.
   * @param string|null $passResetToken
   *   The pass-reset-token value.
   *
   * @return string
   *   The error message.
   */
  public function changePassword(UserInterface $user, string $newPassword, ?string $currentPassword, ?string $passResetToken) {
    // Make sure either current password or the reset token are provided.
    if (!$currentPassword && !$passResetToken) {
      return $this->t('Either currentPassword or passResetToken must be provided.');
    }

    // Check if the user is allowed to edit the password.
    $access = $user->pass->access('edit', NULL, TRUE);

    if (!$access->isAllowed()) {
      return $this->t('Access denied');
    }

    $currentRequest = $this->requestStack->getCurrentRequest();
    $session = $currentRequest->getSession();
    $uid = $user->id();
    $sessionKey = 'pass_reset_' . $uid;

    if ($passResetToken) {
      // Make sure the token provided by the user is the same as the one in
      // the session.
      $tokenFromSession = $session->get($sessionKey);
      $tokenIsValid = $tokenFromSession && hash_equals($tokenFromSession, $passResetToken);

      if (!$tokenIsValid) {
        return $this->t('The provided passResetToken is invalid.');
      }

      // @see \Drupal\user\AccountForm::buildEntity
      // This is the magic that allows us to set a new password without
      // providing the existing one. We can do that here because we have made
      // sure that the passResetToken is valid.
      //
      // This value is checked here:
      // \Drupal\user\Plugin\Validation\Constraint\ProtectedUserFieldConstraintValidator::validate
      $user->_skipProtectedUserFieldConstraint = TRUE;
    }
    else {
      $user->setExistingPassword($currentPassword);
    }

    $user->setPassword($newPassword);
    $validations = $user->validate();
    if ($validations->count() === 0) {
      $session->remove($sessionKey);
      $user->save();

      // Set to false again because we're done with our stuff.
      $user->_skipProtectedUserFieldConstraint = FALSE;
      return;
    }

    $messages = array_map(function (ConstraintViolationInterface $violation) {
      return $violation->getMessage();
    }, iterator_to_array($validations));

    return strip_tags(implode(', ', $messages));
  }

}
