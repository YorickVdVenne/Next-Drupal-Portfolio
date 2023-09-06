<?php

declare(strict_types=1);

/**
 * Update the schema configuration for core_composable servers for the new
 * configuration options.
 *
 * @return void
 */
function graphql_core_schema_post_update_new_configuration() {
  $storage = \Drupal::entityTypeManager()->getStorage('graphql_server');
  $ids = array_values($storage->getQuery()->accessCheck(FALSE)->execute());
  foreach ($ids as $id) {
    /** @var \Drupal\graphql\Entity\Server $server */
    $server = $storage->load($id);
    if ($server && $server->schema === 'core_composable') {
      $server->schema_configuration['core_composable']['entity_base_fields']['fields'] = [
        'uuid' => 1,
        'label' => 1,
        'langcode' => 1,
        'getConfigTarget' => 1,
        'uriRelationships' => 1,
        'referencedEntities' => 1,
        'entityTypeId' => 1,
        'isNew' => 1,
        'accessCheck' => 1,
      ];
      $server->schema_configuration['core_composable']['generate_value_fields'] = 1;
      $server->save();
    }
  }
}
