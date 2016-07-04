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

$editPages = array("about-us");

foreach ($editPages as $page) {

    $app->post("/edit-$page", function (Request $request) use ($app, $context, $stringsFilePath, $strings, $page) {
        $newStrings = $request->request->get("$page");
        if ($newStrings) {
            $strings["$page"] = $newStrings;
            writeJson($strings, $stringsFilePath);
            $context["strings"] = $strings;
        }

        return $app["twig"]->render("admin/edit-$page.twig", $context);
    })->bind("post-edit-$page");
}

$app->post("/edit-contact", function (Request $request) use ($app, $context, $stringsFilePath, $strings) {
    $newStrings = $request->request->get("contact");
    if ($newStrings) {
        $rawAddress = $newStrings["address"];
        $parsedAddress = explode(PHP_EOL, $rawAddress);
        $parsedAddress = array_map('trim', $parsedAddress);
        $parsedAddress = array_filter($parsedAddress);
        $newStrings["address"] = $parsedAddress;

        $strings["contact"] = $newStrings;
        writeJson($strings, $stringsFilePath);
        $context["strings"] = $strings;
    }
    return $app["twig"]->render("admin/edit-contact.twig", $context);
})->bind("post-edit-contact");

$app->post("/edit-what-we-do", function (Request $request) use ($app, $context, $stringsFilePath, $strings) {
    $newStrings = $request->request->get("what-we-do");
    if ($newStrings) {
        $rawRecentClients = $newStrings["recent-clients"];
        $parsedRecentClients = explode(PHP_EOL, $rawRecentClients);
        $parsedRecentClients = array_map('trim', $parsedRecentClients);
        $parsedRecentClients = array_filter($parsedRecentClients);
        $newStrings["recent-clients"] = $parsedRecentClients;

        $rawList1 = $newStrings["list1"];
        $parsedList1 = explode(PHP_EOL, $rawList1);
        $parsedList1 = array_map('trim', $parsedList1);
        $parsedList1 = array_filter($parsedList1);
        $newStrings["list1"] = $parsedList1;

        $rawList2 = $newStrings["list2"];
        $parsedList2 = explode(PHP_EOL, $rawList2);
        $parsedList2 = array_map('trim', $parsedList2);
        $parsedList2 = array_filter($parsedList2);
        $newStrings["list2"] = $parsedList2;

        $strings["what-we-do"] = $newStrings;
        writeJson($strings, $stringsFilePath);
        $context["strings"] = $strings;
    }

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
