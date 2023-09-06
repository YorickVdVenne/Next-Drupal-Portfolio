# Troubleshooting

## Some entities are `null` when not authenticated

If you encounter the issue where certain entities are returning `null` when accessed without authentication, it is likely due to the entity access check returning either **neutral** or **forbidden**. To resolve this, you can follow the steps outlined in the [entity access checks guide](/basics/security.html#adding-entity-access-hook) for a possible solution.

## Type not found in document

If you receive an error message similar to the following:

```
GraphQL\Error\Error: Type "FieldItemTypeDatetime" not found in document.
in GraphQL\Utils\BuildSchema::GraphQL\Utils\{closure}()
(line 149 of /app/vendor/webonyx/graphql-php/src/Utils/BuildSchema.php).
```

It indicates that one of the enabled extensions is attempting to extend an interface or type that does not exist. This can occur if a field is disabled or completely removed from Drupal.

To resolve this issue, perform a search for the mentioned type within your custom extensions or the extensions provided by the module. This will help identify the extension that needs to be disabled.
