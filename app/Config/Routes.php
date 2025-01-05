<?php

use App\Controllers\DashboardController;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('register', 'RegisterController::index');
$routes->post('register/create', 'RegisterController::create');
$routes->get('login', 'LoginController::index');

$routes->post('login', 'LoginController::login');

$routes->get('dashboard', 'DashboardController::index');
//Employee
$routes->post('/search', 'DashboardController::searchDealers');
$routes->post('edit-dealer', 'DashboardController::editDealer');  // Update dealer details (POST)
$routes->get('/get-dealer/(:num)', 'DashboardController::getDealer/$1');

$routes->post('/delete-dealer', 'DashboardController::deleteDealer');


//Dealer

$routes->post('/update-dealer-profile', 'DashboardController::updateDealerProfile');




$routes->get('logout', 'LoginController::logout');
