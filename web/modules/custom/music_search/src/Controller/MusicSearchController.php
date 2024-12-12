<?php

namespace Drupal\music_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\spotify_lookup\Service\SpotifyLookupService;

/**
 * Define MusicSearchController Class
 */

class MusicSearchController extends ControllerBase {
    /**
     * Search Services
     *
     * @var SpotifyLookupService
     */
    protected $spotifyLookupService;

    /**
     * Constructor for search services
     *
     * @param SpotifyLookupService $spotifyLookupService
     *  Spotify Search Service
     */


    public function __construct(SpotifyLookupService $spotifyLookupService) {
        $this->spotifyLookupService = $spotifyLookupService;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('spotify.lookup.service')
        );
    }


    public function music_search_page() {
        // Example content for the overview page.
        $build = [
            'description' => [
                '#markup' => '<p>' . $this->t('Search for music from Spotify, Discogs, and other sources.') . '</p>',
            ],
            'links' => [
                '#theme' => 'item_list',
                '#items' => [
                    $this->t('<a href="/admin/music-search/create-album">Create an Album</a>'),
                    $this->t('<a href="/admin/music-search/search">Search for Music</a>'),
                ],
            ],
        ];

        return $build;
    }

    public function auto_complete() {
        $query = \Drupal::request()->query->get('q');
        $results = $this->spotifyLookupService->SpotifySearch($query, 'album');
        $suggestions = [];

        if (!empty($results['artists']['items'])) {
            foreach ($results['artists']['items'] as $artist) {
                $id = $artist['id'];
                $name = $artist['name'];
                $type = $artist['type'];
                $suggestions[] = [
                    'value' => $name . ' - ' . $type,
                    'label' => $this->t( $type . ': ' . $name),
                ];
            }
        }

        if (!empty($results['tracks']['items'])) {
            foreach ($results['tracks']['items'] as $track) {
                $id = $track['id'];
                $name = $track['name'];
                $type = $track['type'];
                $artist = $track['artists'][0]['name'];
                $suggestions[] = [
                    'value' => $name,
                    'label' => $this->t($name . ' - ' . $artist . '(' . $type . ')'),
                ];
            }
        }

        if (!empty($results['albums']['items'])) {
            foreach ($results['albums']['items'] as $album) {
                $name = $album['name'];
                $type = $album['type'];
                $artist = $album['artists'][0]['name'];
                $suggestions[] = [
                    'value' => $name,
                    'label' => $this->t($name . ' - ' . $artist . '(' . $type . ')'),
                ];
            }
        }



        return new JsonResponse($suggestions);
    }

    public function results_page($query) {
        $results = $this->spotifyLookupService->SpotifySearch($query);

        // Build the results page.
        $output = '<h2>Search Results for: ' . htmlspecialchars($query) . '</h2>';

        // Display artists, tracks, and albums.
        if (!empty($results['artists']['items'])) {
            $output .= '<h3>Artists</h3><ul>';
            foreach ($results['artists']['items'] as $artist) {
                $output .= '<li>' . htmlspecialchars($artist['name']) . '</li>';
            }
            $output .= '</ul>';
        }

        if (!empty($results['tracks']['items'])) {
            $output .= '<h3>Tracks</h3><ul>';
            foreach ($results['tracks']['items'] as $track) {
                $output .= '<li>' . htmlspecialchars($track['name']) . '</li>';
            }
            $output .= '</ul>';
        }

        if (!empty($results['albums']['items'])) {
            $output .= '<h3>Albums</h3><ul>';
            foreach ($results['albums']['items'] as $album) {
                $output .= '<li>' . htmlspecialchars($album['name']) . '</li>';
            }
            $output .= '</ul>';
        }

        return [
            '#markup' => $output,
        ];
    }

}
