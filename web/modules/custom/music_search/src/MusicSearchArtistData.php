<?php

namespace Drupal\music_search;


use Drupal\music_search\MusicSearchData;



class MusicSearchArtistData extends MusicSearchData {
  public array $names;
  public string $profile;
  public array $images;
  public array $genres;
  public array $websites;


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


    $this->names = [];
    $this->profile = $this->discogs_data->profile ?? '';
    $this->images = [];
    $this->genres = [];
    $this->websites = [];
  }


  /**
   * get_names() gets a list of names from all sources and returns array of names
   *
   * @return array
   */
  public function get_names() {
    $names = [];

    if (!empty($this->name)) {
      $names[] = (object) [
        'name' => $this->name,
        'type' => 'spotify',
      ];
    }

    if (!empty($this->discogs_data->namevariations)) {
      foreach ($this->discogs_data->namevariations as $variation) {
        $names[] = (object) [
          'name' => $variation,
          'type' => 'discogs',
          ];
      }
    }

    return $names;
  }

  /**
   * Get images returns an array of image objects.
   *
   * @return array
   *   Array of objects, each representing an image with URL and type.
   */
  public function get_images(): array {
    $images = [];

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

    if (!empty($this->discogs_data->genre)) {
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

  public function get_websites() {
    $websites = [];

    if (!empty($this->spotify_data->external_urls)) {
      foreach ($this->spotify_data->external_urls as $website_url) {
        $websites[] = (object) [
          'url' => $website_url,
          'type' => 'spotify',
        ];
      }
    }

    if (!empty($this->discogs_data->urls)) {
      foreach ($this->discogs_data->urls as $website) {
        $websites[] = (object) [
          'url' => $website,
          'type' => 'discogs',
        ];
      }
    }


    return array_values($websites);

  }



}
