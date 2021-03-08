<?php

namespace Drupal\ds_note\Entity;

use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node as BaseNode;

class DSNode extends BaseNode {


  /**
   * ссылка на первое изображение
   *
   * @param string $image_style
   *
   * @return \Drupal\Core\GeneratedUrl|string|null
   */
  public function getFirstUrlWithStyle($image_style = 'api_list_img_style') {
    $url = NULL;
    if ($image = $this->getFirstImageEntity()) {
      $url = ImageStyle::load($image_style)->buildUrl($image->getFileUri());
    }

    if (!$url) {
      $url = Url::fromUserInput('/themes/custom/ds/images/no_photo_news.png')
        ->toString();
    }
    return $url;
  }

  /**
   * Отдаёт сущность первого изображения
   *
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

  public function getImagesListEntity($image_style = 'api_list_img_style') {
    $data = [];
    foreach ($this->field_images as $file_field) {
      /** @var \Drupal\file\Entity\File $image */
      $image = $file_field->entity;
      $url = ImageStyle::load($image_style)->buildUrl($image->getFileUri());
      $data[] = [
        'uri' => $url,
      ];
    }

    return $data;

  }

  /**
   * отдает карусель ихображений
   */
  public function getImagesCarousel() {
    $build ['wrapper'] = [
      '#type' => 'container',
      '#prefix' => '<div class="wrapper-gallery">',
      '#suffix' => '</div>',
    ];
    $images = $this->getImages();
    $style = ImageStyle::load('show_gallery');
    $style2 = ImageStyle::load('gallery_preview');
    $style_big_img = ImageStyle::load('big_img');

    //https://github.com/nolimits4web/Swiper/blob/master/demos/300-thumbs-gallery.html
    $elements = [
      '#prefix' => '<div class="swiper-container gallery-top"><div class="swiper-wrapper">',
      '#suffix' => Markup::create('</div>
              <div class="swiper-button-next swiper-button-white"></div>
              <div class="swiper-button-prev swiper-button-white"></div>
            </div>'),
      '#attached' => [
        'library' => [
          'deal/swiper',
          'deal/swipebox',
        ],
      ],
    ];
    $elements2 = [
      '#prefix' => '<div class="swiper-container gallery-thumbs"><div class="swiper-wrapper">',
      '#suffix' => '</div></div>',
    ];

    foreach ($images as $delta => $image_field) {
      $image = $image_field->entity;

      $image_uri = $image->getFileUri();
      $image_uri_styling = $style->buildUrl($image_uri);
      $image_uri_styling2 = $style2->buildUrl($image_uri);
      $image_url_big = $style_big_img->buildUrl($image_uri);

      $elements[$delta] = [
        '#markup' => Markup::create('<a href="' . $image_url_big . '" class="swipebox swiper-slide" style="background-image: url(' . $image_uri_styling . ')">
            <span style="background-image: url('.$image_uri_styling.')"></span>
        </a>'),
      ];
      $elements2[$delta] = [
        '#markup' => Markup::create('<div class="swiper-slide" style="background-image: url(' . $image_uri_styling2 . ')"></div>'),
      ];

    }
    $build['wrapper']['top'] = $elements;
    $build['wrapper']['bottom'] = $elements2;
    return $build;
  }

  /**
   * @return array
   */
  public function getImages() {
    $images = [];
    foreach ($this->field_images as $file_field) {
      $images[] = $file_field;
    }
    return $images;
  }

}
