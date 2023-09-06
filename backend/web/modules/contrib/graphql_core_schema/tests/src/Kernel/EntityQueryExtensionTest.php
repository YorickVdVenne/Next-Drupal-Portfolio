<?php

namespace Drupal\Tests\graphql_core_schema\Kernel;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\content_moderation\Traits\ContentModerationTestTrait;
use GraphQL\Server\OperationParams;

/**
 * Tests the entity_query extension.
 *
 * @group graphql_core_schema
 */
class EntityQueryExtensionTest extends CoreComposableKernelTestBase {

  use ContentModerationTestTrait;

  /**
   * Resolves an entity by ID.
   */
  public function testEntityById(): void {
    Vocabulary::create(['vid' => 'location'])->save();
    NodeType::create(['type' => 'article'])->save();

    $user = $this->setUpCurrentUser();
    $role = $this->createRole(['bypass node access', 'administer taxonomy']);
    $user->addRole($role);
    $this->setCurrentUser($user);

    $location = Term::create([
      'vid' => 'location',
      'name' => 'Moon',
    ]);
    $location->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
    ]);
    $node->save();

    $server = $this->getCoreComposableServerBuilder()
      ->enableEntityType('node')
      ->enableEntityType('taxonomy_term')
      ->enableExtension('entity_query')
      ->enableBaseEntityField('label')
      ->enableValueFields()
      ->createServer();

    $query = <<<GQL
    query entityById(\$id: ID!, \$entityType: EntityType!) {
      entityById(id: \$id, entityType: \$entityType) {
        label
      }
    }
    GQL;

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'id' => $node->id(),
        'entityType' => 'NODE',
      ],
    ]));
    $data = $result->data['entityById'];
    $this->assertEquals($node->label(), $data['label']);

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'id' => $location->id(),
        'entityType' => 'TAXONOMY_TERM',
      ],
    ]));
    $data = $result->data['entityById'];
    $this->assertEquals($location->label(), $data['label']);
  }

  /**
   * Resolves an entity by ID in the correct language.
   */
  public function testEntityByIdTranslation(): void {
    $this->createTranslatableContentType();

    $user = $this->setUpCurrentUser();
    $role = $this->createRole(['bypass node access', 'administer taxonomy']);
    $user->addRole($role);
    $this->setCurrentUser($user);

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test EN',
    ]);
    $node->save();

    $node_de = $node->addTranslation('de');
    $node_de->set('title', 'Test DE');
    $node_de->save();

    $node_en_only = Node::create([
      'type' => 'article',
      'title' => 'EN only',
    ]);
    $node_en_only->save();

    $server = $this->getCoreComposableServerBuilder()
      ->enableEntityType('node')
      ->enableExtension('entity_query')
      ->enableBaseEntityField('label')
      ->enableValueFields()
      ->createServer();

    $query = <<<GQL
    query entityById(\$id: ID!) {
      entityById(id: \$id, entityType: NODE) {
        label
      }
    }
    GQL;

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'id' => $node->id(),
      ],
    ]));
    $data = $result->data['entityById'];
    $this->assertEquals($node->label(), $data['label'], 'Resolves node in default language.');

    $this->setCurrentLanguage('de');

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'id' => $node->id(),
      ],
    ]));
    $data = $result->data['entityById'];
    $this->assertEquals($node_de->label(), $data['label'], 'Resolves translated node in correct language.');

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'id' => $node_en_only->id(),
      ],
    ]));
    $data = $result->data['entityById'];
    $this->assertEquals($node_en_only->label(), $data['label'], 'Resolves untranslated node in default language.');
  }

  /**
   * Resolves an entity by UUID.
   */
  public function testEntityByUuid(): void {
    NodeType::create(['type' => 'article'])->save();

    $user = $this->setUpCurrentUser();
    $role = $this->createRole(['bypass node access', 'administer taxonomy']);
    $user->addRole($role);
    $this->setCurrentUser($user);

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
    ]);
    $node->save();

    $server = $this->getCoreComposableServerBuilder()
      ->enableEntityType('node')
      ->enableExtension('entity_query')
      ->enableBaseEntityField('label')
      ->enableValueFields()
      ->createServer();

    $query = <<<GQL
    query entityByUuid(\$uuid: String!) {
      entityByUuid(uuid: \$uuid, entityType: NODE) {
        label
      }
    }
    GQL;

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'uuid' => $node->uuid(),
      ],
    ]));
    $data = $result->data['entityByUuid'];
    $this->assertEquals($node->label(), $data['label']);
  }

  /**
   * Performs access checks.
   */
  public function testAccessCheck(): void {
    NodeType::create(['type' => 'article'])->save();

    $user = $this->setUpCurrentUser();
    $role = $this->createRole(['access content']);
    $user->addRole($role);
    $this->setCurrentUser($user);

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
    ]);
    $node->save();

    $server = $this->getCoreComposableServerBuilder()
      ->enableEntityType('node')
      ->enableExtension('entity_query')
      ->enableBaseEntityField('label')
      ->enableValueFields()
      ->createServer();

    $query = <<<GQL
    query entityById(\$id: ID!) {
      entityById(id: \$id, entityType: NODE) {
        label
      }
    }
    GQL;

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'id' => $node->id(),
      ],
    ]));
    $data = $result->data['entityById'];
    $this->assertNotNull($data, 'Node with access is resolved.');

    $this->setUpCurrentUser();

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'id' => $node->id(),
      ],
    ]));
    $data = $result->data['entityById'];
    $this->assertNull($data, 'Node without access is not resolved.');
  }

  /**
   * Entity query.
   */
  public function testEntityQuery(): void {
    NodeType::create(['type' => 'article'])->save();

    $user = $this->setUpCurrentUser();
    $role = $this->createRole(['access content']);
    $user->addRole($role);
    $this->setCurrentUser($user);

    $expectedResult = [];

    for ($i = 0; $i < 10; $i++) {
      $node = Node::create([
        'type' => 'article',
        'title' => 'Test ' . $i,
      ]);
      $node->save();
      $expectedResult[] = [
        'id' => $node->id(),
        'label' => $node->label(),
      ];
    }

    $server = $this->getCoreComposableServerBuilder()
      ->enableEntityType('node')
      ->enableExtension('entity_query')
      ->enableBaseEntityField('label')
      ->enableValueFields()
      ->createServer();

    $query = <<<GQL
    query entityQuery(\$entityType: EntityType!, \$limit: Int) {
      entityQuery(entityType: \$entityType, limit: \$limit) {
        total
        items {
          id
          label
        }
      }
    }
    GQL;

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'entityType' => 'NODE',
      ],
    ]));
    $data = $result->data['entityQuery'];

    $this->assertEquals(10, $data['total']);
    $this->assertEquals($expectedResult, $data['items']);
    $this->setUpCurrentUser();
  }

  /**
   * Entity query.
   */
  public function testEntityQueryWithRevisions(): void {
    $this->enableModules([
      'workflows',
      'content_moderation',
    ]);

    $this->installEntitySchema('content_moderation_state');
    $this->installConfig('content_moderation');

    NodeType::create(['type' => 'article', 'new_revision' => TRUE])->save();

    $workflow = $this->createEditorialWorkflow();
    $workflow->getTypePlugin()->addEntityTypeAndBundle('node', 'article');
    $workflow->save();

    $this->setUpCurrentUser(['uid' => 1]);

    $node = Node::create([
      'type' => 'article',
      'title' => 'Version 1',
      'moderation_state' => 'published',
    ]);
    $node->save();
    $node->set('title', 'Version 2');
    $node->set('moderation_state', 'draft');
    $node->save();

    $server = $this->getCoreComposableServerBuilder()
      ->enableEntityType('node', ['vid'])
      ->enableExtension('entity_query')
      ->enableBaseEntityField('label')
      ->enableValueFields()
      ->createServer();

    $query = <<<GQL
    query entityQuery(\$entityType: EntityType!, \$limit: Int, \$revisions: EntityQueryRevisionMode) {
      entityQuery(entityType: \$entityType, limit: \$limit, revisions: \$revisions) {
        total
        items {
          ... on Node {
            label
          }
        }
      }
    }
    GQL;

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'entityType' => 'NODE',
        'revisions' => 'DEFAULT',
      ],
    ]));
    $label = $result->data['entityQuery']['items'][0]['label'];
    $this->assertEquals('Version 1', $label, 'Loads the default revision.');

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'entityType' => 'NODE',
        'revisions' => 'LATEST',
      ],
    ]));
    $label = $result->data['entityQuery']['items'][0]['label'];
    $this->assertEquals('Version 2', $label, 'Loads the latest revision.');

    $result = $server->executeOperation(OperationParams::create([
      'query' => $query,
      'variables' => [
        'entityType' => 'NODE',
        'revisions' => 'ALL',
      ],
    ]));
    $items = $result->data['entityQuery']['items'];
    $this->assertEquals([
      ['label' => 'Version 1'],
      ['label' => 'Version 2'],
    ], $items, 'Loads all revisions.');
  }

  /**
   * Entity query.
   */
  public function testEntityQueryWithRevisionsForUnrevisionable(): void {
    NodeType::create(['type' => 'article', 'new_revision' => TRUE])->save();
    NodeType::create(['type' => 'page', 'new_revision' => TRUE])->save();
    NodeType::create(['type' => 'product', 'new_revision' => TRUE])->save();

    $this->setUpCurrentUser(['uid' => 1]);

    $server = $this->getCoreComposableServerBuilder()
      ->enableEntityType('node_type')
      ->enableExtension('entity_query')
      ->enableBaseEntityField('label')
      ->enableValueFields()
      ->createServer();

    $query = <<<GQL
    query entityQuery(\$entityType: EntityType!, \$limit: Int, \$revisions: EntityQueryRevisionMode) {
      entityQuery(entityType: \$entityType, limit: \$limit, revisions: \$revisions) {
        total
        items {
          id
        }
      }
    }
    GQL;

    $revisions = ['DEFAULT', 'LATEST', 'ALL'];
    foreach ($revisions as $revision) {
      $result = $server->executeOperation(OperationParams::create([
        'query' => $query,
        'variables' => [
          'entityType' => 'NODE_TYPE',
          'revisions' => $revision,
        ],
      ]));
      $items = $result->data['entityQuery']['items'];

      $this->assertCount(3, $items);
      $this->assertEquals([
        ['id' => 'article'],
        ['id' => 'page'],
        ['id' => 'product'],
      ], $items);
    }
  }

}
