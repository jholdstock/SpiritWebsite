<?php
require_once 'Photo.php';

class Gallery {
    public function __construct($dir) {
     	$dir = "img/$dir/";
     	$filesInDir = scandir($dir);
     	$this->photos = array();
     	foreach ($filesInDir as $fileName) {
     		if ($fileName == "." || $fileName == "..") continue;
     		$filePath = $dir.$fileName;
     		$pathInfo = pathInfo($filePath);
     		// TODO put thumbnails in once generated
   			array_push($this->photos, new Photo($filePath, $filePath, $pathInfo["filename"]));
     	}
    }
}