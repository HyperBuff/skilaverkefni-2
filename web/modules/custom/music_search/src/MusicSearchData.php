<?php


namespace Drupal\music_search;

/**
 * Music Search Data Object
 */
class MusicSearchData {
  public object | array $spotify_data;
  public object | array $discogs_data;
  public string $spotify_id;
  public string $discogs_id;
  public string $name;
  public string $spotify_type;
  public string $discogs_type;
  public string $image;

  /**
   * Music Search constructor.
   *
   * @param object|array $spotify_results
   *   Raw data from the Spotify API.
   *
   * @param object|array $discogs_results
   *  Raw data from Discogs API
   *
   */
  public function __construct(object|array $spotify_results, object|array $discogs_results) {
    $spotify_data = is_array($spotify_results) ? (object) $spotify_results : $spotify_results;
    $discogs_data = is_array($discogs_results) ? (object) $discogs_results : $discogs_results;

    \Drupal::logger('spotify_lookup')->debug('<pre>' . print_r($spotify_data, TRUE) . '</pre>');

    $this->spotify_data = $spotify_data;
    $this->discogs_data = $discogs_data;
    $this->spotify_id = $spotify_data->id ?? '';
    $this->discogs_id = $discogs_data->id ?? '';
    $this->name = $spotify_data->name ?? '';
    $this->spotify_type = $data->type ?? '';
    $this->discogs_type = $data->type ?? '';
    $this->image = $spotify_data->image ?? '';
  }

}


class MusicArtistData extends MusicSearchData {
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
    $this->profile = $discogs_results->profile ?? '';
    $this->images = [];
    $this->genres = [];
    $this->websites = [];
  }




}
