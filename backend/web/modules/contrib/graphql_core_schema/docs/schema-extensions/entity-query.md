# Entity Query

Query one or more entities.

## Schema

### Base

<<< @/../graphql/entity_query.base.graphqls{graphql}

### Extension

<<< @/../graphql/entity_query.extension.graphqls{graphql}

## `entityById`

```graphql
query getNode {
  node: entityById(entityType: NODE, id: 5) {
    ... on Node {
      title
      body
    }
  }
}
```

## `entityQuery`

### Basic example

The `items` field has the type `Entity`, so you can easily get only the label or id without having to define a type.

```graphql
query getAllTerms {
  entityQuery(entityType: TAXONOMY_TERM, limit: 100) {
    items {
      label
    }
  }
}
```

### Advanced example

Here we fetch nodes of type "article" that are not older than the given timestamp. Results are sorted by `created`.

```graphql
query postsFromToday {
  entityQuery(
    entityType: NODE
    limit: 5
    sort: { field: "created", direction: DESC }
    filter: {
      conditions: [
        { field: "type", value: "article" }
        { field: "created", value: "1680072947", operator: GREATER_THAN }
      ]
    }
  ) {
    total
    items {
      ... on NodeArticle {
        title
        teaserText
        url {
          path
        }
      }
    }
  }
}
```
