<?php

// phpcs:disable Drupal.NamingConventions.ValidVariableName.LowerStart

namespace Drupal\Tests\graphql_core_schema\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use GraphQL\Server\OperationParams;

/**
 * Tests the field name conversion.
 *
 * @group graphql_core_schema
 */
class FieldNameConversionTest extends CoreComposableKernelTestBase {

  /**
   * Test the schema generates the field names correctly.
   */
  public function testFieldNameConversion(): void {
    NodeType::create(['type' => 'article'])->save();

    $fields = [
      'birthday' => 'birthday',
      'field_birthday' => 'fieldBirthday',
      'field_birth_day' => 'fieldBirthDay',
      'field_birthday1' => 'fieldBirthday1',
      'field_birthday_1' => 'field_birthday_1',
      'field_birthday_1a' => 'fieldBirthday1a',
      'field_birthday_1_a' => 'fieldBirthday1A',
      'field_birthday_1a_' => 'field_birthday_1a_',
      'field_birth__day_3b_' => 'fieldBirthDay3b',
      'field_birth__day__3b_' => 'field_birth__day__3b_',
    ];

    $nodeValues = [
      'type' => 'article',
      'title' => 'Test',
    ];

    foreach ($fields as $machineName => $graphqlName) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $machineName,
        'entity_type' => 'node',
        'type' => 'string',
      ]);
      $field_storage->save();

      FieldConfig::create([
        'field_name' => $machineName,
        'field_storage' => $field_storage,
        'entity_type' => 'node',
        'bundle' => 'article',
        'label' => 'Author',
      ])->save();

      $nodeValues[$machineName] = 'Text: ' . $machineName;
    }

    $node = Node::create($nodeValues);
    $node->save();

    $server = $this
      ->getCoreComposableServerBuilder()
      ->enableValueFields()
      ->enableExtension('entity_query')
      ->enableEntityType('node', array_keys($fields))
      ->createServer();

    $schema = $this->getSchema($server);
    $this->setUpCurrentUser(['uid' => 1]);

    /** @var \GraphQL\Type\Definition\ObjectType $NodeArticle */
    $NodeArticle = $schema->getType('NodeArticle');

    foreach ($fields as $machineName => $graphqlName) {
      $this->assertTrue(
        $NodeArticle->hasField($graphqlName),
        "Generates entity value field with name '$graphqlName' for machine name '$machineName'",
      );

      $rawFieldName = $graphqlName . 'RawField';
      $this->assertTrue(
        $NodeArticle->hasField($rawFieldName),
        "Generates entity raw field with name '$rawFieldName' for machine name '$machineName'",
      );
    }

    $query = <<<GQL
    query entityById(\$id: ID!) {
      entityById(id: \$id, entityType: NODE) {
        ... on NodeArticle {
          birthday
          fieldBirthday
          fieldBirthDay
          fieldBirthday1
          field_birthday_1
          fieldBirthday1a
          fieldBirthday1A
          field_birthday_1a_
          fieldBirthDay3b
          field_birth__day__3b_
          birthdayRawField { first { value } }
          fieldBirthdayRawField { first { value } }
          fieldBirthDayRawField { first { value } }
          fieldBirthday1RawField { first { value } }
          field_birthday_1RawField { first { value } }
          fieldBirthday1aRawField { first { value } }
          fieldBirthday1ARawField { first { value } }
          field_birthday_1a_RawField { first { value } }
          fieldBirthDay3bRawField { first { value } }
          field_birth__day__3b_RawField { first { value } }
        }
      }
    }
GQL;
    $params = OperationParams::create([
      'query' => $query,
      'variables' => [
        'id' => $node->id(),
      ],
    ]);
    $result = $server->executeOperation($params);
    $data = $result->data['entityById'];

    foreach ($fields as $machineName => $graphqlName) {
      $this->assertEquals(
        'Text: ' . $machineName,
        $data[$graphqlName],
        "Resolves value field '$graphqlName' for field with machine name '$machineName'"
      );

      $this->assertEquals(
        'Text: ' . $machineName,
        $data[$graphqlName . 'RawField']['first']['value'],
        "Resolves raw field '$graphqlName' for field with machine name '$machineName'"
      );
    }
  }

}
