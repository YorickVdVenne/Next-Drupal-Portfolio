# Environment Indicator (graphql_environment_indicator)

Adds support for the [environment_indicator](https://www.drupal.org/project/environment_indicator) module.

## Schema

### Base

<<< @/../modules/graphql_environment_indicator/graphql/environment_indicator.base.graphqls{graphql}

### Extension

<<< @/../modules/graphql_environment_indicator/graphql/environment_indicator.extension.graphqls{graphql}

## Examples

```graphql
query {
  activeEnvironment {
    name
    fgColor
    bgColor
  }
}
```

```json
{
  "data": {
    "activeEnvironment": {
      "name": "LOCAL",
      "fgColor": "#ffffff",
      "bgColor": "#8618cb"
    }
  }
}
```
