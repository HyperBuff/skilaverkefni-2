<?php

namespace Drupal\music_search;



/**
 * Music Search Data Object
 */

class MusicSearchTrackData extends MusicSearchData {

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

  }




  public function getLinks(): array {
    $links = [];

    if (!empty($this->discogs_data->videos)) {
      foreach ($this->discogs_data->videos as $video) {
        $links[] = (object) [
          'title' => $video['title'],
          'url' => $video['uri'],
          'duration' => $this->formatDuration($video['duration']),
          'type' => 'discogs',
        ];
      }
    }

    return $links;
  }

  private function formatDuration($seconds): string {
    if (!is_numeric($seconds)) {
      return $seconds;
    }
    $minutes = floor($seconds / 60);
    $remaining_seconds = $seconds % 60;
    return sprintf('%02d:%02d', $minutes, $remaining_seconds);
  }




}
