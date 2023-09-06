<?php

declare(strict_types=1);

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\language\LanguageNegotiatorInterface;
use Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationUrl;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns the URL of the given path.
 *
 * @DataProducer(
 *   id = "get_route",
 *   name = @Translation("Load route"),
 *   description = @Translation("Loads a route."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Route")
 *   ),
 *   consumes = {
 *     "path" = @ContextDefinition("string",
 *       label = @Translation("Path")
 *     )
 *   }
 * )
 */
class Route extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The language negotiator service.
   *
   * @var \Drupal\language\LanguageNegotiator
   */
  protected $languageNegotiator;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The redirect repository.
   *
   * @var \Drupal\redirect\RedirectRepository
   */
  protected $redirectRepository;

  /**
   * The inbound path processor.
   *
   * @var \Drupal\Core\PathProcessor\InboundPathProcessorInterface
   */
  protected $pathProcessor;

  /**
   * The redirect entity repository.
   *
   * @var \Drupal\domain_path_redirect\DomainPathRedirectRepository
   */
  protected $domainPathRedirectRepository;

  /**
   * Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiator
   */
  protected $domainNegotiator;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManger;

  /**
   * The redirect 404 storage.
   *
   * @var \Drupal\redirect_404\RedirectNotFoundStorageInterface
   */
  protected $redirectNotFoundStorage;


  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The subrequest buffer.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer
   */
  protected $subRequestBuffer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.validator'),
      $container->get('language_negotiator', ContainerInterface::NULL_ON_INVALID_REFERENCE),
      $container->get('language_manager'),
      $container->get('redirect.repository', ContainerInterface::NULL_ON_INVALID_REFERENCE),
      $container->get('path_processor_manager'),
      $container->get('domain_path_redirect.repository', ContainerInterface::NULL_ON_INVALID_REFERENCE),
      $container->get('domain.negotiator', ContainerInterface::NULL_ON_INVALID_REFERENCE),
      $container->get('entity_type.manager'),
      $container->get('redirect.not_found_storage', ContainerInterface::NULL_ON_INVALID_REFERENCE),
      $container->get('config.factory'),
      $container->get('graphql.buffer.subrequest')
    );
  }

  /**
   * Route constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   The path validator service.
   * @param \Drupal\language\LanguageNegotiator|null $languageNegotiator
   *   The language negotiator.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\redirect\RedirectRepository|null $redirectRepository
   *   The redirect repository, if redirect module is active.
   * @param \Drupal\Core\PathProcessor\InboundPathProcessorInterface $pathProcessor
   *   An inbound path processor, to clean paths before redirect lookups.
   * @param \Drupal\domain_path_redirect\DomainPathRedirectRepository|null $domain_path_redirect_repository
   *   The redirect entity repository.
   * @param \Drupal\domain\DomainNegotiator|null $domain_negotiator
   *   Domain negotiator.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\redirect_404\RedirectNotFoundStorageInterface|null $redirect_not_found_storage
   *   A redirect storage.
   * @param \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer $subRequestBuffer
   *   The sub-request buffer service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    PathValidatorInterface $pathValidator,
    LanguageNegotiatorInterface $languageNegotiator,
    LanguageManagerInterface $languageManager,
    $redirectRepository,
    InboundPathProcessorInterface $pathProcessor,
    $domain_path_redirect_repository,
    $domain_negotiator,
    EntityTypeManagerInterface $entity_type_manager,
    $redirect_not_found_storage,
    ConfigFactoryInterface $config_factory,
    SubRequestBuffer $subRequestBuffer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->redirectRepository = $redirectRepository;
    $this->pathProcessor = $pathProcessor;
    $this->pathValidator = $pathValidator;
    $this->languageNegotiator = $languageNegotiator;
    $this->languageManager = $languageManager;
    $this->domainPathRedirectRepository = $domain_path_redirect_repository;
    $this->domainNegotiator = $domain_negotiator;
    $this->entityTypeManger = $entity_type_manager;
    $this->redirectNotFoundStorage = $redirect_not_found_storage;
    $this->configFactory = $config_factory;
    $this->subRequestBuffer = $subRequestBuffer;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, FieldContext $field) {
    // @TODO: This entire method needs to be refactored and made more
    // configurable/extenable by users of the module.
    $currentLanguage = $this->languageManager->getCurrentLanguage()->getId();
    $field->setContextValue('language', $currentLanguage);
    $request = Request::create($value);
    $path = $this->pathProcessor->processInbound($value, $request);

    if ($this->domainPathRedirectRepository) {
      $domain = $this->domainNegotiator ? $this->domainNegotiator->getActiveId() : NULL;
      // Check for domain_path_redirect.
      $domain_redirect_path = trim($value, '/');
      if ($redirect = $this->domainPathRedirectRepository->findMatchingRedirect($domain_redirect_path, $domain, [], 'und')) {
        return $redirect;
      }
    }

    if ($this->redirectRepository) {
      // Check for regular redirects.
      if ($redirect = $this->redirectRepository->findMatchingRedirect($path, [], $currentLanguage)) {
        return $redirect;
      }
    }

    // Get the URL if valid.
    $url = $this->pathValidator->getUrlIfValidWithoutAccessCheck($value);
    if ($url) {
      $resolve = $this->subRequestBuffer->add($url, function (Url $url) use ($value, $request, $currentLanguage, $path, $field) {
        $negotiatedLangcode = $currentLanguage;

        // Fix for #3373726: Setups without URL based language negotiation.
        if ($this->languageNegotiator->isNegotiationMethodEnabled('language-url')) {
          // Determine the language from the provided url string.
          $negotiator = $this->languageNegotiator->getNegotiationMethodInstance('language-url');
          // @TODO: Also related to #3373726, current user was missing on
          // the negotiator, but that seems wrong. Needs further investigation.
          $currentUser = \Drupal::currentUser();
          $negotiator->setCurrentUser($currentUser);
          $negotiatedUrlLangcode = $negotiator->getLangcode($request);
          if ($negotiatedUrlLangcode) {
            $negotiatedLangcode = $negotiatedUrlLangcode;
          }
        }

        // Check URL access.
        $target_url = $url->toString(TRUE)->getGeneratedUrl();

        // if language detection is domain based, remove domain from $target_url
        $lang_n_config = $this->configFactory->get('language.negotiation');
        if ($lang_n_config->get('url.source') == LanguageNegotiationUrl::CONFIG_DOMAIN) {
          $lang_domain = $lang_n_config->get('url.domains.' . $negotiatedLangcode);
          $target_url = str_replace(['http://', 'https://'], '', $target_url);
          $target_url = str_replace($lang_domain, '', $target_url);
        }

        // Check if the URL has an alias and should be redirected.
        if ($value !== $target_url && $this->entityTypeManger->hasDefinition('redirect')) {
          $redirectStorage = $this->entityTypeManger->getStorage('redirect');
          /** @var \Drupal\redirect\Entity\Redirect $redirect */
          $redirect = $redirectStorage->create();
          $redirect->setRedirect($target_url);
          $redirect->setSource($path);
          $redirect->setLanguage($negotiatedLangcode);
          $redirect->setStatusCode(301);
          return $redirect;
        }

        $access = $url->access(NULL, TRUE);
        $field->addCacheableDependency($access);

        if ($access->isAllowed()) {
          $negotiatedLanguage = $this->languageManager->getLanguage($negotiatedLangcode);
          $url->setOption('language', $negotiatedLanguage);
          $field->setContextValue('language', $negotiatedLangcode);
          return $url;
        }
      });

      // Executes the callback from our subrequest.
      $result = $resolve() ?? NULL;
      if ($result) {
        return $result;
      }
      return $url;
    }

    $field->addCacheTags(['4xx-response']);
    if ($this->redirectNotFoundStorage) {
      $this->redirectNotFoundStorage->logRequest($path, $currentLanguage);
    }
    return NULL;
  }

}
