<?php

namespace Drupal\graphql_tablefield_schema\Plugin\GraphQL\DataProducer;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Always return a boolean.
 *
 * @DataProducer(
 *   id = "tablefield",
 *   name = @Translation("Tablefield Rows"),
 *   description = @Translation("Return the rows of a table field."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Rows"),
 *     required = TRUE,
 *   ),
 *   consumes = {
 *     "table" = @ContextDefinition("any",
 *       label = @Translation("Value")
 *     ),
 *   },
 * )
 */
class Tablefield extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param mixed $table
   *   The table.
   *
   * @return array
   *   The result.
   */
  public function resolve($table) {
    /** @var \Drupal\tablefield\Plugin\Field\FieldType\TablefieldItem $table */
    $value = $table->getValue();
    $tableValues = $value['value'] ?? [];

    $rows = [];

    foreach ($tableValues as $rowIndex => $tableValue) {
      if (is_array($tableValue)) {
        foreach ($tableValue as $cellIndex => $cell) {
          if (is_numeric($cellIndex)) {
            $rows[$rowIndex][$cellIndex] = $cell;
          }
        }
      }
    }
    return $rows;
  }

}
