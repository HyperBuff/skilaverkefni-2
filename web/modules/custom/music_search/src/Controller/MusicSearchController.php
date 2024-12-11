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

    public function auto_complete() {
        $query = \Drupal::request()->query->get('q');
        $results = $this->spotifyLookupService->SpotifySearch($query);
        $suggestions = [];

        if (!empty($results['artists']['items'])) {
            foreach ($results['artists']['items'] as $artist) {
                $suggestions[] = [
                    'value' => $artist['name'],
                    'label' => $this->t('Artist: @name', ['@name' => $artist['name']]),
                ];
            }
        }

        if (!empty($results['tracks']['items'])) {
            foreach ($results['tracks']['items'] as $track) {
                $suggestions[] = [
                    'value' => $track['name'],
                    'label' => $this->t('Track: @name', ['@name' => $track['name']]),
                ];
            }
        }

        if (!empty($results['albums']['items'])) {
            foreach ($results['albums']['items'] as $album) {
                $suggestions[] = [
                    'value' => $album['name'],
                    'label' => $this->t('Album: @name', ['@name' => $album['name']]),
                ];
            }
        }

        return new JsonResponse($suggestions);


    }
}
