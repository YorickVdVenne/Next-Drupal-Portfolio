# Local Tasks

Adds a `localTasks` field on the `InternalUrl` and `EntityUrl` interface to get the [local tasks](https://www.drupal.org/docs/drupal-apis/menu-api/providing-module-defined-local-tasks) for a route.

## Schema

### Base

<<< @/../graphql/local_tasks.base.graphqls{graphql}

### Extension

<<< @/../graphql/local_tasks.extension.graphqls{graphql}

## Example

```graphql
query {
  route(path: "/de/node/15957/edit") {
    ... on InternalUrl {
      localTasks {
        title
        url {
          path
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
      "localTasks": [
        {
          "title": "View",
          "url": {
            "path": "/de/my-little-node"
          }
        },
        {
          "title": "Edit",
          "url": {
            "path": "/de/node/15957/edit"
          }
        },
        {
          "title": "Delete",
          "url": {
            "path": "/de/node/15957/delete"
          }
        },
        {
          "title": "Revisions",
          "url": {
            "path": "/de/node/15957/revisions"
          }
        },
        {
          "title": "Translate",
          "url": {
            "path": "/de/node/15957/translations"
          }
        }
      ]
    }
  }
}
```
