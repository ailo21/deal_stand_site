<?php

namespace Drupal\ds_user\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for ds_user routes.
 */
class DsUserController extends ControllerBase {

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
