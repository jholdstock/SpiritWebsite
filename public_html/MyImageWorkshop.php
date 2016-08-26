<?php

use PHPImageWorkshop\ImageWorkshop;

class MyImageWorkshop extends ImageWorkshop
{
    public static function initVirginLayer($width = 100, $height = 100, $backgroundColor = null)
    {
        $opacity = 0;
        
        if (!$backgroundColor || $backgroundColor == 'transparent') {
            $opacity = 127;
            $backgroundColor = '000000';
        }
        
        return new ImageWorkshopLayer(ImageWorkshopLib::generateImage($width, $height, $backgroundColor, $opacity));
    }
}
