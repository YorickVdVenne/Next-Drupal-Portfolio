<?php

namespace Drupal\graphql_telephone\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_core_schema\TypeAwareSchemaExtensionInterface;
use Drupal\telephone\Plugin\Field\FieldType\TelephoneItem;
use libphonenumber\CountryCodeToRegionCodeMap;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

/**
 * The telephone schema extension.
 *
 * Provides a field to access the parsed phone number on telephone fields.
 *
 * @SchemaExtension(
 *   id = "telephone",
 *   name = "Telephone",
 *   description = "Adds support for parsing and formatting phone numbers.",
 *   schema = "core_composable"
 * )
 */
class TelephoneExtension extends SdlSchemaExtensionPluginBase implements TypeAwareSchemaExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function getTypeExtensionDefinition(array $types) {
    // Only extend schema if the type exists.
    if (in_array('FieldItemTypeTelephone', $types)) {
      return $this->loadDefinitionFile('typeExtension');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    // Resolve the parsed phone number from a telephone field item.
    $registry->addFieldResolver('FieldItemTypeTelephone', 'parsed',
      $builder->compose(
        $builder->callback(function (TelephoneItem $item) {
          return $item->value ?? NULL;
        }),
        $builder->produce('phone_parser')
          ->map('value', $builder->fromParent())
          ->map('region', $builder->fromArgument('region'))
      )
    );

    // Adds the field to get the formatted phone number.
    $registry->addFieldResolver('ParsedPhoneNumber', 'format',
      $builder->produce('phone_formatter')
        ->map('phoneNumber', $builder->fromParent())
        ->map('format', $builder->fromArgument('format'))
    );

    $registry->addFieldResolver('ParsedPhoneNumber', 'countryCode',
      $builder->callback(function (PhoneNumber $value) {
        return $value->getCountryCode();
      })
    );

    $registry->addFieldResolver('ParsedPhoneNumber', 'type',
      $builder->callback(function (PhoneNumber $value) {
        $formatter = PhoneNumberUtil::getInstance();
        $type = $formatter->getNumberType($value);
        $values = PhoneNumberType::values();
        return $values[$type] ?? 'UNKNOWN';
      })
    );

    $registry->addFieldResolver('ParsedPhoneNumber', 'regionCodes',
      $builder->callback(function (PhoneNumber $value) {
        $code = $value->getCountryCode();
        if ($code) {
          return CountryCodeToRegionCodeMap::$countryCodeToRegionCodeMap[$code] ?? [];
        }
        return [];
      })
    );
  }

}
