<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer;

use Drupal\graphql_core_schema\Plugin\GraphQL\DateProducerBase;

/**
 * The data producer for formatted dates.
 *
 * @DataProducer(
 *   id = "formatted_date",
 *   name = @Translation("Formatted Date"),
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
class FormattedDate extends DateProducerBase {

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

}
