<?php

namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\discogs_lookup\Service\DiscogsLookupService;
use Drupal\node\Entity\Node;
use Drupal\spotify_lookup\Service\SpotifyLookupService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\music_search\MusicSearchArtistData;
use Drupal\music_search\MusicSearchHelper;

class MusicSearchCreateArtistForm extends FormBase {

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
    return 'music_search_create_artist_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $spotify_id = \Drupal::routeMatch()->getParameter('spotify_id');
    $discogs_id = \Drupal::routeMatch()->getParameter('discogs_id');


    $spotify_results = $this->spotifyLookupService->getArtistById($spotify_id);
    $discogs_results = $this->discogsLookupService->getArtistById($discogs_id);

    $artist = new MusicSearchArtistData($spotify_results, $discogs_results);



//    \Drupal::messenger()->addMessage($this->t('Results: <pre>@result</pre>', [
//      '@result' => json_encode($artist->discogs_data, JSON_PRETTY_PRINT),
//    ]));

//    \Drupal::messenger()->addMessage($this->t('images: @result', [
//      '@result' => json_encode($artist->get_images(), JSON_PRETTY_PRINT),
//    ]));

    $form['title'] = [
      '#type' => 'radios',
      '#title' => $this->t('Artist Title'),
      '#options' => [],
      '#required' => TRUE,
    ];
    $names = $artist->get_names();
    if (!empty($names)) {
      foreach ($names as $name) {
        $form['title']['#options'][$name->name] = $name->name . ' (' . $name->type . ')' ;
      }
    }


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

    foreach ($artist->get_images() as $key => $image) {
      $form['images_table'][$key]['select'] = [
        '#type' => 'radio',
        '#name' => 'images_table[select]', // Properly namespaced for form submission.
        '#return_value' => $image->url,    // Set the value of this radio button.
      ];

      $form['images_table'][$key]['image'] = [
        '#type' => 'html_tag',
        '#tag' => 'img',
        '#attributes' => [
          'src' => $image->url,
          'alt' => $artist->name,
          'style' => 'width: 150px; height: 150px; object-fit: cover;',
        ],
      ];

      $form['images_table'][$key]['type'] = [
        '#markup' => ucfirst($image->type),
      ];
    }

    $form['field_lysing'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Lýsing (Discogs)'),
      '#format' => 'full_html',
      '#default_value' => $artist->profile,
      '#required' => FALSE,
      '#attributes' => [
        'class' => ['markdown-editor'],
      ],
    ];



    $form['field_hlekkur'] = [
      '#type' => 'radios',
      '#title' => $this->t('Artist websites'),
      '#options' => [],
      '#required' => TRUE,
    ];
    $websites = $artist->get_websites();
    if (!empty($websites)) {
      foreach ($websites as $website) {
        $form['field_hlekkur']['#options'][$website->url] = $website->url . ' (' . $website->type . ')' ;
      }
    }



    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Artist'),
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getValues();

    $title = $values['title'];
    $description = $values['field_lysing']['value'] ?? '';
    $description_format = $values['field_lysing']['format'] ?? 'full_html';

    // Example: Retrieve date fields (if available).
    $founding_date = $values['field_stofndagur'] ?? NULL;
    $death_date = $values['field_danardagur'] ?? NULL;

    $selected_image_url = $values['images_table']['select'] ?? NULL;


    $media = NULL;
    if (!empty($selected_image_url)) {
      $media = $this->helper->create_media_from_external_image($selected_image_url, $title);
    }





    // Create the artist node.
    $node = Node::create([
      'type' => 'listamadur',
      'title' => $title, // Title of the node.
      'field_lysing' => [
        'value' => $description,
        'format' => $description_format,
      ],
      'field_stofndagur' => $founding_date, // Assuming this is a date field.
      'field_danardagur' => $death_date,    // Assuming this is a date field.
      'field_myndir' => $media ? [['target_id' => $media->id(), 'alt' => $title]] : [],
      'field_hlekkur' => [
        'uri' => $values['field_hlekkur'] ?? '',
        'title' => $title,
      ],
    ]);

    $node->save();

    $this->messenger()->addMessage($this->t('The artist %name has been created.', ['%name' => $values['title']]));
  }
}
