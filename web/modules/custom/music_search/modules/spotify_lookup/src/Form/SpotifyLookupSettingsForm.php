<?php

namespace Drupal\spotify_lookup\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Spotify Lookup form
 */

class SpotifyLookupSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['spotify_lookup.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'spotify_lookup_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('spotify_lookup.settings');

    $form['spotify_api_client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify API Client ID'),
      '#description' => $this->t('Enter your Spotify API Client ID.'),
      '#default_value' => $config->get('spotify_api_client_id'),

    ];

    $form['spotify_api_client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify API Client Secret'),
      '#description' => $this->t('Enter your Spotify API Client Secret.'),
      '#default_value' => $config->get('spotify_api_client_secret'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('spotify_lookup.settings')
      ->set('spotify_api_client_id', $form_state->getValue('spotify_api_client_id'))
      ->set('spotify_api_client_secret', $form_state->getValue('spotify_api_client_secret'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
