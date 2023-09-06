<?php

namespace Drupal\graphql_telephone\Plugin\GraphQL\DataProducer;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Format a phone number.
 *
 * @DataProducer(
 *   id = "phone_formatter",
 *   name = @Translation("Phone Formatter"),
 *   description = @Translation("Format a telephone number."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Formatted phone number")
 *   ),
 *   consumes = {
 *     "phoneNumber" = @ContextDefinition("any",
 *       label = @Translation("The parsed PhoneNumber instance."),
 *     ),
 *     "format" = @ContextDefinition("string",
 *       label = @Translation("The telephone format enum."),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class PhoneFormatter extends DataProducerPluginBase {

  /**
   * The resolver.
   *
   * @param \libphonenumber\PhoneNumber $phoneNumber
   *   The PhoneNumber instance.
   * @param string|null $formatEnum
   *   The phone format enum.
   *
   * @return string|null
   *   The formatted phone number.
   */
  public function resolve(PhoneNumber $phoneNumber, string $formatEnum = NULL) {
    $formatter = PhoneNumberUtil::getInstance();

    try {
      $format = $this->getFormat($formatEnum);
      return $formatter->format($phoneNumber, $format);
    }
    catch (\Exception $e) {
    }

    return NULL;
  }

  /**
   * Return the correct phone number format.
   *
   * @param string|null $format
   *   The name of format from the Enum.
   *
   * @return int
   *   The PhoneNumberFormat.
   */
  private function getFormat(string $format = NULL): int {
    if ($format === 'E164') {
      return PhoneNumberFormat::E164;
    }
    elseif ($format === 'NATIONAL') {
      return PhoneNumberFormat::NATIONAL;
    }
    elseif ($format === 'RFC3966') {
      return PhoneNumberFormat::RFC3966;
    }

    return PhoneNumberFormat::INTERNATIONAL;
  }

}
