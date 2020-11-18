<?php

namespace Drupal\layoutbuilder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\layoutbuilder\LayoutBuilderHighlightTrait;
use Drupal\layoutbuilder\SectionComponent;
use Drupal\layoutbuilder\SectionStorageInterface;

/**
 * Provides a form to add a block.
 *
 * @internal
 *   Form classes are internal.
 */
class AddBlockForm extends ConfigureBlockFormBase {

  use LayoutBuilderHighlightTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layoutbuilder_add_block';
  }

  /**
   * {@inheritdoc}
   */
  protected function submitLabel() {
    return $this->t('Add block');
  }

  /**
   * Builds the form for the block.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\layoutbuilder\SectionStorageInterface $section_storage
   *   The section storage being configured.
   * @param int $delta
   *   The delta of the section.
   * @param string $region
   *   The region of the block.
   * @param string|null $plugin_id
   *   The plugin ID of the block to add.
   *
   * @return array
   *   The form array.
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $region = NULL, $plugin_id = NULL) {
    // Only generate a new component once per form submission.
    if (!$component = $form_state->get('layoutbuilder__component')) {
      $component = new SectionComponent($this->uuidGenerator->generate(), $region, ['id' => $plugin_id]);
      $section_storage->getSection($delta)->appendComponent($component);
      $form_state->set('layoutbuilder__component', $component);
    }
    $form['#attributes']['data-layout-builder-target-highlight-id'] = $this->blockAddHighlightId($delta, $region);
    return $this->doBuildForm($form, $form_state, $section_storage, $delta, $component);
  }

}
