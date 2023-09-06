<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer\Route;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\Buffers\EntityRevisionBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads the entity associated with the current URL.
 *
 * @DataProducer(
 *   id = "enhanced_route_entity",
 *   name = @Translation("Enhanced Route Entity"),
 *   description = @Translation("The entity belonging to the current url."),
 *   produces = @ContextDefinition("entity",
 *     label = @Translation("Entity")
 *   ),
 *   consumes = {
 *     "url" = @ContextDefinition("any",
 *       label = @Translation("The URL")
 *     ),
 *     "language" = @ContextDefinition("string",
 *       label = @Translation("Language"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class EnhancedRouteEntity extends DataProducerPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\EntityBuffer
   */
  protected $entityBuffer;

  /**
   * The entity revision buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\EntityRevisionBuffer
   */
  protected $entityRevisionBuffer;

  /**
   * Stores the tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

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
      $container->get('entity_type.manager'),
      $container->get('graphql.buffer.entity'),
      $container->get('graphql.buffer.entity_revision'),
      $container->get('tempstore.private')
    );
  }

  /**
   * RouteEntity constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The language manager service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entityBuffer
   *   The entity buffer service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityRevisionBuffer $entityRevisionBuffer
   *   The entity revision buffer service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *   The factory for the temp store object.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    EntityBuffer $entityBuffer,
    EntityRevisionBuffer $entityRevisionBuffer,
    PrivateTempStoreFactory $tempStoreFactory
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityBuffer = $entityBuffer;
    $this->entityRevisionBuffer = $entityRevisionBuffer;
    $this->tempStoreFactory = $tempStoreFactory;
  }

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Url|mixed $url
   *   The URL to get the route entity from.
   * @param string|null $language
   *   The language code to get a translation of the entity.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The GraphQL field context.
   */
  public function resolve($url, ?string $language, FieldContext $context) {
    if ($url instanceof Url) {
      $routeName = $url->getRouteName();
      $parameters = $url->getRouteParameters();

      // Special handling for node preview URLs.
      // The preview entity's form state is stored in the tempstore. From the
      // form state we can extract the entity. This is basically what
      // \Drupal\node\ParamConverter\NodePreviewConverter is doing.
      if ($routeName === 'entity.node.preview') {
        $store = $this->tempStoreFactory->get('node_preview');
        if ($form_state = $store->get($parameters['node_preview'])) {
          $context->mergeCacheMaxAge(0);
          return $form_state->getFormObject()->getEntity();
        }
        return NULL;
      }

      [, $type] = explode('.', $routeName);
      $id = $parameters[$type] ?? NULL;
      if (!$id) {
        return NULL;
      }

      $revisionId = $parameters[$type . '_revision'] ?? NULL;
      if ($routeName === 'entity.node.latest_version') {
        /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
        $nodeStorage = $this->entityTypeManager->getStorage('node');
        $revisionId = $nodeStorage->getLatestRevisionId($id);
      }
      $resolver = $revisionId
        ? $this->entityRevisionBuffer->add($type, $revisionId)
        : $this->entityBuffer->add($type, $id);

      return new Deferred(function () use ($type, $resolver, $context, $language) {
        /** @var \Drupal\Core\Entity\EntityInterface|null $entity */
        $entity = $resolver();
        if (!$entity) {
          // If there is no entity with this id, add the list cache tags so that
          // the cache entry is purged whenever a new entity of this type is
          // saved.
          $type = $this->entityTypeManager->getDefinition($type);
          /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
          $tags = $type->getListCacheTags();
          $context->addCacheTags($tags)->addCacheTags(['4xx-response']);
          return NULL;
        }

        // Get the correct translation.
        if (isset($language) && $language != $entity->language()->getId() && $entity instanceof TranslatableInterface) {
          $entity = $entity->getTranslation($language);
          $entity->addCacheContexts(["static:language:{$language}"]);
        }

        $access = $entity->access('view', NULL, TRUE);
        $context->addCacheableDependency($access);
        if ($access->isAllowed()) {
          return $entity;
        }
        return NULL;
      });
    }
    return NULL;
  }

}
