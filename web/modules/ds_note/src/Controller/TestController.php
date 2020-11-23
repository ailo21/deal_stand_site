<?php

namespace Drupal\ds_note\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\devel\Plugin\Devel\Dumper\Kint;
use Drupal\ds_note\Entity\DSNode;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * Returns responses for ds_note routes.
 */
class TestController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {


        ksm($result);
    //
    //    foreach ($tree as $term) {
    //      // We get 1st and 2nd levels, also we check parents (only 2nd level has parents).
    //      if (!empty($manager->loadParents($term->id()))) {
    //        $result[] = $term->getTerm();
    //      }
    //    }
    //    ksm($result);

    //    $data = DSNode::load(1);
    //    ksm($data, $data->getEntityType());


    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!__'),
    ];

    return $build;
  }

}
