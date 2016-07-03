<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Gallery.php';

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
})->bind("home");

$app->get('/admin', function () use ($app, $context) {
    return $app['twig']->render('admin/admin-base.twig', $context);
})->bind("admin");

$editPages = array("what-we-do", "about-us", "contact");

foreach ($editPages as $page) {
    $app->get("/edit-$page", function () use ($app, $context, $page) {
        return $app["twig"]->render("admin/edit-$page.twig", $context);
    })->bind("edit-$page");

    $app->post("/edit-$page", function (Request $request) use ($app, $context, $stringsFilePath, $strings, $page) {
        $newStrings = $request->request->get("$page");
        $strings["$page"] = $newStrings;
        writeJson($strings, $stringsFilePath);
        $context["strings"] = $strings;
        return $app["twig"]->render("admin/edit-$page.twig", $context);
    })->bind("post-edit-$page");
}
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
        //throw $e;

        return $app['twig']->render('error.twig', $context);
    }
});

$app->run();