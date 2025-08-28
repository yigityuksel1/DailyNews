<?php

namespace Drupal\mynews\Plugin\WSDecoder;

use Drupal\wsdata\Plugin\WSDecoder\WSDecoderJSON;

//annotation is the most important part
/**
 * @WSDecoder(
 *   id = "decoder",
 *   label = @Translation("Decoder"),
 *   description = @Translation("Decodes JSON from NewsAPI and returns articles array.")
 * )
 */
class Decoder extends WSDecoderJSON {

  /**
   * Decode the JSON response and return the articles array.
   *
   * @param mixed $data
   *   APIden gelen raw data
   *
   * @return array
   *   phpnin derleyebileceği formatta array
   */
  public function decode($data) {
    $data = parent::decode($data);
    return $data['articles'] ?? [];
  }

}
