<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\CoreSchemaExtensionInterface;

/**
 * A schema extension to read field configuration.
 *
 * @SchemaExtension(
 *   id = "field_config",
 *   name = "Field Config Extension",
 *   description = "An extension that provides additional properties for field config entities.",
 *   schema = "core_composable"
 * )
 */
class FieldConfigExtension extends SdlSchemaExtensionPluginBase implements CoreSchemaExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies() {
    return ['field_config', 'field_storage_config'];
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

    $registry->addFieldResolver('FieldItemList', 'fieldConfig',
      $builder->callback(function ($value) {
        if ($value instanceof FieldItemListInterface) {
          return $value->getFieldDefinition();
        }
      })
    );

    $registry->addFieldResolver('FieldConfig', 'fieldStorageDefinition',
      $builder->callback(function (FieldDefinitionInterface $field) {
        return $field->getFieldStorageDefinition();
      })
    );

    $registry->addFieldResolver('FieldDefinition', 'name',
      $builder->callback(function (FieldDefinitionInterface $field) {
        return $field->getName();
      })
    );

    $registry->addFieldResolver('FieldDefinition', 'type',
      $builder->callback(function (FieldDefinitionInterface $field) {
        return $field->getType();
      })
    );

    $registry->addFieldResolver('FieldDefinition', 'targetEntityTypeId',
      $builder->callback(function (FieldDefinitionInterface $field) {
        return $field->getTargetEntityTypeId();
      })
    );
    $registry->addFieldResolver('FieldDefinition', 'targetBundle',
      $builder->callback(function (FieldDefinitionInterface $field) {
        return $field->getTargetBundle();
      })
    );
    $registry->addFieldResolver('FieldDefinition', 'isRequired',
      $builder->callback(function (FieldDefinitionInterface $field) {
        return $field->isRequired();
      })
    );
    $registry->addFieldResolver('FieldDefinition', 'isReadOnly',
      $builder->callback(function (FieldDefinitionInterface $field) {
        return $field->isReadOnly();
      })
    );
    $registry->addFieldResolver('FieldDefinition', 'isTranslatable',
      $builder->callback(function (FieldDefinitionInterface $field) {
        return $field->isTranslatable();
      })
    );
    $registry->addFieldResolver('FieldDefinition', 'uniqueIdentifier',
      $builder->callback(function (FieldDefinitionInterface $field) {
        return $field->getUniqueIdentifier();
      })
    );

    $registry->addFieldResolver('BaseFieldDefinition', 'description',
      $builder->callback(function (FieldDefinitionInterface $field) {
        return $field->getDescription();
      })
    );

    $registry->addTypeResolver('FieldDefinition', function ($value) {
      if ($value instanceof BaseFieldDefinition) {
        return 'BaseFieldDefinition';
      }
      return 'FieldConfig';
    });
  }

}
