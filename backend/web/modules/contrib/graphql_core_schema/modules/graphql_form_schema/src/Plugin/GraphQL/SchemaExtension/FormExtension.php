<?php

namespace Drupal\graphql_form_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * The Form API schema extension.
 *
 * @SchemaExtension(
 *   id = "form",
 *   name = "Form API",
 *   description = "Support for rendering, validating and submitting Drupal forms.",
 *   schema = "core_composable"
 * )
 */
class FormExtension extends SdlSchemaExtensionPluginBase {

  /**
 * The mapping of Drupal form elements to the GraphQL type.
 *
 * @var string[]
 */
  const ELEMENT_TYPE_MAP = [
    'checkbox' => 'FormElementCheckbox',
    'radio' => 'FormElementRadio',
    'textfield' => 'FormElementTextfield',
    'select' => 'FormElementSelect',
    'language_select' => 'FormElementSelect',
    'address_country' => 'FormElementSelect',
    'radios' => 'FormElementRadios',
    'datetime' => 'FormElementDate',
    'date' => 'FormElementDate',
  ];

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addTypeResolver('FormElement', function (array $element) {
      $elementType = $element['#type'] ?? NULL;
      if ($elementType) {
        $graphqlType = self::ELEMENT_TYPE_MAP[$elementType] ?? NULL;
        if ($graphqlType) {
          return $graphqlType;
        }
      }

      return 'FormElementUnknown';
    });

    $optionsResolver = function (array $element) {
      $options = [];

      foreach ($element['#options'] as $value => $label) {
        $options[] = [
          'value' => $value,
          'label' => $label,
        ];
      }
      return $options;
    };

    $dateResolver = function (array $element) {
      return [
        'value' => $element['#value'],
      ];
    };

    $registry->addFieldResolver('FormElementSelect', 'options', $builder->callback($optionsResolver));
    $registry->addFieldResolver('FormElementRadios', 'options', $builder->callback($optionsResolver));

    $registry->addFieldResolver('FormItem', 'name',
      $builder->callback(function (array $element) {
        return $element['#name'];
      })
    );

    $registry->addFieldResolver('FormElementDate', 'value', $builder->callback($dateResolver));

    $registry->addFieldResolver('FormItem', 'description',
      $builder->callback(function (array $element) {
        return $element['#description'] ?? NULL;
      })
    );

    $registry->addFieldResolver('FormItem', 'title',
      $builder->callback(function (array $element) {
        return $element['#title'] ?? NULL;
      })
    );

    $registry->addFieldResolver('FormItem', 'defaultValue',
      $builder->produce('form_item_default_value')->map('item', $builder->fromParent())
    );

    $registry->addFieldResolver('FormItem', 'required',
      $builder->callback(function (array $element) {
        return $element['#required'] ?? FALSE;
      })
    );

    $registry->addFieldResolver('FormItem', 'error',
      $builder->callback(function (array $element) {
        return $element['#collected_error'] ?? NULL;
      })
    );

    $registry->addFieldResolver('FormElement', 'type',
      $builder->callback(function (array $element) {
        return $element['#type'] ?? 'unknown';
      })
    );

    $registry->addFieldResolver('FormItem', 'element', $builder->fromParent());
  }

}
