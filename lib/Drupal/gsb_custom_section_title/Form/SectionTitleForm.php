<?php

/**
 * @file
 * Contains \Drupal\gsb_custom_section_title\Form\SectionTitleForm.
 */

namespace Drupal\gsb_custom_section_title\Form;

use Drupal\Core\Form\FormBase;

/**
 * @todo.
 */
class SectionTitleForm extends FormBase {

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a new SectionTitleForm.
   */
  public function __construct() {
    $this->config = $this->config('gsb_custom_section_title.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gsb_custom_section_title_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, $section_id = NULL) {
    $form['#tree'] = TRUE;
    $is_new = $section_id === '_new';
    $section = $this->getSection($section_id);
    $form['sections'] = array(
      '#title' => $is_new ? $this->t('Add new section title') : $this->t('Edit section title'),
      '#type' => 'fieldset',
      '#attributes' => array(
        'id' => 'gsb-custom-section-title-fieldset',
      ),
      '_new' => $this->getRowForm($section_id, $section),
    );
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $is_new ? $this->t('Save section title') : $this->t('Update section title'),
    );
    return $form;
  }

  /**
   * Gets the custom section title for a given key.
   *
   * @param int|string $section_id
   *   The specific section.
   *
   * @return array
   *   An array representing the custom section title.
   */
  protected function getSection($section_id) {
    $sections = $this->config->get('sections');
    if (!isset($sections[$section_id])) {
      $sections[$section_id] = array(
        'title' => '',
        'link' => FALSE,
        'link_path' => '',
        'paths' => '',
      );
    }
    return $sections[$section_id];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $sections = $this->config->get('sections');
    $new_section = $form_state['values']['sections']['_new'];
    $count = count($sections);
    // If this is a new section, make the ID the last key.
    if ($new_section['id'] === '') {
      $new_section['id'] = $count;
    }
    // If this section already exists, overwrite the old section.
    if ($new_section['id'] < $count) {
      $sections[$new_section['id']] = $new_section;
      drupal_set_message($this->t('A section title named %title has been added.', array('%title' => $new_section['title'])));
    }
    // Otherwise, append it to the end.
    else {
      $sections[] = $new_section;
      drupal_set_message($this->t('The section title named %title has been updated.', array('%title' => $new_section['title'])));
    }
    $sections = array_values($sections);
    foreach ($sections as $key => &$section) {
      $section['id'] = $key;
    }
    $this->config->set('sections', $sections)->save();
  }

  /**
   * Generates the section title form item for a single row.
   *
   * @param string|int $row
   *   The unique identifier for this row. Either an integer or '_new'.
   * @param array $data
   *   The data for this row.
   *
   * @return array
   *   The form item for a single section title row.
   */
  protected function getRowForm($row, array $data) {
    $form['id'] = array(
      '#type' => 'hidden',
    );
    $form['title'] = array(
      '#title' => $this->t('Title'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $data['title'],
    );
    $form['link'] = array(
      '#title' => $this->t('Link?'),
      '#type' => 'checkbox',
      '#default_value' => $data['link'],
    );
    $form['link_path'] = array(
      '#title' => $this->t('Link path'),
      '#type' => 'textfield',
      '#field_prefix' => url(NULL, array('absolute' => TRUE)),
      '#default_value' => $data['link_path'],
      '#states' => array(
        'visible' => array(
          ':input[name="sections[' . $row . '][link]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $form['paths'] = array(
      '#title' => $this->t('Paths'),
      '#type' => 'textarea',
      '#default_value' => $data['paths'],
      '#rows' => 4,
      '#required' => TRUE,
    );
    $form['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#route_name' => 'gsb_custom_section_title.list',
      '#access' => $row !== '_new',
    );
    return $form;
  }

}
