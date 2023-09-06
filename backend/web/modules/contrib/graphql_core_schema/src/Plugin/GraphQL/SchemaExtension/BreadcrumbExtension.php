<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Link;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;

/**
 * A schema extension for breadcrumbs.
 *
 * @SchemaExtension(
 *   id = "breadcrumb",
 *   name = "Breadcrumb",
 *   description = "An extension that provides breadcrumbs.",
 *   schema = "core_composable"
 * )
 */
class BreadcrumbExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionDependencies() {
    return ['routing'];
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();
    $registry->addFieldResolver('InternalUrl', 'breadcrumb', $builder->compose(
      $builder->produce('breadcrumb')->map('url', $builder->fromParent())
    ));
    $registry->addFieldResolver('Breadcrumb', 'title',
      $builder->callback(function (Link $link) {
        // Handle all possible return values for the text.
        $text = $link->getText();
        if (is_string($text)) {
          return $text;
        }
        elseif ($text instanceof MarkupInterface) {
          return (string) $text;
        }
        elseif (!empty($text['#markup'])) {
          return $text['#markup'];
        }

        // @TODO: How is this possible?
        return NULL;
      })
    );
    $registry->addFieldResolver('Breadcrumb', 'url',
      $builder->callback(function (Link $link) {
        return $link->getUrl();
      })
    );
  }

}
