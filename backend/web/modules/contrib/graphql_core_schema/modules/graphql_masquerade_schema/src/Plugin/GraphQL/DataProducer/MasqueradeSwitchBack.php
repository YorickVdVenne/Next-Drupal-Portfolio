<?php

declare(strict_types = 1);

namespace Drupal\graphql_masquerade_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\masquerade\Masquerade;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Switch back to the previous user.
 *
 * @DataProducer(
 *   id = "masquerade_switch_back",
 *   name = @Translation("Masquerade Switch Back"),
 *   description = @Translation("Switch back to the previous user."),
 *   produces = @ContextDefinition("boolean",
 *     label = @Translation("TRUE if successfully switched back.")
 *   )
 * )
 */
class MasqueradeSwitchBack extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The masquerade service.
   *
   * @var \Drupal\masquerade\Masquerade
   */
  protected Masquerade $masquerade;

  /**
   * Construct a new MasqueradeContext plugin.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param array $plugin_definition
   *   Plugin definition.
   * @param \Drupal\masquerade\Masquerade $masquerade
   *   The masquerade service.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    Masquerade $masquerade
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->masquerade = $masquerade;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('masquerade')
    );

  }

  /**
   * The resolver.
   *
   * @return bool
   *   The result.
   */
  public function resolve() {
    return $this->masquerade->switchBack();
  }

}
