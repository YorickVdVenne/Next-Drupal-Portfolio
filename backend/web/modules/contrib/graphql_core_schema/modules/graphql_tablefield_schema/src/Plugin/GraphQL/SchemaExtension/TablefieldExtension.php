<?php

namespace Drupal\graphql_tablefield_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\TypeAwareSchemaExtensionInterface;

/**
 * A schema extension for the table field element.
 *
 * @SchemaExtension(
 *   id = "tablefield",
 *   name = "Tablefield",
 *   description = "An extension that provides an array of rows for tablefield fields.",
 *   schema = "core_composable"
 * )
 */
class TablefieldExtension extends SdlSchemaExtensionPluginBase implements ContainerFactoryPluginInterface, TypeAwareSchemaExtensionInterface {

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
  public function getTypeExtensionDefinition(array $types) {
    // Only extend schema if the type exists.
    if (in_array('FieldItemTypeTablefield', $types)) {
      return "
        extend type FieldItemTypeTablefield {
          rows: [[String]]
        }
      ";
    }
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('FieldItemTypeTablefield', 'rows',
      $builder->produce('tablefield')
        ->map('table', $builder->fromParent())
    );
  }

}
