<?php

// phpcs:disable Drupal.NamingConventions.ValidVariableName.LowerStart

namespace Drupal\Tests\graphql_core_schema\Kernel;

use Drupal\graphql\Entity\ServerInterface;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use GraphQL\Server\OperationParams;

/**
 * Tests the routing extension.
 *
 * @group graphql_core_schema
 */
class RoutingExtensionTest extends CoreComposableKernelTestBase {

  /**
   * The GraphQL server.
   */
  protected ServerInterface $server;

  /**
   * Test the Url type resolvers.
   */
  public function testUrlTypeResolver(): void {
    NodeType::create(['type' => 'article'])->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
    ]);
    $node->save();

    $this->server = $this
      ->getCoreComposableServerBuilder()
      ->enableValueFields()
      ->enableExtension('routing')
      ->enableEntityType('node')
      ->createServer();

    $this->setUpCurrentUser(['uid' => 1]);

    $this->assertUrlType('/en/node/1', 'EntityCanonicalUrl');
    $this->assertUrlType('/en/node/1/edit', 'DefaultEntityUrl');
    $this->assertUrlType('/en/admin/content', 'DefaultInternalUrl');
    $this->assertUrlType('https://www.example.com', 'ExternalUrl');
  }

  /**
   * Assert the resolved Url type.
   *
   * @param string $path
   *   The path.
   * @param string $type
   *   The expected GraphQL type.
   */
  private function assertUrlType(string $path, string $type) {
    $query = <<<GQL
    query route(\$path: String!) {
      route(path: \$path) {
        __typename
      }
    }
    GQL;
    $params = OperationParams::create([
      'query' => $query,
      'variables' => [
        'path' => $path,
      ],
    ]);
    $result = $this->server->executeOperation($params);
    $data = $result->data['route'];
    $this->assertEquals($type, $data['__typename']);
  }

}
