<?php

namespace Drupal\graphql_form_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\graphql_form_schema\Plugin\GraphQL\EntityFormBase;

/**
 * Resolve the default value for a form item.
 *
 * @DataProducer(
 *   id = "form_item_default_value",
 *   name = @Translation("Form Item: Default Value"),
 *   description = @Translation("Resolves the default value for a form item."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("The default value.")
 *   ),
 *   consumes = {
 *     "item" = @ContextDefinition("any",
 *       label = @Translation("The form item."),
 *     ),
 *   }
 * )
 */
class FormItemDefaultValue extends EntityFormBase {

  /**
   * The resolver.
   *
   * @param array $item
   *   The form item.
   *
   * @return mixed
   *   The default value.
   */
  public function resolve(array $item) {
    $defaultValue = $item['#value'] ?? $item['#default_value'] ?? NULL;
    if (is_array($defaultValue)) {
      $result = [];
      foreach ($defaultValue as $value) {
        $result[] = $this->mapValue($value);
      }
      return $result;
    }

    return $this->mapValue($defaultValue);
  }

  /**
   * Map a single value.
   *
   * @param mixed $value
   *   The value.
   *
   * @return mixed
   *   The resolved value.
   */
  private function mapValue($value) {
    if (is_array($value)) {
      if (!empty($value['value'])) {
        return $this->mapValue($value['value']);
      }
    }
    elseif ($value instanceof DrupalDateTime) {
      return $value->format('Y-m-d\TH:i:s');
    }

    return $value;
  }

}
