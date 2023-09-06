# GraphQL Core Schema

Fully configurable automatic schema based on entities and fields, built on top of the [GraphQL module](https://www.drupal.org/project/graphql).
It can be seen as the successor to the graphql_core module that existed before the V4 release.

The base module provides a custom schema called `core_composable`, which is fully configurable:

- Select which entity types to expose
- Select which fields you want to expose

The default schema without any entity types selected is extremely small. It comes with a few sane interfaces like `Entity`, `FieldItemList` or `FieldItemType`.

## Included features

The module comes bundled with over a dozen schema extensions you can enable:

| Schema Extension      | Description                                                                                                            |
| --------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| Breadcrumb            | Adds types and fields to get the breadcrumb for a route.                                                               |
| Entity Query          | Implements the `entityById` and `entityQuery` fields.                                                                  |
| Field Config          | Adds support to get information about Drupal fields (like `name`, `description` or `isTranslatable`).                  |
| Formatted Date        | Get formatted dates from date/timestamp fields.                                                                        |
| Language Switch Links | Get language switch links for a route.                                                                                 |
| Local Tasks           | Get the local task links for a route.                                                                                  |
| Media                 | Additional helper fields for dealing with media entities.                                                              |
| Menu                  | Load menus incl. links and child links.                                                                                |
| Render Field Item     | Adds `viewField` and `viewFieldItem` fields to get the rendered markup of any field.                                   |
| Routing               | Adds a `route` query field to load a URL from a string.                                                                |
| Taxonomy              | Additional fields for dealing with taxonomy terms (e.g. `children`).                                                   |
| User                  | Adds a query field to get the current user and additional fields on the `User` type to check for permissions or roles. |
| User Login            | Adds mutations to handle user login, logout or password reset.                                                         |
| Views                 | Adds a query field to execute a view and return the resulting entities.                                                |

## Included modules

Support for contrib modules or niche features is implemented via these included modules:

| Module                        | Project Link                                                                  | Description                                                                                                          |
| ----------------------------- | ----------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| graphql_messenger_schema      |                                                                               | Adds a `messengerMessages` query field to fetch all Drupal messenger messages collected during resolving.            |
| graphql_environment_indicator | [environment_indicator](https://www.drupal.org/project/environment_indicator) | Fields to get name and color of the active environment.                                                              |
| graphql_metatag_schema        | [metatag](https://www.drupal.org/project/metatag)                             | Types and fields to get metatags for routes and entities.                                                            |
| graphql_masquerade_schema     | [masquerade](https://www.drupal.org/project/masquerade)                       | Adds a `masqueradeContext` query field and a `masqueradeSwitchBack` mutation.                                        |
| graphql_rokka_schema          | [rokka](https://www.drupal.org/project/rokka)                                 | Support for the [rokka.io image service](https://rokka.io).                                                          |
| graphql_tablefield_schema     | [tablefield](https://www.drupal.org/project/tablefield)                       | Adds a field to `FieldItemTypeTablefield` with the structured table data.                                            |
| graphql_telephone             | [telephone](https://www.drupal.org/project/telephone)                         | Adds a field to `FieldItemTypeTelephone` with the parsed phone number with the possibility to get formatted numbers. |

## Required patches for the GraphQL module

To support Views and Entity Queries, the GraphQL4 module needs the missing SubrequestBuffer class. A [merge request is open on GitHub](https://github.com/drupal-graphql/graphql/pull/1313).

You can add the patch to your composer.json like this:

```
 "drupal/graphql":  {
   "Add missing subrequest buffer.": "https://patch-diff.githubusercontent.com/raw/drupal-graphql/graphql/pull/1313.patch"
 },
```

Generating a large schema with a lot of schema extensions leads to a serious performance with the Drupal GraphQL v4 module. These issues have been outlined in this [GitHub issue](https://github.com/drupal-graphql/graphql/issues/1312). Until the related merge request is accepted, these improvements are part of this module and no additional patch is needed. As soon as they are merged, these changes will be removed from our module.

## Installation and configuration

- Enable the module and the needed submodules.
- Create a new GraphQL Server under
  `/admin/config/graphql`
- In the Schema dropdown, select "Core composable Schema"
- Enable the needed Schema extensions
- Enable the needed Views
- Configure the exposed entity types

## Hint

- Disable the development mode of the GraphQL 4 module if you are not actively developing the schema. You will get a huge performance gain because the schema and the schema extensions will be cached.
- To extend and change the schema, you can write your one schema extension plugin and inherit from `schema = "core_composable"`
- If you find a bug or have a valuable contribution, please open an issue and create a patch / open a merge request. Contributions are very welcome.

## Roadmap

This module is currently in a **beta** state. Feel free to test it and give feedback, but expect some breaking changes in the upcoming weeks.
We are currently fine-tuning and improving the module in very short cycles.
A stable release is expected by the end of in Q1 / 2023.
