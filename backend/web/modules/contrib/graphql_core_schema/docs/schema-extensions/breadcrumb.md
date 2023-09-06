# Breadcrumb

Adds types and fields to get the breadcrumb for a route.

## Schema

### Base

<<< @/../graphql/breadcrumb.base.graphqls{graphql}

### Extension

<<< @/../graphql/breadcrumb.extension.graphqls{graphql}

## Example

```graphql
query {
  route(path: "/de/admin/config/system/site-information") {
    ... on InternalUrl {
      breadcrumb {
        url {
          path
        }
        title
      }
    }
  }
}
```

```json
{
  "data": {
    "route": {
      "breadcrumb": [
        {
          "url": {
            "path": "/de"
          },
          "title": "Startseite"
        },
        {
          "url": {
            "path": "/de/admin"
          },
          "title": "Verwaltung"
        },
        {
          "url": {
            "path": "/de/admin/config"
          },
          "title": "Konfiguration"
        },
        {
          "url": {
            "path": "/de/admin/config/system"
          },
          "title": "System"
        }
      ]
    }
  }
}
```
