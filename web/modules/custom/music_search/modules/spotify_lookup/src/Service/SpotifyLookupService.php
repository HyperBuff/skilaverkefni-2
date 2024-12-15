<?php


namespace Drupal\spotify_lookup\Service;

use GuzzleHttp\Client;

/**
 * Spotify Lookup Service
 */

class SpotifyLookupService {
    private $client;

    public function __construct(Client $client) {
      $this->client = new Client(['base_uri' => 'https://api.spotify.com/v1/']);
    }

    private function getAccessToken() {
      $config = \Drupal::config('spotify_lookup.settings');
      $client_id = $config->get('spotify_api_client_id');
      $client_secret = $config->get('spotify_api_client_secret');

      $authClient = new Client(['base_uri' => 'https://accounts.spotify.com/']);
      $response = $authClient->post('api/token', [
        'headers' => [
          'Authorization' => 'Basic ' . base64_encode("$client_id:$client_secret"),
        ],
        'form_params' => [
          'grant_type' => 'client_credentials',
        ],
      ]);

      $data = json_decode($response->getBody(), true);
      return $data['access_token'];
    }

  public function SpotifySearch($query, $type) {

    try {
      $accessToken = $this->getAccessToken();
      $response = $this->client->get('search', [
        'headers' => [
          'Authorization' => 'Bearer ' . $accessToken,
        ],
        'query' => [
          'q' => $query,
          'type' => $type,
          'limit' => 5,
        ],
      ]);
      return json_decode($response->getBody(), true);
    } catch (\Exception $e) {
      \Drupal::logger('spotify_lookup')->error('Spotify API error: @message', ['@message' => $e->getMessage()]);
      return [];
    }
  }

  public function getArtistById($id) {
    try {
      $accessToken = $this->getAccessToken();
      $response = $this->client->get('artists/' . $id, [
        'headers' => [
          'Authorization' => 'Bearer ' . $accessToken,
        ],
      ]);
      return json_decode($response->getBody(), true);
    } catch (\Exception $e) {
      \Drupal::logger('spotify_lookup')->error('Spotify API error: @message', ['@message' => $e->getMessage()]);
      return [];
    }
  }

  public function getAlbumById($id) {
    try {
      $accessToken = $this->getAccessToken();
      $response = $this->client->get('albums/' . $id, [
        'headers' => [
          'Authorization' => 'Bearer ' . $accessToken,
        ],
      ]);
      return json_decode($response->getBody(), true);
    } catch (\Exception $e) {
      \Drupal::logger('spotify_lookup')->error('Spotify API error: @message', ['@message' => $e->getMessage()]);
      return [];
    }
  }

  public function getTrackById($id) {
    try {
      $accessToken = $this->getAccessToken();
      $response = $this->client->get('tracks/' . $id, [
        'headers' => [
          'Authorization' => 'Bearer ' . $accessToken,
        ],
      ]);
      return json_decode($response->getBody(), true);
    } catch (\Exception $e) {
      \Drupal::logger('spotify_lookup')->error('Spotify API error: @message', ['@message' => $e->getMessage()]);
      return [];
    }
  }



}
