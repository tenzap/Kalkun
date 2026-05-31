<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
#$routes->get('/', 'Home::index');

$routes->get('/', 'Kalkun::getIndex');
$routes->get('logout', 'Login::logout');
$routes->match(['GET', 'POST'], 'login', 'Login::index');
$routes->match(['GET', 'POST'], 'login/forgot_password', 'Login::forgot_password');
$routes->match(['GET', 'POST'], 'login/password_reset', 'Login::password_reset');
$routes->match(['GET', 'POST'], 'kalkun', 'Kalkun::getIndex');
$routes->match(['GET', 'POST'], 'settings/(:any)', 'Kalkun::settings/$1');

#$route['plugin/(.+)'] = '$1'; // CI4-TODO
