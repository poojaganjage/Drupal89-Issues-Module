<?php

namespace Drupal\layoutbuilder\Field;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layoutbuilder\Section;
use Drupal\layoutbuilder\SectionListInterface;
use Drupal\layoutbuilder\SectionStorage\SectionStorageTrait;

/**
 * Defines a item list class for layout section fields.
 *
 * @internal
 *   Plugin classes are internal.
 *
 * @see \Drupal\layoutbuilder\Plugin\Field\FieldType\LayoutSectionItem
 */
class LayoutSectionItemList extends FieldItemList implements SectionListInterface {

  use SectionStorageTrait;

  /**
   * Numerically indexed array of field items.
   *
   * @var \Drupal\layoutbuilder\Plugin\Field\FieldType\LayoutSectionItem[]
   */
  protected $list = [];

  /**
   * {@inheritdoc}
   */
  public function getSections() {
    $sections = [];
    foreach ($this->list as $delta => $item) {
      $sections[$delta] = $item->section;
    }
    return $sections;
  }

  /**
   * {@inheritdoc}
   */
  protected function setSections(array $sections) {
    $this->list = [];
    $sections = array_values($sections);
    /** @var \Drupal\layoutbuilder\Plugin\Field\FieldType\LayoutSectionItem $item */
    foreach ($sections as $section) {
      $item = $this->appendItem();
      $item->section = $section;
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    $entity = parent::getEntity();

    // Ensure the entity is updated with the latest value.
    $entity->set($this->getName(), $this->getValue());
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    parent::preSave();
    // Loop through each section and reconstruct it to ensure that all default
    // values are present.
    foreach ($this->list as $delta => $item) {
      $item->section = Section::fromArray($item->section->toArray());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function equals(FieldItemListInterface $list_to_compare) {
    if (!$list_to_compare instanceof LayoutSectionItemList) {
      return FALSE;
    }

    // Convert arrays of section objects to array values for comparison.
    $convert = function (LayoutSectionItemList $list) {
      return array_map(function (Section $section) {
        return $section->toArray();
      }, $list->getSections());
    };
    return $convert($this) === $convert($list_to_compare);
  }

  /**
   * Overrides \Drupal\Core\Field\FieldItemListInterface::defaultAccess().
   *
   * @ingroup layoutbuilder_access
   */
  public function defaultAccess($operation = 'view', AccountInterface $account = NULL) {
    // @todo Allow access in https://www.drupal.org/node/2942975.
    return AccessResult::forbidden();
  }

}
