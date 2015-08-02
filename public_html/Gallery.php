<?php
require_once 'Photo.php';

class Gallery {
    public function __construct($dir, $gallery_json) {

    	$this->displayName = $gallery_json["displayName"];
    	$this->cssName = "gallery-$dir";
     	$imgDir = "img/galleries/$dir/";
        $thumbDir = "img/thumbnails/$dir/";
     	$filesInDir = scandir($imgDir);
     	$this->photos = array();
     	foreach ($filesInDir as $fileName) {
     		if ($fileName == "." || $fileName == "..") continue;
            $imgPath   = $imgDir  .$fileName;
            $thumbPath = $thumbDir.$fileName;
            
            $pathInfo = pathInfo($imgPath);
            $fileName = $pathInfo["filename"];
            $gallery = $gallery_json["images"][$fileName];

     		// TODO put thumbnails in once generated
   			array_push($this->photos, new Photo($imgPath, $thumbPath, $gallery["caption"], $gallery["sub"]));
     	}
    }
}