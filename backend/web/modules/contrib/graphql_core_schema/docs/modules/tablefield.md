# Tablefield (graphql_tablefield_schema)

Adds support for the [tablefield](https://www.drupal.org/project/tablefield) module.

## Schema

### Extension

```graphql
extend type FieldItemTypeTablefield {
  rows: [[String]]
}
```

## Examples

```graphql
query getNode {
  node: entityById(entityType: NODE, id: 5) {
    ... on Node {
      fieldTable
    }
  }
}
```

```json
{
  "data": {
    "node": {
      "fieldTable": [
        ["Header 1", "Header 2", "Header 3"],
        ["Row 1", "Row 2", "Row 3"],
        ["Row 1", "Row 2", "Row 3"],
        ["Row 1", "Row 2", "Row 3"]
      ]
    }
  }
}
```
