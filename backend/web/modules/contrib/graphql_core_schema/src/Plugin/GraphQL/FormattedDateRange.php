<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\Plugin\Field\FieldType\TimestampItem;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\Plugin\DataType\Timestamp;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Teh data producer for formatted dates..
 *
 * @DataProducer(
 *   id = "formatted_date_range",
 *   name = @Translation("Formatted Date Range"),
 *   description = @Translation("Return a formatted date."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Formatted Date")
 *   ),
 *   consumes = {
 *     "timestamp" = @ContextDefinition("any",
 *       label = @Translation("Date as timestamp"),
 *     ),
 *     "format" = @ContextDefinition("string",
 *       label = @Translation("Format"),
 *       required = FALSE
 *     ),
 *     "drupalDateFormat" = @ContextDefinition("string",
 *       label = @Translation("Drupal date format"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class FormattedDateRange extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

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
      $container->get('date.formatter')
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
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    DateFormatterInterface $dateFormatter
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * The resolver.
   *
   * @param int $timestamp
   *   The timestamp.
   * @param string $format
   *   The needed format.
   * @param string $dateFormat
   *   The dateformat.
   *
   * @return string|null
   *   The formatted date string.
   */
  public function resolve($timestamp, $format, $dateFormat) {
    $dateTime = $this->getDateTime($timestamp);
    if (!$dateTime) {
      return NULL;
    }
    if ($dateFormat) {
      return $this->dateFormatter->format($dateTime->getTimestamp(), strtolower($dateFormat));
    }
    elseif ($format) {
      return $dateTime->format($format);
    }

    return (string) $dateTime->getTimestamp();
  }

  /**
   * Get a Drupal DateTime object.
   *
   * @param string|int|\Drupal\Core\Datetime\DrupalDateTime|null $value
   *   The date input in various formats.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   The DrupalDateTime object.
   */
  private function getDateTime($value) {
    if ($value instanceof DrupalDateTime) {
      return $value;
    }
    elseif ($value instanceof TimestampItem) {
      $timestampValue = $value->get('value');
      if ($timestampValue instanceof Timestamp) {
        return $timestampValue->getDateTime();
      }
    }
    elseif ($value instanceof DateTimeItem) {
      /** @var \Drupal\Core\TypedData\Plugin\DataType\DateTimeIso8601 $date */
      $date = $value->get('value');
      $date_time = $date->getDateTime();
      return $date_time;
    }
    if (is_string($value) || is_int($value)) {
      $timestamp = (string) $value;
      return DrupalDateTime::createFromTimestamp($timestamp);
    }

    return NULL;
  }

}
