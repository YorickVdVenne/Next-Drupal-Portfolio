# Telephone (graphql_telephone)

Adds support for the [telephone](https://www.drupal.org/project/telephone) module.

Make sure to have at least one telephone field enabled in the schema.

The extension adds a `parsed` field on the `FieldItemTypeTelephone`
type that returns a the parsed number as a `ParsedPhoneNumber` type.
On it are some fields like `countryCode` or `type`. In addition you can get the
formatted number.

All exceptions during parsing and formatting are caught, so the field will
return NULL if the entered number is not valid.

## Schema

### Base

<<< @/../modules/graphql_telephone/graphql/telephone.base.graphqls{graphql}

### Extension

<<< @/../modules/graphql_telephone/graphql/telephone.typeExtension.graphqls{graphql}

## Examples

```graphql
query {
  entityById(entityType: NODE, id: "123") {
    ... on NodeContact {
      phone: fieldPhoneRawField {
        first {
          parsed {
            ...phoneNumber
          }
        }
      }
    }
  }
}

fragment phoneNumber on ParsedPhoneNumber {
  e164: format(format: E164)
  international: format(format: INTERNATIONAL)
  national: format(format: NATIONAL)
  rfc3966: format(format: RFC3966)
  default: format
  type
  countryCode
  regionCodes
}
```

```json
{
  "data": {
    "entityById": {
      "phone": {
        "first": {
          "parsed": {
            "e164": "+41791234567",
            "international": "+41 79 123 45 67",
            "national": "079 123 45 67",
            "rfc3966": "tel:+41-79-123-45-67",
            "default": "+41 79 123 45 67",
            "type": "MOBILE",
            "countryCode": 41,
            "regionCodes": ["CH"]
          }
        }
      }
    }
  }
}
```

## Data Producers

### phone_parser

Parse a phone number given in the `value` argument. Optionally provide a `region` argument if the value does not contain a country code.

Returns a [PhoneNumber](https://github.com/giggsey/libphonenumber-for-php/blob/master/src/PhoneNumber.php) instance.

```php
$registry->addFieldResolver('Query', 'contactNumber',
  $builder->produce('phone_parser')
    ->map('value', $builder->fromValue('+41791234567')),
);
```

### phone_formatter

Format a phone number. argument `phoneNumber` is a [PhoneNumber](https://github.com/giggsey/libphonenumber-for-php/blob/master/src/PhoneNumber.php) instance.

The `format` argument is the name of the format, e.g. `INTERNATIONAL` or `RFC3966`.

```php
$registry->addFieldResolver('Person', 'phoneFormatted',
  $builder->compose(
    $builder->produce('phone_parser')
      ->map('value', $builder->fromValue('+41791234567')),
    $builder->produce('phone_formatter')
      ->map('phoneNumber', $builder->fromParent())
      ->map('format', $builder->fromValue('INTERNATIONAL'))
  )
);
```
