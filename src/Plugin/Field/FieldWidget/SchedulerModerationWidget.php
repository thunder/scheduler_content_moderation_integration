<?php

namespace Drupal\scheduler_content_moderation_integration\Plugin\Field\FieldWidget;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'scheduler moderation' widget.
 *
 * @FieldWidget(
 *   id = "scheduler_moderation",
 *   label = @Translation("Scheduler Moderation"),
 *   description = @Translation("Select list for choosing a state. Defined by Scheduler Content Moderation Integration module."),
 *   field_types = {
 *     "list_string",
 *   }
 * )
 */
class SchedulerModerationWidget extends OptionsSelectWidget implements ContainerFactoryPluginInterface {

  /**
   * The moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInformation;

  /**
   * The moderated entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ModerationInformationInterface $moderation_information) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->moderationInformation = $moderation_information;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['third_party_settings'], $container->get('content_moderation.moderation_information'));
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    if ($form_state->getFormObject() instanceof ContentEntityForm) {
      $this->entity = $form_state->getFormObject()->getEntity();
      if (!$this->moderationInformation->isModeratedEntity($this->entity)) {
        $element['#access'] = FALSE;
      }
    }

    // When the user is not allowed schedule nodes pass-thru selected values.
    if (!\Drupal::currentUser()->hasPermission('schedule publishing of nodes')) {
      // Prevent losing values when the user does not have access to content
      // moderation states selected by another user. In this case getOptions()
      // does not return states and passing selected states will fail
      // validation.
      $selected_options = [];
      foreach ($items as $item) {
        $selected_options[] = $item->{$this->column};
      }
      $element['#type'] = 'hidden';
      $element['#default_value'] = $selected_options;
      $element['#element_validate'] = [];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    if ($this->entity && $this->moderationInformation->isModeratedEntity($this->entity)) {
      return '';
    }
    return parent::getEmptyLabel();
  }

  /**
   * {@inheritdoc}
   */
  public static function validateElement(array $element, FormStateInterface $form_state) {
    if (is_array($element['#value'])) {
      $value = current($element['#value']);
    }
    else {
      $value = $element['#value'];
    }
    $form_state->setValueForElement($element, [
      $element['#key_column'] => $value,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if ($field_definition instanceof BaseFieldDefinition && $field_definition->getProvider() === 'scheduler_content_moderation_integration') {
      return TRUE;
    }
  }

}
