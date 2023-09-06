<?php

// phpcs:disable Drupal.NamingConventions.ValidVariableName.LowerStart

namespace Drupal\Tests\graphql_core_schema\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\graphql_core_schema\EntitySchemaHelper;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\graphql_core_schema\Traits\CoreComposableServerBuilder;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ListOfType;

/**
 * Tests the graphql_core_schema schema generation.
 *
 * @group graphql_core_schema
 */
class GenerateSchemaTest extends CoreComposableKernelTestBase {

  /**
   * Test that the base schema is generated correctly.
   */
  public function testBaseSchema(): void {
    $builder = new CoreComposableServerBuilder();
    $builder->enableEntityType('node')->enableBaseEntityField('label');
    $server = $builder->createServer();
    $schema = $this->getSchema($server);

    /** @var \GraphQL\Type\Definition\InterfaceType $Entity */
    $Entity = $schema->getType('Entity');
    $this->assertNotNull($Entity);
    $this->assertNotNull($Entity->getField('id'), 'Generates the ID field for the Entity interface.');
    $this->assertNotNull($Entity->getField('label'), 'Generates an enabled base entity field.');
    $this->assertFalse($Entity->hasField('entityBundle'), 'Does not generate a base entity field that is not enabled.');

    /** @var \GraphQL\Type\Definition\InterfaceType $EntityDescribable */
    $EntityDescribable = $schema->getType('EntityDescribable');
    $this->assertNotNull($EntityDescribable);
    $EntityDescribable_entityDescription = $EntityDescribable->getField('entityDescription');
    $this->assertNotNull($EntityDescribable_entityDescription);
    $this->assertEquals('String', $EntityDescribable_entityDescription->getType()->name);

    /** @var \GraphQL\Type\Definition\InterfaceType $EntityLinkable */
    $EntityLinkable = $schema->getType('EntityLinkable');
    $this->assertNotNull($EntityLinkable);
    $this->assertNotNull($EntityLinkable->getField('url'));
    $this->assertEquals('Url', $EntityLinkable->getField('url')->getType()->name);

    /** @var \GraphQL\Type\Definition\InterfaceType $EntityTranslatable */
    $EntityTranslatable = $schema->getType('EntityTranslatable');
    $this->assertNotNull($EntityTranslatable);
    $EntityTranslatable_translations = $EntityTranslatable->getField('translations');
    $this->assertNotNull($EntityTranslatable_translations);
    /** @var \GraphQL\Type\Definition\ListOfType $translations_list_type */
    $translations_list_type = $EntityTranslatable_translations->getType();
    $translations_list_item_type = $translations_list_type->getOfType();
    $this->assertEquals('EntityTranslatable', $translations_list_item_type->name);

    /** @var \GraphQL\Type\Definition\ObjectType $Query */
    $Query = $schema->getType('Query');
    $this->assertTrue($Query->hasField('ping'));
    /** @var \GraphQL\Type\Definition\ObjectType $Mutation */
    $Mutation = $schema->getType('Mutation');
    $this->assertTrue($Mutation->hasField('ping'));
  }

  /**
   * Generates the enum for langcodes.
   */
  public function testLangcodeEnum(): void {
    $builder = new CoreComposableServerBuilder();
    $builder->enableEntityType('node')->enableBaseEntityField('label');
    $server = $builder->createServer();
    $schema = $this->getSchema($server);

    /** @var \GraphQL\Type\Definition\EnumType $Langcode */
    $Langcode = $schema->getType('Langcode');
    $this->assertNotNull($Langcode->getValue('DE'));
    $this->assertNotNull($Langcode->getValue('IT'));
    $this->assertEquals('German', $Langcode->getValue('DE')->description);
    $this->assertEquals('Italian', $Langcode->getValue('IT')->description);
  }

  /**
   * Generates the enum for entity types.
   */
  public function testEntityTypeEnum(): void {
    $builder = new CoreComposableServerBuilder();
    $server = $builder
      ->enableEntityType('node')
      ->enableEntityType('node_type')
      ->enableEntityType('user')
      ->enableEntityType('taxonomy_term')
      ->createServer();
    $schema = $this->getSchema($server);

    /** @var \GraphQL\Type\Definition\EnumType $EntityType */
    $EntityType = $schema->getType('EntityType');
    $this->assertNotNull($EntityType->getValue('NODE'));
    $this->assertNotNull($EntityType->getValue('NODE_TYPE'));
    $this->assertNotNull($EntityType->getValue('USER'));
    $this->assertNotNull($EntityType->getValue('TAXONOMY_TERM'));
  }

  /**
   * Generates the enum for date formats.
   */
  public function testDateFormatEnum(): void {
    $builder = new CoreComposableServerBuilder();
    $server = $builder
      ->enableEntityType('node')
      ->createServer();
    $schema = $this->getSchema($server);

    /** @var \GraphQL\Type\Definition\EnumType $EntityType */
    $EntityType = $schema->getType('DrupalDateFormat');
    $this->assertNotNull($EntityType->getValue('LONG'));
    $this->assertNotNull($EntityType->getValue('SHORT'));
    $this->assertNotNull($EntityType->getValue('FALLBACK'));
  }

  /**
   * Test that entity fields are generated.
   */
  public function testEntityFields(): void {
    NodeType::create(['type' => 'article'])->save();
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_author',
      'entity_type' => 'node',
      'type' => 'entity_reference',
    ]);
    $field_storage->save();

    FieldConfig::create([
      'field_name' => 'field_author',
      'field_storage' => $field_storage,
      'entity_type' => 'node',
      'bundle' => 'article',
      'label' => 'Author',
    ])->save();

    $builder = new CoreComposableServerBuilder();
    $builder->enableEntityType('node', ['field_author']);
    $server = $builder->createServer();
    $schema = $this->getSchema($server);

    /** @var \GraphQL\Type\Definition\InterfaceType $Node */
    $Node = $schema->getType('Node');
    $this->assertNotNull($Node);

    /** @var \GraphQL\Type\Definition\InterfaceType $NodeArticle */
    $NodeArticle = $schema->getType('NodeArticle');
    $this->assertNotNull($NodeArticle);
    $NodeArticle_fieldAuthor = $NodeArticle->getField('fieldAuthorRawField');
    $this->assertNotNull($NodeArticle_fieldAuthor);
  }

  /**
   * Test that disabled entity types are not created.
   */
  public function testNotGenerateDisabledTypes(): void {
    Vocabulary::create(['vid' => 'tags'])->save();
    NodeType::create(['type' => 'article'])->save();

    $builder = new CoreComposableServerBuilder();
    $server = $builder->enableEntityType('node')->createServer();
    $schema = $this->getSchema($server);
    $this->expectException(Error::class);
    $schema->getType('TaxonomyTerm');
  }

  /**
   * Test that entity reference fields are generated correctly.
   */
  public function testEntityReferenceFields(): void {
    Vocabulary::create(['vid' => 'tag'])->save();
    Vocabulary::create(['vid' => 'location'])->save();
    NodeType::create(['type' => 'article'])->save();
    $this->createEntityReferenceField(
      'node',
      'article',
      'single_term',
      'Location',
      'taxonomy_term',
      'default',
      ['target_bundles' => ['location' => 'location']],
      1
    );

    $this->createEntityReferenceField(
      'node',
      'article',
      'multiple_terms',
      'Tags',
      'taxonomy_term',
      'default',
      ['target_bundles' => ['tag' => 'tag']],
      6
    );

    $this->createEntityReferenceField(
      'node',
      'article',
      'multiple_vocab_single_term',
      'Multiple',
      'taxonomy_term',
      'default',
      ['target_bundles' => ['tag' => 'tag', 'location' => 'location']],
      1
    );

    $this->createEntityReferenceField(
      'node',
      'article',
      'multiple_vocab_multiple_terms',
      'Multiple Vocabularies',
      'taxonomy_term',
      'default',
      ['target_bundles' => ['tag' => 'tag', 'location' => 'location']],
      6
    );

    $builder = new CoreComposableServerBuilder();
    $server = $builder
      ->enableEntityType('node', [
        'single_term',
        'multiple_terms',
        'multiple_vocab_single_term',
        'multiple_vocab_multiple_terms',
      ])
      ->enableEntityType('taxonomy_term')
      ->enableValueFields()
      ->createServer();
    $schema = $this->getSchema($server);

    /** @var \GraphQL\Type\Definition\ObjectType $NodeArticle */
    $NodeArticle = $schema->getType('NodeArticle');
    $singleTerm = $NodeArticle->getField('singleTerm');
    $this->assertEquals('TaxonomyTermLocation', $singleTerm->getType()->name, 'Generates a value field with the target bundle as the type.');

    $multipleTerms = $NodeArticle->getField('multipleTerms');
    $this->assertEquals('TaxonomyTermTag', $multipleTerms->getType()->getOfType()->name, 'Generates a value field with the target bundle as the type.');

    $multipleVocabSingleTerm = $NodeArticle->getField('multipleVocabSingleTerm');
    $this->assertEquals('TaxonomyTerm', $multipleVocabSingleTerm->getType()->name, 'Generates a value field with the target entity type interface as the type.');

    $multipleVocabMultipleTerms = $NodeArticle->getField('multipleVocabMultipleTerms');
    $this->assertEquals('TaxonomyTerm', $multipleVocabMultipleTerms->getType()->getOfType()->name, 'Generates a value field with the target entity type interface as the type.');
  }

  /**
   * Generates value fields for string field items.
   */
  public function testScalarValueFields(): void {
    $this->enableModules(['telephone', 'options']);
    NodeType::create(['type' => 'article'])->save();

    $fieldNames = [];
    $fields = [
      'string' => 'String',
      'string_long' => 'String',
      'text' => 'String',
      'text_with_summary' => 'String',
      'text_long' => 'String',
      'uri' => 'String',
      'email' => 'String',
      'telephone' => 'String',
      'list_string' => 'String',
      'timestamp' => 'String',
      'changed' => 'String',
      'created' => 'String',
      'boolean' => 'Boolean',
      'decimal' => 'Float',
      'float' => 'Float',
      'integer' => 'Int',
      'map' => 'MapData',
      'language' => 'LanguageInterface',
    ];

    for ($cardinality = 1; $cardinality <= 2; $cardinality++) {
      foreach ($fields as $drupalType => $graphqlType) {
        $fieldName = 'field_' . $drupalType . '_' . $cardinality;
        $fieldNames[] = $fieldName;
        $field_storage = FieldStorageConfig::create([
          'field_name' => $fieldName,
          'entity_type' => 'node',
          'type' => $drupalType,
          'cardinality' => $cardinality,
        ]);
        $field_storage->save();

        FieldConfig::create([
          'field_name' => $fieldName,
          'field_storage' => $field_storage,
          'entity_type' => 'node',
          'bundle' => 'article',
          'label' => $fieldName,
        ])->save();
      }
    }

    $builder = new CoreComposableServerBuilder();
    $server = $builder->enableEntityType('node', $fieldNames)->enableValueFields()->createServer();
    $schema = $this->getSchema($server);
    /** @var \GraphQL\Type\Definition\ObjectType $NodeArticle */
    $NodeArticle = $schema->getType('NodeArticle');

    foreach ($fields as $drupalType => $graphqlType) {
      $fieldNameSingle = EntitySchemaHelper::toCamelCase('field_' . $drupalType . '_1');
      $fieldSingle = $NodeArticle->getField($fieldNameSingle);
      $this->assertEquals($graphqlType, $fieldSingle->getType()->name, "Value field for '$drupalType' generates type '$graphqlType'.");

      $fieldNameMultiple = EntitySchemaHelper::toCamelCase('field_' . $drupalType . '_2');
      $fieldMultiple = $NodeArticle->getField($fieldNameMultiple);
      /** @var ListOfType $fieldMultipleType */
      $fieldMultipleType = $fieldMultiple->getType();
      $this->assertInstanceOf(ListOfType::class, $fieldMultipleType);
      $this->assertEquals($graphqlType, $fieldMultipleType->getOfType()->name, "List value field for '$drupalType' generates type '[$graphqlType]'.");
    }

    $textWithSummary = $NodeArticle->getField('fieldTextWithSummary1');
    $arg = $textWithSummary->getArg('summary');
    $this->assertNotNull($arg, 'Adds the summary argument to text_with_summary value fields.');
    $this->assertEquals('Boolean', $arg->getType()->name);
  }

  /**
   * Uses interfaces for field item types that share a value property of the same scalar type.
   */
  public function testFieldItemInterfaces(): void {
    $this->enableModules(['telephone', 'options']);
    NodeType::create(['type' => 'article'])->save();

    $fieldNames = [];
    $fields = [
      'changed' => 'FieldItemTypeTimestampInterface',
      'timestamp' => 'FieldItemTypeTimestampInterface',
      'text' => 'FieldItemTypeStringInterface',
      'string' => 'FieldItemTypeStringInterface',
      'integer' => 'FieldItemTypeIntegerInterface',
    ];

    foreach ($fields as $drupalType => $graphqlType) {
      $fieldName = 'field_' . $drupalType;
      $fieldNames[] = $fieldName;
      $field_storage = FieldStorageConfig::create([
        'field_name' => $fieldName,
        'entity_type' => 'node',
        'type' => $drupalType,
      ]);
      $field_storage->save();

      FieldConfig::create([
        'field_name' => $fieldName,
        'field_storage' => $field_storage,
        'entity_type' => 'node',
        'bundle' => 'article',
        'label' => $fieldName,
      ])->save();
    }

    $builder = new CoreComposableServerBuilder();
    $server = $builder->enableEntityType('node', $fieldNames)->createServer();
    $schema = $this->getSchema($server);
    /** @var \GraphQL\Type\Definition\ObjectType $NodeArticle */
    $NodeArticle = $schema->getType('NodeArticle');

    foreach ($fields as $drupalType => $interfaceName) {
      $fieldName = EntitySchemaHelper::toCamelCase('field_' . $drupalType . '_raw_field');
      $field = $NodeArticle->getField($fieldName);

      /** @var \GraphQL\Type\Definition\ObjectType $fieldType */
      $fieldType = $field->getType();

      /** @var \GraphQL\Type\Definition\ObjectType $fieldItemType */
      $fieldItemType = $fieldType->getField('first')->getType();

      // The names of the interfaces this type implements.
      $implemetingInterfaces = array_map(function (InterfaceType $interface) {
        return $interface->name;
      }, $fieldItemType->getInterfaces());

      $this->assertContains(
        $interfaceName,
        $implemetingInterfaces,
        "Field items of type '$drupalType' implement interface '$interfaceName'"
      );
    }
  }

  /**
   * Test a schema that generates complex data types.
   */
  public function testComplexDataTypes(): void {
    NodeType::create(['type' => 'article'])->save();

    $server = $this
      ->getCoreComposableServerBuilder()
      ->enableValueFields()
      ->enableEntityType('graphql_server', [
        'persisted_queries_settings',
      ])
      ->createServer();

    $schema = $this->getSchema($server);

    /** @var \GraphQL\Type\Definition\ObjectType $GraphqlServer */
    $GraphqlServer = $schema->getType('GraphqlServer');
    $field = $GraphqlServer->getField('persistedQueriesSettings');
    /** @var \GraphQL\Type\Definition\ListOfType $type */
    $type = $field->getType();
    $itemType = $type->getOfType();
    $this->assertEquals('DataTypePluginPluginConfigurationPersistedQueryKey', $itemType->name);

    /** @var \GraphQL\Type\Definition\ObjectType $dataType */
    $dataType = $schema->getType('DataTypePluginPluginConfigurationPersistedQueryKey');
    $weightField = $dataType->getField('weight');
    $this->assertEquals('Int', $weightField->getType()->name);
  }

  /**
   * Test a schema that generates complex data types.
   */
  public function testConfigEntities(): void {
    $server = $this
      ->getCoreComposableServerBuilder()
      ->enableValueFields()
      ->enableEntityType('node_type', [
        'preview_mode',
        'status',
        'type',
        'help',
        'name',
        'display_submitted',
        'new_revision',
      ])
      ->createServer();

    $schema = $this->getSchema($server);

    $tests = [
      'previewMode' => 'Int',
      'status' => 'Boolean',
      'type' => 'String',
      'help' => 'String',
      'name' => 'String',
      'displaySubmitted' => 'Boolean',
      'newRevision' => 'Boolean',
    ];

    /** @var \GraphQL\Type\Definition\ObjectType $NodeType */
    $NodeType = $schema->getType('NodeType');

    foreach ($tests as $fieldName => $expectedType) {
      $field = $NodeType->getField($fieldName);
      $type = $field->getType();
      $this->assertEquals($expectedType, $type->name);
    }
  }

}
