# User

Adds a query field to get the current user and additional fields on the `User` type to check for permissions or roles.

## Schema

### Extension

<<< @/../graphql/user.extension.graphqls{graphql}

## Examples

### Get current user

```graphql
query {
  currentUser {
    id
    hasPermission(permission: "access toolbar")
    hasRole(role: "administrator")
    roleIds
  }
}
```

```json
{
  "data": {
    "currentUser": {
      "id": "1",
      "hasPermission": true,
      "hasRole": true,
      "roleIds": ["authenticated", "administrator"]
    }
  }
}
```
