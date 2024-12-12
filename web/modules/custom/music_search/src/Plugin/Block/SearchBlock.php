<?php

namespace Drupal\music_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block for the Music Search form with autocomplete.
 *
 * @Block(
 *   id = "search_block",
 *   admin_label = @Translation("Search Block")
 * )
 */
class SearchBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\music_search\Form\MusicSearchForm');
  }
}
