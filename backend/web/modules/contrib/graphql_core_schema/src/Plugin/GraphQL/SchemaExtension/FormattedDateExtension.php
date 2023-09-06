<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\TypeAwareSchemaExtensionInterface;

/**
 * A schema extensions for date formatting.
 *
 * @SchemaExtension(
 *   id = "formatted_date",
 *   name = "Formatted Date and Time",
 *   description = "Provides fields to get PHP and Drupal formatted dates.",
 *   schema = "core_composable"
 * )
 */
class FormattedDateExtension extends SdlSchemaExtensionPluginBase implements TypeAwareSchemaExtensionInterface {

  /**
   * Array of possible types to extend.
   *
   * @var string[]
   */
  const POSSIBLE_TYPES = [
    'FieldItemTypeChanged',
    'FieldItemTypeCreated',
    'FieldItemTypeTimestamp',
    'FieldItemTypeDatetime',
    'FieldItemTypeDaterange',
  ];

  /**
   * {@inheritdoc}
   */
  public function getTypeExtensionDefinition(array $types) {
    // First check if this interface was generated. If not, that means no date
    // item exists.
    if (!in_array('FieldItemTypeTimestampInterface', $types)) {
      return '';
    }

    $extension = [
      $this->loadDefinitionFile('extension'),
    ];
    foreach (self::POSSIBLE_TYPES as $type) {
      if (in_array($type, $types)) {
        $extension[] = "extend type $type {
          formatted(format: String, drupalDateFormat: DrupalDateFormat): String!
        }";
      }
    }

    if (in_array('FieldItemTypeDaterange', $types)) {
      $extension[] = $this->loadDefinitionFile('FieldItemTypeDaterange');
    }

    return implode("\n", $extension);
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver(
      'FieldItemTypeTimestampInterface',
      'formatted',
      $builder->produce('formatted_date')
        ->map('timestamp', $builder->fromParent())
        ->map('format', $builder->fromArgument('format'))
        ->map('drupalDateFormat', $builder->fromArgument('drupalDateFormat'))
    );

    $registry->addFieldResolver(
      'FieldItemTypeDaterange',
      'startDate',
      $builder->produce('formatted_date')
        ->map('timestamp', $builder->fromParent())
        ->map('format', $builder->fromArgument('format'))
        ->map('drupalDateFormat', $builder->fromArgument('drupalDateFormat'))
    );
    $registry->addFieldResolver(
      'FieldItemTypeDaterange',
      'endDate',
      $builder->produce('formatted_date')
        ->map('timestamp', $builder->fromParent())
        ->map('format', $builder->fromArgument('format'))
        ->map('drupalDateFormat', $builder->fromArgument('drupalDateFormat'))
    );

    $registry->addFieldResolver(
      'FieldItemTypeDaterange',
      'formatted',
      $builder->produce('formatted_date_range')
        ->map('start',
          $builder->callback(function (DateRangeItem $value) {
            return $value->get('start_date');
          })
        )
        ->map('end',
          $builder->callback(function (DateRangeItem $value) {
            return $value->get('end_date');
          })
        )
        ->map('format', $builder->fromArgument('format'))
        ->map('drupalDateFormat', $builder->fromArgument('drupalDateFormat'))
    );
  }

}
