# Metatag (graphql_metatag_schema)

Adds support for the [metatag](https://www.drupal.org/project/metatag) module.

## Schema

### Base

<<< @/../modules/graphql_metatag_schema/graphql/metatag.base.graphqls{graphql}

### Extension

<<< @/../modules/graphql_metatag_schema/graphql/metatag.extension.graphqls{graphql}

## Examples

### Get Metatags for a route

```graphql
query {
  route(path: "/de") {
    __typename
    ... on EntityUrl {
      metatags {
        id
        tag
        attributes {
          key
          value
        }
      }
    }
  }
}
```

```json
{
  "data": {
    "route": {
      "__typename": "EntityCanonicalUrl",
      "metatags": [
        {
          "id": "canonical_url",
          "tag": "link",
          "attributes": [
            {
              "key": "rel",
              "value": "canonical"
            },
            {
              "key": "href",
              "value": "https://example.com/de"
            }
          ]
        },
        {
          "id": "title",
          "tag": "meta",
          "attributes": [
            {
              "key": "name",
              "value": "title"
            },
            {
              "key": "content",
              "value": "Homepage | example.com"
            }
          ]
        }
      ]
    }
  }
}
```
