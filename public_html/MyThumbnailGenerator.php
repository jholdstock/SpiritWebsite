<?php

require_once 'MyGeneratorController.php';

use Cybits\Silex\Provider\LazyThumbnailGenerator;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;

class MyThumbnailGenerator extends LazyThumbnailGenerator
{
    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app        An Application instance
     * @param string      $route      the current route
     * @param array       $parameters array of parameter for current directory
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app, $route, $parameters)
    {
        /** @var $controllers ControllerCollection */
        $controllers = $app['controllers_factory'];
        if (isset($app['logger']) && $app['logger'] instanceof LoggerInterface) {
            $logger = array($app['logger'], 'log');
        } else {
            $logger = function () {
            };
        }
        $controller = $controllers->match(
            '/{arguments}',
            'MyGeneratorController::generateAction'
        )
            ->assert('arguments', '.*')
            ->convert(
                'arguments',
                function ($arguments) use ($app, $route, $parameters, $logger) {
                    $pattern = explode('/', $arguments, 2);
                    if (count($pattern) != 2 || !preg_match('/^([0-9]*)x([0-9]*)$/', $pattern[0], $matches)) {
                        $logger(Logger::ERROR, "Invalid call, need the '([0-9]*)x([0-9]*)/image.jpg'");
                        $app->abort(404);

                        //Stupid IDE
                        return false;
                    }
                    $file = $pattern[1];
                    $path = $app['lazy.thumbnail.web_root'] . $route . '/' . $file;
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    $allowedExt = !isset($parameters['allowed_ext']) ? $parameters['allowed_ext'] : 'jpeg,jpg,gif,png';
                    $allowedExt = array_map('trim', explode(',', strtolower($allowedExt)));
                    if (!file_exists($path) ||
                        !in_array($ext, $allowedExt)
                    ) {
                        $logger(Logger::ERROR, "The $path is invalid or its not allowed image.");
                        $app->abort(404);
                    }

                    if (isset($parameters['max_size']) &&
                        preg_match('/^([0-9]*)\.([0-9]*)$/', $parameters['max_size'], $maxSize)
                    ) {
                        if ($maxSize[2] < $matches[2] || $maxSize[1] < $matches[1]) {
                            $logger(Logger::ERROR, "The maximum size is reached.");
                            $app->abort(404);
                        }
                    }
                    $size = $parameters['allowed_size'];
                    foreach ($size as $wDotH) {
                        $wDotH = explode('.', $wDotH);
                        if (
                            count($wDotH) == 2 &&
                            ($wDotH[0] == '*' || $wDotH[0] == $matches[1]) &&
                            ($wDotH[1] == '*' || $wDotH[1] == $matches[2])
                        ) {
                            return array(
                                'file' => $path,
                                'width' => $matches[1],
                                'height' => $matches[2],
                                'on_the_fly' => isset($parameters['on_the_fly']) ? $parameters['on_the_fly'] : true,
                                'web_root' => $app['lazy.thumbnail.web_root'],
                                'mount' => $route,
                                'logger' => $logger
                            );
                        }
                    }

                    $logger(Logger::ERROR, "Not allowed size.");
                    $app->abort(404);

                    //Stupid IDE :)
                    return false;
                }
            );

        if (isset($parameters['route_name'])) {
            $controller->bind($parameters['route_name']);
        }

        return $controllers;
    }
}
