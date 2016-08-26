<?php

require_once 'MyImageWorkshop.php';

use Monolog\Logger;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MyGeneratorController
{
    public function generateAction(Application $app, Request $req, $arguments)
    {
        $expectedWidth = $arguments['width'];
        $expectedHeight = $arguments['height'];

        $largestSide = max($expectedWidth, $expectedHeight);
        $base = MyImageWorkshop::initFromPath($arguments['file']);
        //$base->cropMaximumInPixel(0, 0, "MM");
        $base->resizeInPixel($largestSide, null, true, 0 ,0);
        $base->cropInPixel($expectedWidth, $expectedHeight, 0, 0, 'MM');
        $fileName = basename($arguments['file']);
        if (!$arguments['on_the_fly']) {
            $folder = $arguments['web_root'] . $arguments['mount'] .
                '/' . $arguments['width'] . 'x' . $arguments['height'];

            $base->save($folder, $fileName, true);
            $arguments['logger'](Logger::DEBUG, "File saved in '$folder/$fileName'");
        }

        $ext = strtolower(pathinfo($arguments['file'], PATHINFO_EXTENSION));
        if ($ext == 'jpg') {
            $ext = 'jpeg';
        }
        $mimeType = 'image/' . $ext;
        $func = 'image' . $ext;
        if (!function_exists($func)) {
            $arguments['logger'](Logger::CRITICAL, "How this possible?");
            $app->abort(404);
        }

        //I don't know any way to pass an image resource to symfony Response object.
        ob_start();
        $func($base->getResult());
        $result = ob_get_clean();
        return new Response(
            $result,
            200,
            array(
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'filename="'. $fileName . '"'
            )
        );

    }
}
