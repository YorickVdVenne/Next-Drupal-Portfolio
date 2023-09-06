<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\media\MediaInterface;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Produce a media file URL.
 *
 * @DataProducer(
 *   id = "media_file_url",
 *   name = @Translation("Media File URL"),
 *   description = @Translation("Return the URL to the media file."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("URL")
 *   ),
 *   consumes = {
 *     "media" = @ContextDefinition("entity:media",
 *       label = @Translation("Media"),
 *     )
 *   }
 * )
 */
class MediaFileUrl extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\EntityBuffer
   */
  protected $entityBuffer;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGenerator
   */
  protected $fileUrlGenerator;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('graphql.buffer.entity'),
      $container->get('file_url_generator'),
      $container->get('renderer')
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
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entityBuffer
   *   The entity buffer.
   * @param \Drupal\Core\File\FileUrlGenerator $fileUrlGenerator
   *   The file URL generator.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityBuffer $entityBuffer,
    FileUrlGeneratorInterface $fileUrlGenerator,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityBuffer = $entityBuffer;
    $this->fileUrlGenerator = $fileUrlGenerator;
    $this->renderer = $renderer;
  }

  /**
   * The resolver.
   *
   * @param \Drupal\media\Entity\Media $media
   *   The media entity.
   *
   * @return array|\GraphQL\Deferred
   *   The result.
   */
  public function resolve(MediaInterface $media, FieldContext $context) {
    $fid = $media->getSource()->getSourceFieldValue($media);

    if (!$fid) {
      return NULL;
    }

    $resolver = $this->entityBuffer->add('file', $fid);

    return new Deferred(function () use ($resolver, $context) {
      /** @var \Drupal\Core\File|null $file */
      $file = $resolver();

      if (!$file) {
        return NULL;
      }

      $context->addCacheableDependency($file);

      $renderContext = new RenderContext();
      $result = $this->renderer->executeInRenderContext($renderContext, function () use ($file) {
        $uri = $file->getFileUri();
        return $this->fileUrlGenerator->generate($uri);
      });

      $context->addCacheableDependency($renderContext);
      return $result;
    });
  }

}
