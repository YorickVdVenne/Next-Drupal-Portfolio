# GraphQL Telephone

Adds support to parse and format phone numbers.

## Usage via core_composable schema

Make sure to have at least one telephone field enabled in the schema.

The extension adds a `parsed` field on the `FieldItemTypeTelephone`
type that returns a the parsed number as a `ParsedPhoneNumber` type.
On it are some fields like `countryCode` or `type`. In addition you can get the
formatted number.

All exceptions during parsing and formatting are caught, so the field will
return NULL if the entered number is not valid.

### Example Queries

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

The result:

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

A more specific query:

```graphql
fragment paragraphGovernmentContactPerson on TaxonomyTermPerson {
    phoneNumber: fieldPhonenumberRawField {
        first {
            parsed {
                e164: format(format: E164)
                international: format(format: INTERNATIONAL)
            }
        }
    }
}
```

## Custom parsing

Two data producers are available to also add support for non-telephone fields.

### Parse number and resolve to ParsedPhoneNumber

```php
<?php

$registry->addFieldResolver('Person', 'phone',
  $builder->compose(
    $builder->callback(function () {
      // Can be from parent or some other place.
      return '+41791234567';
    }),
    $builder->produce('phone_parser')->map('value', $builder->fromParent())
  )
);
```

### Parse number and format it

```php
<?php

$registry->addFieldResolver('Person', 'phoneFormatted',
  $builder->compose(
    $builder->callback(function () {
      // Can be from parent or some other place.
      return '+41791234567';
    }),
    $builder->produce('phone_parser')->map('value', $builder->fromParent()),
    $builder->produce('phone_formatter')
      ->map('phoneNumber', $builder->fromParent())
      ->map('format', $builder->fromValue('INTERNATIONAL'))
  )
);
```
