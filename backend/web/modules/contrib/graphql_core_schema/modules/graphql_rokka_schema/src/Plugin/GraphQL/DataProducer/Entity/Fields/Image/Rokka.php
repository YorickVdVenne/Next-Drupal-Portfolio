<?php

namespace Drupal\graphql_rokka_schema\Plugin\GraphQL\DataProducer\Entity\Fields\Image;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\rokka\RokkaServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns an image style derivative of an image.
 *
 * @DataProducer(
 *   id = "rokka",
 *   name = @Translation("Rokka"),
 *   description = @Translation("Returns an rokka details."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Rokka Image properties")
 *   ),
 *   consumes = {
 *     "file" = @ContextDefinition("entity",
 *       label = @Translation("File"),
 *       required = TRUE,
 *     ),
 *   }
 * )
 */
class Rokka extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Rokka service.
   *
   * @var \Drupal\rokka\RokkaServiceInterface
   */
  protected $rokkaService;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('rokka.service')
    );
  }

  /**
   * The constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\rokka\RokkaServiceInterface $rokka_service
   *   The rokka service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    RokkaServiceInterface $rokka_service
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->rokkaService = $rokka_service;
  }

  /**
   * The resolver.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The metadata.
   *
   * @return \Drupal\rokka\Entity\RokkaMetadata|null
   *   The rokka metadata.
   */
  public function resolve(FileInterface $file, RefinableCacheableDependencyInterface $metadata) {
    $rokkaMetadata = array_values($this->rokkaService->loadRokkaMetadataByUri($file->uri->value));
    if (!empty($rokkaMetadata)) {
      $metadata->addCacheableDependency($rokkaMetadata[0]);
      return $rokkaMetadata[0];
    }

    return NULL;
  }

}
