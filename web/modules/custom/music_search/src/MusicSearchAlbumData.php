<?php

namespace Drupal\music_search;


use Drupal\Core\Render\Element\Date;
use Drupal\music_search\MusicSearchData;

/**
 * Music Search Data Object
 */

class MusicSearchAlbumData extends MusicSearchData {
  public array $images;
  public array $genres;
  public string $year;


  /**
   * Music Artist constructor.
   *
   * @param object|array $spotify_results
   *   Raw data from the Spotify API.
   * @param object|array $discogs_results
   *   Raw data from the Discogs API.
   */
  public function __construct(object|array $spotify_results, object|array $discogs_results) {
    parent::__construct($spotify_results, $discogs_results);
    $this->year = date($this->discogs_data->released);

  }



  /**
   * Get images returns an array of image objects.
   *
   * @return array
   *   Array of objects, each representing an image with URL and type.
   */
  public function get_images(): array {
    $this->images = [];

    if (!empty($this->spotify_data->images)) {
      $images[] = (object) [
        'url' => $this->spotify_data->images[0]['url'],
        'type' => 'spotify'
      ];
    }

    if (!empty($this->discogs_data->images)) {
      foreach ($this->discogs_data->images as $image) {
        $images[] = (object) [
          'url' => $image['uri'],
          'type' => 'discogs',
        ];
      }
    }


    return array_values($images);
  }

  public function get_genres() {

  }

  /**
   * @return array
   */



}
