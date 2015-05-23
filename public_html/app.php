<?php

require_once __DIR__ . '/../vendor/autoload.php';

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
    
);

//
// ROUTING
//

$app->get('/', function () use ($app, $context) {
    return $app['twig']->render('construction.twig', $context);
})->bind("home");

$app->get('/about-us', function () use ($app, $context) {
    return $app['twig']->render('about-us.twig', $context);
})->bind("about-us");

$app->get('/what-we-do', function () use ($app, $context) {
    return $app['twig']->render('what-we-do.twig', $context);
})->bind("what-we-do");

$app->get('/contact', function () use ($app, $context) {
    return $app['twig']->render('contact.twig', $context);
})->bind("contact");

$app->get('/preview', function () use ($app, $context) {
    return $app['twig']->render('base.twig', $context);
})->bind("preview");
//
// ERROR HANDLER
//
$app->error(function (\Exception $e, $code) use ($app, $context) {

	return $app['twig']->render('construction.twig', $context);

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