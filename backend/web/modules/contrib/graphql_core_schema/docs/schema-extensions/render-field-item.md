# Render Field Item

Adds `viewField` and `viewFieldItem` fields to get the rendered markup of any field.

```graphql
query getNode {
  node: entityById(entityType: NODE, id: 5) {
    ... on Node {
      bodyRawField {
        viewField
      }
    }
  }
}
```
