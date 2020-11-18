<?php

namespace Drupal\ds_note\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Returns responses for ds_note routes.
 */
class TestController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $data = Node::load(1);
//
    var_dump($data);
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
