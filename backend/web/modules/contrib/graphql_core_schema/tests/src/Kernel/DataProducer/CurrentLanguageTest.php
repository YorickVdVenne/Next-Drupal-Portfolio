<?php

namespace Drupal\Tests\graphql_core_schema\Kernel\DataProducer;

use Drupal\Tests\graphql\Traits\DataProducerExecutionTrait;
use Drupal\Tests\graphql_core_schema\Kernel\CoreComposableKernelTestBase;

/**
 * Tests the current_language data producer.
 *
 * @group graphql_core_schema
 */
class CurrentLanguageTest extends CoreComposableKernelTestBase {

  use DataProducerExecutionTrait;

  /**
   * Resolves the current language.
   */
  public function testResolvesMenuLink(): void {
    $this->assertEquals('en', $this->executeDataProducer('current_language'));
    $this->setCurrentLanguage('de');
    $this->assertEquals('de', $this->executeDataProducer('current_language'));
  }

}
