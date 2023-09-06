# Routing

Adds additional types and fields for routing.

## Types

<<< @/../graphql/routing.base.graphqls{graphql}

## Extension

<<< @/../graphql/routing.extension.graphqls{graphql}

## How the `route` query field works

The field itself will try to behave as close as possible to how Drupal would when making a request. This means it will handle path aliases, redirects or set the language.

The `entity` field on the `EntityUrl` interface tries its best to find the correct entity.
For paths like `/node/123`, this is very easy, as there is only a single ID present. This of course also works for the node's alias.
And even a path like `/de/node/15957/revisions/16072/view` works.

The problem is for routes that are provided by contrib modules or your own code that are custom:
`/node/123/person/789`

What would the `entity` be here? A Drupal controller might render a `person` with ID `789` here, but there is no way to know that when resolving the `entity` field.

The best way to solve this problem would be to not rely on the `route` query, but instead directly query for the person entity with ID `789`.

However, there is an option to implement a custom `Url` type for a specific route, where you can handle the entity yourself.

## Examples

### Node Canonical

```graphql
query {
  route(path: "/de/my-little-page") {
    __typename
    ... on EntityUrl {
      entity {
        label
      }
    }
  }
}
```

```json
{
  "data": {
    "route": {
      "__typename": "EntityCanonicalUrl",
      "entity": {
        "label": "My little page"
      }
    }
  }
}
```

### Redirects

In this example the resolved value is a redirect.

```graphql
query {
  route(path: "/de/node/15957") {
    __typename
    ... on RedirectUrl {
      path
    }
  }
}
```

```json
{
  "data": {
    "route": {
      "__typename": "RedirectUrl",
      "path": "/de/my-little-page"
    }
  }
}
```

### Revisions

Revisions are supported as long as the routes follow normal Drupal conventions.

The route entity resolver looks at the route parameters and searches for one that matches `ENTITY_TYPE_revision`, e.g. `node_revision`.
If it finds one, it uses the parameter value as the ID to load the entity.

```graphql
query {
  route(path: "/de/node/15957/revisions/16072/view") {
    ... on DefaultEntityUrl {
      entity {
        ... on Node {
          vid
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
      "entity": {
        "vid": 16072
      }
    }
  }
}
```
