<?php
/**
 * KYROS RENT CAR — front controller.
 * All web requests are routed here via public/.htaccess.
 */
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Router;
use App\Core\Request;
use App\Core\Csrf;

$router  = new Router();
$request = new Request();

// CSRF protection for all state-changing requests.
Csrf::check();

// Load route definitions.
require dirname(__DIR__) . '/app/routes.php';

$router->dispatch($request);
