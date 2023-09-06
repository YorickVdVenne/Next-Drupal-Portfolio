<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns the rendered field item in a given view mode.
 *
 * @DataProducer(
 *   id = "render_field",
 *   name = @Translation("Rendered Field List or Item"),
 *   description = @Translation("Returns the rendered field list or item."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Rendered output")
 *   ),
 *   consumes = {
 *     "field" = @ContextDefinition("any",
 *       label = @Translation("Field List or Item")
 *     ),
 *     "viewMode" = @ContextDefinition("string",
 *       label = @Translation("View mode"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class FieldRenderer extends DataProducerPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
      $container->get('renderer')
    );
  }

  /**
   * RenderFieldItem constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->renderer = $renderer;
  }

  /**
   * The resolver.
   *
   * @param \Drupal\Core\Field\FieldItemInterface|FieldItemListInterface|null $field
   *   The current field.
   * @param string $viewMode
   *   The requested view mode.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The metadata.
   *
   * @return string|void
   *   The result.
   */
  public function resolve($field, $viewMode, RefinableCacheableDependencyInterface $metadata) {
    if ($field instanceof FieldItemInterface || $field instanceof FieldItemListInterface) {
      $context = new RenderContext();
      $result = $this->renderer->executeInRenderContext($context, function () use ($viewMode, $field) {
        $renderArray = $field->view($viewMode ?? 'default');
        return $this->renderer->render($renderArray);
      });

      if (!$context->isEmpty()) {
        $metadata->addCacheableDependency($context->pop());
      }

      return (string) $result;
    }
  }

}
