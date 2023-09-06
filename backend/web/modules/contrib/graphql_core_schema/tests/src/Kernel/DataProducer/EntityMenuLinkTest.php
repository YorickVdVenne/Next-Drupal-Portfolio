<?php

namespace Drupal\Tests\graphql_core_schema\Kernel\DataProducer;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\system\Entity\Menu;
use Drupal\Tests\graphql\Traits\DataProducerExecutionTrait;
use Drupal\Tests\graphql_core_schema\Kernel\CoreComposableKernelTestBase;

/**
 * Tests the breadcrumb extension.
 *
 * @group graphql_core_schema
 */
class EntityMenuLinkTest extends CoreComposableKernelTestBase {

  use DataProducerExecutionTrait;

  protected Menu $menu;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->enableModules(['redirect', 'path_alias']);
    $this->installEntitySchema('redirect');
    $this->installEntitySchema('path_alias');
    $this->installEntitySchema('menu_link_content');

    $this->menu = Menu::create([
      'id' => 'menu_test',
      'label' => 'Test menu',
      'description' => 'Description text',
    ]);

    $this->menu->save();
  }

  /**
   * Resolves the menu link of an entity.
   */
  public function testResolvesMenuLink(): void {
    NodeType::create(['type' => 'article'])->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Test',
    ]);
    $node->save();

    $node2 = Node::create([
      'type' => 'article',
      'title' => 'Test 2',
    ]);
    $node2->save();

    $values = [
      'title' => 'Menu link test',
      'provider' => 'graphql_core_schema',
      'menu_name' => 'menu_test',
      'link' => [
        'uri' => 'entity:node/' . $node2->id(),
      ],
      'description' => 'Test description',
    ];
    $link = MenuLinkContent::create($values);
    $link->save();
    $this->setUpCurrentUser(['uid' => 1]);

    /** @var MenuLinkTreeElement $result */
    $result = $this->executeDataProducer('entity_menu_link', [
      'entity' => $node2,
      'menu_name' => 'menu_test',
    ]);

    $this->assertInstanceOf(MenuLinkTreeElement::class, $result);
    $this->assertEquals('Menu link test', $result->link->getTitle());

    $result = $this->executeDataProducer('entity_menu_link', [
      'entity' => $node,
      'menu_name' => 'menu_test',
    ]);

    $this->assertNull($result, 'Returns no menu link for a node without menu link.');
  }

}
