# Getting Started

`graphql_core_schema` is a custom schema for Drupal's [graphql](https://www.drupal.org/project/graphql) module, which is its only dependency.

## Installation

```bash
composer require drupal/graphql drupal/graphql_core_schema
```

## Enable

```bash
drush en graphql_core_schema
```

## Create Schema

1. Go to the GraphQL configuration page at `/admin/config/graphql`.
2. Click on "Add new server".
3. Select "Core Composable Schema" from the "Schema" dropdown.
4. Save the configuration.

## Configuration

1. Click on "Edit" for the newly created server at `/admin/config/graphql`.
2. Choose at least one entity type under "Enabled entity types".
3. Select the fields you want to enable under "Enabled fields".
4. Enable any required extensions.
5. Save the configuration.
6. Rebuild the cache.

## Usage

You can now navigate to the "Explorer" tab and start writing your first query!
