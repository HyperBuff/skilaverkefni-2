<?php

namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\discogs_lookup\Service\DiscogsLookupService;
use Drupal\node\Entity\Node;
use Drupal\spotify_lookup\Service\SpotifyLookupService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\music_search\MusicSearchAlbumData;
use Drupal\music_search\MusicSearchHelper;

class MusicSearchCreateAlbumForm extends FormBase {

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


    $spotify_results = $this->spotifyLookupService->getAlbumById($spotify_id);
    $masters_results = $this->discogsLookupService->getMasterById($discogs_id);
    $discogs_results = $this->discogsLookupService->getMainReleaseById($masters_results['main_release']);

    $album = new MusicSearchAlbumData($spotify_results, $discogs_results);



//    \Drupal::messenger()->addMessage($this->t('Results: <pre>@result</pre>', [
//      '@result' => json_encode($artist->discogs_data, JSON_PRETTY_PRINT),
//    ]));

//    \Drupal::messenger()->addMessage($this->t('images: @result', [
//      '@result' => json_encode($artist->get_images(), JSON_PRETTY_PRINT),
//    ]));

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Album Title'),
      '#default_value' => $album->name,
    ];


    $header = [
      $this->t('Select'),
      $this->t('Image'),
      $this->t('Type'),
    ];

    $form['images_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => $this->t('No images found'),
    ];

    foreach ($album->get_images() as $key => $image) {
      $form['images_table'][$key]['select'] = [
        '#type' => 'radio',
        '#name' => 'images_table[select]',
        '#return_value' => $image->url,
      ];

      $form['images_table'][$key]['image'] = [
        '#type' => 'html_tag',
        '#tag' => 'img',
        '#attributes' => [
          'src' => $image->url,
          'alt' => $album->name,
          'style' => 'width: 150px; height: 150px; object-fit: cover;',
        ],
      ];

      $form['images_table'][$key]['type'] = [
        '#markup' => ucfirst($image->type),
      ];
    }

    $form['field_lysing'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Lýsing'),
      '#format' => 'full_html',
      '#required' => FALSE,
      '#attributes' => [
        'class' => ['markdown-editor'],
      ],
    ];



    $form['field_utgafuar'] = [
      '#type' => 'date',
      '#title' => $this->t('Released Date'),
      '#required' => TRUE,
      '#default_value' => $album->year
    ];


    $form['field_listamadur'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Members'),
      '#target_type' => 'node',
      '#selection_settings' => [
        'target_bundles' => ['listamadur'],
      ],
      '#tags' => TRUE,
      '#required' => FALSE,
    ];

    if (!empty($node_id)) {
      $node = Node::load($node_id);
      if ($node) {
        $form['field_listamadur']['#default_value'] = [$node];
      }
    }


    $form['field_utgefandi'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Labels'),
      '#target_type' => 'node',
      '#selection_settings' => [
        'target_bundles' => ['utgefandi'],
      ],
      '#tags' => TRUE, // Allow multiple values.
      '#required' => FALSE,
    ];


    $form['field_highlighted'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Highlighted'),
    ];


    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Album'),
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $spotify_id = \Drupal::routeMatch()->getParameter('spotify_id');
    $discogs_id = \Drupal::routeMatch()->getParameter('discogs_id');
    $values = $form_state->getValues();

    // Retrieve form values
    $title = $values['title'];
    $description = $values['field_lysing']['value'] ?? '';
    $description_format = $values['field_lysing']['format'] ?? 'full_html';
    $released = $values['field_utgafuar'] ?? NULL;
    $highlighted = $values['field_highlighted'] ?? FALSE;
    $selected_image_url = $values['images_table']['select'] ?? NULL;

    // Handle media creation for field_mynd (Image)
    $media = NULL;
    if (!empty($selected_image_url)) {
      $media = $this->helper->create_media_from_external_image($selected_image_url, $title);
    }

    $field_listamadur_values = $values['field_listamadur'] ?? [];
    $listamadur_references = [];
    foreach ($field_listamadur_values as $item) {
      if (!empty($item['target_id'])) {
        $listamadur_references[] = ['target_id' => $item['target_id']];
      }
    }

// Process field_utgefandi (Labels)
    $field_utgefandi_values = $values['field_utgefandi'] ?? [];
    $utgefandi_references = [];
    foreach ($field_utgefandi_values as $item) {
      if (!empty($item['target_id'])) {
        $utgefandi_references[] = ['target_id' => $item['target_id']];
      }
    }

    // Create the album node
    $node = Node::create([
      'type' => 'plata', // Content type machine name
      'title' => $title,
      'field_spotify_id' => $spotify_id,
      'field_discogs_id' => $discogs_id,
      'field_lysing' => [
        'value' => $description,
        'format' => $description_format,
      ],
      'field_mynd' => $media ? [['target_id' => $media->id(), 'alt' => $title]] : [],
      'field_utgafuar' => $released,
      'field_highlighted' => $highlighted,
      'field_listamadur' => $listamadur_references,
      'field_utgefandi' => $utgefandi_references,
    ]);

    // Save the node
    $node->save();

    // Redirect to the newly created node's page
    $form_state->setRedirect(
      'entity.node.canonical',
      ['node' => $node->id()]
    );

    // Add a confirmation message
    $this->messenger()->addMessage($this->t('The album %name has been created.', ['%name' => $title]));
  }


}
