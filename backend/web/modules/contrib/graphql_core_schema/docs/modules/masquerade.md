# Masquerade (graphql_masquerade_schema)

Adds support for the [masquerade](https://www.drupal.org/project/masquerade) module.

## Schema

### Base

<<< @/../modules/graphql_masquerade_schema/graphql/masquerade.base.graphqls{graphql}

### Extension

<<< @/../modules/graphql_masquerade_schema/graphql/masquerade.extension.graphqls{graphql}

## Examples

### Determine if the current session is masquerading

```graphql
query {
  masqueradeContext {
    isMasquerading
  }
}
```

```json
{
  "data": {
    "masqueradeContext": {
      "isMasquerading": true
    }
  }
}
```

### Switch back to the previous user

```graphql
mutation {
  masqueradeSwitchBack
}
```

```json
{
  "data": {
    "masqueradeSwitchBack": true
  }
}
```
