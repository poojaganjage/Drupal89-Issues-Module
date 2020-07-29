<?php

namespace Drupal\package_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\DescriptionAwareFileFormatterBase;

/**
 * Plugin implementation of the 'custom package' field formatter.
 *
 * @FieldFormatter(
 *   id = "package_file_formatter",
 *   label = @Translation("Package File Field Formatter"),
 *   field_types = {
 *     "package_field"
 *   }
 * )
 */
class PackageFileHTMLFormatter extends DescriptionAwareFileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $base_uri = 'public://custom_html_packages/';
    $elements = [];
    $package_helper = \Drupal::service('package_field.package_helper');
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $archived_folder = $package_helper->buildDestination($file, $base_uri) . '/' . pathinfo($file->getFileName())['filename'];
      $source = $archived_folder . '/index.html';
      $elements[$delta] = [
        '#markup' => file_get_contents($source),
        '#cache' => [
          'tags' => $file->getCacheTags(),
        ],
        '#attached' => [
          'library' => [
            'package_field/package_field-' . $file->id()
          ]
        ]
      ];
    }

    return $elements;
  }

}
