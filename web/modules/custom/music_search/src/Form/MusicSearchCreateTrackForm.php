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
        '#return_value' => $link->url,
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
    $selected_link_url = $values['links_table']['select'] ?? NULL;

    $youtube_media = NULL;
    if (!empty($selected_link_url)) {
      $youtube_media = $this->createOrLoadMedia($selected_link_url, $title);
    }

    $length = $values['field_lengd'] ?? '';
    $spotify_id = $values['field_spotify_id'] ?? '';
    $discogs_id = $values['field_discogs_id'] ?? '';

    $node = Node::create([
      'type' => 'lag',
      'title' => $title,
      'field_lengd' => $length,
      'field_spotify_id' => $spotify_id,
      'field_discogs_id' => $discogs_id,
      'field_youtube_link' => $youtube_media ? [['target_id' => $youtube_media->id()]] : [],
    ]);

    $node->save();

    $form_state->setRedirect(
      'entity.node.canonical',
      ['node' => $node->id()]
    );

    $this->messenger()->addMessage($this->t('The track %name has been created.', ['%name' => $title]));
  }

  /**
   * Helper function to create or load a media entity for a YouTube link.
   */
  protected function createOrLoadMedia(string $url, string $title) {
    $media_storage = \Drupal::entityTypeManager()->getStorage('media');
    $existing_media = $media_storage->loadByProperties(['field_media_oembed_video' => $url]);

    if (!empty($existing_media)) {
      return reset($existing_media);
    }

    $media = $media_storage->create([
      'bundle' => 'remote_video',
      'name' => $title,
      'uid' => \Drupal::currentUser()->id(),
      'status' => 1,
      'field_media_oembed_video' => $url,
    ]);
    $media->save();

    return $media;
  }



}
