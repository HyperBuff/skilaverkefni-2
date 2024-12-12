<?php

namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\spotify_lookup\Service\SpotifyLookupService;

/**
 * Music Search Form
 */
class MusicSearchCreateAlbumForm extends FormBase {
  /**
   * MusicSearch Services
   *
   * @var SpotifyLookupService
   *
   */

  protected $spotifyLookupService;


  /**
   * Constructor for MusicSearch services
   *
   * @param SpotifyLookupService $spotifyLookupService
   */

  public function __construct(SpotifyLookupService $spotifyLookupService) {
    $this->spotifyLookupService = $spotifyLookupService;
  }

  /**
   *  {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('spotify_lookup.service')
    );
  }

  /**
   *  {@inheritdoc}
   */

  public function getFormId() {
    return 'music_search_form';
  }

  /**
   *  {@inheritdoc}
   */

  public function buildForm(array $form, FormStateInterface $form_state) {


    $form['query'] = [
      '#type' => 'search',
      '#title' => $this->t('Search'),
      '#autocomplete_route_name' => 'music_search.auto_complete_search',
      '#size' => 30,
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
      ]
    );
  }






}
