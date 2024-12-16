<?php

namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\spotify_lookup\Service\SpotifyLookupService;
use Drupal\music_search\MusicSearchHelper;

/**
 * Music Search Form
 */
class MusicSearchForm extends FormBase {
  /**
   * MusicSearch Services
   *
   * @var SpotifyLookupService
   *
   */

  protected $spotifyLookupService;
  protected $helper;


  /**
   * Constructor for MusicSearch services
   *
   * @param SpotifyLookupService $spotifyLookupService
   */

  public function __construct(SpotifyLookupService $spotifyLookupService) {
    $this->spotifyLookupService = $spotifyLookupService;
    $this->helper = new MusicSearchHelper();

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
    $type = \Drupal::routeMatch()->getParameter('type');


    if ($type == 'track' || $type == 'album') {
      $form['artist_query'] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('Artist search'),
        '#target_type' => 'node',
        '#selection_handler' => 'default',
        '#selection_settings' => [
          'target_bundles' => ['listamadur'],
        ],
        '#tags' => FALSE,
        '#required' => FALSE,
        '#placeholder' => $this->t('Search artist here...'),

      ];
    }


    $form['query'] = [
      '#type' => 'search',
      '#title' => $this->t('Search for ' . $type),
      '#autocomplete_route_name' => 'music_search.auto_complete_search',
      '#autocomplete_route_parameters' => [
        'type' => $type,
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
    $type = \Drupal::routeMatch()->getParameter('type');
    $query = $form_state->getValue('query');

    if ($type == 'album' || $type == 'track') {
      $node_id = $form_state->getValue('artist_query');
      \Drupal::messenger()->addMessage("node_id: " . $node_id);

      $artist_name = Node::load($node_id)->label();
      if ($node_id) {
        if ($type == 'album') {
          $query =  'album_name=' .$query . '?artist_name=' . $artist_name . '?node_id=' . $node_id;
        }
        if ($type == 'track') {
          $query =  'track_name=' .$query . '?artist_name=' . $artist_name . '?node_id=' . $node_id;
        }


      }
    }

    $form_state->setRedirect(
      'music_search.results',
      [
        'query' => $query,
        'type' => $type,
      ]
    );
  }






}
