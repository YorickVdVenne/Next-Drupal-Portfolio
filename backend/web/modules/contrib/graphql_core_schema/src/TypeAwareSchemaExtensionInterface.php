<?php

namespace Drupal\graphql_core_schema;

/**
 * The TypeAwareSchemaExtension interface.
 *
 * Provides a way for schema extensions to only extend the schema for types
 * that have been generated previously.
 */
interface TypeAwareSchemaExtensionInterface {

  /**
   * Return the schema extension based on the generated types.
   *
   * @param string[] $types
   *   Array of all generated GraphQL types.
   *
   * @return string|null
   *   The schema extension.
   */
  public function getTypeExtensionDefinition(array $types);

}
