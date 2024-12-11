<?php

namespace Drupal\spotify_lookup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\spotify_lookup\Service\SpotifyLookupService;

/**
 * Define SpotifyLookupController Class
 */

class SpotifyLookupController extends ControllerBase {
  /**
   * The Spotify Lookup Service.
   *
   * @var SpotifyLookupService
   */
  protected $spotifyLookupService;

  /**
   * Construct a SpotifyLookupService object
   *
   * @param SpotifyLookupService $spotifyLookupService
   *  The Spotify Lookup Service
   */

  public function __construct(SpotifyLookupService $spotifyLookupService) {
    $this->spotifyLookupService = $spotifyLookupService;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('spotify_lookup.service')
    );
  }


  /**
   *
   * @param string $query
   *  The to search query
   *
   * @return array
   *  returns array
   */

  public function search($query) {
    $results = $this->spotifyLookupService->SpotifySearch($query);
    $output = '<h2>Artists</h2>';
    $output .= '<ul>';
    foreach ($results['artists']['items'] as $artist) {
      $output .= '<li>' . $artist['name'] . '</li>';
    }
    $output .= '</ul>';
    $output .= '<h2>Tracks</h2>';
    $output .= '<ul>';
    foreach ($results['tracks']['items'] as $track) {
      $output .= '<li>' . $track['name'] . '</li>';
    }
    $output .= '</ul>';
    $output .= '<h2>Albums:</h2>';
    $output .= '<ul>';
    foreach ($results['albums']['items'] as $track) {
      $output .= '<li>' . $track['name'] . '</li>';
    }
    $output .= '</ul>';

    return [
      '#markup' => $output,
    ];
  }
}
