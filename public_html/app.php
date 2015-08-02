<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Gallery.php';

use Symfony\Component\HttpFoundation\Response;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Application();

//
// REGISTER SERVICES
//
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/twigs',
));

$app->register(new UrlGeneratorServiceProvider());

//
// CONTEXT
//

$img = array (
    "galleries" => array(
        "automotive" => array(
            "displayName" => "Automotive",
            "images" => array(
                "1" => array(
                    "caption" => "automotive caption 1",
                    "sub" => "automotive subcaption 1",
                ),
                "2" => array(
                    "caption" => "automotive caption 2",
                    "sub" => "automotive subcaption 2",
                ),
                "3" => array(
                    "caption" => "automotive caption 3",
                    "sub" => "automotive subcaption 3",
                ),
                "4" => array(
                    "caption" => "automotive caption 4",
                    "sub" => "automotive subcaption 4",
                ),
                "5" => array(
                    "caption" => "automotive caption 5",
                    "sub" => "automotive subcaption 5",
                ),
            )
        ),
         "concert" => array(
            "displayName" => "Concert/Touring",
            "images" => array(
                "1" => array(
                    "caption" => "concert caption 1",
                    "sub" => "concert subcaption 1",
                ),
                "2" => array(
                    "caption" => "concert caption 2",
                    "sub" => "concert subcaption 2",
                ),
                "3" => array(
                    "caption" => "concert caption 3",
                    "sub" => "concert subcaption 3",
                ),
                "4" => array(
                    "caption" => "concert caption 4",
                    "sub" => "concert subcaption 4",
                ),
                "5" => array(
                    "caption" => "concert caption 5",
                    "sub" => "concert subcaption 5",
                ),
                "6" => array(
                    "caption" => "concert caption 6",
                    "sub" => "concert subcaption 6",
                ),
            )
        ),
        "corporate" => array(
            "displayName" => "Corporate",
            "images" => array(
                "1" => array(
                    "caption" => "corporate caption 1",
                    "sub" => "corporate subcaption 1",
                ),
                "2" => array(
                    "caption" => "corporate caption 2",
                    "sub" => "corporate subcaption 2",
                ),
            )
        ),
    )
);

$fp = fopen('results.json', 'w');
fwrite($fp, json_encode($img));
fclose($fp);

$context = array(
    "galleries" => array (
        new Gallery("automotive", "Automotive"),
        new Gallery("concert", "Concert/Touring"),
        new Gallery("corporate", "Corporate"),
        new Gallery("exhibition", "Exhibitions"),
        new Gallery("theatre", "Theatre/Television"),
        new Gallery("fashion", "Fashion"),
        new Gallery("live", "Live Events"),
    ),
);

//
// ROUTING
//

$app->get('/', function () use ($app, $context) {
    return $app['twig']->render('construction.twig', $context);
})->bind("home");

$app->get('/preview', function () use ($app, $context) {
    return $app['twig']->render('base.twig', $context);
})->bind("preview");

//
// ERROR HANDLER
//

$app->error(function (\Exception $e, $code) use ($app, $context) {

//	return $app['twig']->render('construction.twig', $context);

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'Something went terribly wrong.';
            var_dump($e);
    }
    return new Response($message);
});

$app->run();