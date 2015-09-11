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

// Photo galleries
$string = file_get_contents("photo-galleries.json");
$json_a = json_decode($string, true);
$galleries = array();
foreach($json_a['galleries'] as $key => $value) {
    array_push($galleries, new Gallery($key, $value));
}

// Background photos
$bgImages = array_diff(scandir('./img/bg'), array('..', '.'));

$context = array(
    "galleries" => $galleries,
    "bgImages" => $bgImages,
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