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

function loadJson($filePath) {
    $string = file_get_contents($filePath);
    return json_decode($string, true);
}

// Photo galleries
$json = loadJson("photo-galleries.json");
$galleries = array();
foreach($json['galleries'] as $key => $value) {
    array_push($galleries, new Gallery($key, $value));
}

// Strings
$strings = loadJson("strings.json");

// Background photos
$bgImages = array_diff(scandir('./img/bg'), array('..', '.'));

$context = array(
    "galleries" => $galleries,
    "bgImages"  => $bgImages,
    "strings"   => $strings,
);

//
// ROUTING
//

$app->get('/', function () use ($app, $context) {
    return $app['twig']->render('base.twig', $context);
})->bind("home");

$app->get('/admin', function () use ($app, $context) {
    return $app['twig']->render('admin.twig', $context);
})->bind("admin");

//
// ERROR HANDLER
//

$app->error(function (\Exception $e, $code) use ($app, $context) {
    if ($code == 404) {
        $context = array(
            "httpStatus" => "404 Not Found",
            "sub" => "The requested page could not be found. Please try the <a href='/'>home page</a> instead."
        );

        return $app['twig']->render('error.twig', $context);
    } else {
        $context = array(
            "httpStatus" => "500 Internal Error",
            "sub" => "Something has gone wrong. Please try again later"
        );

        return $app['twig']->render('error.twig', $context);
    }
});

$app->run();