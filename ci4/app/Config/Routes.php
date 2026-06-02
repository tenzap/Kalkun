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
$routes->post('kalkun/rename_folder', 'Kalkun::rename_folder');
$routes->post('kalkun/add_folder', 'Kalkun::add_folder');
$routes->get('kalkun/delete_folder', 'Kalkun::delete_folder');
$routes->get('kalkun/delete_filter', 'Kalkun::delete_filter');
$routes->get('kalkun/delete_folder/(:any)', 'Kalkun::delete_folder/$1');
$routes->get('kalkun/delete_filter/(:any)', 'Kalkun::delete_filter/$1');
$routes->get('kalkun/get_csrf_hash', 'Kalkun::get_csrf_hash');
$routes->get('kalkun/notification', 'Kalkun::notification');
$routes->get('settings', 'Kalkun::settings');

$routes->get('kalkun/unread_count', 'Kalkun::unread_count');
$routes->match(['GET', 'POST'], 'kalkun/phone_number_validation', 'Kalkun::phone_number_validation');
$routes->match(['GET', 'POST'], 'kalkun/phone_number_validation_multiple', 'Kalkun::phone_number_validation_multiple');
$routes->match(['GET', 'POST'], 'settings/(:any)', 'Kalkun::settings/$1');
$routes->get('kalkun/get_statistic/(:any)', 'Kalkun::get_statistic/$1');
$routes->get('kalkun/get_statistic', 'Kalkun::get_statistic');

#$route['plugin/(.+)'] = '$1'; // CI4-TODO
