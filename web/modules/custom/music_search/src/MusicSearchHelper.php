<?php



namespace Drupal\music_search;


use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

class MusicSearchHelper {


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
