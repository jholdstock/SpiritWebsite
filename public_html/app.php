<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Gallery.php';
require_once 'Controller.php';
require_once 'AboutUsController.php';
require_once 'WhatWeDoController.php';
require_once 'GalleriesController.php';
require_once 'ContactController.php';
require_once 'MyThumbnailGenerator.php';

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Application();

$timeFilePath = "../conf/time.json";
$configFilePath = "../conf/config.json";
$credentialsFilePath = "../conf/credentials.json";

//
// REGISTER SERVICES
//
$app->register(new SwiftmailerServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new TwigServiceProvider(), array('twig.path' => __DIR__.'/../twigs'));
$app->register(new MyThumbnailGenerator());

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
$context["maxUpload"] = ini_get("post_max_size");
$context["maxFile"] = ini_get("upload_max_filesize");

function thumbnailConfig() {
    $routes = array();
    $gals = array("automotive", "corporate", "exhibition", "concert", "fashion", "live", "theatre");
    foreach ($gals as $n) {
        array_push($routes, array(
            "route" => "/img/galleries/$n",
            'allowed_ext' => 'jpg,jpeg,png',
            'allowed_size' => array('200.133'),
            'max_size' => '200.133',
            'on_the_fly' => false,
            'route_name' => "thumbnail_$n"
        ));
    }
    return $routes;
}
$app['lazy.thumbnail.mount_paths'] = thumbnailConfig();

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

$app->post('/admin/password', function (Request $request) use ($app, $context, $credentials) {
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

$app->post('/admin/login', function (Request $request) use ($app, $context, $credentials) {
    $username = $request->request->get("username");
    $password = $request->request->get("password");
    if ($username == $credentials["username"] && $password == $credentials["password"]) {
        return $app['twig']->render('admin/edit-landing.twig', $context);
    } else {
        $context["authError"] = true;
        return $app['twig']->render('admin/admin-login.twig', $context, 403);
    }
})->bind("post-admin");

$app->post("/admin/edit-about-us", function (Request $request) use ($app, $context, $config) {
    $controller = new AboutUsController($request->request, $config, $context);
    $context = $controller->handle();

    return $app["twig"]->render("admin/edit-about-us.twig", $context);
})->bind("post-edit-about-us");

$app->post("/admin/edit-contact", function (Request $request) use ($app, $context, $config) {
    $controller = new ContactController($request->request, $config, $context);
    $context = $controller->handle();
    
    return $app["twig"]->render("admin/edit-contact.twig", $context);
})->bind("post-edit-contact");

$app->post("/admin/edit-what-we-do", function (Request $request) use ($app, $context, $config) {
    $controller = new WhatWeDoController($request->request, $config, $context);
    $context = $controller->handle();

    return $app["twig"]->render("admin/edit-what-we-do.twig", $context);
})->bind("post-edit-what-we-do");

$app->post("/admin/edit-portfolio", function (Request $request) use ($app, $context, $config) {
    $controller = new GalleriesController($request->request, $config, $context);
    $context = $controller->handle();

    return $app["twig"]->render("admin/edit-portfolio.twig", $context);
})->bind("post-edit-portfolio");

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function iniGetBytes($val)
{
    $val = trim(ini_get($val));
    if ($val != '') {
        $last = strtolower(
            $val{strlen($val) - 1}
        );
    } else {
        $last = '';
    }
    switch ($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

$app->post("/admin/edit-gallery", function (Request $request) use ($app, $context, $config) {

    $maxPostSize = iniGetBytes('post_max_size');
    if ($_SERVER['CONTENT_LENGTH'] > $maxPostSize) {
        die("<br>Upload size exceeded server maximum");
    }

    $gallery_id = $request->request->get("chosenGalleryId");
    if ($gallery_id) {
        // Save strings
        $oldConfig = $config["galleries"][$gallery_id]["images"];
        $newConfig = $request->request->get("images");

        foreach($oldConfig as $key => $value) {
            $newConfig2 = array_merge($value, $newConfig[$key]);
            $oldConfig[$key] = $newConfig2;
        }

        if ($oldConfig === $config["galleries"][$gallery_id]["images"]) {

        } else {
            $config["galleries"][$gallery_id]["images"] = $oldConfig;

            writeJson($config, $GLOBALS["configFilePath"]);
            
            $context["saveSuccess"] = true;
        }

        // Save Images
        $images = $request->files->get("newImages");
        if ($images[0]) {
            foreach($images as $image) {
                $originalFileName = $image->getClientOriginalName();
                try {
                    $name = generateRandomString().".".$image->getClientOriginalExtension();
                    $image->move("img/galleries/".$config["galleries"][$gallery_id]["directoryName"], $name);
                    array_push($config["galleries"][$gallery_id]["images"], array(
                        "filename" => $name,
                        "caption" => "",
                        "sub" => ""    
                    ));

                    writeJson($config, $GLOBALS["configFilePath"]);
                    if (!isset($context["uploadSuccess"])) { 
                        $context["uploadSuccess"] = "";
                    }
                    $context["uploadSuccess"] = $context["uploadSuccess"] . $originalFileName . " was uploaded<br/>";    
                }
                catch (FileException $e) {
                    $msg = $e->getMessage();
                    if (strpos($msg, "exceeds your upload_max_filesize") !== false) {
                        if (!isset($context["uploadError"])) { 
                            $context["uploadError"] = "";
                        }
                        $context["uploadError"] = $context["uploadError"] . $originalFileName . " exceeds max file size<br/>";    
                    } else {
                        throw $e;
                    }
                }
            }
        }

        $context["config"] = $config;

        addGalleriesToContext($config, $context);
    } else {
        $gallery_id = $request->request->get("gallery_id");
    }

    $context["chosenGalleryId"] = $gallery_id;
    return $app["twig"]->render("admin/edit-gallery.twig", $context);

})->bind("post-edit-gallery");

$app->post("/admin/delete-image", function (Request $request) use ($app, $context, $config) {
    $gallery_id = $request->request->get("gallery_id");
    $image_id = $request->request->get("image_id");

    $directoryName = $config["galleries"][$gallery_id]["directoryName"];
    $filename = $config["galleries"][$gallery_id]["images"][$image_id]["filename"];

    unlink("img/galleries/$directoryName/$filename");
    unlink("img/galleries/$directoryName/200x133/$filename");

    unset($config["galleries"][$gallery_id]["images"][$image_id]);
    $context["uploadSuccess"] = "Image deleted";
    $context["config"] = $config;

    addGalleriesToContext($config, $context);
    writeJson($config, $GLOBALS["configFilePath"]);

    $context["chosenGalleryId"] = $gallery_id;
    return $app["twig"]->render("admin/edit-gallery.twig", $context);
})->bind("post-delete-image");

$app->get("/admin/forgotten", function (Request $request) use ($app, $credentials) {
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
    error_log($e);
    //throw $e;
    return $app['twig']->render('error.twig', $context);
});

$app->run();
