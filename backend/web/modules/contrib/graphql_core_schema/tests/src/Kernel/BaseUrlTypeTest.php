<?php

// phpcs:disable Drupal.NamingConventions.ValidVariableName.LowerStart

namespace Drupal\Tests\graphql_core_schema\Kernel;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use GraphQL\Server\OperationParams;
use GraphQL\Type\Definition\InterfaceType;

/**
 * Tests the Url and DefaultUrl types from the base schema.
 *
 * @group graphql_core_schema
 */
class BaseUrlTypeTest extends CoreComposableKernelTestBase {

  /**
   * Test that the Url type and the path field can be resolved without the
   * routing extension enabled.
   */
  public function testBaseUrlType(): void {
    NodeType::create(['type' => 'article'])->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
    ]);
    $node->save();

    $server = $this
      ->getCoreComposableServerBuilder()
      ->enableValueFields()
      ->enableExtension('entity_query')
      ->enableEntityType('node')
      ->createServer();

    $schema = $this->getSchema($server);
    $this->setUpCurrentUser(['uid' => 1]);

    /** @var \GraphQL\Type\Definition\ObjectType $NodeArticle */
    $NodeArticle = $schema->getType('NodeArticle');

    $interfaces = array_map(function (InterfaceType $v) {
      return $v->name;
    }, $NodeArticle->getInterfaces());

    $this->assertContains(
      'EntityLinkable',
      $interfaces,
      'An entity that is linkable implements the EntityLinkable interface.'
    );

    $query = <<<GQL
    query entityById(\$id: ID!) {
      entityById(id: \$id, entityType: NODE) {
        ... on NodeArticle {
          url {
            __typename
            path
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
    $this->assertEquals('/en/node/1', $data['url']['path']);
    $this->assertEquals('DefaultUrl', $data['url']['__typename']);
  }

}
