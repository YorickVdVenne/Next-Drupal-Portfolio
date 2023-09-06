# Language Switch Links

Adds a `LanguageSwitchLink` type and a `languageSwitchLinks` field.

## Schema

### Base

<<< @/../graphql/language_switch_links.base.graphqls{graphql}

### Extension

<<< @/../graphql/language_switch_links.extension.graphqls{graphql}

## Example

```graphql
query {
  route(path: "/de/admin/config/system/site-information") {
    ... on InternalUrl {
      languageSwitchLinks {
        active
        title
        language {
          id
        }
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
      "languageSwitchLinks": [
        {
          "active": true,
          "title": "German",
          "language": {
            "id": "de"
          },
          "url": {
            "path": "/de/admin/config/system/site-information"
          }
        },
        {
          "active": false,
          "title": "French",
          "language": {
            "id": "fr"
          },
          "url": {
            "path": "/fr/admin/config/system/site-information"
          }
        },
        {
          "active": false,
          "title": "Italian",
          "language": {
            "id": "it"
          },
          "url": {
            "path": "/it/admin/config/system/site-information"
          }
        }
      ]
    }
  }
}
```
