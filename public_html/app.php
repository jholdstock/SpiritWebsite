<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Gallery.php';
require_once 'Controller.php';
require_once 'AboutUsController.php';
require_once 'WhatWeDoController.php';
require_once 'GalleriesController.php';
require_once 'ContactController.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Application();

$configFilePath = "config.json";
$credentialsFilePath = "credentials.json";
$timeFilePath = "time.json";

//
// REGISTER SERVICES
//
$app->register(new SwiftmailerServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new TwigServiceProvider(), array('twig.path' => __DIR__.'/twigs'));
    

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

$config = loadJson($configFilePath);

// Photo galleries

function addGalleriesToContext($config, &$context) {
    $galleries = array();
    foreach($config['galleries'] as $key => $value) {
        $gal = new Gallery($value);
        $galleries[$key] = $gal;
    }
    $context["galleries"] = $galleries;
}

$credentials = loadJson($credentialsFilePath);

// Background photos
$bgImages = array_diff(scandir('./img/bg'), array('..', '.'));
$context = array(
    "bgImages"  => $bgImages,
    "config"    => $config,
);

addGalleriesToContext($config, $context);

//
// ROUTING
//
$app->get('/', function () use ($app, $context) {
    return $app['twig']->render('base.twig', $context);
})->bind("get-home");

$app->get('/admin', function () use ($app, $context) {
    return $app['twig']->render('admin/admin-login.twig', $context);
})->bind("get-admin");

$app->post('/password', function (Request $request) use ($app, $context, $credentials) {
    $password = $request->request->get("oldPassword");
    if ($password) {
            if ($password == $credentials["password"]) {
                $credentials["password"] = $request->request->get("password");
                writeJson($credentials, $GLOBALS["credentialsFilePath"]);

                $context["changePasswordSuccess"] = true;
                return $app['twig']->render('admin/edit-password.twig', $context);
            } else {
                $context["changePasswordError"] = true;
                return $app['twig']->render('admin/edit-password.twig', $context);
            }
    }
    else {
        return $app['twig']->render('admin/edit-password.twig', $context);
    }
})->bind("post-password");

$app->post('/admin', function (Request $request) use ($app, $context, $credentials) {
    $username = $request->request->get("username");
    $password = $request->request->get("password");
    if ($username == $credentials["username"] && $password == $credentials["password"]) {
        return $app['twig']->render('admin/edit-landing.twig', $context);
    } else {
        $context["authError"] = true;
        return $app['twig']->render('admin/admin-login.twig', $context, 403);
    }
})->bind("post-admin");

$app->post("/edit-about-us", function (Request $request) use ($app, $context, $config) {
    $controller = new AboutUsController($request->request, $config, $context);
    $context = $controller->handle();

    return $app["twig"]->render("admin/edit-about-us.twig", $context);
})->bind("post-edit-about-us");

$app->post("/edit-contact", function (Request $request) use ($app, $context, $config) {
    $controller = new ContactController($request->request, $config, $context);
    $context = $controller->handle();
    
    return $app["twig"]->render("admin/edit-contact.twig", $context);
})->bind("post-edit-contact");

$app->post("/edit-what-we-do", function (Request $request) use ($app, $context, $config) {
    $controller = new WhatWeDoController($request->request, $config, $context);
    $context = $controller->handle();

    return $app["twig"]->render("admin/edit-what-we-do.twig", $context);
})->bind("post-edit-what-we-do");

$app->post("/edit-portfolio", function (Request $request) use ($app, $context, $config) {
    $controller = new GalleriesController($request->request, $config, $context);
    $context = $controller->handle();

    return $app["twig"]->render("admin/edit-portfolio.twig", $context);
})->bind("post-edit-portfolio");

$app->post("/edit-gallery", function (Request $request) use ($app, $context, $config) {
    
    $gallery_id = $request->request->get("chosenGalleryId");
    if ($gallery_id) {
        $oldConfig = $config["galleries"][$gallery_id]["images"];
        $newConfig = $request->request->get("images");

        foreach($oldConfig as $key => $value) {
            $newConfig2 = array_merge($value, $newConfig[$key]);
            $oldConfig[$key] = $newConfig2;
        }

        $config["galleries"][$gallery_id]["images"] = $oldConfig;

        writeJson($config, $GLOBALS["configFilePath"]);
        
        $context["saveSuccess"] = true;
        $context["config"] = $config;

        addGalleriesToContext($config, $context);
    } else {
        $gallery_id = $request->request->get("gallery_id");
    }

    $context["chosenGalleryId"] = $gallery_id;

    return $app["twig"]->render("admin/edit-gallery.twig", $context);

})->bind("post-edit-gallery");

$app->post("/delete-image", function (Request $request) use ($app, $context, $config) {
    $gallery_id = $request->request->get("gallery_id");
    $image_id = $request->request->get("image_id");
    echo("delete image $image_id from gallery $gallery_id");
    // delete image
    // delete thumbnail
    // update json

    $context["chosenGalleryId"] = $gallery_id;
    return $app["twig"]->render("admin/edit-gallery.twig", $context);
})->bind("post-delete-image");

$app->get("/forgotten", function (Request $request) use ($app, $credentials) {
    $now = time();
    $then = file_get_contents($GLOBALS["timeFilePath"]);

    if (($now - $then) > (60*60*24)) {
        $app['swiftmailer.options'] = array(
            'host' => 'smtp.gmail.com',
            'port' => '465',
            'username' => 'Spirit.Design.Website@gmail.com',
            'password' => $credentials["emailpassword"],
            'encryption' => "ssl",
            'auth_mode' => "login"
        );  
        $app['swiftmailer.use_spool'] = false;
        
        $username = $credentials["username"];
        $password = $credentials["password"];
        $msg = "Username = $username\r\nPassword = $password";
        
        $email = \Swift_Message::newInstance()
            ->setSubject('Website password')
            ->setFrom(array('Spirit.Design.Website@gmail.com'))
            ->setTo(array('Spirit.Design.Website@gmail.com'))
            ->setBody($msg);
            
        try {
            $app['mailer']->send($email);
        }
        catch(\Exception $e) {
            return new Response(null, 500);
        }

        file_put_contents($GLOBALS["timeFilePath"], $now);
        return new Response(null, 204);
    } else {
        return new Response(null, 403);
    }
})->bind("get-forgotten");

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
