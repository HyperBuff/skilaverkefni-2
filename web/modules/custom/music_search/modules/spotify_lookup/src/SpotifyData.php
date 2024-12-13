<?php


namespace Drupal\spotify_lookup;

/**
 * Spotify Artist Data Object
 */
class SpotifyData {
  public string $id;
  public string $name;
  public string $type;
  public string $image;
  public bool $selected;

  /**
   * SpotifyData constructor.
   *
   * @param object|array $spotifyArtistData
   *   Raw data from the Spotify API.
   */
  public function __construct(object|array $spotifyArtistData) {
    // Convert array to object if necessary.
    $data = is_array($spotifyArtistData) ? (object) $spotifyArtistData : $spotifyArtistData;

    \Drupal::logger('spotify_lookup')->debug('<pre>' . print_r($data, TRUE) . '</pre>');


    $this->id = $data->id ?? '';
    $this->name = $data->name ?? '';
    $this->type = $data->type ?? '';
    $this->image = $data->image ?? '';
    $this->selected = false;
  }

  public function getId(): string {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getType(): string {
    return $this->type;
  }

  public function getImage(): string {
    return $this->image;
  }

  public function isSelected(): bool {
    return $this->selected;
  }

  public function setSelected(bool $selected): void {
    $this->selected = $selected;
  }
}
