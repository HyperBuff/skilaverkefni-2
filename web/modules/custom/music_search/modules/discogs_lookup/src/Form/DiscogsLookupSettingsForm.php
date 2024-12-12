<?php

namespace Drupal\discogs_lookup\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Discogs Lookup form
 */

class DiscogsLookupSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['discogs_lookup.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'discogs_lookup_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('discogs_lookup.settings');

    $form['discogs_api_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Discogs API token'),
      '#description' => $this->t('Enter your Discogs API token.'),
      '#default_value' => $config->get('discogs_api_token'),

    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('discogs_lookup.settings')
      ->set('discogs_api_token', $form_state->getValue('discogs_api_token'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
