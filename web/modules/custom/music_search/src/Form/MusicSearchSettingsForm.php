<?php

namespace Drupal\music_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\spotify_lookup\Service\SpotifyLookupService;

/**
 * Configuration form for Music Search module
 */

class MusicSearchSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['music_search.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'music_search_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('music_search.settings');

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#default_value' => $config->get('search'),
      '#description' => $this->t('Enter your Spotify API Key.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('music_search.settings')
      ->set('spotify_api_key', $form_state->getValue('spotify_api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
