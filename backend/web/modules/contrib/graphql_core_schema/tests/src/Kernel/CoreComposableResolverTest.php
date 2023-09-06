<?php

namespace Drupal\Tests\graphql_core_schema\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\graphql_core_schema\Traits\CoreComposableServerBuilder;
use GraphQL\Server\OperationParams;

/**
 * Tests the default resolver.
 *
 * @group graphql_core_schema
 */
class CoreComposableResolverTest extends CoreComposableKernelTestBase {

  /**
   * Resolves scalar values correctly.
   */
  public function testResolveScalarValues(): void {
    $this->enableModules(['telephone', 'options']);
    NodeType::create(['type' => 'article'])->save();

    $fieldNames = [];
    $nodeValues = [
      'type' => 'article',
      'title' => 'Test',
    ];
    $fields = [
      'string' => 'foobar',
      'string_long' => 'foobar',
      'text' => '<p>foobar</p>',
      'text_with_summary' => '<p>foobar</p>',
      'text_long' => '<p>foobar</p>',
      'uri' => 'internal:/foobar',
      'email' => 'foobar@example.com',
      'telephone' => '+411234567',
      'list_string' => 'foobar',
      'timestamp' => '1668433941',
      'changed' => '1668433941',
      'created' => '1668433941',
      'boolean' => TRUE,
      'decimal' => 3.14,
      'float' => 1.2345,
      'integer' => 42,
    ];

    foreach ($fields as $drupalType => $value) {
      $fieldName = 'field_' . $drupalType;
      $fieldNames[] = $fieldName;
      $field_storage = FieldStorageConfig::create([
        'field_name' => $fieldName,
        'entity_type' => 'node',
        'type' => $drupalType,
        'cardinality' => 1,
      ]);
      $field_storage->save();

      FieldConfig::create([
        'field_name' => $fieldName,
        'field_storage' => $field_storage,
        'entity_type' => 'node',
        'bundle' => 'article',
        'label' => $fieldName,
      ])->save();

      if (str_starts_with($drupalType, 'text')) {
        $nodeValues[$fieldName] = [
          'value' => $value,
          'format' => 'default',
        ];
      }
      else {
        $nodeValues[$fieldName] = $value;
      }
    }

    $user = $this->setUpCurrentUser();
    $role = $this->createRole(['bypass node access']);
    $user->addRole($role);
    $this->setCurrentUser($user);

    $node = Node::create($nodeValues);
    $node->save();

    $builder = new CoreComposableServerBuilder();
    $server = $builder
      ->enableEntityType('node', $fieldNames)
      ->enableExtension('entity_query')
      ->enableValueFields()
      ->createServer();

    $query = <<<GQL
    query entityById(\$id: ID!) {
      entityById(id: \$id, entityType: NODE) {
        ... on NodeArticle {
          field_string: fieldString
          field_string_long: fieldStringLong
          field_text: fieldText
          field_text_with_summary: fieldTextWithSummary
          field_text_long: fieldTextLong
          field_uri: fieldUri
          field_email: fieldEmail
          field_telephone: fieldTelephone
          field_list_string: fieldListString
          field_timestamp: fieldTimestamp
          field_changed: fieldChanged
          field_created: fieldCreated
          field_boolean: fieldBoolean
          field_decimal: fieldDecimal
          field_float: fieldFloat
          field_integer: fieldInteger
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

    $this->assertEquals($fields['string'], $data['field_string']);
    $this->assertEquals($fields['string_long'], $data['field_string_long']);
    $this->assertEquals($fields['text'], trim($data['field_text']));
    $this->assertEquals($fields['text_with_summary'], trim($data['field_text_with_summary']));
    $this->assertEquals($fields['text_long'], trim($data['field_text_long']));
    $this->assertEquals($fields['uri'], $data['field_uri']);
    $this->assertEquals($fields['email'], $data['field_email']);
    $this->assertEquals($fields['telephone'], $data['field_telephone']);
    $this->assertEquals($fields['list_string'], $data['field_list_string']);
    $this->assertEquals('2022-11-15T00:52:21+1100', $data['field_timestamp']);
    $this->assertEquals('2022-11-15T00:52:21+1100', $data['field_changed']);
    $this->assertEquals('2022-11-15T00:52:21+1100', $data['field_created']);
    $this->assertEquals($fields['boolean'], $data['field_boolean']);
    $this->assertEquals($fields['decimal'], $data['field_decimal']);
    $this->assertEquals($fields['float'], $data['field_float']);
    $this->assertEquals($fields['integer'], $data['field_integer']);
  }

  /**
   * Resolves entity references correctly.
   */
  public function testResolveEntityReference(): void {
    Vocabulary::create(['vid' => 'tag'])->save();
    Vocabulary::create(['vid' => 'location'])->save();
    NodeType::create(['type' => 'article'])->save();

    $this->createEntityReferenceField(
      'node',
      'article',
      'field_location',
      'Location',
      'taxonomy_term',
      'default',
      ['target_bundles' => ['location' => 'location']],
      1
    );

    $this->createEntityReferenceField(
      'node',
      'article',
      'field_tags',
      'Tags',
      'taxonomy_term',
      'default',
      ['target_bundles' => ['tag' => 'tag']],
      6
    );

    $user = $this->setUpCurrentUser();
    $role = $this->createRole(['bypass node access', 'administer taxonomy']);
    $user->addRole($role);
    $this->setCurrentUser($user);

    $tag1 = Term::create([
      'vid' => 'tag',
      'name' => 'Tag 1',
    ]);
    $tag1->save();

    $tag2 = Term::create([
      'vid' => 'tag',
      'name' => 'Tag 2',
    ]);
    $tag2->save();

    $location = Term::create([
      'vid' => 'location',
      'name' => 'Moon',
    ]);
    $location->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
      'field_tags' => [
        [
          'target_id' => $tag1->id(),
        ],
        [
          'target_id' => $tag2->id(),
        ],
      ],
      'field_location' => [
        [
          'target_id' => $location->id(),
        ],
      ],
    ]);
    $node->save();

    $builder = new CoreComposableServerBuilder();
    $server = $builder
      ->enableEntityType('node', ['field_location', 'field_tags'])
      ->enableEntityType('taxonomy_term')
      ->enableExtension('entity_query')
      ->enableValueFields()
      ->createServer();

    $query = <<<GQL
    query entityById(\$id: ID!) {
      entityById(id: \$id, entityType: NODE) {
        ... on NodeArticle {
          field_tags: fieldTags {
            id
          }
          field_location: fieldLocation {
            id
          }
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
    $this->assertEquals([
      [
        'id' => $tag1->id(),
      ],
      [
        'id' => $tag2->id(),
      ],
    ], $data['field_tags']);
    $this->assertEquals(['id' => $location->id()], $data['field_location']);
  }

  /**
   * Resolves field item lists correctly.
   */
  public function testResolveFieldItemList(): void {
    Vocabulary::create(['vid' => 'tag'])->save();
    NodeType::create(['type' => 'article'])->save();

    $this->createEntityReferenceField(
      'node',
      'article',
      'field_tags',
      'Location',
      'taxonomy_term',
      'default',
      ['target_bundles' => ['tag' => 'tag']],
      2
    );

    $user = $this->setUpCurrentUser();
    $role = $this->createRole(['bypass node access', 'administer taxonomy']);
    $user->addRole($role);
    $this->setCurrentUser($user);

    $tag1 = Term::create([
      'vid' => 'tag',
      'name' => 'Tag 1',
    ]);
    $tag1->save();

    $tag2 = Term::create([
      'vid' => 'tag',
      'name' => 'Tag 2',
    ]);
    $tag2->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
      'field_tags' => [
        [
          'target_id' => $tag1->id(),
        ],
        [
          'target_id' => $tag2->id(),
        ],
      ],
    ]);
    $node->save();

    $builder = new CoreComposableServerBuilder();
    $server = $builder
      ->enableEntityType('node', ['field_tags'])
      ->enableEntityType('taxonomy_term')
      ->enableEntityType('field_config')
      ->enableEntityType('field_storage_config')
      ->enableExtension('entity_query')
      ->enableExtension('field_config')
      ->enableBaseEntityField('label')
      ->createServer();

    $query = <<<GQL
    query entityById(\$id: ID!) {
      entityById(id: \$id, entityType: NODE) {
        ... on NodeArticle {
          fieldTagsRawField {
            isEmpty
            count
            fieldConfig {
              name
            }
            first {
              targetId
              entity {
                label
              }
            }
            list {
              targetId
              entity {
                label
              }
            }
          }
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
    $data = $result->data['entityById']['fieldTagsRawField'];
    $this->assertEquals([
      [
        'targetId' => $tag1->id(),
        'entity' => [
          'label' => $tag1->label(),
        ],
      ],
      [
        'targetId' => $tag2->id(),
        'entity' => [
          'label' => $tag2->label(),
        ],
      ],
    ], $data['list']);

    $this->assertEquals([
      'targetId' => $tag1->id(),
      'entity' => [
        'label' => $tag1->label(),
      ],
    ], $data['first']);

    $this->assertFalse($data['isEmpty']);
    $this->assertEquals(2, $data['count']);
    $this->assertEquals('field_tags', $data['fieldConfig']['name']);
  }

}
