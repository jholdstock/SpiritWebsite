<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Gallery.php';
require_once 'Controller.php';
require_once 'AboutUsController.php';
require_once 'WhatWeDoController.php';
require_once 'ContactController.php';

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Application();

$stringsFilePath = "strings.json";
$galleriesFilePath = "photo-galleries.json";

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

function vdump($obj) {
    echo '<pre>';
    var_dump($obj);
    echo '</pre>';
    return null;
}

function loadJson($filePath) {
    $string = file_get_contents($filePath);
    return json_decode($string, true);
}

function writeJson($obj, $filePath) {
    file_put_contents($filePath, json_encode($obj));
}

// Photo galleries
$json = loadJson($galleriesFilePath);
$galleries = array();
foreach($json['galleries'] as $key => $value) {
    array_push($galleries, new Gallery($key, $value));
}

// Strings
$strings = loadJson($stringsFilePath);

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
})->bind("get-home");

$app->get('/admin', function () use ($app, $context) {
    return $app['twig']->render('admin/admin-login.twig', $context);
})->bind("get-admin");

$app->post('/admin', function (Request $request) use ($app, $context) {
    $username = $request->request->get("username");
    $password = $request->request->get("password");
    if ($username == "spirit" && $password == "password") {
        return $app['twig']->render('admin/edit-base.twig', $context);
    } else {
        $context["authError"] = true;
        return $app['twig']->render('admin/admin-login.twig', $context, 403);
    }
})->bind("post-admin");

$app->post("/edit-about-us", function (Request $request) use ($app, $context, $strings) {
    $controller = new AboutUsController($request->request);
    $strings = $controller->handle($strings);
    $context["strings"] = $strings;

    return $app["twig"]->render("admin/edit-about-us.twig", $context);
})->bind("post-edit-about-us");

$app->post("/edit-contact", function (Request $request) use ($app, $context, $strings) {
    $controller = new ContactController($request->request);
    $strings = $controller->handle($strings);
    $context["strings"] = $strings;

    return $app["twig"]->render("admin/edit-contact.twig", $context);
})->bind("post-edit-contact");

$app->post("/edit-what-we-do", function (Request $request) use ($app, $context, $strings) {
    $controller = new WhatWeDoController($request->request);
    $strings = $controller->handle($strings);
    $context["strings"] = $strings;

    return $app["twig"]->render("admin/edit-what-we-do.twig", $context);
})->bind("post-edit-what-we-do");
//
// ERROR HANDLER
//

$app->error(function (\Exception $e, $code) use ($app, $context) {
    if ($code == 404 || $code == 405) {
        $context = array(
            "httpStatus" => "404 Not Found",
            "sub" => "The requested page could not be found. Please try the <a href='/'>home page</a> instead."
        );
    } else {
        $context = array(
            "httpStatus" => "$code",
            "sub" => "Something has gone horribly wrong. Please try again later or try the <a href='/'>home page</a> instead."
        );
    }

    //throw $e;
    return $app['twig']->render('error.twig', $context);
});

$app->run();
