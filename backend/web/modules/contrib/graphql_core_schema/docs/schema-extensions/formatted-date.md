# Formatted Date

Get formatted dates from date/timestamp fields.

This adds a `formatted` field to all date or timestamp fields.
Also contains support for the `daterange` field type.

## Schema

### Extension

<<< @/../graphql/formatted_date.extension.graphqls{graphql}
<<< @/../graphql/formatted_date.FieldItemTypeDaterange.graphqls{graphql}

## Example with Drupal Format

Use the `drupalDateFormat` argument with the name of the Drupal date format:

```graphql
query {
  entityById(entityType: NODE, id: 5) {
    ... on Node {
      fieldDate {
        formatted(drupalDateFormat: HTML_DATETIME)
      }
    }
  }
}
```

## Example with PHP Format

Use the `format` argument if you want to provide the [datetime.format](https://www.php.net/manual/en/datetime.format.php) yourself:

```graphql
query {
  entityById(entityType: NODE, id: 5) {
    ... on Node {
      fieldDate {
        formatted(format: "Y")
      }
    }
  }
}
```
