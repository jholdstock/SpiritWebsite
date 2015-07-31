<?php

class Photo {
    public function __construct($url, $thumbnailUrl, $caption) {
     	$this->url = $url;
      $this->thumbnailUrl = $thumbnailUrl;
      $this->caption = $caption;
      $sizeArray = getimagesize($this->url);
      $this->size = $sizeArray[0]."x".$sizeArray[1];
    }
}