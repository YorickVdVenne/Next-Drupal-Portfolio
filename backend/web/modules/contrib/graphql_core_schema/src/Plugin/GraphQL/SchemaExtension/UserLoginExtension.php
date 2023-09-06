<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;

/**
 * A schema extension to provide the current user.
 *
 * @SchemaExtension(
 *   id = "user_login",
 *   name = "User Login",
 *   description = "An extension that provides mutations to login and logout.",
 *   schema = "core_composable"
 * )
 */
class UserLoginExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return ['user'];
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionDependencies() {
    return ['user'];
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('Mutation', 'userLogin',
      $builder->produce('user_login')
        ->map('username', $builder->fromArgument('username'))
        ->map('password', $builder->fromArgument('password'))
    );

    $registry->addFieldResolver('Mutation', 'userLogout',
      $builder->produce('user_logout')
        ->map('logoutToken', $builder->fromArgument('logoutToken'))
        ->map('csrfToken', $builder->fromArgument('csrfToken'))
    );

    $registry->addFieldResolver('Mutation', 'userPasswordReset',
      $builder->produce('user_password_reset')
        ->map('username', $builder->fromArgument('username'))
        ->map('email', $builder->fromArgument('email'))
    );

    $registry->addFieldResolver('Mutation', 'userPasswordChange',
      $builder->compose(
        $builder->produce('entity_load')
          ->map('id', $builder->fromArgument('id'))
          ->map('type', $builder->fromValue('user')),
        $builder->produce('user_password_change')
          ->map('user', $builder->fromParent())
          ->map('newPassword', $builder->fromArgument('newPassword'))
          ->map('currentPassword', $builder->fromArgument('currentPassword'))
          ->map('passResetToken', $builder->fromArgument('passResetToken'))
      )
    );

    $registry->addFieldResolver('Mutation', 'userPasswordResetLogin',
      $builder->produce('user_password_reset_login')
        ->map('uid', $builder->fromArgument('id'))
        ->map('timestamp', $builder->fromArgument('timestamp'))
        ->map('hash', $builder->fromArgument('hash'))
    );

  }

}
