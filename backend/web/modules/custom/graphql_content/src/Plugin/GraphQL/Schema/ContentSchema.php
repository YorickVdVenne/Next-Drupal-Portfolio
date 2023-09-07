<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Schema;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\graphql_content\Wrappers\QueryConnection;

/**
 * @Schema(
 *   id = "graphql_content",
 *   name = "Graphql content schema"
 * )
 */
class ContentSchema extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry();

    $this->addQueryFields($registry, $builder);
    $this->addProjectFields($registry, $builder);

    // Re-usable connection type fields.
    $this->addConnectionFields('ProjectConnection', $registry, $builder);

    return $registry;
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
  */
  protected function addProjectFields(ResolverRegistry $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver('Project', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Project', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent()),
      )
    );

    $registry->addFieldResolver('Project', 'brand',
    $builder->produce('property_path')
      ->map('type', $builder->fromValue('entity:node'))
      ->map('value', $builder->fromParent())
      ->map('path', $builder->fromValue('field_project_brand.value'))
    );

    $registry->addFieldResolver('Project', 'period',
    $builder->produce('property_path')
      ->map('type', $builder->fromValue('entity:node'))
      ->map('value', $builder->fromParent())
      ->map('path', $builder->fromValue('field_project_period.value'))
    );

    $registry->addFieldResolver('Project', 'description',
    $builder->produce('property_path')
      ->map('type', $builder->fromValue('entity:node'))
      ->map('value', $builder->fromParent())
      ->map('path', $builder->fromValue('body.value'))
    );

    $registry->addFieldResolver('Project', 'roles',
    $builder->produce('entity_reference')
      ->map('entity', $builder->fromParent())
      ->map('field', $builder->fromValue('field_roles'))
    );

    $registry->addFieldResolver('Project', 'technologies',
    $builder->produce('entity_reference')
      ->map('entity', $builder->fromParent())
      ->map('field', $builder->fromValue('field_technologies'))
    );

    $registry->addFieldResolver('Project', 'codeLink',
    $builder->produce('property_path')
      ->map('type', $builder->fromValue('entity:node'))
      ->map('value', $builder->fromParent())
      ->map('path', $builder->fromValue('field_code_link.value'))
    );

    $registry->addFieldResolver('Project', 'siteLink',
    $builder->produce('property_path')
      ->map('type', $builder->fromValue('entity:node'))
      ->map('value', $builder->fromParent())
      ->map('path', $builder->fromValue('field_site_link.value'))
    );

    $registry->addFieldResolver('Project', 'featured',
    $builder->produce('property_path')
      ->map('type', $builder->fromValue('entity:node'))
      ->map('value', $builder->fromParent())
      ->map('path', $builder->fromValue('field_featured_project.value'))
    );

    $registry->addFieldResolver('Role', 'id',
    $builder->produce('entity_id')
      ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Role', 'name',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Technology', 'id',
    $builder->produce('entity_id')
      ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Technology', 'name',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addQueryFields(ResolverRegistry $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver('Query', 'project',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['project']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'projects',
      $builder->produce('query_projects')
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
    );
  }

  /**
   * @param string $type
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addConnectionFields($type, ResolverRegistry $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver($type, 'total',
      $builder->callback(function (QueryConnection $connection) {
        return $connection->total();
      })
    );

    $registry->addFieldResolver($type, 'items',
      $builder->callback(function (QueryConnection $connection) {
        return $connection->items();
      })
    );
  }

}
