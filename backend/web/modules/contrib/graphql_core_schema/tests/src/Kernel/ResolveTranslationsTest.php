<?php

namespace Drupal\Tests\graphql_core_schema\Kernel;

use Drupal\node\Entity\Node;
use GraphQL\Server\OperationParams;

/**
 * Tests the default resolver for translation handling.
 *
 * @group graphql_core_schema
 */
class ResolveTranslationsTest extends CoreComposableKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $user = $this->setUpCurrentUser();
    $role = $this->createRole(['bypass node access', 'administer taxonomy']);
    $user->addRole($role);
    $this->setCurrentUser($user);

    $this->createTranslatableContentType();
  }

  /**
   * Resolves entities in correct language.
   */
  public function testResolveCorrectLanguage(): void {
    $node = Node::create([
      'type' => 'article',
      'title' => 'English Article',
    ]);
    $node->save();
    $node_de = $node->addTranslation('de');
    $node_de->set('title', 'Deutscher Artikel');
    $node_de->save();

    $server = $this->getCoreComposableServerBuilder()
      ->enableEntityType('node')
      ->enableExtension('entity_query')
      ->enableBaseEntityField('label')
      ->enableValueFields()
      ->createServer();

    $query = <<<GQL
    query entityById(\$id: ID!) {
      entityById(id: \$id, entityType: NODE) {
        ... on NodeArticle {
          label
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
    $this->assertEquals('English Article', $data['label']);

    $this->setCurrentLanguage('de');
    $result = $server->executeOperation($params);
    $data = $result->data['entityById'];
    $this->assertEquals('Deutscher Artikel', $data['label']);
  }

  /**
   * Resolves entities in correct language across entity references.
   */
  public function testResolveCorrectLanguageReference(): void {
    $this->createEntityReferenceField('node', 'article', 'field_related_articles', NULL, 'node', 'default', ['target_bundles' => ['article' => 'article']], 10);
    $related_node = Node::create([
      'type' => 'article',
      'title' => 'Related node in all languages',
    ]);
    $related_node->save();

    $related_node_translation = $related_node->addTranslation('de');
    $related_node_translation->set('title', 'Verwandte Node in allen Sprachen');
    $related_node_translation->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Article in all languages',
      'field_related_articles' => [
        'target_id' => $related_node->id(),
      ],
    ]);
    $node->save();

    $node = Node::load($node->id());

    $node_de = $node->addTranslation('de');
    $node_de->set('title', 'Artikel in allen Sprachen');
    $node_de->set('field_related_articles', $related_node);
    $node_de->save();

    $node_en_only = Node::create([
      'type' => 'article',
      'title' => 'Node in English only',
      'field_related_articles' => [
        'target_id' => $related_node->id(),
      ],
    ]);
    $node_en_only->save();

    $server = $this->getCoreComposableServerBuilder()
      ->enableEntityType('node', ['field_related_articles'])
      ->enableExtension('entity_query')
      ->enableBaseEntityField('label')
      ->enableValueFields()
      ->createServer();

    // Set current language to German.
    $this->setCurrentLanguage('de');

    $query = <<<GQL
    query entityById(\$id: ID!) {
      entityById(id: \$id, entityType: NODE) {
        ... on NodeArticle {
          label
          fieldRelatedArticles {
            label
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

    // Expect both the node and the referenced node to be resolved in the current language.
    $this->assertEquals('Artikel in allen Sprachen', $data['label']);
    $this->assertEquals('Verwandte Node in allen Sprachen', $data['fieldRelatedArticles'][0]['label']);

    $params = OperationParams::create([
      'query' => $query,
      'variables' => [
        'id' => $node_en_only->id(),
      ],
    ]);
    $result = $server->executeOperation($params);
    $data = $result->data['entityById'];

    // The node should be resolved in English, as it has no German translation.
    // The referenced related node should be resolved in German.
    $this->assertEquals('Node in English only', $data['label']);
    $this->assertEquals('Verwandte Node in allen Sprachen', $data['fieldRelatedArticles'][0]['label']);
  }

}
