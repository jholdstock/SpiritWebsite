<?php
require_once 'Photo.php';

class Gallery {
    public function __construct($dir, $gallery_json) {

    	$this->displayName = $gallery_json["displayName"];
    	$this->cssName = "gallery-$dir";
     	$dir = "img/galleries/$dir/";
     	$filesInDir = scandir($dir);
     	$this->photos = array();
     	foreach ($filesInDir as $fileName) {
     		if ($fileName == "." || $fileName == "..") continue;
            $filePath = $dir.$fileName;
            $pathInfo = pathInfo($filePath);
            $fileName = $pathInfo["filename"];


            $gallery = $gallery_json["images"][$fileName];
     		// TODO put thumbnails in once generated
   			array_push($this->photos, new Photo($filePath, "/img/bg/1-sm.jpg", $gallery["caption"], $gallery["sub"]));
     	}
    }
}