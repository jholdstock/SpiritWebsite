<?php

use PHPImageWorkshop\ImageWorkshop;

class MyImageWorkshop extends ImageWorkshop
{
    public static function initVirginLayer($width = 100, $height = 100, $backgroundColor = '000000')
    {
	      $opacity = 255;
        
        return new ImageWorkshopLayer(ImageWorkshopLib::generateImage($width, $height, $backgroundColor, $opacity));
    }
}
