<?php

namespace Drupal\graphql_form_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormValidatorInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Return the form result.
 *
 * @DataProducer(
 *   id = "form_result",
 *   name = @Translation("Form result."),
 *   description = @Translation("Build an array of form elements."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("The form result.")
 *   ),
 *   consumes = {
 *     "form" = @ContextDefinition("any",
 *       label = @Translation("The form object."),
 *     ),
 *     "formState" = @ContextDefinition("any",
 *       label = @Translation("The FormState object."),
 *     ),
 *     "input" = @ContextDefinition("any",
 *       label = @Translation("The input object."),
 *       required = FALSE,
 *     ),
 *     "excludeTypes" = @ContextDefinition("boolean",
 *       label = @Translation("Element types to exclude."),
 *       required = FALSE,
 *     ),
 *   }
 * )
 */
class FormResult extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected FormBuilderInterface $formBuilder;

  /**
   * The form validator.
   *
   * @var \Drupal\Core\Form\FormValidator
   */
  protected FormValidatorInterface $formValidator;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected MessengerInterface $messenger;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

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
      $container->get('form_builder'),
      $container->get('form_validator'),
      $container->get('messenger'),
      $container->get('renderer'),
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
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    FormBuilderInterface $formBuilder,
    FormValidatorInterface $formValidator,
    MessengerInterface $messenger,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->formBuilder = $formBuilder;
    $this->formValidator = $formValidator;
    $this->messenger = $messenger;
    $this->renderer = $renderer;
  }

  /**
   * Handles form validation and submitting.
   *
   * Resolves the form elements and errors.
   *
   * @param \Drupal\Core\Form\FormInterface $formObject
   *   The form object.
   * @param \Drupal\Core\Form\FormState $formState
   *   The form state object.
   * @param array $input
   *   The input object for this form.
   * @param array $excludeTypes
   *   Array of form element types to exclude, e.g. "textfield".
   *
   * @return array
   *   The form elements.
   */
  public function resolve(
    FormInterface $formObject,
    FormStateInterface $formState,
    $input,
    $excludeTypes,
    FieldContext $field
  ) {
    $context = new RenderContext();
    $formState->setProgrammedBypassAccessCheck(FALSE);

    $result = $this->renderer->executeInRenderContext($context, function () use ($formObject, $formState, $input, $excludeTypes) {
      $validate = $input['validate'] ?? FALSE;
      $submit = $input['submit'] ?? FALSE;
      $values = $input['values'] ?? [];

      // Build form initially without values.
      $form = $this->formBuilder->buildForm($formObject, $formState);

      // If an input is provided and validation is enabled, set the values on
      // FormState.
      if (!empty($input) && ($validate || $submit)) {
        // This is crucial and makes sure we can validate and submit
        // programatically.
        $formState->setProgrammed();

        // Convert the input array of element items to an array of form values.
        $formValues = $this->buildUserInput($values);

        // Set values.
        $formState->setValues($formValues);

        // Make sure the user input reflects the provided values.
        $formState->setUserInput($formState->getValues());
      }

      // Submit form if requested.
      if ($submit) {
        // SubmitHandlers are normally add in builder process that happens before submission.
        $formState->setSubmitHandlers(['::submitForm', '::save']);
        $this->formBuilder->submitForm($formObject, $formState);
      }

      // Rebuild the form to reflect the provided input values.
      $form = $this->formBuilder->buildForm($formObject, $formState);

      // Collect form elements.
      $elements = [];
      $this->getElements($form, $elements, $excludeTypes);

      // Collect errors belonging to form elements.
      $elementErrors = [];

      foreach ($elements as $key => $element) {
        // Check if this element has an error.
        $elementError = $formState->getError($element);
        if ($elementError) {
          // Get the element error as a string.
          $elementError = (string) $elementError;
          // Custom array key which is used by the field resolver to resolve this
          // error.
          $elements[$key]['#collected_error'] = $elementError;
          $elementErrors[] = $elementError;
        }
      }

      // Get all form errors and convert them to strings.
      $errors = array_map('strval', $formState->getErrors());

      // Collect all errors that don't belong to a form element.
      $remainingErrors = [];
      foreach ($errors as $error) {
        if (!in_array($error, $elementErrors)) {
          $remainingErrors[] = $error;
        }
      }

      // Purge all messages generated during form handling.
      $this->messenger->deleteAll();

      return [
        'items' => $elements,
        // TODO: errors not attached to an element will not be returned in GraphQL,
        // but may prevent the entity from being saved.
        // 'errors' => $remainingErrors,
        'errors' => $errors,
        'isSubmitted' => $formState->isSubmitted(),
        'entity' => $this->getEntity($formObject),
      ];
    });

    $field->addCacheableDependency($context);
    return $result;
  }

  /**
   * Get the entity relating to the form.
   *
   * @param \Drupal\Core\Form\FormInterface $formObject
   *   The form object.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null $entity
   *   The resulting entity.
   */
  private function getEntity(FormInterface $formObject): ?EntityInterface {
    if ($formObject instanceof EntityFormInterface) {
      $entity = $formObject->getEntity();
      if ($entity && !$entity->isNew()) {
        return $entity;
      }
    }

    return NULL;
  }

  /**
   * Convert the incoming array of CommerceFormInput objects to a structured
   * array of form values.
   *
   * @param array $inputElements
   *   The array of CommerceFormInput items.
   *
   * @return array
   *   The resulting form values.
   */
  private function buildUserInput(array $inputElements) {
    // Collect all query values.
    $parts = [];
    foreach ($inputElements as $item) {
      $value = $item['value'] ?? NULL;
      if (is_array($value)) {
        foreach ($value as $itemValue) {
          $parts[] = $item['name'] . '=' . $this->mapUserInputValue($itemValue);
        }
      }
      else {
        $parts[] = $item['name'] . '=' . $this->mapUserInputValue($value);
      }
    }

    // Create the query string.
    $str = implode('&', $parts);

    // Parse the query string to an array.
    $values = [];
    parse_str($str, $values);

    foreach ($values as $key => $value) {
      // Replace empty strings with NULL.
      if ($values[$key] === '') {
        $values[$key] = NULL;
      }
      elseif (isset($values[$key]['value'])) {
        if ($values[$key]['value'] === '') {
          $values[$key]['value'] = NULL;
        }
      }
    }

    return $values;
  }

  /**
   * Encode the value for building the user input.
   *
   * @param mixed $value
   *   The value.
   *
   * @return string|null
   *   The mapped value.
   */
  private function mapUserInputValue($value): ?string {
    if ($value === FALSE || $value === NULL) {
      return $value;
    }
    elseif ($value === TRUE) {
      return '1';
    }

    $result = urlencode($value);
    return $result;
  }

  /**
   * Collect all form elements relevant to data input.
   *
   * @param array $form
   *   The form render array.
   * @param array $elements
   *   Array to collect all elements.
   * @param array $excludeTypes
   *   Array of form element types to exclude.
   */
  protected function getElements(array $form, array &$elements, array $excludeTypes) {
    $children = Element::children($form);
    $ignoreTypes = [
      'container',
      'field_multiple_value_form',
    ];

    foreach ($children as $child) {
      $element = $form[$child];
      $name = $element['#name'] ?? NULL;
      $type = $element['#type'] ?? NULL;
      if ($name && $type !== 'submit' && !in_array($type, $excludeTypes) && !in_array($type, $ignoreTypes)) {
        $elements[] = $element;
      }
      $this->getElements($element, $elements, $excludeTypes);
    }
  }

}
