# Configuration

After installing the module, navigate to the GraphQL configuration page at `/admin/config/graphql`.

## Enabling Entity Types

Specify which entity types should be included in the schema. It is important to **select at least one entity type** for the module to function correctly.

![Screenshot of checkboxes to enable entity types.](/screenshots/enabled-entity-types.png)

::: warning
Note that even when an entity type is not enabled, it's still possible to access some of its fields via the `Entity` interface, for example the `id` field. Access checks are still performed, and it is encouraged not to rely solely on GraphQL to protect sensitive information. Instead, use Drupal's entity and field access mechanisms.
:::

Once you have selected an entity type, save the configuration and rebuild the cache. The schema will now include a type for the entity:

```graphql
type User implements Entity & EntityLinkable {
  id: String
  url(rel: String): Url
}
```

### Entity Types with Bundles

If the selected entity type supports bundles, the module generates both an interface and types for each bundle:

```graphql
interface Node implements Entity & EntityLinkable {
  id: String
  url(rel: String): Url
}

type NodeNews implements Entity & EntityLinkable & EntityTranslatable & Node {
  id: String
  translation(fallback: Boolean, langcode: Langcode!): NodeNews
  translations: [NodeNews]
  url(rel: String): Url
}
```

## Enabling Fields

After enabling an entity type, navigate to the **Enabled fields** section on the configuration form.

![Screenshot of checkboxes to enable fields.](/screenshots/enabled-fields.png)

There, you can select which fields you would like to include in the schema. Most field types will work out of the box. However, some field types, such as `metatag`, contain serialized values that may not be useful to consumers. Support for these fields must be implemented through schema extensions.

The module generates GraphQL types for **all** enabled field types, even if they do not extend a more basic field type. This allows schema extensions to provide additional functionality by extending the existing types.

## Base Entity Fields

The schema generates a base `Entity` interface that all enabled entity types implement. You can define which fields should be added to this interface.

![Screenshot of checkboxes to enable base entity fields.](/screenshots/enabled-entity-base-fields.png)

The `id` field is always added and cannot be disabled. This is because each type must have at least one field, as required by GraphQL.

If, for some reason, you do not want to expose the ID field, you can easily add your own resolver for this field:

```php
$registry->addFieldResolver('Entity', 'id', $builder->fromValue(NULL));
```
