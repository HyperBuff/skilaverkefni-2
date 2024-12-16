<?php

namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\discogs_lookup\Service\DiscogsLookupService;
use Drupal\node\Entity\Node;
use Drupal\spotify_lookup\Service\SpotifyLookupService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\music_search\MusicSearchTrackData;
use Drupal\music_search\MusicSearchHelper;

class MusicSearchCreateTrackForm extends FormBase {

  protected $spotifyLookupService;
  protected $discogsLookupService;
  protected $helper;
  /**
   * Constructor.
   */
  public function __construct(SpotifyLookupService $spotifyLookupService, DiscogsLookupService $discogsLookupService) {
    $this->spotifyLookupService = $spotifyLookupService;
    $this->discogsLookupService = $discogsLookupService;
    $this->helper = new MusicSearchHelper();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('spotify.lookup.service'),
      $container->get('discogs.lookup.service'),
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'music_search_create_album_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $spotify_id = \Drupal::routeMatch()->getParameter('spotify_id');
    $discogs_id = \Drupal::routeMatch()->getParameter('discogs_id');
    $node_id = \Drupal::routeMatch()->getParameter('node_id');


    $spotify_results = $this->spotifyLookupService->getTrackById($spotify_id);
    $discogs_results = $this->discogsLookupService->getMainReleaseById($discogs_id);

    $track = new MusicSearchTrackData($spotify_results, $discogs_results);


//    \Drupal::messenger()->addMessage($this->t('images: @result', [
//      '@result' => json_encode($artist->get_images(), JSON_PRETTY_PRINT),
//    ]));

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Track Title'),
      '#default_value' => $track->name,
    ];


    $header = [
      $this->t('Select'),
      $this->t('Title'),
      $this->t('Duration'),
      $this->t('Type'),
    ];

    $form['links_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => $this->t('No links found'),
    ];

    foreach ($track->getLinks() as $key => $link) {
      $form['links_table'][$key]['select'] = [
        '#type' => 'radio',
        '#name' => 'links_table[select]',
        '#return_value' => json_encode([
          'url' => $link->url,
          'duration' => $link->duration,
        ]),
      ];

      $form['links_table'][$key]['title'] = [
        '#markup' => $link->title,
      ];

      $form['links_table'][$key]['duration'] = [
        '#markup' => $link->duration,
      ];

      $form['links_table'][$key]['type'] = [
        '#markup' => ucfirst($link->type),
      ];
    }


    $form['field_genre'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Genres'),
      '#options' => [],
    ];
    $genres = $track->get_genres();
    if (!empty($genres)) {
      foreach ($genres as $genre) {
        $form['field_genre']['#options'][$genre->name] = $genre->name;
      }
    }


    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Track'),
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $spotify_id = \Drupal::routeMatch()->getParameter('spotify_id');
    $discogs_id = \Drupal::routeMatch()->getParameter('discogs_id');
    $values = $form_state->getValues();

    $title = $values['title'];


    $selected_value = $values['links_table']['select'] ?? NULL;
    if ($selected_value) {
      $decoded_value = json_decode($selected_value, TRUE);
      $url = $decoded_value['url'] ?? '';
      $duration = $decoded_value['duration'] ?? '';

      $youtube_media = NULL;
      if (!empty($url)) {
        $youtube_media = $this->helper->create_or_load_media($url, $title);
      }

      $selected_genres = $values['field_genre'] ?? [];
      $genre_references = $this->helper->create_or_find_genres($selected_genres);


      $node = Node::create([
        'type' => 'lag',
        'title' => $title,
        'field_lengd' => $duration,
        'field_spotify_id' => $spotify_id,
        'field_discogs_id' => $discogs_id,
        'field_youtube_link' => $youtube_media ? [['target_id' => $youtube_media->id()]] : [],
        'field_genres' => $genre_references,
      ]);

      $node->save();

      $form_state->setRedirect(
        'entity.node.canonical',
        ['node' => $node->id()]
      );


    }



    $this->messenger()->addMessage($this->t('The track %name has been created.', ['%name' => $title]));
  }





}
