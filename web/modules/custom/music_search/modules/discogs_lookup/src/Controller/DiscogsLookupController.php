<?php

namespace Drupal\discogs_lookup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\discogs_lookup\Service\DiscogsLookupService;

/**
 * Define DiscogsLookupController Class
 */

class DiscogsLookupController extends ControllerBase {
  /**
   * The Spotify Lookup Service.
   *
   * @var DiscogsLookupService
   */
  protected $discogsLookupService;

  /**
   * Construct a DiscogsLookupService object
   *
   * @param DiscogsLookupService $discogsLookupService
   *  The Discogs Lookup Service
   */

  public function __construct(DiscogsLookupService $discogsLookupService) {
    $this->discogsLookupService = $discogsLookupService;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('discogs_lookup.service')
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

  public function search($query, $type) {
    $results = $this->discogsLookupService->DiscogsSearch($query, $type);

    // Check if there are any results.
    if (empty($results['results'])) {
      return [
        '#markup' => '<p>No results found for "' . htmlspecialchars($query) . '".</p>',
      ];
    }


    $grouped_results = [];
    foreach ($results['results'] as $result) {
      if (!empty($result['type'])) {
        $grouped_results[$result['type']][] = $result;
      }
    }


    $output = '<h2>Search Results for "' . htmlspecialchars($query) . '"</h2>';

    foreach ($grouped_results as $type => $items) {
      $output .= '<h3>' . ucfirst($type) . 's</h3>';
      $output .= '<ul>';
      foreach ($items as $item) {
        $output .= '<li>';
        $output .= '<strong>' . htmlspecialchars($item['title']) . '</strong>';
        if (!empty($item['year'])) {
          $output .= ' (' . htmlspecialchars($item['year']) . ')';
        }
        if (!empty($item['uri'])) {
          $output .= ' (<a href="https://www.discogs.com' . htmlspecialchars($item['uri']) . '" target="_blank">View on Discogs</a>)';
        }
        $output .= '</li>';
      }
      $output .= '</ul>';
    }

    return [
      '#markup' => $output,
    ];
  }
}
