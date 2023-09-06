<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;
use Drupal\graphql_core_schema\CoreSchemaInterfaceExtensionInterface;
use Drupal\graphql_core_schema\EntitySchemaBuilder;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Adds a field to perform reverse entity reference queries.
 *
 * @SchemaExtension(
 *   id = "reverse_entity_reference",
 *   name = "Reverse Entity Reference",
 *   description = "Adds a field to perform reverse entity reference queries.",
 *   schema = "core_composable"
 * )
 */
class ReverseEntityReference extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface, CoreSchemaInterfaceExtensionInterface {

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
    return ['entity_query'];
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseDefinition() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getInterfaceExtender() {
    return [
      'Entity' => [
        function (EntitySchemaBuilder $builder, array &$fields) {
          $fields['reverseReference'] = [
            'type' => new ObjectType([
              'name' => 'ReverseReferenceContext',
              'fields' => [
                'targetId' => [
                  'type' => Type::string(),
                  'description' => 'The target ID used for the reverse reference query.',
                ],
              ],
            ]),
            'description' => 'Query for entities that reference this entity.',
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

    $registry->addFieldResolver('Entity', 'reverseReferences',
      $builder->callback(function (EntityInterface $entity) {
        return $entity;
      })
    );

    $idProducer = $builder->produce('entity_id')->map('entity', $builder->fromParent());

    $registry->addFieldResolver('ReverseReferenceContext', 'query',
      $builder->produce('reverse_entity_query')
        ->map('targetId', $idProducer)
        ->map('referenceFields', $builder->fromArgument('referenceFields'))
        ->map('entityType', $builder->fromArgument('entityType'))
        ->map('limit', $builder->fromArgument('limit'))
        ->map('offset', $builder->fromArgument('offset'))
        ->map('revisions', $builder->fromArgument('revisions'))
        ->map('sort', $builder->fromArgument('sort'))
        ->map('filter', $builder->fromArgument('filter')),
    );

    $registry->addFieldResolver('ReverseReferenceContext', 'targetId', $idProducer);
  }

}
