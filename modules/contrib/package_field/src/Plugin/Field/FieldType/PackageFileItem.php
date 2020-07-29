<?php

namespace Drupal\package_field\Plugin\Field\FieldType;

use Drupal\file\Plugin\Field\FieldType\FileItem;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'custom package' field type.
 *
 * @FieldType(
 *   id = "package_field",
 *   label = @Translation("Package File"),
 *   category = @Translation("Reference"),
 *   description = @Translation("This field stores the ID of an package file as an integer value."),
 *   default_formatter = "package_file_formatter",
 *   default_widget = "file_generic",
 *   serialized_property_names = {
 *     "settings"
 *   },
 *   list_class = "\Drupal\file\Plugin\Field\FieldType\FileFieldItemList",
 *   constraints = {"ReferenceAccess" = {}, "FileValidation" = {}}
 * )
 */
class PackageFileItem extends FileItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = [
      'file_extensions' => 'zip',
      'file_directory' => 'packages/[date:custom:Y]-[date:custom:m]',
    ] + parent::defaultFieldSettings();
    // Remove field option.
    unset($settings['description_field']);

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'target_id' => [
          'description' => 'The ID of the file entity.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
      ],
      'indexes' => [
        'target_id' => ['target_id'],
      ],
      'foreign keys' => [
        'target_id' => [
          'table' => 'file_managed',
          'columns' => ['target_id' => 'fid'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);
    
    // unset the default values from the file module
    unset($properties['display']);
    unset($properties['description']);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = [];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $settings = $this->getSettings();

    $element = parent::fieldSettingsForm($form, $form_state);

    // Make the extension list a little more human-friendly by comma-separation.
    $extensions = str_replace(' ', ', ', $settings['file_extensions']);

    $element['file_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed file extensions'),
      '#default_value' => $extensions,
      '#description' => $this->t(
        'The extensions for this upload is restrincted to zip file types.'
      ),
      '#element_validate' => [[get_class($this), 'validateExtensions']],
      '#weight' => 1,
      '#maxlength' => 256,
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];
    // Remove the description option.
    unset($element['description_field']);

    return $element;
  }

}
