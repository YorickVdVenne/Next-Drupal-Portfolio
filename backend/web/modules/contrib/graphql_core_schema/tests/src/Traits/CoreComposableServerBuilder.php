<?php

namespace Drupal\Tests\graphql_core_schema\Traits;

use Drupal\graphql\Entity\Server;
use Drupal\graphql\Entity\ServerInterface;

/**
 * Helper to create a server.
 */
class CoreComposableServerBuilder {

  /**
   * The enabled entity types.
   */
  protected array $enabledEntityTypes;

  /**
   * The enabled extensions.
   */
  protected array $extensions;

  /**
   * Wheter value fields are enabled.
   */
  protected bool $generateValueFields;

  /**
   * The enabled entity base fields.
   */
  protected array $entityBaseFields;

  /**
   * The enabled entity fields.
   */
  protected array $fields;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->enabledEntityTypes = [];
    $this->extensions = [];
    $this->entityBaseFields = [];
    $this->fields = [];
    $this->generateValueFields = FALSE;
  }

  /**
   * Enable value fields
   *
   * @return static
   */
  public function enableValueFields(): static {
    $this->generateValueFields = TRUE;
    return $this;
  }

  /**
   * Enable extensions.
   *
   * @param string $extension
   *   The extension to enable.
   *
   * @return static
   */
  public function enableExtension(string $extension): static {
    $this->extensions[] = $extension;
    return $this;
  }

  /**
   * Enable base entity field.
   *
   * @param string $field
   *   The base entity field.
   *
   * @return static
   */
  public function enableBaseEntityField(string $field): static {
    $this->entityBaseFields[] = $field;
    return $this;
  }

  /**
   * Enable entity type.
   *
   * @param string $entityType
   *   The entity type to enable.
   * @param string[] $fields
   *   The fields of the entity type to enable.
   *
   * @return static
   */
  public function enableEntityType(string $entityType, array $fields = []): static {
    $this->enabledEntityTypes[] = $entityType;
    $this->fields[$entityType] = array_combine($fields, $fields);
    return $this;
  }

  /**
   * Create a server.
   *
   * @param array $entityBaseFields
   *   The enabled entity base fields.
   * @param array $extensions
   *   The enabled extensions.
   *
   * @return Server
   *   The server
   */
  public function createServer(): ServerInterface {
    $server = Server::create([
      'schema' => 'core_composable',
      'name' => 'test',
      'endpoint' => '/graphql',
      'caching' => FALSE,
      'schema_configuration' => [
        'core_composable' => [
          'generate_value_fields' => $this->generateValueFields,
          'fields' => $this->fields,
          'extensions' => array_combine($this->extensions, $this->extensions),
          'entity_base_fields' => [
            'fields' => array_combine($this->entityBaseFields, $this->entityBaseFields),
          ],
          'enabled_entity_types' => array_fill_keys($this->enabledEntityTypes, 1),
        ],
      ],
    ]);
    $server->save();
    return $server;
  }

}
