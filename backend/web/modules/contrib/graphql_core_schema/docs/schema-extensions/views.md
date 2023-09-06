# Views

Adds a query field to execute a view and return the resulting entities.

::: warning

After enabling the extension and saving the server form a new subform appears where you can select which views should be enabled.
By default no view is enabled and it's not possible to execute any view.

:::

## Schema

### Base

The extension generates types for every enabled view, including the available arguments:

```graphql
type ViewExecutableMediaDefault implements ViewExecutable {
  execute(
    langcode: String
    name: String
    page: ID
    sortBy: String
    sortOrder: String
    status: String
    type: String
  ): ViewExecutableResult
  itemsPerPage: Int!
}

type ViewExecutableWhoSOnlineBlock implements ViewExecutable {
  execute(page: ID, sortBy: String, sortOrder: String): ViewExecutableResult
  itemsPerPage: Int!
}
```

### Extension

<<< @/../graphql/views.extension.graphqls{graphql}

## Examples

### Simple view

```graphql
query {
  entityById(entityType: VIEW, id: "who_s_online") {
    ... on View {
      executable {
        execute {
          rows {
            ... on User {
              name
              uid
            }
          }
        }
      }
    }
  }
}
```

### With arguments

You can also pass arguments to the view for pagination, filtering or sorting.

```graphql
query {
  entityById(entityType: VIEW, id: "media") {
    ... on View {
      executable {
        ... on ViewExecutableMediaDefault {
          execute(name: "test", sortBy: "created") {
            rows {
              ... on Media {
                label
              }
            }
          }
        }
      }
    }
  }
}
```
