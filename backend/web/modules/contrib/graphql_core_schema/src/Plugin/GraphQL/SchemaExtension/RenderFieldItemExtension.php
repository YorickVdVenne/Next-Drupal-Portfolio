<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaInterfaceExtensionInterface;
use Drupal\graphql_core_schema\EntitySchemaBuilder;
use GraphQL\Type\Definition\Type;

/**
 * A schema extension for rendered fields.
 *
 * @SchemaExtension(
 *   id = "render_field_item",
 *   name = "Render Field Item",
 *   description = "An extension that adds fields to render field items.",
 *   schema = "core_composable"
 * )
 */
class RenderFieldItemExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaInterfaceExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function getBaseDefinition() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionDefinition() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getInterfaceExtender() {
    return [
      'FieldItemType' => [
        function (EntitySchemaBuilder $builder, array &$fields) {
          $fields['viewFieldItem'] = [
            'type' => Type::nonNull(Type::string()),
            'args' => [
              'viewMode' => Type::string(),
            ],
            'description' => 'Render the field item in the given viewmode.',
          ];
        },
      ],
      'FieldItemList' => [
        function (EntitySchemaBuilder $builder, array &$fields) {
          $fields['viewField'] = [
            'type' => Type::nonNull(Type::string()),
            'args' => [
              'viewMode' => Type::string(),
            ],
            'description' => 'Render the field in the given viewmode.',
          ];
        },
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('FieldItemType', 'viewFieldItem',
      $builder->produce('render_field')
        ->map('field', $builder->fromParent())
        ->map('viewMode', $builder->fromArgument('viewMode'))
    );

    $registry->addFieldResolver('FieldItemList', 'viewField',
      $builder->produce('render_field')
        ->map('field', $builder->fromParent())
        ->map('viewMode', $builder->fromArgument('viewMode'))
    );
  }

}
