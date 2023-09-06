# Messenger (graphql_messenger)

Adds a `messengerMessages` query field to fetch all Drupal messenger messages collected during resolving.

## Schema

### Base

<<< @/../modules/graphql_messenger/graphql/messenger.base.graphqls{graphql}

### Extension

<<< @/../modules/graphql_messenger/graphql/messenger.extension.graphqls{graphql}

## Examples

We assume this custom mutation adds a message using Drupal's `Messenger`.
These messages are collected across multiple requests until they are fetched.
Normally this results in these messages collecting until a page rendered by Drupal is visited.

```graphql
mutation {
  messengerMessages
  customMutationThatAddsMessages
}
```

```json
{
  "data": {
    "messengerMessages": [
      {
        "type": "error",
        "message": "This error is added by something we have no control over."
      }
    ],
    "customMutationThatAddsMessages": true
  }
}
```

## How it works

The resolver returns an instance of a special class that implements a `jsonSerialize()` method.
Here the messages are deleted and returned.

<<< @/../modules/graphql_messenger/src/MessageWrapper.php
