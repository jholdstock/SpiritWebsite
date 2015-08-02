<?php
require_once 'Photo.php';

class Gallery {
    public function __construct($dir, $displayName) {
    	$this->displayName = $displayName;
    	$this->cssName = "gallery-$dir";
     	$dir = "img/galleries/$dir/";
     	$filesInDir = scandir($dir);
     	$this->photos = array();
     	foreach ($filesInDir as $fileName) {
     		if ($fileName == "." || $fileName == "..") continue;
     		$filePath = $dir.$fileName;
     		$pathInfo = pathInfo($filePath);
     		// TODO put thumbnails in once generated
   			array_push($this->photos, new Photo($filePath, "/img/bg/1-sm.jpg", $pathInfo["filename"]));
     	}
    }
}