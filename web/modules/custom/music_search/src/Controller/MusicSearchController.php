<?php

namespace Drupal\music_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\discogs_lookup\Service\DiscogsLookupService;
use Drupal\node\Entity\Node;
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
     * @var DiscogsLookupService
     */
    protected $spotifyLookupService;
    protected $discogsLookupService;

    /**
     * Constructor for search services
     *
     * @param SpotifyLookupService $spotifyLookupService
     *  Spotify Search Service
     * @param DiscogsLookupService $discogsLookupService
     *  Discogs Search Service
     */

    public function __construct(SpotifyLookupService $spotifyLookupService, DiscogsLookupService $discogsLookupService) {
        $this->spotifyLookupService = $spotifyLookupService;
        $this->discogsLookupService = $discogsLookupService;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('spotify.lookup.service'),
            $container->get('discogs.lookup.service'),
        );
    }


    public function music_search_page() {
        $build = [
            'description' => [
                '#markup' => '<p>' . $this->t('Search for music from Spotify, Discogs, and other sources.') . '</p>',
            ],
            'links' => [
                '#theme' => 'item_list',
                '#items' => [
                    $this->t('<a href="/admin/music-search/search/artist">Create Artist</a>'),
                    $this->t('<a href="/admin/music-search/search/album">Create Album</a>'),
                    $this->t('<a href="/admin/music-search/search/track">Create Track</a>'),
                ],
            ],
        ];

        return $build;
    }


    public function auto_complete() {
        $query = \Drupal::request()->query->get('q');
        $type = \Drupal::request()->query->get('type');


        $results = $this->spotifyLookupService->SpotifySearch($query, $type);
        $suggestions = [];

        if (!empty($results['artists']['items'])) {
            foreach ($results['artists']['items'] as $artist) {
                $name = $artist['name'];
                $suggestions[] = [
                    'value' => $name,
                    'label' => $this->t( $name),
                ];
            }
        }

        if (!empty($results['tracks']['items'])) {
            foreach ($results['tracks']['items'] as $track) {
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

    /**
     * CreateSpotifyTable
     * Construct Spotify Table
     * @param $type
     * @param $query
     * @return array
     */
    public function create_spotify_table($type, $query) {
        $results = $this->spotifyLookupService->SpotifySearch($query, $type);

        $header = [
            $this->t('Select'),
            $this->t('Image'),
            $this->t('Name'),
            $this->t('Type'),
            $this->t('Spotify ID'),
        ];

        $rows = [];

        if (!empty($results['artists']['items'])) {
            foreach ($results['artists']['items'] as $artist) {
                $artistData = new SpotifyData((object) [
                    'id' => $artist['id'],
                    'name' => $artist['name'],
                    'type' => $artist['type'],
                    'image' => $artist['images'][0]['url'],
                    'selected' => false,
                ]);

                $rows[] = [
                    'data' => [
                        [
                            'data' => [
                                '#type' => 'radio',
                                '#name' => 'spotify_selection',
                                '#value' => $artistData->getId(),
                                '#attributes' => [
                                    'data-name' => $artistData->getName(),
                                    'data-type' => $artistData->getType(),
                                    'data-image' => $artistData->getImage(),
                                ],
                            ],
                        ],
                        [
                            'data' => [
                                '#type' => 'html_tag',
                                '#tag' => 'img',
                                '#attributes' => [
                                    'src' => $artistData->getImage(),
                                    'alt' => $artistData->getName(),
                                    'style' => 'width: 50px; height: 50px; object-fit: contain;',
                                ],
                            ],
                        ],
                        htmlspecialchars($artistData->getName()),
                        htmlspecialchars($artistData->getType()),
                        htmlspecialchars($artistData->getId()),
                    ],
                ];
            }
        }

        return [
            '#type' => 'table',
            '#header' => $header,
            '#rows' => $rows,
            '#empty' => $this->t('No results found for your query.'),
        ];
    }


    public function create_discogs_table($query, $type) {
        $results = $this->discogsLookupService->DiscogsSearch($query, $type);

        // Define the table header.
        $header = [
            $this->t('Select'),
            $this->t('Image'),
            $this->t('Name'),
            $this->t('Type'),
            $this->t('Discogs ID'),
        ];

        $rows = [];

        if (!empty($results['results'])) {
            foreach ($results['results'] as $artist) {
                $image_url = !empty($artist['thumb']) ? $artist['thumb'] : '';

                $rows[] = [
                    'data' => [
                        [
                            'data' => [
                                '#type' => 'radio',
                                '#name' => 'discogs_selection',
                                '#value' => $artist['id'],
                                '#attributes' => [
                                    'data-name' => $artist['title'],
                                    'data-type' => $artist['type'],
                                    'data-image' => $image_url,
                                ],
                            ],
                        ],
                        [
                            'data' => [
                                '#type' => 'html_tag',
                                '#tag' => 'img',
                                '#attributes' => [
                                    'src' => $image_url,
                                    'alt' => $artist['title'],
                                    'style' => 'width: 50px; height: 50px; object-fit: cover;',
                                ],
                            ],
                        ],
                        htmlspecialchars($artist['title']),
                        htmlspecialchars($artist['type']),
                        htmlspecialchars($artist['id']),
                    ],
                ];
            }
        }

        // Render the table.
        return [
            '#type' => 'table',
            '#header' => $header,
            '#rows' => $rows,
            '#empty' => $this->t('No results found for your query on Discogs.'),
        ];
    }

    public function results_page($type, $query) {

        return [
            'spotify_section' => [
                '#type' => 'details',
                '#title' => $this->t('Spotify Results'),
                '#open' => TRUE,
                'content' => $this->create_spotify_table($type, $query),
            ],
        ];
    }




}
