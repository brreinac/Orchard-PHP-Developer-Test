<?php

namespace Drupal\product_of_the_day\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Product of the Day routes.
 */
class ProductOfTheDayController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
