<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\Wrappers\QueryConnection;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * The schema extension for entity queries.
 *
 * @SchemaExtension(
 *   id = "entity_query",
 *   name = "Entity Query Extension",
 *   description = "An extension that provides a field to perform entity queries.",
 *   schema = "core_composable"
 * )
 */
class EntityQueryExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $this->addQueryFields($registry, $builder);

    // Re-usable connection type fields.
    $this->addConnectionFields('EntityQueryResult', $registry, $builder);
  }

  /**
   * Adds the needed query fields.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   The registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The builder.
   */
  protected function addQueryFields(ResolverRegistry $registry, ResolverBuilder $builder): void {
    $resolveEnumArgument = function ($name) {
      return function ($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field) use ($name) {
        return !empty($args[$name]) ? strtolower($args[$name]) : NULL;
      };
    };
    $resolveLangcode = $builder->defaultValue(
      $builder->callback($resolveEnumArgument('langcode')),
      $builder->produce('current_language'),
    );

    $registry->addFieldResolver('Query', 'entityQuery',
      $builder->produce('entity_query')
        ->map('entityType', $builder->fromArgument('entityType'))
        ->map('limit', $builder->fromArgument('limit'))
        ->map('offset', $builder->fromArgument('offset'))
        ->map('revisions', $builder->fromArgument('revisions'))
        ->map('sort', $builder->fromArgument('sort'))
        ->map('filter', $builder->fromArgument('filter'))
    );

    // First load the entity without translation and afterwards use custom
    // fallback producer to get translation if exists.
    $registry->addFieldResolver('Query', 'entityById',
      $builder->compose(
        $builder->produce('entity_load')
          ->map('type', $builder->callback($resolveEnumArgument('entityType')))
          ->map('id', $builder->fromArgument('id')),
        $builder->produce('entity_translation_fallback')
          ->map('entity', $builder->fromParent())
          ->map('fallback', $builder->fromValue(TRUE))
          ->map('langcode', $resolveLangcode)
      )
    );

    $registry->addFieldResolver('Query', 'entityByUuid',
      $builder->compose(
        $builder->produce('entity_load_by_uuid')
          ->map('type', $builder->callback($resolveEnumArgument('entityType')))
          ->map('uuid', $builder->fromArgument('uuid')),
        $builder->produce('entity_translation_fallback')
          ->map('entity', $builder->fromParent())
          ->map('fallback', $builder->fromValue(TRUE))
          ->map('langcode', $resolveLangcode)
      )
    );
  }

  /**
   * Adds the connection fields.
   *
   * @param string $type
   *   The type.
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   The registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The builder.
   */
  protected function addConnectionFields($type, ResolverRegistry $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver($type, 'total',
      $builder->callback(function (QueryConnection $connection) {
        return $connection->total();
      })
    );

    $registry->addFieldResolver($type, 'items',
      $builder->callback(function (QueryConnection $connection, $args, ResolveContext $context) {
        return $connection->items($context);
      })
    );
  }

}
