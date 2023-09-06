# Rokka (graphql_rokka_schema)

Adds support for the [rokka](https://www.drupal.org/project/rokka) module.

## Schema

### Extension

<<< @/../modules/graphql_rokka_schema/graphql/rokka.extension.graphqls{graphql}

## Examples

```graphql
query {
  entityById(entityType: MEDIA, id: "3") {
    ... on MediaImage {
      fieldImage {
        entity {
          rokkaMetadata {
            hash
            height
            width
            format
            filesize
          }
        }
      }
    }
  }
}
```

```json
{
  "data": {
    "entityById": {
      "fieldImage": {
        "entity": {
          "rokkaMetadata": {
            "hash": "ee66313163ff11940509d8c3ec67ba82fa0a3a62",
            "height": 768,
            "width": 768,
            "format": "jpg",
            "filesize": 123847
          }
        }
      }
    }
  }
}
```
