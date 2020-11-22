<?php

namespace Drupal\ds_note\Entity;

use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node  as BaseNode;

class DSNode extends BaseNode{


  /**
   * ссылка на первое изображение
   * @param string $image_style
   *
   * @return \Drupal\Core\GeneratedUrl|string|null
   */
  public function getFirstUrlWithStyle($image_style = 'api_list_img_style') {
    $url = NULL;
    if($image = $this->getFirstImageEntity()){
      $url = ImageStyle::load($image_style)->buildUrl($image->getFileUri());
    }

    if(!$url){
      $url = Url::fromUserInput('/themes/custom/ds/images/no_photo_news.png')->toString();
    }
    return $url;
  }

  /**
   * Отдаёт сущность первого изображения
   * @return \Drupal\file\Entity\File|null
   */
  public function getFirstImageEntity() {
    foreach ($this->field_images as $file_field) {
      /** @var \Drupal\file\Entity\File $image */
      $image = $file_field->entity;
      return $image;
    }

    return NULL;
  }

  public function getImagesListEntity($image_style = 'api_list_img_style'){
    $data=[];
    foreach ($this->field_images as $file_field){
      /** @var \Drupal\file\Entity\File $image */
      $image = $file_field->entity;
      $url = ImageStyle::load($image_style)->buildUrl($image->getFileUri());
      $data[]=[
        'uri'=>$url
      ];
    }

    return $data;

  }

}
