<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\Core\Session\AccountProxy;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;
use Drupal\user\UserInterface;

/**
 * A schema extension to provide the current user.
 *
 * @SchemaExtension(
 *   id = "user",
 *   name = "User",
 *   description = "An extension that provides additional user fields.",
 *   schema = "core_composable"
 * )
 */
class UserExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface {

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
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();
    $registry->addFieldResolver('Query', 'currentUser', $builder->compose(
      $builder->produce('current_user'),
      $builder->callback(function ($account) {
        if ($account instanceof AccountProxy) {
          return $account->id();
        }
      }),
      $builder->produce('entity_load')
        ->map('id', $builder->fromParent())
        ->map('type', $builder->fromValue('user'))
    ));

    $registry->addFieldResolver('User', 'hasPermission', $builder->compose(
      $builder->callback(function ($user, $args) {
        if ($user instanceof UserInterface) {
          return $user->hasPermission($args['permission']);
        }
        return FALSE;
      })
    ));

    $registry->addFieldResolver('User', 'hasRole', $builder->compose(
      $builder->callback(function ($user, $args) {
        if ($user instanceof UserInterface) {
          return $user->hasRole($args['role']);
        }
        return FALSE;
      })
    ));

    $registry->addFieldResolver('User', 'roleIds', $builder->compose(
      $builder->callback(function ($user) {
        if ($user instanceof UserInterface) {
          $canView = $user->get('roles')->access('view', $user);
          if ($canView) {
            return $user->getRoles();
          }
        }
        return [];
      })
    ));
  }

}
