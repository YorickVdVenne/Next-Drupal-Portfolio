# Entity & Field Schema

This page provides an overview of the interfaces and types generated based on the specific setup.

## Entity Types (without bundles)

For each enabled entity type, a GraphQL type with the same name is generated.

Let's consider the example of enabling the `user` entity type with its `name` and `created` fields. The resulting type would look like this:

```graphql
type User implements Entity & EntityLinkable {
  created: String
  createdRawField: FieldItemListCreated
  entityTypeId: String!
  id: String
  label: String
  name: String
  nameRawField: FieldItemListString
  uriRelationships: [String]
  url(rel: String): Url
  uuid: String!
}
```

Some fields are inherited from the interfaces it implements, such as `uuid` (from `Entity`) or `url` (from `EntityLinkable`).

## Entity Types (with bundles)

If an entity type supports bundles, an interface with the name of the entity type is generated:

```graphql
interface Node implements Entity & EntityLinkable {
  created: String
  createdRawField: FieldItemListCreated
  entityTypeId: String!
  id: String
  label: String
  langcode: String
  uriRelationships: [String]
  url(rel: String): Url
}
```

For each bundle, a type is generated that implements the interface:

```graphql
type NodePage implements Entity & EntityLinkable & EntityTranslatable & Node {
  # Fields inherited from Entity interface.
  created: String
  createdRawField: FieldItemListCreated
  entityTypeId: String!
  id: String
  label: String
  langcode: String

  # Fields inherited from EntityTranslatable interface.
  translation(fallback: Boolean, langcode: Langcode!): NodePage
  translations: [NodePage]

  # Fields inherited from EntityLinkable interface.
  uriRelationships: [String]
  url(rel: String): Url

  # Bundle-specific fields.
  fieldMediaImage: MediaImage
  fieldMediaImageRawField: FieldItemListEntityReference
}
```

## Field Types

The schema generates a type for every **available** field type.

Continuing with our example, where we enabled an `entity_reference` field, the generated type would be:

```graphql
type FieldItemListEntityReference implements FieldItemList {
  # Inherited from FieldItemList.
  count: Int!
  entity: Entity
  getString: String!
  isEmpty: Boolean!

  # Type-specific fields.
  first: FieldItemTypeEntityReference
  list: [FieldItemTypeEntityReference]
}
```

The type includes default fields inherited from the interface, such as `isEmpty` or `count`.

The `first` and `list` fields contain the actual field item. A type is generated for each field item type as well:

```graphql
type FieldItemTypeEntityReference implements FieldItemType {
  entity: Entity
  isEmpty: Boolean!
  targetId: Int
}
```

The generated type is not specific to the `NodePage` entity; it is a generic type used for all `entity_reference` fields.

## Field Values

If we revisit our `NodePage` type, we can see that the field `field_media_image` is added twice:

```graphql
type NodePage implements Entity & EntityLinkable & EntityTranslatable & Node {
  fieldMediaImage: MediaImage
  fieldMediaImageRawField: FieldItemListEntityReference
}
```

For each entity field, there is an additional field with the `rawField` suffix. It always contains a type that implements `FieldItemList`.

However, accessing the "real" value can become tedious, such as `fieldMediaImage.list.[0].entity.label`. To simplify this, a _value field_ is provided that attempts to resolve a useful value.

In the case of the `entity_reference` field, the resolved value is the referenced entity. The schema generator also tries to determine the type. If the field allows only a single target bundle, the type will be that exact bundle (e.g., `MediaImage`). If multiple target bundles are enabled, the type would be `Media`. If the target entity type cannot be determined, the type would be `Entity`.

Most field item types resolve to a sensible scalar value. However, some do not, such as the `address` field, which contains properties for different address parts like `locality` or `postalCode`.

For these cases, the value field type is always the corresponding `FieldItemType`, such as `FieldItemTypeAddress`:

```graphql
type TaxonomyTermLocation implements Entity & EntityLinkable & TaxonomyTerm {
  fieldAddress: FieldItemTypeAddress
  fieldAddressRawField: FieldItemListAddress
}

type FieldItemTypeAddress implements FieldItemType {
  additionalName: String
  addressLine1: String
  addressLine2: String
  administrativeArea: String
  countryCode: String
  dependentLocality: String
  familyName: String
  givenName: String
  isEmpty: Boolean!
  langcode: String
  locality: String
  organization: String
  postalCode: String
  sortingCode: String
}
```
