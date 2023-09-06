# Debugging

Adds a query field to return the headers from the request.

::: danger NOT FOR PRODUCTION
Only use this during local development or on other development environments.
Using this extension in production can potentially leak sensitive information.
:::

## Schema

### Base

<<< @/../modules/graphql_debugging/graphql/debugging.base.graphqls{graphql}

### Extension

<<< @/../modules/graphql_debugging/graphql/debugging.extension.graphqls{graphql}

## Example

```graphql
query {
  requestHeaders {
    key
    value
  }
}
```

```json
{
  "data": {
    "requestHeaders": [
      {
        "key": "x-lando",
        "value": ["on"]
      },
      {
        "key": "x-forwarded-server",
        "value": ["39dad861e9d8"]
      },
      {
        "key": "x-forwarded-host",
        "value": ["foobar.lndo.site"]
      },
      {
        "key": "sec-fetch-site",
        "value": ["same-origin"]
      },
      {
        "key": "referer",
        "value": [
          "https://foobar.lndo.site/de/admin/config/graphql/servers/manage/graphql_compose_server/explorer"
        ]
      },
      {
        "key": "origin",
        "value": ["https://foobar.lndo.site"]
      },
      {
        "key": "cookie",
        "value": [
          "XDEBUG_SESSION=PHPSTORM; SSESSfa3d6941efe7a0c8c7da72e2eb8bc0e6=OH7liaho9Oe8kgb8UnEu8bP3KOHdnbaPrEeq4HEDAf6O13qq"
        ]
      }
    ]
  }
}
```
