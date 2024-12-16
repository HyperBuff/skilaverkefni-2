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
    $this->images = [];
    $this->genres = [];
  }



  /**
   * Get images returns an array of image objects.
   *
   * @return array
   *   Array of objects, each representing an image with URL and type.
   */
  public function get_images(): array {

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
    $genres = [];

    if (!empty($this->spotify_data->genres)) {
      foreach ($this->spotify_data->genres as $genre) {
        $genres[] = (object) [
          'name' => $genre,
          'type' => 'spotify'
        ];
      }
    }

    if (!empty($this->discogs_data->genres)) {
      foreach ($this->discogs_data->genres as $genre) {
        $genres[] = (object) [
          'name' => $genre,
          'type' => 'discogs',
        ];
      }
    }

    return array_values($genres);

  }

  /**
   * @return array
   */



}
