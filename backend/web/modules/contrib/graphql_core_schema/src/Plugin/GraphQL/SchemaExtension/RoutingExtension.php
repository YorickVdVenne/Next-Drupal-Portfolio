<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\TypeAwareSchemaExtensionInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * A schema extension for routing.
 *
 * @SchemaExtension(
 *   id = "routing",
 *   name = "Routing",
 *   description = "Add URL and routing fileds and types.",
 *   schema = "core_composable"
 * )
 */
class RoutingExtension extends SdlSchemaExtensionPluginBase implements TypeAwareSchemaExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function getTypeExtensionDefinition(array $types) {
    // Extend the RedirectUrl type with a redirect field if the redirect
    // entity is enabled.
    if (in_array('Redirect', $types)) {
      return $this->loadDefinitionFile('Redirect');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();
    $registry->addTypeResolver('Url', function ($value) {
      if ($value instanceof EntityInterface && $value->getEntityTypeId() === 'redirect') {
        return 'RedirectUrl';
      }
      elseif ($value instanceof Url) {
        if ($value->isExternal()) {
          return 'ExternalUrl';
        }
        return $this->getInternalUrlType($value);
      }

      return 'Url';
    });
    $registry->addFieldResolver('Query', 'route', $builder->compose(
      $builder->produce('get_route')->map('path', $builder->fromArgument('path'))
    ));

    $registry->addFieldResolver('EntityUrl', 'entity', $builder->compose(
      $builder->produce('enhanced_route_entity')->map('url', $builder->fromParent()),
      $builder->callback(function ($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field) {
        if ($value && $value instanceof TranslatableInterface) {
          $language = $field->getContextValue(name: 'language');
          if ($language && $value->hasTranslation($language)) {
            return $value->getTranslation($language);
          }
        }
        return $value;
      })
    ));
    $registry->addFieldResolver('InternalUrl', 'routeName',
      $builder->callback(function (Url $url) {
        return $url->getRouteName();
      })
    );
    $registry->addFieldResolver('InternalUrl', 'internalPath',
      $builder->callback(function (Url $url) {
        return $url->getInternalPath();
      })
    );
    $registry->addFieldResolver('RedirectUrl', 'path',
      $builder->callback(function ($redirect) {
        /** @var \Drupal\redirect\Entity\Redirect $redirect */
        return $redirect->getRedirectUrl()->toString(TRUE)->getGeneratedUrl();
      })
    );
    $registry->addFieldResolver('RedirectUrl', 'redirect', $builder->fromParent());
  }

  /**
   * Determine if the URL is entity canonical.
   *
   * @param \Drupal\Core\Url $url
   *   The URL to check.
   *
   * @return string
   *   The internal URL type.
   */
  protected function getInternalUrlType(Url $url): string {
    if ($url->isRouted()) {
      $routeName = $url->getRouteName();
      if ($routeName === 'entity.node.preview') {
        return 'DefaultEntityUrl';
      }
      $parts = explode('.', $routeName);
      if (count($parts) === 3) {
        [$prefix, $entityType, $suffix] = $parts;
        $parameters = $url->getRouteParameters();

        if (($prefix === 'entity') && array_key_exists($entityType, $parameters)) {
          if ($suffix === 'canonical') {
            return 'EntityCanonicalUrl';
          }
          return 'DefaultEntityUrl';
        }
      }
    }

    return 'DefaultInternalUrl';
  }

}
