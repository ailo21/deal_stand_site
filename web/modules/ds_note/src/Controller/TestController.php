<?php

namespace Drupal\ds_note\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\devel\Plugin\Devel\Dumper\Kint;
use Drupal\ds_note\Entity\DSNode;
use Drupal\node\Entity\Node;

/**
 * Returns responses for ds_note routes.
 */
class TestController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $data = DSNode::load(1);
    ksm($data, $data->getEntityType());
   ;
////
//    ksm($data->get('body')->getValue()[0]['value']);
//    ksm($data->getFirstUrlWithStyle());

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!__'),
    ];

    return $build;
  }

}
