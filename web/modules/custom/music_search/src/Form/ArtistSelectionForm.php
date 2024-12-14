<?php

namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\discogs_lookup\Service\DiscogsLookupService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\spotify_lookup\Service\SpotifyLookupService;
use Drupal\spotify_lookup\SpotifyData;

class ArtistSelectionForm extends FormBase implements ContainerInjectionInterface
{

  protected $spotifyLookupService;
  protected $discogsLookupService;

  /**
   * Constructor.
   */
  public function __construct(SpotifyLookupService $spotifyLookupService, DiscogsLookupService $discogsLookupService)
  {
    $this->spotifyLookupService = $spotifyLookupService;
    $this->discogsLookupService = $discogsLookupService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('spotify.lookup.service'),
      $container->get('discogs.lookup.service'),
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'artist_selection_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $type = \Drupal::routeMatch()->getParameter('type');
    $query = \Drupal::routeMatch()->getParameter('query');

    $form['spotify_header'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Spotify Results'),
      '#prefix' => '<h2>',
      '#suffix' => '</h2>',
    ];

    $form['spotify_table'] = $this->createSpotifyTable($query, $type);

    $form['discogs_header'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Discogs Results'),
      '#prefix' => '<h2>',
      '#suffix' => '</h2>',
    ];
    $form['discogs_table'] = $this->createDiscogsTable($query, $type);

    // Submission button.
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
      ],
    ];

    return $form;
  }

  /**
   * Create Spotify Table.
   */
  private function createSpotifyTable($query, $type)
  {
    $results = $this->spotifyLookupService->SpotifySearch($query, $type);
    $api_type = '';
    if ($type == 'artist') {
      $api_type = 'artists';
    } elseif ($type == 'album') {
      $api_type = 'albums';
    } elseif ($type == 'track') {
      $api_type = 'tracks';
    }


    $header = [
      $this->t('Select'),
      $this->t('Image'),
      $this->t('Name'),
      $this->t('Type'),
      $this->t('Spotify ID'),
    ];

    $table = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => $this->t('No results found for Spotify.'),
    ];

    if (!empty($results[$api_type]['items'])) {
      foreach ($results[$api_type]['items'] as $key => $item) {
        if ($type == 'track') {
          $image_url = !empty($item['album']['images'][0]['url']) ? $item['album']['images'][0]['url'] : 'https://via.placeholder.com/50';

        } else {
          $image_url = !empty($item['images'][0]['url']) ? $item['images'][0]['url'] : 'https://via.placeholder.com/50';

        }

        $table[$key]['select'] = [
          '#type' => 'radio',
          '#title' => '',
          '#return_value' => $item['id'],
          '#parents' => ['spotify_selection'],
          '#attributes' => [
            'data-name' => $item['name'],
            'data-type' => $item['type'],
            'data-image' => $image_url,
          ],
        ];

        $table[$key]['image'] = [
          '#type' => 'html_tag',
          '#tag' => 'img',
          '#attributes' => [
            'src' => $image_url,
            'alt' => $item['name'],
            'style' => 'width: 50px; height: 50px; object-fit: contain;',
          ],
        ];

        $table[$key]['name'] = [
          '#markup' => htmlspecialchars($item['name']),
        ];

        $table[$key]['type'] = [
          '#markup' => htmlspecialchars($item['type']),
        ];

        $table[$key]['id'] = [
          '#markup' => htmlspecialchars($item['id']),
        ];
      }
    }

    return $table;
  }


  private function createDiscogsTable($query, $type)
  {
    $api_type = '';
    if ($type == 'artist') {
      $api_type = 'artist';
    } elseif ($type == 'album') {
      $api_type = 'master';
    } elseif ($type == 'track') {
      $api_type = 'track';
    }
    $results = $this->discogsLookupService->DiscogsSearch($query, $api_type);


    $header = [
      $this->t('Select'),
      $this->t('Image'),
      $this->t('Name'),
      $this->t('Type'),
      $this->t('Discogs ID'),
    ];

    $table = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => $this->t('No results found for your query on Discogs.'),
    ];

    if (!empty($results['results'])) {
      foreach ($results['results'] as $key => $artist) {
        $image_url = !empty($artist['thumb']) ? $artist['thumb'] : '';

        $table[$key]['select'] = [
          '#type' => 'radio',
          '#title' => '',
          '#return_value' => $artist['id'],
          '#parents' => ['discogs_selection'],
          '#attributes' => [
            'data-name' => $artist['title'],
            'data-type' => $artist['type'],
            'data-image' => $image_url,
          ],
        ];

        $table[$key]['image'] = [
          '#type' => 'html_tag',
          '#tag' => 'img',
          '#attributes' => [
            'src' => $image_url,
            'alt' => $artist['title'],
            'style' => 'width: 50px; height: 50px; object-fit: cover;',
          ],
        ];

        $table[$key]['name'] = [
          '#markup' => htmlspecialchars($artist['title']),
        ];

        $table[$key]['type'] = [
          '#markup' => htmlspecialchars($artist['type']),
        ];

        $table[$key]['id'] = [
          '#markup' => htmlspecialchars($artist['id']),
        ];
      }
    }

    return $table;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $spotify_id = $form_state->getValue('spotify_selection');
    $discogs_id = $form_state->getValue('discogs_selection');

    if ($spotify_id && $discogs_id) {
        $form_state->setRedirect(
            'music_search.create_artist',
            [
                'spotify_id' => $spotify_id,
                'discogs_id' => $discogs_id,
            ]
        );

    }
    else {
        \Drupal::messenger()->addError($this->t('You need to add both discogs and spotify item to continue'));
    }

  }
}
