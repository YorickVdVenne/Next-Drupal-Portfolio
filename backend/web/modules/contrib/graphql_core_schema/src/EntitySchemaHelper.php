<?php

namespace Drupal\graphql_core_schema;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use function Symfony\Component\String\u;

/**
 * The EntitySchemaHelper class.
 */
class EntitySchemaHelper {

  /**
   * Always return an array from the given input.
   *
   * @param string|string[] $parts
   *   The input.
   *
   * @return string
   *   The string.
   */
  private static function processArg($parts) {
    if (is_string($parts)) {
      return $parts;
    }
    return implode('_', $parts);
  }

  /**
   * Get the GraphQL type for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to get the type for.
   *
   * @return string
   *   The GraphQL type name.
   */
  public static function getGraphqlTypeForEntity(EntityInterface $entity) {
    $type = $entity->getEntityType();
    $hasBundles = $type->hasKey('bundle');
    return $hasBundles
      ? self::toPascalCase([$type->id(), $entity->bundle()])
      : self::toPascalCase([$type->id()]);
  }

  /**
   * Create a PascalCase string from the parts.
   *
   * @param string|string[] $parts
   *   The parts to concatenate and build the string.
   *
   * @return string
   *   THe PascalCase string.
   */
  public static function toPascalCase($parts): string {
    $result = u(self::processArg($parts))->title()->camel()->title()->toString();
    return $result;
  }

  /**
   * Create a snake_case string.
   *
   * @param string|string[] $parts
   *   The input string.
   *
   * @return string
   *   The snake_case string.
   */
  public static function toSnakeCase($parts): string {
    return u(self::processArg($parts))->snake()->toString();
  }

  /**
   * Create a camelCase string.
   *
   * @param string|string[] $parts
   *   The input string.
   *
   * @return string
   *   The camelCase string.
   */
  public static function toCamelCase($parts): string {
    return u(self::processArg($parts))->camel()->toString();
  }

  /**
   * Get the GraphQL type name for a field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $fieldItem
   *   The field item.
   *
   * @return string
   *   The type.
   */
  public static function getTypeForFieldItem(FieldItemInterface $fieldItem): string {
    $dataType = $fieldItem->getDataDefinition()->getDataType();
    return self::toPascalCase([$dataType]);
  }

}
