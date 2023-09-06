<?php

namespace Drupal\graphql_telephone\Plugin\GraphQL\DataProducer;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use libphonenumber\PhoneNumberUtil;

/**
 * Parse a phone number from a string and return a PhoneNumber instance.
 *
 * @DataProducer(
 *   id = "phone_parser",
 *   name = @Translation("Phone Parser"),
 *   description = @Translation("Parse a telephone number."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Parsed PhoneNumber instance from libphonenumber.")
 *   ),
 *   consumes = {
 *     "value" = @ContextDefinition("string",
 *       label = @Translation("The telephone number as a string."),
 *     ),
 *     "region" = @ContextDefinition("string",
 *       label = @Translation("The default region to use."),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class PhoneParser extends DataProducerPluginBase {

  /**
   * The resolver.
   *
   * @param string $value
   *   The telephone number.
   * @param string|null $region
   *   The default region to use.
   *
   * @return \libphonenumber\PhoneNumber|null
   *   The parsed phone number.
   */
  public function resolve(string $value, string $region = NULL) {
    $formatter = PhoneNumberUtil::getInstance();

    // Try to parse the phone number.
    try {
      return $formatter->parse($value, $region);
    }
    catch (\Exception $e) {
    }

    return NULL;
  }

}
