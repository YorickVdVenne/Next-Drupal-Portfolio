<?php

namespace Drupal\Tests\graphql_core_schema\Kernel;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\filter\Entity\FilterFormat;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\language\Entity\ContentLanguageSettings;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\Tests\graphql_core_schema\Traits\CoreComposableSchemaTrait;
use Drupal\Tests\graphql_core_schema\Traits\CoreComposableServerBuilder;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests the entity_query extension
 */
class CoreComposableKernelTestBase extends EntityKernelTestBase {

  use CoreComposableSchemaTrait;
  use NodeCreationTrait;
  use ContentTypeCreationTrait;
  use EntityReferenceTestTrait;

  protected array $languages = [];
  protected LanguageManagerInterface $languageManager;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'language',
    'field',
    'node',
    'graphql',
    'entity_reference_test',
    'link',
    'user',
    'taxonomy',
    'menu_link_content',
    'content_translation',
    'typed_data',
    'text',
    'graphql_core_schema',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig('user');
    $this->installConfig('system');
    $this->installConfig('graphql');
    $this->installConfig('graphql');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('taxonomy_term');
    $this->installSchema('node', ['node_access']);
    $this->installSchema('user', ['users_data']);
    $this->installEntitySchema('graphql_server');
    $this->installEntitySchema('configurable_language');
    $this->installConfig(['language']);
    $this->installEntitySchema('menu_link_content');
    $this->installConfig(['graphql_core_schema']);

    FilterFormat::create([
      'format' => 'default',
      'name' => 'My text format',
      'filters' => [
        'filter_autop' => [
          'module' => 'filter',
          'status' => TRUE,
        ],
      ],
    ])->save();

    $this->languages['en'] = ConfigurableLanguage::load('en');
    $this->languages['it'] = ConfigurableLanguage::createFromLangcode('it')->setWeight(1);
    $this->languages['de'] = ConfigurableLanguage::createFromLangcode('de')->setWeight(2);
    $this->languages['it']->save();
    $this->languages['de']->save();

    $config = $this->config('language.negotiation');
    $config->set('url.prefixes', [
      'en' => 'en',
      'de' => 'de',
    ])->save();

    \Drupal::service('kernel')->rebuildContainer();
    \Drupal::service('router.builder')->rebuild();
    $this->languageManager = $this->container->get('language_manager');
    $this->languageManager->reset();
  }

  /**
   * Get the core_composable server builder.
   *
   * @return CoreComposableServerBuilder
   *   The core composable server builder.
   */
  protected function getCoreComposableServerBuilder() {
    return new CoreComposableServerBuilder();
  }

  /**
   * Create translatable content type.
   *
   * @param string $bundle
   *   The bundle name.
   * @return NodeType
   *   The node type.
   */
  protected function createTranslatableContentType(string $bundle = 'article'): NodeType {
    $type = NodeType::create(['type' => $bundle]);
    $type->save();
    $config = ContentLanguageSettings::loadByEntityTypeBundle('node', $bundle);
    $config->setDefaultLangcode(LanguageInterface::LANGCODE_SITE_DEFAULT);
    $config->setLanguageAlterable(TRUE);
    $config->save();

    $content_translation_manager = $this->container->get('content_translation.manager');
    $content_translation_manager->setEnabled('node', $bundle, TRUE);

    return $type;
  }

  /**
   * Change current language.
   *
   * @param string $langcode
   *   The langcode.
   */
  protected function setCurrentLanguage(string $langcode) {
    \Drupal::service('language.default')->set($this->languages[$langcode]);
    \Drupal::languageManager()->reset();
  }

}
