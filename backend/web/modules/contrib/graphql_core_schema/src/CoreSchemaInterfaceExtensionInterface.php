<?php

namespace Drupal\graphql_core_schema;

/**
 * Defines an interface for interface extending schema extensions.
 *
 * Schema extensions that implement this can extend interface types with
 * additional fields. These are then inherited automatically for all GraphQL
 * types that implement the interface.
 */
interface CoreSchemaInterfaceExtensionInterface {

  /**
   * Extend interfaces with fields.
   *
   * @return array
   *   The extended interfaces.
   */
  public function getInterfaceExtender();

}
