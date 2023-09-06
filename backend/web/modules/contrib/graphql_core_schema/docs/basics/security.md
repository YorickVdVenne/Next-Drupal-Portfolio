# Security / Access Checks

The module includes a default field resolver that serves as a fallback when no other resolver is defined for a field.

This resolver performs access checks and only returns a field value if the access result is **allowed**.

However, it's important to note that many entities return a **neutral** access result and will **NOT** be resolved. This means that you need to add access hooks yourself to handle these cases.

## Adding an Entity Access Hook

For example, let's consider the `Menu` entity, for which you will likely need to add an access check:

```php
/**
 * Implements hook_ENTITY_TYPE_access().
 */
function MY_MODULE_menu_access(EntityInterface $entity, $operation, AccountInterface $account) {
  // Grant view access to the main menu to everyone.
  $id = $entity->id();
  if ($operation === 'view' && $id === 'main') {
    return AccessResult::allowed();
  }

  return AccessResult::neutral();
}
```

In this example, we implement the `hook_ENTITY_TYPE_access()` hook to provide the access check for the `Menu` entity. We grant view access to the main menu for all users by returning `AccessResult::allowed()`. For other menus or operations, we return `AccessResult::neutral()` to indicate no specific access result.

## Custom Resolvers

If you write your own field resolver, it's important to handle access checks yourself, as this will bypass the default resolver. You should incorporate the necessary access control logic to ensure that the resolver returns field values only when appropriate access is granted.
