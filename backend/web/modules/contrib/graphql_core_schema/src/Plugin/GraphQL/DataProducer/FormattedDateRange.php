<?php

namespace Drupal\graphql_core_schema\Plugin\GraphQL\DataProducer;

use Drupal\graphql_core_schema\Plugin\GraphQL\DateProducerBase;

/**
 * The data producer for formatted date ranges.
 *
 * @DataProducer(
 *   id = "formatted_date_range",
 *   name = @Translation("Formatted Date Range"),
 *   description = @Translation("Return a formatted date range."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Formatted Date Range")
 *   ),
 *   consumes = {
 *     "start" = @ContextDefinition("any",
 *       label = @Translation("Start date as timestamp"),
 *     ),
 *     "end" = @ContextDefinition("any",
 *       label = @Translation("End date as timestamp"),
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
class FormattedDateRange extends DateProducerBase {

  /**
   * The resolver.
   *
   * @param mixed $start
   *   The start date.
   * @param mixed $end
   *   The end date.
   * @param string $format
   *   The needed format.
   * @param string $dateFormat
   *   The dateformat.
   *
   * @return string|null
   *   The formatted date string.
   */
  public function resolve($start, $end, $format, $dateFormat) {
    $startTime = $this->getDateTime($start);
    $endTime = $this->getDateTime($end);

    if (!$startTime || !$endTime) {
      return NULL;
    }

    $dates = array_map(function ($date) use ($format, $dateFormat) {
      if ($dateFormat) {
        return $this->dateFormatter->format($date->getTimestamp(), strtolower($dateFormat));
      }
      elseif ($format) {
        return $date->format($format);
      }
      return (string) $date->getTimestamp();
    }, [$startTime, $endTime]);

    return implode(' - ', $dates);
  }

}
