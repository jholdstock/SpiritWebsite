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