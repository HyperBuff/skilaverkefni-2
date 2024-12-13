<?php

namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Music Search Create Artist Form
 */
class MusicSearchArtistForm extends FormBase {


  /**
   *  {@inheritdoc}
   */

  public function getFormId() {
    return 'music_search_create_artist_form';
  }

  /**
   *  {@inheritdoc}
   */

  public function buildForm(array $form, FormStateInterface $form_state) {


    $form['query'] = [
      '#type' => 'search',
      '#title' => $this->t('Search'),
      '#autocomplete_route_name' => 'music_search.auto_complete_search',
      '#autocomplete_route_parameters' => [
        'type' => 'artist',
      ],
      '#size' => 5,
      '#placeholder' => $this->t('Search here...'),
    ];



    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = $form_state->getValue('query');

    // Redirect to results page with query and type as parameters.
    $form_state->setRedirect(
      'music_search.results',
      [
        'query' => $query,
        'type' => 'artist',
      ]
    );
  }






}
