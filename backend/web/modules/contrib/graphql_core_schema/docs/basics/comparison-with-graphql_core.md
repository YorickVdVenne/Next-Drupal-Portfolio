# Comparison to graphql_core V3

Version 3 of the `graphql` module shipped with `graphql_core`, which was also a ready-to-use schema generator.

This module can be seen as the successor to `graphql_core`, but it introduces a few key differences:

## Opt-in Approach

In graphql_core, it was not easy to define which entity types and fields should be included in the schema. However, in the new module, everything is opt-in by default, allowing for more control over what gets included.

## entityById and entityQuery

Previously a byId and query field was generated for every entity type. This quickly results in a lot of duplication - 20 entity types result in 40 query fields.

This has changed to two generic entity query fields: `entityById` and `entityQuery`. Both take a required argument called `entityType`.

## Reverse Entity Query

This is the process of querying for entities that reference the _current_ entity.
In graphql_core a field was generated for every entity_reference field on every entity type. This added potentially hundreds of fields to the schema that are likely never used:

```graphql
type MediaFile {
  reverseFieldMediaScreenshotNode(
    filter: EntityQueryFilterInput = null
    limit: Int = 10
    offset: Int = 0
    revisions: EntityQueryRevisionMode = DEFAULT
    sort: [EntityQuerySortInput] = null
  ): EntityQueryResult!
}
```

This feature is now opt-in via the [Reverse Entity Reference](/schema-extensions/reverse-entity-reference) extension.

It only adds a single field called `reverseReference` on each entity:

```graphql
type MediaFile {
  reverseReference: ReverseReferenceContext
}
```

There it's possible to execute reference lookup queries by providing the target fields yourself.

Here's how this compares:

```graphql
query {
  mediaById(id: "5") {
    ... on MediaFile {
      reverseFieldMediaScreenshotNode {
        items {
          label
        }
      }
    }
  }
}
```

Now you would write it like this:

```graphql
query {
  entityById(entityType: MEDIA, id: 5) {
    reverseReferences {
      query(referenceFields: ["field_media_screenshot"], entityType: NODE) {
        items {
          label
        }
      }
    }
  }
}
```

## Generic Field Types

In graphql_core, a separate type was generated for each entity + field type combination. For example, if there were two entity reference fields:

### graphql_core

```graphql
type NodePage {
  fieldTeaserMedia: FieldNodePageFieldTeaserMedia
}

type NodeArticle {
  fieldTeaserMedia: FieldNodeArticleFieldTeaserMedia
}

type FieldNodePageFieldTeaserMedia {
  entity: Media
  targetId: Int
}

type FieldNodeArticleFieldTeaserMedia {
  entity: Media
  targetId: Int
}
```

This made it very hard to write fragments for a specific field type because they didn't share a common interface.

### graphql_core_schema

Now, the field type is generated only once and is used for all fields with the same type.

Additionally, the field is added twice, with one always resolving to the referenced entity, providing a sensible and useful value. For example:

```graphql
type NodePage {
  fieldTeaserMedia: MediaImage
  fieldTeaserMediaRawField: FieldItemListEntityReference
}

type NodeArticle {
  fieldTeaserMedia: MediaImage
  fieldTeaserMediaRawField: FieldItemListEntityReference
}

type FieldItemListEntityReference implements FieldItemList {
  isEmpty: Boolean!
  first: FieldItemTypeEntityReference
  list: [FieldItemTypeEntityReference]
}
```

## Improved Interfaces

The interfaces provided in graphql_core were verbose and repetitive. However, the new module offers only three entity interfaces, and the fields for the `Entity` interface are configurable. For example:

```graphql
interface Entity {
  id: String
  label: String
}

interface EntityLinkable {
  url(rel: String): Url
}

interface EntityTranslatable {
  translation(fallback: Boolean, langcode: Langcode!): EntityTranslatable
  translations: [EntityTranslatable]
}
```

## Reduced Schema Size

Thanks to the configurability of entity types and fields, as well as the use of generic field types, the schema size is significantly smaller compared to graphql_core.

## Extensibility

With the introduction of generic field types and smaller interfaces, it is now much easier to extend generated types with less code required. Furthermore, the module is fully compatible with version 4 of the [GraphQL module](https://www.drupal.org/project/graphql).
