<?php

namespace Drupal\Tests\graphql_core_schema\Kernel\DataProducer;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\graphql\Traits\DataProducerExecutionTrait;
use Drupal\Tests\graphql_core_schema\Kernel\CoreComposableKernelTestBase;

/**
 * Tests the local_tasks data producer.
 *
 * @group graphql_core_schema
 */
class LocalTasksTest extends CoreComposableKernelTestBase {

  use DataProducerExecutionTrait;

  /**
   * Resolves the local tasks for users with access.
   */
  public function testResolvesLocalTasks(): void {
    NodeType::create(['type' => 'article'])->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
    ]);
    $node->save();

    $this->setUpCurrentUser(['uid' => 1]);

    $tasks = $this->executeDataProducer('local_tasks', [
      'url' => $node->toUrl(),
    ]);

    $this->assertCount(4, $tasks);
    $canonical = $tasks[0];
    $this->assertEquals('entity.node.canonical', $canonical['_key']);
    /** @var \Drupal\Core\Url $url */
    $url = $canonical['#link']['url'];
    $this->assertEquals('/en/node/1', $url->toString());
  }

  /**
   * Resolves the local tasks for users with access.
   */
  public function testResolvesOnlyAccessibleLocalTasks(): void {
    NodeType::create(['type' => 'article'])->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
    ]);
    $node->save();

    $this->setUpCurrentUser();

    $tasks = $this->executeDataProducer('local_tasks', [
      'url' => $node->toUrl(),
    ]);

    $this->assertCount(0, $tasks);
  }

}
