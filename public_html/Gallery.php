<?php
require_once 'Photo.php';

class Gallery {
    public function __construct($gallery_json) {
        $dir = $gallery_json["directoryName"];
    	$this->displayName = $gallery_json["displayName"];
    	$this->cssName = "gallery-$dir";
     	$imgDir = "img/galleries/$dir/";
        $thumbDir = "img/galleries/$dir/200x133/";

     	$filesInDir = scandir($imgDir);
     	
        $this->photos = array();
        foreach ($gallery_json["images"] as $key => $image_json) {
            $imgPath   = $imgDir  .$image_json["filename"];
            $thumbPath = $thumbDir.$image_json["filename"];
   			array_push($this->photos, new Photo($imgPath, $thumbPath, $image_json["caption"], $image_json["sub"], $key));
        }
    }
}