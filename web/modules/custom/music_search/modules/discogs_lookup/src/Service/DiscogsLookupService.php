<?php


namespace Drupal\discogs_lookup\Service;

use GuzzleHttp\Client;

/**
 * Discogs Lookup Service
 */

class DiscogsLookupService {
  private $client;

  public function __construct(Client $client) {
    $this->client = new Client(['base_uri' => 'https://api.discogs.com/']);
  }

  private function getAccessToken() {
    $config = \Drupal::config('discogs_lookup.settings');
    return $config->get('discogs_api_token');
  }

  public function DiscogsSearch($query, $type) {
    $access_token = $this->getAccessToken();
    try {
      $response = $this->client->get('database/search', [
        'query' => [
          'q' => $query,
          'type' => $type,
          'token' => $access_token,
          'per_page' => 5,
        ],
      ]);
      return json_decode($response->getBody(), true);
    } catch (\Exception $e) {
      \Drupal::logger('discogs_lookup')->error('Discogs API error: @message', ['@message' => $e->getMessage()]);
      return [];
    }
  }


  public function getArtistById($id) {
    $access_token = $this->getAccessToken();
    try {
      $response = $this->client->get('artists/' . $id, [
        'query' => [
          'token' => $access_token,
        ],
      ]);
      return json_decode($response->getBody(), true);
    } catch (\Exception $e) {
      \Drupal::logger('discogs_lookup')->error('Discogs API error: @message', ['@message' => $e->getMessage()]);
      return [];
    }
  }



}
