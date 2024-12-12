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
    $token = $config->get('discogs_api_token');
    return $token;
  }

  public function DiscogsSearch($query) {
    $access_token = $this->getAccessToken();
    try {
      $response = $this->client->get('database/search', [
        'query' => [
          'q' => $query,
          'type' => 'artist,master,track',
          'token' => $access_token,
          'per_page' => 20,
        ],
      ]);
      return json_decode($response->getBody(), true);
    } catch (\Exception $e) {
      \Drupal::logger('discogs_lookup')->error('Discogs API error: @message', ['@message' => $e->getMessage()]);
      return [];
    }
  }


}
