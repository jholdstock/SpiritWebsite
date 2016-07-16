<?php

class Photo {
    public function __construct($url, $thumbnailUrl, $caption, $subcaption, $id) {
      $sizeArray = getimagesize($url);
      $this->size = $sizeArray[0]."x".$sizeArray[1];

    	$this->id = $id;
     	$this->url = "/".$url;
      $this->thumbnailUrl = "/".$thumbnailUrl;
      $this->caption = $caption;
      $this->subcaption = $subcaption;
    }
}