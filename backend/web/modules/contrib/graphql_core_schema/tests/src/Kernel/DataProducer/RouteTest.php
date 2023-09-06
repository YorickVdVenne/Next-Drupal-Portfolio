<?php

namespace Drupal\Tests\graphql_core_schema\Kernel\DataProducer;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\graphql\Traits\DataProducerExecutionTrait;
use Drupal\Tests\graphql_core_schema\Kernel\CoreComposableKernelTestBase;

/**
 * Tests the route data producer.
 *
 * @group graphql_core_schema
 */
class RouteTest extends CoreComposableKernelTestBase {

  use DataProducerExecutionTrait;

  /**
   * Resolves the entity canonical route.
   */
  public function testResolveNode(): void {
    NodeType::create(['type' => 'article'])->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
    ]);
    $node->save();

    $node_de = $node->addTranslation('de');
    $node_de->set('title', 'Test Deutsch');
    $node_de->save();

    $this->setUpCurrentUser(['uid' => 1]);

    $this->setCurrentLanguage('en');
    $url = $this->resolveRouteUrl('/en/node/1');
    $this->assertEquals('entity.node.canonical', $url->getRouteName());
    $this->assertEquals('1', $url->getRouteParameters()['node']);
    $this->assertEquals('en', $url->getOption('language')->getId());

    $this->setCurrentLanguage('de');
    $url = $this->resolveRouteUrl('/de/node/1');
    $this->assertEquals('entity.node.canonical', $url->getRouteName());
    $this->assertEquals('1', $url->getRouteParameters()['node']);
    $this->assertEquals('de', $url->getOption('language')->getId());

    $this->setCurrentLanguage('en');
    $url = $this->resolveRouteUrl('/node/1');
    $this->assertEquals('entity.node.canonical', $url->getRouteName());
    $this->assertEquals('1', $url->getRouteParameters()['node']);
    $this->assertEquals('en', $url->getOption('language')->getId());

    $this->setCurrentLanguage('de');
    $url = $this->resolveRouteUrl('/node/1');
    $this->assertEquals('entity.node.canonical', $url->getRouteName());
    $this->assertEquals('1', $url->getRouteParameters()['node']);
    $this->assertEquals('de', $url->getOption('language')->getId());
  }

  /**
   * Execute route data producer.
   *
   * @param string $path
   *   The path.
   *
   * @return Url|null
   *   The result.
   */
  private function resolveRouteUrl(string $path): Url|null {
    return $this->executeDataProducer('get_route', [
      'path' => $path,
    ]);
  }

}
