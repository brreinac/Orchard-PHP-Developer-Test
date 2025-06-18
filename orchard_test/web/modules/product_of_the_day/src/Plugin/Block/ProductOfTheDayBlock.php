<?php

namespace Drupal\product_of_the_day\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Database;

/**
 * Provides a 'Product of the Day' block.
 *
 * @Block(
 *   id = "product_of_the_day_block",
 *   admin_label = @Translation("Product of the Day")
 * )
 */
class ProductOfTheDayBlock extends BlockBase {

  public function build() {
    $row = Database::getConnection()
      ->query("SELECT id,name,summary,image_fid FROM {product_of_the_day} WHERE is_featured = 1 ORDER BY RAND() LIMIT 1")
      ->fetchAssoc();

    if (! $row) {
      return ['#markup' => $this->t('No featured products available.')];
    }

    $file = \Drupal\file\Entity\File::load($row['image_fid']);
    $url = file_create_url($file->getFileUri());

    return [
      '#theme' => 'product_of_the_day',
      '#name' => $row['name'],
      '#summary' => $row['summary'],
      '#image_url' => $url,
      '#cache' => ['max-age' => 0],
    ];
  }
}
