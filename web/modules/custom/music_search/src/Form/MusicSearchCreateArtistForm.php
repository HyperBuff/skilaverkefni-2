<?php

namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\discogs_lookup\Service\DiscogsLookupService;
use Drupal\node\Entity\Node;
use Drupal\spotify_lookup\Service\SpotifyLookupService;
use Symfony\Component\DependencyInjection\ContainerInterface;


class MusicSearchCreateArtistForm extends FormBase {

  protected $spotifyLookupService;
  protected $discogsLookupService;

  /**
   * Constructor.
   */
  public function __construct(SpotifyLookupService $spotifyLookupService, DiscogsLookupService $discogsLookupService) {
    $this->spotifyLookupService = $spotifyLookupService;
    $this->discogsLookupService = $discogsLookupService;
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $spotify_id = \Drupal::routeMatch()->getParameter('spotify_id');
    $discogs_id = \Drupal::routeMatch()->getParameter('discogs_id');


    $spotify_results = $this->spotifyLookupService->getArtistById($spotify_id);
    $discogs_results = $this->discogsLookupService->getArtistById($discogs_id);

    //$this->messenger()->addMessage('<pre>' . print_r($discogs_results, TRUE) . '</pre>');


    $name = $spotify_results['name'];



    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Artist Name'),
      '#required' => TRUE,
      '#default_value' => $name,
    ];

    $form['field_lysing'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Lýsing'),
      '#format' => 'full_html',
      '#default_value' => $discogs_results['profile'],
      '#required' => FALSE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Artist'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Create the artist node.
    $node = Node::create([
      'type' => 'listamadur',
      'title' => $values['title'],
    ]);

    $node->save();

    $this->messenger()->addMessage($this->t('The artist %name has been created.', ['%name' => $values['title']]));
  }
}
