<?php



namespace Drupal\music_search;


use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;

class MusicSearchHelper {


  public function remove_id_from_title($string) {
    // Use regex to remove " (number)" from the end of the string.
    return preg_replace('/\s*\(\d+\)$/', '', $string);
  }

  /**
   * extract node id from string
   *
   * @param string $query
   *
   * @return string | bool
   *
   */

  public function extract_node_id($query)
  {
    if (preg_match('/\((\d+)\)$/', $query, $matches)) {
      $id = $matches[1];
      return $id;

    } else {
      return FALSE;
    }
  }


  /**
   * get spotify and discogs ids
   *
   * @param string $query
   *
   * @return array
   *
   */
  public function get_music_service_ids($query) {
    $node_id = $this->extract_node_id($query);
    $ids = [];

    if ($node_id !== FALSE) {
      $node = Node::load($node_id);
      if ($node) {
        $spotify_id = $node->get('field_spotify_id')->value;
        $discogs_id = $node->get('field_discogs_id')->value;
        array_push($ids, $spotify_id, $discogs_id);
      }
    }

    return $ids;

  }


  /**
   * @param string $url
   * @param string $title
   * @return \Drupal\Core\Entity\EntityInterface|false|mixed
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function create_or_load_media(string $url, string $title) {
    $media_storage = \Drupal::entityTypeManager()->getStorage('media');
    $existing_media = $media_storage->loadByProperties(['field_media_oembed_video' => $url]);

    if (!empty($existing_media)) {
      return reset($existing_media);
    }

    $media = $media_storage->create([
      'bundle' => 'remote_video',
      'name' => $title,
      'uid' => \Drupal::currentUser()->id(),
      'status' => 1,
      'field_media_oembed_video' => $url,
    ]);
    $media->save();

    return $media;
  }

  public function create_or_find_genres(array $selected_genres) {
    $genre_references = [];

    if (!empty($selected_genres)) {
      foreach ($selected_genres as $genre_name) {
        if ($genre_name) {
          $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
          $existing_terms = $term_storage->loadByProperties([
            'name' => $genre_name,
            'vid' => 'tegund',
          ]);


          if (!empty($existing_terms)) {
            $term = reset($existing_terms);
          } else {
            $term = $term_storage->create([
              'name' => $genre_name,
              'vid' => 'tegund',
            ]);
            $term->save();
          }

          $genre_references[] = ['target_id' => $term->id()];

        }
      }
    }

    return $genre_references;

  }


  /**
   * Save an external image as a media entity.
   *
   * @param string $image_url
   *   The external image URL.
   * @param string $media_name
   *   The name for the media entity.
   *
   * @return Media|null
   *   The created media entity, or NULL on failure.
   */
  public function create_media_from_external_image(string $image_url, string $media_name) {
    try {
      $image_data = file_get_contents($image_url);
      if ($image_data === FALSE) {
        \Drupal::messenger()->addError(t('Failed to download image from URL: @url', ['@url' => $image_url]));
        return NULL;
      }

      $file_name = basename(parse_url($image_url, PHP_URL_PATH));
      $directory = 'public://external_images/';
      \Drupal::service('file_system')->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);
      $destination = $directory . $file_name . '.png';

      $destination = \Drupal::service('file_system')->getDestinationFilename($destination, \Drupal\Core\File\FileSystemInterface::EXISTS_RENAME);

      file_put_contents($destination, $image_data);

      $file = File::create([
        'uri' => $destination,
      ]);
      $file->setMimeType('image/png');
      $file->setPermanent();
      $file->save();

      // Step 3: Create the media entity.
      $media = Media::create([
        'bundle' => 'image',
        'name' => $media_name,
        'status' => 1,
        'field_media_image' => [
          'target_id' => $file->id(),
          'alt' => $media_name,
        ],
      ]);

      $media->save();

      \Drupal::messenger()->addMessage(t('Media entity created successfully: @name', ['@name' => $media_name]));

      return $media;

    } catch (\Exception $e) {
      \Drupal::messenger()->addError(t('An error occurred: @message', ['@message' => $e->getMessage()]));
      return NULL;
    }
  }
}
