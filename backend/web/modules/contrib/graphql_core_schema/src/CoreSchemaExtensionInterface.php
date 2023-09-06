<?php

namespace Drupal\graphql_core_schema;

use Drupal\graphql\Plugin\SchemaExtensionPluginInterface;

/**
 * The CoreSchemaExtensionInterface class.
 */
interface CoreSchemaExtensionInterface extends SchemaExtensionPluginInterface {

  /**
   * Return the required entity type IDs.
   *
   * @return string[]
   *   The result array.
   */
  public function getEntityTypeDependencies();

  /**
   * Return the required extension IDs.
   *
   * @return string[]
   *   The result array.
   */
  public function getExtensionDependencies();

}
