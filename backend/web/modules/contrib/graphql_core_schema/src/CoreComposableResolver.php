<?php

namespace Drupal\graphql_core_schema;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem;
use Drupal\Core\Field\Plugin\Field\FieldType\EmailItem;
use Drupal\Core\Field\Plugin\Field\FieldType\LanguageItem;
use Drupal\Core\Field\Plugin\Field\FieldType\NumericItemBase;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItemBase;
use Drupal\Core\Field\Plugin\Field\FieldType\TimestampItem;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\TypedData\Plugin\DataType\BooleanData;
use Drupal\Core\TypedData\Plugin\DataType\IntegerData;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\Core\TypedData\Plugin\DataType\Timestamp;
use Drupal\Core\TypedData\Plugin\DataType\Uri;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\Url;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\options\Plugin\Field\FieldType\ListStringItem;
use Drupal\telephone\Plugin\Field\FieldType\TelephoneItem;
use Drupal\text\Plugin\Field\FieldType\TextItemBase;
use Drupal\text\TextProcessed;
use GraphQL\Executor\Executor;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\WrappingType;

/**
 * The core composable resolver class.
 */
class CoreComposableResolver {

  /**
   * Resolves a default value for a field.
   *
   * @param mixed $value
   *   The value.
   * @param mixed $args
   *   The arguments.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The graphql resolver info.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field
   *   The field context.
   *
   * @return mixed|null
   *   The result.
   */
  public static function resolveFieldDefault($value, $args, ResolveContext $context, ResolveInfo $info, RefinableCacheableDependencyInterface $field) {
    // Find out if this is a value field.
    $fieldDescription = $info->fieldDefinition->description ?? '';
    $isValueField = str_ends_with($fieldDescription, '(field value)');

    // The GraphQL field name.
    $fieldName = $field->getFieldName();
    $returnType = $info->returnType;
    $isList = $returnType instanceof ListOfType;

    // The Drupal field name.
    $drupalFieldName = self::getDrupalFieldName($fieldName, $fieldDescription);

    // Handle value fields.
    if ($isValueField) {
      $results = self::resolveFieldValue($value, $drupalFieldName, $args);
      return $isList ? $results : $results[0] ?? NULL;
    }

    // Handle all other fields.
    if ($value instanceof EntityInterface) {
      if ($value instanceof FieldableEntityInterface || $value instanceof ConfigEntityInterface) {
        return $value->get($drupalFieldName) ?? $value->get($fieldName);
      }
      else {
        // @todo Is this ever the case?
      }
    }
    elseif ($value instanceof FieldItemListInterface) {
      return iterator_to_array($value);
    }
    elseif ($value instanceof FieldItemInterface) {
      return self::resolveItem($value, $info, $drupalFieldName);
    }
    elseif (is_array($value)) {
      return $value[$drupalFieldName] ?? NULL;
    }

    return Executor::defaultFieldResolver($value, $args, $context, $info);
  }

  /**
   * Convert the GraphQL field name to the Drupal field name.
   *
   * @param string $fieldName
   *   The GraphQL field name.
   * @param string|null $description
   *   The GraphQL field description.
   *
   * @return string
   */
  private static function getDrupalFieldName(string $fieldName, string|null $description = NULL): string {
    if ($description) {
      // If the description contains something like [field: field_foobar_1_a], use this.
      $matches = [];
      preg_match('/\[field: (.+)\]/', $description, $matches);
      $match = $matches[1] ?? NULL;
      if ($match) {
        return $match;
      }
    }

    // Convert the field name to snake case.
    $drupalFieldName = EntitySchemaHelper::toSnakeCase($fieldName);
    if (str_ends_with($drupalFieldName, '_raw_field')) {
      $drupalFieldName = substr($drupalFieldName, 0, -10);
    }
    return $drupalFieldName;
  }

  /**
   * Resolve a value field.
   *
   * Unlike the FieldItemList fields, these directly resolve to a scalar or
   * other "sane" object type.
   */
  private static function resolveFieldValue($parent, string $fieldName, array $args): array {
    $result = [];

    if ($parent instanceof FieldableEntityInterface) {
      $field = $parent->get($fieldName);

      // Perform access check here because we directly resolve a field value.
      if (!$field->access('view')) {
        return [];
      }

      // Special handling for file fields, which inherit from EntityReferenceItem.
      // Their value fields are not the referenced entity (file), but the field item.
      // This is because some files like images have additional properties like
      // alt and title, which would otherwise not be available on the File
      // type.
      if ($field instanceof FileFieldItemList) {
        return iterator_to_array($field);
      }

      // Entity reference fields, directly get the entities via the
      // referencedEntities helper method.
      if ($field instanceof EntityReferenceFieldItemListInterface) {
        return $field->referencedEntities();
      }

      foreach ($field as $item) {
        $result[] = self::extractFieldValue($item, $args);
      }
    }

    return $result;
  }

  /**
   * Extract the value for a value field.
   *
   * This logic corresponds to the logic in
   * EntitySchemaBuilder::buildGraphqlValueField, where the GraphQL scalar type
   * is determined.
   */
  private static function extractFieldValue(FieldItemInterface $item, array $args) {
    if (
      $item instanceof StringItem ||
      $item instanceof StringItemBase ||
      $item instanceof EmailItem ||
      $item instanceof BooleanItem ||
      $item instanceof ListStringItem ||
      $item instanceof NumericItemBase ||
      $item instanceof TelephoneItem
    ) {
      return $item->value;
    }
    elseif ($item instanceof TextItemBase) {
      if (isset($args['summary'])) {
        return $item->summary_processed;
      }
      return $item->processed;
    }
    elseif ($item instanceof TimestampItem) {
      $value = $item->value;
      if ($value) {
        return date(DATE_ISO8601, $value);
      }
    }
    elseif ($item instanceof LanguageItem) {
      return $item->language;
    }

    return $item;
  }

  /**
   * Resolve item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The graphql context.
   * @param string $property
   *   The proery name.
   *
   * @return \Drupal\Component\Render\MarkupInterface|\Drupal\Core\Entity\ContentEntityInterface|mixed|string|null
   *   The resolved item.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \GraphQL\Error\Error
   */
  private static function resolveItem(FieldItemInterface $item, ResolveInfo $info, string $property) {
    $result = $item->get($property);

    if ($result instanceof Uri) {
      return Url::fromUri($result->getValue());
    }
    elseif ($result instanceof StringData) {
      return $result->getValue() ?? '';
    }
    elseif ($result instanceof BooleanData) {
      return $result->getValue() ?? FALSE;
    }
    elseif ($result instanceof Timestamp) {
      return $result->getValue();
    }
    elseif ($result instanceof IntegerData) {
      return $result->getValue();
    }
    elseif ($result instanceof TextProcessed) {
      return $result->getValue();
    }
    elseif ($result instanceof TypedDataInterface) {
      return $result->getValue();
    }

    $type = $info->returnType;
    $type = $type instanceof WrappingType ? $type->getWrappedType(TRUE) : $type;
    if ($type instanceof ScalarType) {
      $result = is_null($result) ? NULL : $type->serialize($result);
    }

    return $result;
  }

  /**
   * Returns NULL as default type.
   *
   * @param mixed $value
   *   The value.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolver info.
   *
   * @return null
   *   The default type.
   */
  public static function resolveTypeDefault($value, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof EntityInterface) {
      $type = EntitySchemaHelper::getGraphqlTypeForEntity($value);
      return $type;
    }
    elseif ($value instanceof FieldItemInterface) {
      return EntitySchemaHelper::getTypeForFieldItem($value);
    }
    elseif ($value instanceof PluginDefinitionInterface) {
      return EntitySchemaHelper::toPascalCase([$value->id(), '_plugin']);
    }
    elseif ($value instanceof Url) {
      return 'DefaultUrl';
    }
    return NULL;
  }

  /**
   * Register field item and field item list resolvers.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   The resolver registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The resolver builder.
   */
  public static function registerFieldListResolvers(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('FieldItemList', 'first',
      $builder->callback(function ($value) {
        if ($value instanceof FieldItemListInterface) {
          return $value->first();
        }
      })
    );
    $registry->addFieldResolver('FieldItemList', 'isEmpty',
      $builder->callback(function ($value) {
        if ($value instanceof FieldItemListInterface) {
          return $value->isEmpty();
        }
      })
    );
    $registry->addFieldResolver('FieldItemList', 'count',
      $builder->callback(function ($value) {
        if ($value instanceof FieldItemListInterface) {
          return $value->count();
        }
      })
    );
    $registry->addFieldResolver('FieldItemList', 'getString',
      $builder->callback(function ($value) {
        if ($value instanceof FieldItemListInterface) {
          return $value->getString();
        }
      })
    );
    $registry->addFieldResolver('FieldItemType', 'isEmpty',
      $builder->callback(function ($value) {
        if ($value instanceof FieldItemInterface) {
          return $value->isEmpty();
        }
      })
    );
    $registry->addFieldResolver('FieldItemList', 'entity',
      $builder->callback(function ($value) {
        if ($value instanceof FieldItemListInterface) {
          return $value->getEntity();
        }
      })
    );
  }

  /**
   * Register entity resolvers.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   The resolver registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The resolver builder.
   */
  public static function registerEntityResolvers(ResolverRegistry $registry, ResolverBuilder $builder) {
    $resolveEnumArgument = function ($name) {
      return function ($value, $args) use ($name) {
        return strtolower($args[$name]);
      };
    };

    $registry->addFieldResolver('Entity', 'id',
      $builder->produce('entity_id')->map('entity', $builder->fromParent())
    );
    $registry->addFieldResolver('Entity', 'label',
      $builder->callback(function ($value) {
        if ($value instanceof EntityInterface) {
          return (string) $value->label();
        }
      })
    );
    $registry->addFieldResolver('Entity', 'uuid',
      $builder->produce('entity_uuid')->map('entity', $builder->fromParent())
    );
    $registry->addFieldResolver('Entity', 'entityTypeId',
      $builder->produce('entity_type_id')->map('entity', $builder->fromParent())
    );
    $registry->addFieldResolver('Entity', 'language',
      $builder->produce('entity_language')->map('entity', $builder->fromParent())
    );
    $registry->addFieldResolver('Entity', 'langcode',
      $builder->callback(function (EntityInterface $entity) {
        return $entity->language()->getId();
      })
    );
    $registry->addFieldResolver('Entity', 'isNew',
      $builder->callback(function (EntityInterface $value) {
        return $value->isNew();
      })
    );
    $registry->addFieldResolver('Entity', 'toArray',
      $builder->callback(function (EntityInterface $value) {
        return $value->toArray();
      })
    );
    $registry->addFieldResolver('Entity', 'uriRelationships',
      $builder->callback(function (EntityInterface $value) {
        return $value->uriRelationships();
      })
    );
    $registry->addFieldResolver('Entity', 'entityBundle',
      $builder->callback(function (EntityInterface $value) {
        return $value->bundle();
      })
    );
    $registry->addFieldResolver('Entity', 'referencedEntities',
      $builder->callback(function (EntityInterface $value) {
        return $value->referencedEntities();
      })
    );
    $registry->addFieldResolver('Entity', 'getConfigTarget',
      $builder->callback(function (EntityInterface $value) {
        return $value->getConfigTarget();
      })
    );
    $registry->addFieldResolver('EntityLinkable', 'url',
      $builder->produce('entity_url')
        ->map('entity', $builder->fromParent())
        ->map('rel', $builder->fromArgument('rel'))
    );

    $registry->addFieldResolver('Entity', 'accessCheck',
      $builder->produce('entity_access')
        ->map('entity', $builder->fromParent())
        ->map('operation', $builder->fromArgument('operation'))
    );

    $registry->addFieldResolver('EntityTranslatable', 'translations',
      $builder->produce('entity_translations')->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('EntityTranslatable', 'translation',
      $builder->produce('entity_translation_fallback')
        ->map('entity', $builder->fromParent())
        ->map('langcode', $builder->callback($resolveEnumArgument('langcode')))
        ->map('fallback', $builder->fromArgument('fallback'))
    );

    $registry->addFieldResolver('EntityDescribable', 'entityDescription',
      $builder->callback(function (EntityDescriptionInterface $value) {
        return $value->getDescription();
      })
    );
  }

  /**
   * Register fields for the base Url fields.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   The resolver registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The resolver builder.
   */
  public static function registerUrlResolvers(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Url', 'path', $builder->compose(
      $builder->produce('url_path')->map('url', $builder->fromParent())
    ));
  }

  /**
   * Register language resolvers.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   The resolver registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The resolver builder.
   */
  public static function registerLanguageResolvers(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('LanguageInterface', 'name',
      $builder->callback(function (LanguageInterface $value) {
        return $value->getName();
      })
    );
    $registry->addFieldResolver('LanguageInterface', 'id',
      $builder->callback(function (LanguageInterface $value) {
        return $value->getId();
      })
    );
    $registry->addFieldResolver('LanguageInterface', 'direction',
      $builder->callback(function (LanguageInterface $value) {
        return $value->getDirection();
      })
    );
    $registry->addFieldResolver('LanguageInterface', 'weight',
      $builder->callback(function (LanguageInterface $value) {
        return $value->getWeight();
      })
    );
    $registry->addFieldResolver('LanguageInterface', 'isLocked',
      $builder->callback(function (LanguageInterface $value) {
        return $value->isLocked();
      })
    );

    $registry->addTypeResolver('LanguageInterface', function ($value) {
      if ($value instanceof ConfigurableLanguage) {
        return 'ConfigurableLanguage';
      }
      return 'Language';
    });
  }

  /**
   * Register ping resolvers.
   *
   * These are needed because it's possible to not have a single query or
   * mutation when all extensions are disabled. This way we can make sure that
   * the schema can be generated without an exception.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   The resolver registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The resolver builder.
   */
  public static function registerPingResolvers(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'ping', $builder->fromValue('pong'));
    $registry->addFieldResolver('Mutation', 'ping', $builder->fromValue('pong'));
  }

}
