# Reverse Entity Reference

Adds fields to query for entities that reference an entity.

## Schema

### Base

```graphql
type ReverseReferenceContext {
  targetId: String
}
```

### Extension

<<< @/../graphql/reverse_entity_reference.extension.graphqls{graphql}

```graphql
extend interface Entity {
  reverseReference: ReverseReferenceContext
}
```

## Examples

Here we load a specific person taxonomy term by ID and then query for all _nodes_ that reference the term `943` via the `field_contact_person` or `field_author_person` fields.

```graphql
query {
  entityById(entityType: TAXONOMY_TERM, id: 943) {
    reverseReferences {
      query(
        referenceFields: ["field_contact_person", "field_author_person"]
        entityType: NODE
      ) {
        items {
          id
          label
        }
      }
    }
  }
}
```

In this example we use the `currentUser` field from the [User extension](/schema-extensions/user) to get the current user.
Then we can get the 5 newest nodes and documents they have created.

```graphql
query {
  currentUser {
    reverseReferences {
      nodes: query(
        referenceFields: ["uid"]
        entityType: NODE
        limit: 5
        sort: { field: "created", direction: DESC }
      ) {
        items {
          label
        }
      }
      documents: query(
        referenceFields: ["uid"]
        entityType: MEDIA
        filter: { conditions: [{ field: "bundle", value: "document" }] }
        limit: 5
        sort: { field: "created", direction: DESC }
      ) {
        items {
          label
        }
      }
    }
  }
}
```

```json
{
  "data": {
    "currentUser": {
      "reverseReferences": {
        "nodes": {
          "items": [
            {
              "label": "My little page"
            },
            {
              "label": "Homepage"
            },
            {
              "label": "Contact"
            }
          ]
        },
        "documents": {
          "items": [
            {
              "label": "Terms and conditions.pdf"
            }
          ]
        }
      }
    }
  }
}
```
