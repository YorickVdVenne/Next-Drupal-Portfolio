# User Login

Adds mutations to handle user login, logout or password reset.

## Schema

### Base

<<< @/../graphql/user_login.base.graphqls{graphql}

### Extension

<<< @/../graphql/user_login.extension.graphqls{graphql}

## Examples

### Login

```graphql
mutation userLogin($username: String!, $password: String!) {
  userLogin(username: $username, password: $password) {
    error
    success
    uid
    name
  }
}
```

### Request Password Reset

```graphql
mutation userPasswordReset($username: String, $email: String) {
  userPasswordReset(username: $username, email: $email) {
    error
    success
  }
}
```

### Reset a password

```graphql
mutation userPasswordResetLogin($id: ID!, $timestamp: Int!, $hash: String!) {
  userPasswordResetLogin(id: $id, timestamp: $timestamp, hash: $hash) {
    error
    success
    token
  }
}
```
